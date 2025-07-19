# Scheduler System

A cron task scheduling system implementing the Symfony Scheduler component.

## Features

- **Singleton pattern** for adding tasks
- **Single file execution** from cron (`tasks.php`)
- **Priority-based task execution**
- **CLI management tools**

## Quick Start

### 1. Create a Custom Task

```php
<?php
use Domain\Scheduler\AbstractTask;

class MyCustomTask extends AbstractTask
{
    public function __construct()
    {
        parent::__construct(
            name: 'my-custom-task',
            cronExpression: '0 9 * * *', // Daily at 9 AM
            enabled: true,
            priority: 10
        );
    }
    
    public function execute(): void
    {
        // Your task implementation
        echo "Executing my custom task...\n";
        
        // Example: Send emails, process data, cleanup files, etc.
    }
}
```

### 2. Register and Run Tasks

`/path/to/your/project/tasks.php`:

```php
<?php
use Domain\Scheduler\TaskScheduler;

// Get scheduler instance (singleton)
$scheduler = TaskScheduler::getInstance();

// Add your task
$scheduler->addTask(new MyCustomTask());

// Run due tasks
$executedTasks = $scheduler->runDueTasks();
```

### 3. Setup Cron Job

Add this to your crontab to run tasks every minute:

```bash
* * * * * php /path/to/your/project/tasks.php
```

## Architecture

### Core Components

1. **TaskInterface** - Contract for all scheduled tasks
2. **AbstractTask** - Base implementation for tasks
3. **TaskScheduler** - Singleton scheduler managing tasks
4. **TaskRegistry** - Registry for task management
5. **TaskHandler** - Handles task execution
6. **TaskMessage** - Message wrapper for Symfony integration

### Directory Structure

```
src/Domain/Scheduler/
├── TaskInterface.php           # Task contract
├── AbstractTask.php           # Base task implementation
├── TaskScheduler.php          # Main scheduler (singleton)
├── TaskRegistry.php           # Task registry
├── TaskHandler.php            # Task execution handler
├── TaskMessage.php            # Message wrapper
├── SchedulerHelper.php        # Helper utilities
└── Tasks/                     # Example tasks
    ├── CleanupTask.php        # Daily cleanup example
    ├── NotificationTask.php   # Notification processing
    └── BackupTask.php         # Weekly backup example
```

## Usage Examples

### Basic Task Creation

```php
<?php
use Domain\Scheduler\AbstractTask;

class EmailDigestTask extends AbstractTask
{
    public function __construct()
    {
        parent::__construct(
            name: 'email-digest',
            cronExpression: '0 8 * * 1', // Every Monday at 8 AM
            enabled: true,
            priority: 15
        );
    }
    
    public function execute(): void
    {
        // Send weekly email digest
        $this->sendWeeklyDigest();
    }
    
    private function sendWeeklyDigest(): void
    {
        // Implementation...
    }
}
```

### Advanced Scheduler Usage

```php
<?php
use Domain\Scheduler\TaskScheduler;
use Domain\Scheduler\SchedulerHelper;

// Bootstrap with default tasks
$scheduler = SchedulerHelper::bootstrap();

// Add custom tasks
$scheduler->addTask(new EmailDigestTask());
$scheduler->addTask(new DataExportTask());

// Check if specific task is due
$task = $scheduler->getTask('email-digest');
if ($scheduler->isTaskDue($task)) {
    echo "Email digest is due for execution\n";
}

// Get next run times
$nextRun = $scheduler->getNextRunTime($task);
echo "Next run: " . $nextRun->format('Y-m-d H:i:s') . "\n";

// Run all due tasks
$executedTasks = $scheduler->runDueTasks();
echo "Executed: " . implode(', ', $executedTasks) . "\n";

// Get schedule information
$info = $scheduler->getScheduleInfo();
print_r($info);
```

### Using the CLI Tool

```bash
# Show task status and information
php scheduler.php status

# List all registered tasks
php scheduler.php list

# Run due tasks manually
php scheduler.php run

# Test a specific task
php scheduler.php test cleanup

# Test all tasks
php scheduler.php test

# Show help
php scheduler.php help
```

## Cron Expression Examples

| Expression | Description |
|------------|-------------|
| `* * * * *` | Every minute |
| `0 * * * *` | Every hour |
| `0 0 * * *` | Daily at midnight |
| `0 2 * * *` | Daily at 2 AM |
| `0 0 * * 0` | Weekly on Sunday |
| `0 0 1 * *` | Monthly on 1st |
| `*/5 * * * *` | Every 5 minutes |
| `0 */6 * * *` | Every 6 hours |
| `0 9 * * 1-5` | Weekdays at 9 AM |

