<?php
declare(strict_types=1);

namespace Domain\Scheduler;

use DateTimeImmutable;
use Domain\Scheduler\Tasks\CleanupTask;
use Domain\Scheduler\Tasks\NotificationTask;
use Domain\Scheduler\Tasks\BackupTask;

/**
 * Helper class for scheduler management and utilities
 */
class SchedulerHelper
{
    /**
     * Bootstrap the scheduler with default tasks
     */
    public static function bootstrap(): TaskScheduler
    {
        $scheduler = TaskScheduler::getInstance();
        
        // Register default tasks
        $scheduler->addTask(new CleanupTask());
        $scheduler->addTask(new NotificationTask());
        $scheduler->addTask(new BackupTask());
        
        return $scheduler;
    }
    
    /**
     * Get a human-readable description of a cron expression
     */
    public static function describeCronExpression(string $cronExpression): string
    {
        // This is a simple implementation - you could use a library like
        // lorisleiva/cron-translator for more sophisticated descriptions
        
        $parts = explode(' ', $cronExpression);
        if (count($parts) !== 5) {
            return "Invalid cron expression";
        }
        
        [$minute, $hour, $day, $month, $dayOfWeek] = $parts;
        
        // Handle some common patterns
        if ($cronExpression === '0 0 * * *') {
            return "Daily at midnight";
        }
        
        if ($cronExpression === '0 2 * * *') {
            return "Daily at 2 AM";
        }
        
        if ($cronExpression === '0 1 * * 0') {
            return "Weekly on Sunday at 1 AM";
        }
        
        if (preg_match('/^\*\/(\d+) \* \* \* \*$/', $cronExpression, $matches)) {
            return "Every {$matches[1]} minutes";
        }
        
        if (preg_match('/^0 \*\/(\d+) \* \* \*$/', $cronExpression, $matches)) {
            return "Every {$matches[1]} hours";
        }
        
        return "At {$minute} minutes past {$hour} hours on day {$day} of month {$month} and day {$dayOfWeek} of week";
    }
    
    /**
     * Validate a cron expression
     */
    public static function isValidCronExpression(string $cronExpression): bool
    {
        return \Cron\CronExpression::isValidExpression($cronExpression);
    }
    
    /**
     * Get the next N run times for a cron expression
     * 
     * @param string $cronExpression
     * @param int $count
     * @param DateTimeImmutable|null $startTime
     * @return DateTimeImmutable[]
     */
    public static function getNextRunTimes(string $cronExpression, int $count = 5, ?DateTimeImmutable $startTime = null): array
    {
        if (!self::isValidCronExpression($cronExpression)) {
            return [];
        }
        
        $cron = new \Cron\CronExpression($cronExpression);
        $startTime = $startTime ?? new DateTimeImmutable();
        
        $times = [];
        $currentTime = $startTime;
        
        for ($i = 0; $i < $count; $i++) {
            $nextTime = $cron->getNextRunDate($currentTime);
            $times[] = DateTimeImmutable::createFromInterface($nextTime);
            $currentTime = $nextTime;
        }
        
        return $times;
    }
    
    /**
     * Create a task summary report
     */
    public static function createTaskReport(TaskScheduler $scheduler): array
    {
        $report = [
            'total_tasks' => count($scheduler->getTasks()),
            'enabled_tasks' => count($scheduler->getTaskRegistry()->getEnabledTasks()),
            'tasks' => []
        ];
        
        foreach ($scheduler->getTasks() as $task) {
            $taskInfo = [
                'name' => $task->getName(),
                'cron' => $task->getCronExpression(),
                'description' => self::describeCronExpression($task->getCronExpression()),
                'enabled' => $task->isEnabled(),
                'priority' => $task->getPriority(),
                'last_executed' => $task->getLastExecutedAt()?->format('Y-m-d H:i:s'),
                'is_due' => $scheduler->isTaskDue($task),
                'next_runs' => array_map(
                    fn(DateTimeImmutable $time) => $time->format('Y-m-d H:i:s'),
                    self::getNextRunTimes($task->getCronExpression(), 3)
                )
            ];
            
            $report['tasks'][] = $taskInfo;
        }
        
        return $report;
    }
    
    /**
     * Print a formatted task report
     */
    public static function printTaskReport(TaskScheduler $scheduler): void
    {
        $report = self::createTaskReport($scheduler);
        
        echo "=== Scheduler Task Report ===\n";
        echo "Total tasks: {$report['total_tasks']}\n";
        echo "Enabled tasks: {$report['enabled_tasks']}\n";
        echo "\n";
        
        foreach ($report['tasks'] as $task) {
            echo "Task: {$task['name']}\n";
            echo "  Cron: {$task['cron']}\n";
            echo "  Description: {$task['description']}\n";
            echo "  Status: " . ($task['enabled'] ? 'Enabled' : 'Disabled') . "\n";
            echo "  Priority: {$task['priority']}\n";
            echo "  Last executed: " . ($task['last_executed'] ?? 'Never') . "\n";
            echo "  Is due: " . ($task['is_due'] ? 'Yes' : 'No') . "\n";
            echo "  Next runs: " . implode(', ', $task['next_runs']) . "\n";
            echo "\n";
        }
    }
}
