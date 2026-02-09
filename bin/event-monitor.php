#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Async Publisher Event Monitor
 *
 * CLI tool for monitoring and managing the Event Store.
 * Provides status overview, health checks, failed event inspection,
 * throughput statistics, and operational actions (purge, retry).
 *
 * This script is framework-agnostic and delegates bootstrapping
 * to a user-provided file via the --bootstrap argument.
 *
 * ─────────────────────────────────────────────────────────────────────────────────
 * USAGE
 * ─────────────────────────────────────────────────────────────────────────────────
 *
 *   php event-monitor.php --bootstrap=path/to/bootstrap.php [COMMAND] [OPTIONS]
 *
 * COMMANDS:
 *   dashboard    Full dashboard with all sections (default)
 *   status       Status summary (event counts by status)
 *   health       Health check with assessment
 *   failed       List failed events
 *   stuck        List events stuck in processing
 *   recent       List recent events
 *   purge        Delete completed events
 *   retry        Retry failed events
 *   watch        Live auto-refreshing dashboard
 *
 * OPTIONS:
 *   --bootstrap=PATH     Path to bootstrap file (required)
 *   --limit=N            Limit results (default: 20)
 *   --before=DATE        For purge: delete completed before this date (Y-m-d)
 *   --id=N               For retry: retry a specific event ID
 *   --interval=N         For watch: refresh interval in seconds (default: 5)
 *   --stuck-threshold=N  Seconds before processing event is considered stuck (default: 300)
 *
 * BOOTSTRAP FILE:
 *   Must return an array with 'monitor' key:
 *     return ['monitor' => new BitrixEventStoreMonitor()];
 *
 * EXAMPLES:
 *   php event-monitor.php --bootstrap=bin/monitor-bootstrap.php
 *   php event-monitor.php --bootstrap=bin/monitor-bootstrap.php failed --limit=10
 *   php event-monitor.php --bootstrap=bin/monitor-bootstrap.php retry --id=15
 *   php event-monitor.php --bootstrap=bin/monitor-bootstrap.php purge --before=2026-01-01
 *   php event-monitor.php --bootstrap=bin/monitor-bootstrap.php watch --interval=3
 */

use Domain\Event\Monitoring\EventStoreEntryCollection;
use Domain\Event\Monitoring\EventStoreHealth;
use Domain\Event\Monitoring\EventStoreMonitorInterface;
use Domain\Event\Monitoring\EventStoreStatusSummary;
use Domain\Event\Monitoring\EventStoreThroughput;
use Domain\Event\Monitoring\HealthStatus;

// ─── Main entry point ────────────────────────────────────────────────────────

exit(main($argv));

/**
 * @param string[] $argv
 */
function main(array $argv): int
{
    $args = parseArgs($argv);
    
    // ─── Help ────────────────────────────────────────────────────────────
    if ( isset($args['help']) ) {
        printUsage();
        return 0;
    }
    
    // ─── Bootstrap ───────────────────────────────────────────────────────
    $bootstrapFile = $args['bootstrap'] ?? null;
    
    if ( null === $bootstrapFile || '' === $bootstrapFile ) {
        err("ERROR: --bootstrap argument is required.");
        err("Usage: php event-monitor.php --bootstrap=path/to/bootstrap.php [COMMAND]");
        return 1;
    }
    
    if ( !file_exists($bootstrapFile) ) {
        err("ERROR: Bootstrap file not found: {$bootstrapFile}");
        return 1;
    }
    
    $config = require $bootstrapFile;
    
    if ( !is_array($config) || !isset($config['monitor']) ) {
        err("ERROR: Bootstrap file must return ['monitor' => EventStoreMonitorInterface]");
        return 1;
    }
    
    $monitor = $config['monitor'];
    
    if ( !$monitor instanceof EventStoreMonitorInterface ) {
        err("ERROR: 'monitor' must implement EventStoreMonitorInterface");
        return 1;
    }
    
    // ─── Route command ───────────────────────────────────────────────────
    $command        = $args['command'] ?? 'dashboard';
    $limit          = (int)($args['limit'] ?? 20);
    $stuckThreshold = (int)($args['stuck-threshold'] ?? 300);
    
    try {
        return match ($command) {
            'dashboard' => commandDashboard($monitor, $limit, $stuckThreshold),
            'status'    => commandStatus($monitor),
            'health'    => commandHealth($monitor, $stuckThreshold),
            'failed'    => commandFailed($monitor, $limit),
            'stuck'     => commandStuck($monitor, $stuckThreshold),
            'recent'    => commandRecent($monitor, $limit),
            'purge'     => commandPurge($monitor, $args['before'] ?? null),
            'retry'     => commandRetry($monitor, isset($args['id']) ? (int)$args['id'] : null),
            'watch'     => commandWatch($monitor, (int)($args['interval'] ?? 5), $limit, $stuckThreshold),
            default     => commandUnknown($command),
        };
    } catch (Throwable $e ) {
        err("ERROR: " . $e->getMessage());
        return 1;
    }
}