## Task Priority

Tasks are executed in priority order (higher number = higher priority):

```php
parent::__construct(
    name: 'critical-task',
    cronExpression: '*/5 * * * *',
    enabled: true,
    priority: 100  // High priority
);
```

## Error Handling

The scheduler includes comprehensive error handling:

```php
try {
    $executedTasks = $scheduler->runDueTasks();
} catch (Exception $e) {
    error_log("Scheduler error: " . $e->getMessage());
    // Handle error appropriately
}
```

## Testing

### Unit Tests

```bash
# Run scheduler tests
vendor/bin/phpunit tests/Domain/Scheduler/
```

### Manual Testing

```bash
# Test all tasks manually
php scheduler.php test

# Test specific task
php scheduler.php test cleanup
```

## Integration with Events System

The Scheduler system follows the same patterns as the framework's Event system:

- **Singleton pattern** for global access
- **Registry pattern** for component management
- **Clean separation** between interfaces and implementations
- **Type-safe** implementations throughout

## Best Practices

### 1. Task Design

- Keep tasks focused on a single responsibility
- Use meaningful names and descriptions
- Handle errors gracefully
- Log important operations

### 2. Cron Expressions

- Use specific times for resource-intensive tasks
- Avoid overlapping executions
- Consider time zones in deployment

### 3. Performance

- Implement task timeouts for long-running operations
- Use proper logging for debugging
- Monitor task execution times
- Consider task dependencies

### 4. Security

- Validate all input data in tasks
- Use proper file permissions
- Log security-relevant operations
- Implement proper error handling

## Monitoring and Debugging

### Get Task Status

```php
$scheduler = TaskScheduler::getInstance();
$info = $scheduler->getScheduleInfo();

foreach ($info as $task) {
    echo "Task: {$task['name']}\n";
    echo "Status: " . ($task['enabled'] ? 'Enabled' : 'Disabled') . "\n";
    echo "Last executed: " . ($task['last_executed'] ?? 'Never') . "\n";
    echo "Is due: " . ($task['is_due'] ? 'Yes' : 'No') . "\n";
}
```

### Generate Reports

```php
use Domain\Scheduler\SchedulerHelper;

$scheduler = SchedulerHelper::bootstrap();
$report = SchedulerHelper::createTaskReport($scheduler);

// Print formatted report
SchedulerHelper::printTaskReport($scheduler);
```

## Extending the System

### Custom Task Base Classes

```php
<?php
abstract class DatabaseTask extends AbstractTask
{
    protected PDO $db;
    
    public function __construct(string $name, string $cron)
    {
        parent::__construct($name, $cron);
        $this->db = $this->getDatabaseConnection();
    }
    
    abstract protected function getDatabaseConnection(): PDO;
}
```

### Task Middleware

```php
<?php
class LoggingTaskWrapper implements TaskInterface
{
    public function __construct(private TaskInterface $task)
    {
    }
    
    public function execute(): void
    {
        $start = microtime(true);
        
        try {
            $this->task->execute();
            $duration = microtime(true) - $start;
            error_log("Task {$this->task->getName()} completed in {$duration}s");
        } catch (Exception $e) {
            error_log("Task {$this->task->getName()} failed: {$e->getMessage()}");
            throw $e;
        }
    }
    
    // Implement other TaskInterface methods by delegating to $this->task
}
```

## Configuration

### Environment-Specific Tasks

```php
<?php
class ProductionOnlyTask extends AbstractTask
{
    public function __construct()
    {
        $enabled = ($_ENV['APP_ENV'] ?? 'dev') === 'production';
        
        parent::__construct(
            name: 'production-only',
            cronExpression: '0 3 * * *',
            enabled: $enabled
        );
    }
}
```

## Troubleshooting

### Common Issues

1. **Tasks not executing**: Check cron setup and file permissions
2. **Invalid cron expressions**: Use the CLI tool to validate
3. **Memory issues**: Implement proper cleanup in tasks
4. **Overlapping executions**: Use file locks or database locks

### Debug Mode

```php
<?php
// Enable debug output in tasks.php
$_ENV['SCHEDULER_DEBUG'] = true;

// This will show additional information
if ($_ENV['SCHEDULER_DEBUG'] ?? false) {
    echo "Debug: Running scheduler with " . count($scheduler->getTasks()) . " tasks\n";
    SchedulerHelper::printTaskReport($scheduler);
}
```
