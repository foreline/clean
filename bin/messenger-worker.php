<?php
declare(strict_types=1);

/**
 * Async Event Messenger Worker
 *
 * Long-running CLI process that consumes async domain events from the configured
 * transport and dispatches them to their target subscribers.
 *
 * This script is framework-agnostic and module-agnostic. It delegates application
 * bootstrapping to a user-provided bootstrap file via the --bootstrap argument.
 *
 * ─────────────────────────────────────────────────────────────────────────────────
 * USAGE
 * ─────────────────────────────────────────────────────────────────────────────────
 *
 *   php vendor/bin/messenger-worker --bootstrap=path/to/bootstrap.php
 *
 * The bootstrap file MUST:
 *   1. Initialize the application (framework bootstrap, autoloading, etc.)
 *   2. Ensure Domain\Event\Publisher subscribers are registered
 *   3. Return an array with 'transport' and 'handler' keys:
 *      - 'transport' => TransportInterface (the message queue)
 *      - 'handler'   => callable (the message handler)
 *
 * Example bootstrap file for Bitrix:
 *
 *   <?php
 *   $_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../../../');
 *   require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
 *   \Bitrix\Main\Loader::includeModule('your_module');
 *   return [
 *       'transport' => new \Infrastructure\Event\Transport\DatabaseTransport(),
 *       'handler'   => new \Infrastructure\Event\Handler\AsyncEventMessageHandler(),
 *   ];
 *
 * ─────────────────────────────────────────────────────────────────────────────────
 * ENVIRONMENT VARIABLES (.env)
 * ─────────────────────────────────────────────────────────────────────────────────
 *
 * Environment variables are consumed by the bootstrap file, not by this worker.
 * Refer to your bootstrap file for required environment configuration.
 *
 * Common variables:
 *   ASYNC_EVENTS_ENABLED=Y       — Enable async event dispatch
 *   MESSENGER_TRANSPORT_DSN=...  — Transport DSN (consumed by bootstrap)
 *
 * ─────────────────────────────────────────────────────────────────────────────────
 * PROCESS SUPERVISION
 * ─────────────────────────────────────────────────────────────────────────────────
 *
 * The worker should be managed by a process supervisor to ensure it
 * restarts on failure and starts on system boot.
 *
 * --- systemd (Linux) ---
 *
 *   [Unit]
 *   Description=Async Event Messenger Worker
 *   After=network.target mysql.service
 *
 *   [Service]
 *   Type=simple
 *   User=www-data
 *   WorkingDirectory=/var/www/project
 *   ExecStart=/usr/bin/php vendor/bin/messenger-worker --bootstrap=bootstrap.php
 *   Restart=always
 *   RestartSec=5
 *   StandardOutput=append:/var/log/messenger-worker.log
 *   StandardError=append:/var/log/messenger-worker-error.log
 *
 *   [Install]
 *   WantedBy=multi-user.target
 *
 * --- Supervisor (Linux) ---
 *
 *   [program:messenger-worker]
 *   command=php vendor/bin/messenger-worker --bootstrap=bootstrap.php
 *   directory=/var/www/project
 *   user=www-data
 *   autostart=true
 *   autorestart=true
 *   startsecs=0
 *   startretries=10
 *   stopwaitsecs=30
 *   stdout_logfile=/var/log/messenger-worker.log
 *   stderr_logfile=/var/log/messenger-worker-error.log
 *
 * --- NSSM (Windows Service) ---
 *
 *   nssm install MessengerWorker "C:\php\php.exe"
 *   nssm set MessengerWorker AppParameters "vendor\bin\messenger-worker --bootstrap=bootstrap.php"
 *   nssm set MessengerWorker AppDirectory "C:\www\project"
 *   nssm set MessengerWorker AppStdout "C:\logs\messenger-worker.log"
 *   nssm set MessengerWorker AppStderr "C:\logs\messenger-worker-error.log"
 *   nssm start MessengerWorker
 *
 * ─────────────────────────────────────────────────────────────────────────────────
 * GRACEFUL SHUTDOWN
 * ─────────────────────────────────────────────────────────────────────────────────
 *
 * On Linux (with ext-pcntl), the worker handles SIGTERM and SIGINT signals
 * for graceful shutdown — it finishes the current message before stopping.
 *
 * On Windows (no pcntl), the worker stops immediately when the process
 * is terminated. Messages in 'processing' state may need manual recovery.
 */

use Infrastructure\Event\Config\MessengerFactory;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Component\Messenger\Worker;

// ─── Parse CLI arguments ─────────────────────────────────────────────────────

$bootstrapFile = null;

foreach ( $argv as $i => $arg ) {
    if ( str_starts_with($arg, '--bootstrap=') ) {
        $bootstrapFile = substr($arg, strlen('--bootstrap='));
        break;
    }
    if ( '--bootstrap' === $arg && isset($argv[$i + 1]) ) {
        $bootstrapFile = $argv[$i + 1];
        break;
    }
}

if ( null === $bootstrapFile || '' === $bootstrapFile ) {
    fwrite(STDERR, "ERROR: --bootstrap argument is required.\n");
    fwrite(STDERR, "Usage: php messenger-worker --bootstrap=path/to/bootstrap.php\n");
    exit(1);
}

if ( !file_exists($bootstrapFile) ) {
    fwrite(STDERR, "ERROR: Bootstrap file not found: {$bootstrapFile}\n");
    exit(1);
}

// ─── Bootstrap application ───────────────────────────────────────────────────

/** @var array{transport: TransportInterface, handler: callable} $config */
$config = require $bootstrapFile;

if ( !is_array($config) || !isset($config['transport']) || !isset($config['handler']) ) {
    fwrite(STDERR, "ERROR: Bootstrap file must return ['transport' => TransportInterface, 'handler' => callable]\n");
    exit(1);
}

$transport = $config['transport'];
$handler = $config['handler'];

// ─── Create worker bus ───────────────────────────────────────────────────────

echo "[" . date('Y-m-d H:i:s') . "] Messenger Worker starting...\n";

$workerBus = MessengerFactory::createWorkerBus($handler);

// ─── Signal handling (graceful shutdown) ─────────────────────────────────────

if ( function_exists('pcntl_signal') ) {
    $worker = null;

    // Deliver signals asynchronously: without this, the handlers below are only
    // invoked on pcntl_signal_dispatch(), which nothing calls — SIGTERM from
    // systemd would be queued forever and the process killed after TimeoutStopSec.
    pcntl_async_signals(true);

    pcntl_signal(SIGTERM, static function () use (&$worker) {
        echo "\n[" . date('Y-m-d H:i:s') . "] Received SIGTERM, stopping gracefully...\n";
        $worker?->stop();
    });
    
    pcntl_signal(SIGINT, static function () use (&$worker) {
        echo "\n[" . date('Y-m-d H:i:s') . "] Received SIGINT, stopping gracefully...\n";
        $worker?->stop();
    });
}

// ─── Run worker ──────────────────────────────────────────────────────────────

$worker = new Worker(
    ['async' => $transport],
    $workerBus,
);

echo "[" . date('Y-m-d H:i:s') . "] Worker ready. Waiting for messages...\n";

$worker->run();

echo "[" . date('Y-m-d H:i:s') . "] Worker stopped.\n";