// ─── Commands ────────────────────────────────────────────────────────────────

function commandDashboard(EventStoreMonitorInterface $monitor, int $limit, int $stuckThreshold): int
{
    renderHeader();
    renderStatusSummary($monitor->getStatusSummary());
    renderThroughput($monitor->getThroughput());
    renderHealthCheck($monitor->getHealth($stuckThreshold));
    
    $failed = $monitor->getFailedEvents($limit);
    if ( !$failed->isEmpty() ) {
        renderEntryList('FAILED EVENTS', $failed);
    }
    
    $stuck = $monitor->getStuckEvents($stuckThreshold);
    if ( !$stuck->isEmpty() ) {
        renderEntryList('STUCK EVENTS (processing > ' . formatDuration($stuckThreshold) . ')', $stuck);
    }
    
    return 0;
}

function commandStatus(EventStoreMonitorInterface $monitor): int
{
    renderHeader();
    renderStatusSummary($monitor->getStatusSummary());
    return 0;
}

function commandHealth(EventStoreMonitorInterface $monitor, int $stuckThreshold): int
{
    renderHeader();
    renderHealthCheck($monitor->getHealth($stuckThreshold));
    return $monitor->getHealth($stuckThreshold)->isHealthy() ? 0 : 1;
}

function commandFailed(EventStoreMonitorInterface $monitor, int $limit): int
{
    renderHeader();
    $failed = $monitor->getFailedEvents($limit);
    
    if ( $failed->isEmpty() ) {
        out("  No failed events.");
        out();
        return 0;
    }
    
    renderEntryList('FAILED EVENTS', $failed);
    return 0;
}

function commandStuck(EventStoreMonitorInterface $monitor, int $stuckThreshold): int
{
    renderHeader();
    $stuck = $monitor->getStuckEvents($stuckThreshold);
    
    if ( $stuck->isEmpty() ) {
        out("  No stuck events (threshold: " . formatDuration($stuckThreshold) . ").");
        out();
        return 0;
    }
    
    renderEntryList('STUCK EVENTS', $stuck);
    return 0;
}

function commandRecent(EventStoreMonitorInterface $monitor, int $limit): int
{
    renderHeader();
    $recent = $monitor->getRecentEvents($limit);
    
    if ( $recent->isEmpty() ) {
        out("  No events found.");
        out();
        return 0;
    }
    
    renderEntryList('RECENT EVENTS (last ' . $limit . ')', $recent);
    return 0;
}

function commandPurge(EventStoreMonitorInterface $monitor, ?string $beforeDate): int
{
    renderHeader();
    
    $before = null;
    if ( null !== $beforeDate && '' !== $beforeDate ) {
        try {
            $before = new DateTime($beforeDate);
        } catch (Exception) {
            err("ERROR: Invalid date format: {$beforeDate}. Use Y-m-d (e.g., 2026-01-01).");
            return 1;
        }
        out("  Purging completed events before {$before->format('Y-m-d H:i:s')}...");
    } else {
        out("  Purging ALL completed events...");
    }
    
    $count = $monitor->purgeCompleted($before);
    out("  Deleted: {$count} event(s).");
    out();
    return 0;
}

function commandRetry(EventStoreMonitorInterface $monitor, ?int $eventId): int
{
    renderHeader();
    
    if ( null !== $eventId ) {
        out("  Retrying event #{$eventId}...");
    } else {
        out("  Retrying ALL failed events...");
    }
    
    $count = $monitor->retryFailed($eventId);
    out("  Reset to pending: {$count} event(s).");
    out();
    return 0;
}

function commandWatch(EventStoreMonitorInterface $monitor, int $interval, int $limit, int $stuckThreshold): int
{
    if ( 1 > $interval ) {
        $interval = 1;
    }
    
    while ( true ) {
        // Clear screen (ANSI escape — works on modern terminals including Windows 10+)
        echo "\033[2J\033[H";
        
        commandDashboard($monitor, $limit, $stuckThreshold);
        out("  Auto-refreshing every {$interval}s. Press Ctrl+C to stop.");
        out();
        
        sleep($interval);
    }
    
    /** @phpstan-ignore-next-line deadCode.unreachable */
    return 0;
}

function commandUnknown(string $command): int
{
    err("ERROR: Unknown command: {$command}");
    err("Run with --help to see available commands.");
    return 1;
}

// ─── Rendering ───────────────────────────────────────────────────────────────

function renderHeader(): void
{
    out();
    out('  ========================================================');
    out('    Async Publisher Event Monitor');
    out('    ' . date('Y-m-d H:i:s'));
    out('  ========================================================');
    out();
}

