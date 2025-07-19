<?php
declare(strict_types=1);

/**
 * Scheduler entry point for cron execution
 * 
 * This file should be called from cron to run scheduled tasks.
 * Example crontab entry:
 * * * * * * php /path/to/your/project/tasks.php
 */

use Domain\Scheduler\TaskScheduler;
use Domain\Scheduler\Tasks\CleanupTask;
use Domain\Scheduler\Tasks\NotificationTask;
use Domain\Scheduler\Tasks\BackupTask;

// Include Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

try {
    // Get the scheduler instance
    $scheduler = TaskScheduler::getInstance();
    
    // Register tasks
    $scheduler->addTask(new CleanupTask());
    $scheduler->addTask(new NotificationTask());
    $scheduler->addTask(new BackupTask());
    
    // You can also add custom tasks on the fly:
    // $customTask = new class extends \Domain\Scheduler\AbstractTask {
    //     public function __construct() {
    //         parent::__construct('custom', '*/5 * * * *'); // Every 5 minutes
    //     }
    //     
    //     public function execute(): void {
    //         echo "Custom task executed!\n";
    //     }
    // };
    // $scheduler->addTask($customTask);
    
    // Run due tasks manually (useful for cron-based execution)
    $executedTasks = $scheduler->runDueTasks();
    
    if (!empty($executedTasks)) {
        echo "Executed tasks: " . implode(', ', $executedTasks) . "\n";
    } else {
        echo "No tasks were due for execution.\n";
    }
    
    // For debugging, you can uncomment the following to see schedule info:
    // echo "Schedule information:\n";
    // print_r($scheduler->getScheduleInfo());
    
} catch (Exception $e) {
    // Log error or handle it appropriately
    error_log("Scheduler error: " . $e->getMessage());
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
