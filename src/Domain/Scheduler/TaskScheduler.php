<?php
declare(strict_types=1);

namespace Domain\Scheduler;

use Cron\CronExpression;
use DateTimeImmutable;
use Exception;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Scheduler;

/**
 * Scheduler system implementing singleton pattern for task management
 * Uses Symfony Scheduler component with in-memory transport
 */
class TaskScheduler
{
    private static ?self $instance = null;
    private Schedule $schedule;
    private TaskRegistry $taskRegistry;
    
    /**
     * Private constructor for singleton pattern
     */
    private function __construct()
    {
        $this->schedule = new Schedule();
        $this->taskRegistry = new TaskRegistry();
    }
    
    /**
     * Get singleton instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Add a task to the scheduler
     */
    public function addTask(TaskInterface $task): void
    {
        // Validate cron expression
        if (!CronExpression::isValidExpression($task->getCronExpression())) {
            throw new Exception("Invalid cron expression: {$task->getCronExpression()}");
        }
        
        // Register the task
        $this->taskRegistry->register($task);
        
        // Add to schedule
        $this->schedule->add(
            RecurringMessage::cron(
                $task->getCronExpression(),
                new TaskMessage($task->getName())
            )
        );
    }
    
    /**
     * Remove a task from the scheduler
     */
    public function removeTask(string $taskName): void
    {
        $this->taskRegistry->remove($taskName);
        // Note: Symfony Scheduler doesn't support removing individual messages
        // The schedule would need to be rebuilt for this to work fully
    }
    
    /**
     * Get the task registry
     */
    public function getTaskRegistry(): TaskRegistry
    {
        return $this->taskRegistry;
    }
    
    /**
     * Get all registered tasks
     * 
     * @return TaskInterface[]
     */
    public function getTasks(): array
    {
        return $this->taskRegistry->all();
    }
    
    /**
     * Get a specific task by name
     */
    public function getTask(string $name): TaskInterface
    {
        return $this->taskRegistry->get($name);
    }
    
    /**
     * Check if a task is due to run based on its cron expression
     */
    public function isTaskDue(TaskInterface $task, ?DateTimeImmutable $currentTime = null): bool
    {
        $currentTime = $currentTime ?? new DateTimeImmutable();
        $cron = new CronExpression($task->getCronExpression());
        
        $lastRun = $task->getLastExecutedAt();
        if ($lastRun === null) {
            return $cron->isDue($currentTime);
        }
        
        $nextRun = $cron->getNextRunDate($lastRun);
        return $nextRun <= $currentTime;
    }
    
    /**
     * Get the next run time for a task
     */
    public function getNextRunTime(TaskInterface $task, ?DateTimeImmutable $currentTime = null): DateTimeImmutable
    {
        $currentTime = $currentTime ?? new DateTimeImmutable();
        $cron = new CronExpression($task->getCronExpression());
        
        $lastRun = $task->getLastExecutedAt() ?? $currentTime;
        return DateTimeImmutable::createFromInterface($cron->getNextRunDate($lastRun));
    }
    
    /**
     * Run all due tasks manually (useful for testing or manual execution)
     */
    public function runDueTasks(?DateTimeImmutable $currentTime = null): array
    {
        $currentTime = $currentTime ?? new DateTimeImmutable();
        $executedTasks = [];
        
        foreach ($this->taskRegistry->getEnabledTasks() as $task) {
            if ($this->isTaskDue($task, $currentTime)) {
                try {
                    $task->execute();
                    $task->setLastExecutedAt($currentTime);
                    $executedTasks[] = $task->getName();
                } catch (Exception $e) {
                    // In a real implementation, you might want to log this
                    throw new Exception("Failed to execute task '{$task->getName()}': " . $e->getMessage(), 0, $e);
                }
            }
        }
        
        return $executedTasks;
    }
    
    /**
     * Run the scheduler with Symfony Scheduler component
     * This is the main method to be called from tasks.php
     */
    public function run(): void
    {
        if (empty($this->taskRegistry->all())) {
            return; // No tasks to run
        }
        
        $taskHandler = new TaskHandler($this->taskRegistry);
        
        $scheduler = new Scheduler(
            [$taskHandler],
            [$this->schedule]
        );
        
        $scheduler->run();
    }
    
    /**
     * Get schedule information for debugging
     */
    public function getScheduleInfo(): array
    {
        $info = [];
        
        foreach ($this->taskRegistry->all() as $task) {
            $info[] = [
                'name' => $task->getName(),
                'cron' => $task->getCronExpression(),
                'enabled' => $task->isEnabled(),
                'priority' => $task->getPriority(),
                'last_executed' => $task->getLastExecutedAt()?->format('Y-m-d H:i:s'),
                'next_run' => $this->getNextRunTime($task)->format('Y-m-d H:i:s'),
                'is_due' => $this->isTaskDue($task)
            ];
        }
        
        return $info;
    }
    
    /**
     * Prevent cloning of singleton
     */
    private function __clone(): void
    {
    }
    
    /**
     * Prevent unserialization of singleton
     */
    public function __wakeup(): void
    {
        throw new Exception("Cannot unserialize singleton");
    }
}