function renderStatusSummary(EventStoreStatusSummary $summary): void
{
    out('  STATUS SUMMARY');
    out('  ' . str_repeat('-', 40));
    out('    Pending:    ' . padLeft((string)$summary->getPending(), 8));
    out('    Processing: ' . padLeft((string)$summary->getProcessing(), 8));
    out('    Completed:  ' . padLeft((string)$summary->getCompleted(), 8));
    out('    Failed:     ' . padLeft((string)$summary->getFailed(), 8));
    out('    ' . str_repeat('-', 17));
    out('    Total:      ' . padLeft((string)$summary->getTotal(), 8));
    out();
}

function renderThroughput(EventStoreThroughput $throughput): void
{
    $execTime   = $throughput->getAvgExecutionTimeSeconds();
    $waitTime   = $throughput->getAvgQueueWaitSeconds();
    $e2eTime    = $throughput->getAvgEndToEndSeconds();
    $execStr    = null !== $execTime ? formatSeconds($execTime) : 'n/a';
    $waitStr    = null !== $waitTime ? formatSeconds($waitTime) : 'n/a';
    $e2eStr     = null !== $e2eTime  ? formatSeconds($e2eTime)  : 'n/a';
    
    out('  THROUGHPUT');
    out('  ' . str_repeat('-', 40));
    out('    Last minute:  ' . padLeft((string)$throughput->getCompletedLastMinute(), 6) . ' completed');
    out('    Last hour:    ' . padLeft((string)$throughput->getCompletedLastHour(), 6) . ' completed');
    out('    Last 24h:     ' . padLeft((string)$throughput->getCompletedLast24Hours(), 6) . ' completed');
    out('    Failed/hour:  ' . padLeft((string)$throughput->getFailedLastHour(), 6));
    out('  ' . str_repeat('-', 40));
    out('    Avg exec:     ' . padLeft($execStr, 10) . '  (subscriber work)');
    out('    Avg wait:     ' . padLeft($waitStr, 10) . '  (queue wait)');
    out('    Avg e2e:      ' . padLeft($e2eStr, 10) . '  (total latency)');
    out();
}

function renderHealthCheck(EventStoreHealth $health): void
{
    $statusLabel = match ($health->getStatus()) {
        HealthStatus::HEALTHY  => '[OK]      HEALTHY',
        HealthStatus::DEGRADED => '[WARNING] DEGRADED',
        HealthStatus::CRITICAL => '[ALERT]   CRITICAL',
    };
    
    out('  HEALTH: ' . $statusLabel);
    out('  ' . str_repeat('-', 40));
    
    $summary = $health->getSummary();
    
    if ( $health->isHealthy() ) {
        out('    All systems operational.');
    }
    
    if ( 0 < $health->getStuckCount() ) {
        out('    ! ' . $health->getStuckCount() . ' event(s) stuck in processing');
    }
    
    if ( 0 < $summary->getFailed() ) {
        out('    ! ' . $summary->getFailed() . ' failed event(s) need attention');
    }
    
    if ( 50 < $summary->getPending() ) {
        out('    ! Queue depth: ' . $summary->getPending() . ' pending (growing)');
    }
    
    if ( 0 < $health->getThroughput()->getFailedLastHour() ) {
        out('    ! ' . $health->getThroughput()->getFailedLastHour() . ' failure(s) in the last hour');
    }
    
    out();
}

function renderEntryList(string $title, EventStoreEntryCollection $entries): void
{
    out('  ' . $title . ' (' . count($entries) . ')');
    out('  ' . str_repeat('-', 40));
    
    foreach ( $entries as $entry ) {
        $statusBadge = match ($entry->getStatus()->value) {
            'pending'    => '[PEND]',
            'processing' => '[PROC]',
            'completed'  => '[ OK ]',
            'failed'     => '[FAIL]',
            default      => '[????]',
        };
        
        $execTime = $entry->getExecutionTimeSeconds();
        $waitTime = $entry->getQueueWaitSeconds();
        
        $timingParts = [];
        if ( null !== $execTime ) {
            $timingParts[] = 'exec:' . formatSeconds($execTime);
        }
        if ( null !== $waitTime ) {
            $timingParts[] = 'wait:' . formatSeconds($waitTime);
        }
        $timingStr = !empty($timingParts) ? ' (' . implode(', ', $timingParts) . ')' : '';
        
        out('    #' . padLeft((string)$entry->getId(), 6) . ' ' . $statusBadge . ' ' . $entry->getShortEventClass() . ' -> ' . $entry->getShortSubscriberClass() . $timingStr);
        
        if ( null !== $entry->getError() && '' !== $entry->getError() ) {
            $errorPreview = mb_substr($entry->getError(), 0, 120);
            if ( 120 < mb_strlen($entry->getError()) ) {
                $errorPreview .= '...';
            }
            out('           Error: ' . $errorPreview);
        }
        
        $meta = 'Attempts: ' . $entry->getAttempts();
        
        if ( null !== $entry->getProcessedAt() ) {
            $meta .= ' | Processed: ' . $entry->getProcessedAt()->format('Y-m-d H:i:s');
        } elseif ( null !== $entry->getDateCreated() ) {
            $meta .= ' | Created: ' . $entry->getDateCreated()->format('Y-m-d H:i:s');
        }
        
        out('           ' . $meta);
        out();
    }
}

