<?php
declare(strict_types=1);

/**
 * CLI tool for scheduler management
 * 
 * Usage:
 *   php scheduler.php status        - Show task status
 *   php scheduler.php run           - Run due tasks manually
 *   php scheduler.php test [task]   - Test a specific task or all tasks
 */

use Domain\Scheduler\SchedulerHelper;

require_once __DIR__ . '/vendor/autoload.php';

/**
 * @return void
 */
function showHelp(): void
{
    echo "Scheduler CLI Tool\n";
    echo "Usage:\n";
    echo "  php scheduler.php status        - Show task status\n";
    echo "  php scheduler.php run           - Run due tasks manually\n";
    echo "  php scheduler.php test [task]   - Test a specific task or all tasks\n";
    echo "  php scheduler.php list          - List all registered tasks\n";
    echo "  php scheduler.php help          - Show this help\n";
    echo "\n";
}

/**
 * @return void
 * @throws Exception
 */
function handleStatus(): void
{
    $scheduler = SchedulerHelper::bootstrap();
    SchedulerHelper::printTaskReport($scheduler);
}

/**
 * @return void
 * @throws Exception
 */
function handleRun(): void
{
    $scheduler = SchedulerHelper::bootstrap();
    
    echo "Running due tasks...\n";
    $executedTasks = $scheduler->runDueTasks();
    
    if (!empty($executedTasks)) {
        echo "Executed tasks: " . implode(', ', $executedTasks) . "\n";
    } else {
        echo "No tasks were due for execution.\n";
    }
}

/**
 * @param string|null $taskName
 * @return void
 * @throws Exception
 */
function handleTest(?string $taskName = null): void
{
    $scheduler = SchedulerHelper::bootstrap();
    
    if ($taskName) {
        // Test specific task
        try {
            $task = $scheduler->getTask($taskName);
            echo "Testing task: {$taskName}\n";
            $task->execute();
            echo "Task '{$taskName}' completed successfully.\n";
        } catch (Exception $e) {
            echo "Error testing task '{$taskName}': " . $e->getMessage() . "\n";
        }
    } else {
        // Test all tasks
        echo "Testing all tasks...\n";
        foreach ($scheduler->getTasks() as $task) {
            if (!$task->isEnabled()) {
                echo "Skipping disabled task: {$task->getName()}\n";
                continue;
            }
            
            try {
                echo "Testing task: {$task->getName()}\n";
                $task->execute();
                echo "Task '{$task->getName()}' completed successfully.\n";
            } catch (Exception $e) {
                echo "Error testing task '{$task->getName()}': " . $e->getMessage() . "\n";
            }
            echo "\n";
        }
    }
}

/**
 * @return void
 * @throws Exception
 */
function handleList(): void
{
    $scheduler = SchedulerHelper::bootstrap();
    
    echo "Registered Tasks:\n";
    echo "================\n";
    
    foreach ($scheduler->getTasks() as $task) {
        $status = $task->isEnabled() ? 'Enabled' : 'Disabled';
        $description = SchedulerHelper::describeCronExpression($task->getCronExpression());
        
        echo "• {$task->getName()}\n";
        echo "  Cron: {$task->getCronExpression()}\n";
        echo "  Description: {$description}\n";
        echo "  Status: {$status}\n";
        echo "  Priority: {$task->getPriority()}\n";
        echo "\n";
    }
}

// Main execution
try {
    $command = $argv[1] ?? 'help';
    
    switch ($command) {
        case 'status':
            handleStatus();
            break;
            
        case 'run':
            handleRun();
            break;
            
        case 'test':
            $taskName = $argv[2] ?? null;
            handleTest($taskName);
            break;
            
        case 'list':
            handleList();
            break;
            
        case 'help':
        default:
            showHelp();
            break;
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