// ─── Argument parsing ────────────────────────────────────────────────────────

/**
 * Parse CLI arguments into an associative array.
 *
 * @param string[] $argv
 * @return array<string, string|true>
 */
function parseArgs(array $argv): array
{
    $args = [];
    $commandFound = false;
    
    for ( $i = 1, $iMax = count($argv); $i < $iMax; $i++ ) {
        $arg = $argv[$i];
        
        // Named options: --key=value or --key value
        if ( str_starts_with($arg, '--') ) {
            $option = substr($arg, 2);
            
            if ( str_contains($option, '=') ) {
                // --key=value
                [$key, $value] = explode('=', $option, 2);
                $args[$key] = $value;
            } elseif ( isset($argv[$i + 1]) && !str_starts_with($argv[$i + 1], '--') ) {
                // --key value (next arg is the value, not another flag)
                $args[$option] = $argv[++$i];
            } else {
                // --flag (boolean flag)
                $args[$option] = true;
            }
            continue;
        }
        
        // First positional argument is the command
        if ( !$commandFound ) {
            $args['command'] = $arg;
            $commandFound = true;
        }
    }
    
    return $args;
}

// ─── Output helpers ──────────────────────────────────────────────────────────

function out(string $text = ''): void
{
    echo $text . PHP_EOL;
}

function err(string $text): void
{
    fwrite(STDERR, $text . PHP_EOL);
}

function padLeft(string $text, int $width): string
{
    return str_pad($text, $width, ' ', STR_PAD_LEFT);
}

function formatDuration(int $seconds): string
{
    if ( 60 > $seconds ) {
        return $seconds . 's';
    }
    
    $minutes = intdiv($seconds, 60);
    $remaining = $seconds % 60;
    
    if ( 0 === $remaining ) {
        return $minutes . 'min';
    }
    
    return $minutes . 'min ' . $remaining . 's';
}

/**
 * Format float seconds into human-readable string.
 * Examples: 0.05s, 3.20s, 1min 23s, 24min 15s
 */
function formatSeconds(float $seconds): string
{
    if ( 60.0 > $seconds ) {
        return number_format($seconds, 2) . 's';
    }
    
    $totalSeconds = (int)round($seconds);
    $minutes = intdiv($totalSeconds, 60);
    $remaining = $totalSeconds % 60;
    
    if ( 60 > $minutes ) {
        return $minutes . 'min ' . $remaining . 's';
    }
    
    $hours = intdiv($minutes, 60);
    $remainMinutes = $minutes % 60;
    
    return $hours . 'h ' . $remainMinutes . 'min';
}

function printUsage(): void
{
    out('Async Publisher Event Monitor');
    out();
    out('USAGE:');
    out('  php event-monitor.php --bootstrap=PATH [COMMAND] [OPTIONS]');
    out();
    out('COMMANDS:');
    out('  dashboard    Full dashboard with all sections (default)');
    out('  status       Status summary (event counts by status)');
    out('  health       Health check with assessment');
    out('  failed       List failed events');
    out('  stuck        List events stuck in processing');
    out('  recent       List recent events');
    out('  purge        Delete completed events');
    out('  retry        Retry failed events');
    out('  watch        Live auto-refreshing dashboard');
    out();
    out('OPTIONS:');
    out('  --bootstrap=PATH       Path to bootstrap file (required)');
    out('  --limit=N              Limit results (default: 20)');
    out('  --before=DATE          For purge: delete before this date (Y-m-d)');
    out('  --id=N                 For retry: retry specific event ID');
    out('  --interval=N           For watch: refresh interval in seconds (default: 5)');
    out('  --stuck-threshold=N    Seconds before processing is stuck (default: 300)');
    out('  --help                 Show this help message');
    out();
    out('EXAMPLES:');
    out('  php event-monitor.php --bootstrap=bin/monitor-bootstrap.php');
    out('  php event-monitor.php --bootstrap=bin/monitor-bootstrap.php failed --limit=10');
    out('  php event-monitor.php --bootstrap=bin/monitor-bootstrap.php retry --id=15');
    out('  php event-monitor.php --bootstrap=bin/monitor-bootstrap.php purge --before=2026-01-01');
    out('  php event-monitor.php --bootstrap=bin/monitor-bootstrap.php watch --interval=3');
}
