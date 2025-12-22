<?php
declare(strict_types=1);

namespace Domain\Scheduler;

use Cron\CronExpression;
use DateTimeImmutable;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Scheduler;
use Throwable;

/**
 * Scheduler system implementing singleton pattern for task management
 * Uses Symfony Scheduler component with in-memory transport
 */
class TaskScheduler
{
    private static ?self $instance = null;
    private Schedule $schedule;
    private TaskRegistry $taskRegistry;
    private LoggerInterface $logger;
    
    /**
     * Private constructor for singleton pattern
     */
    private function __construct(?LoggerInterface $logger = null)
    {
        $this->schedule = new Schedule();
        $this->taskRegistry = new TaskRegistry();
        $this->logger = $logger;
    }
    
    /**
     * Get singleton instance
     * @return static
     */
    public static function getInstance(?LoggerInterface $logger = null): self
    {
        if ( null === self::$instance ) {
            self::$instance = new self($logger);
        }
        
        return self::$instance;
    }
    
    /**
     * Add a task to the scheduler
     * @param TaskInterface $task
     * @return $this
     * @throws Exception
     */
    public function addTask(TaskInterface $task): self
    {
        // Validate cron expression
        if ( !CronExpression::isValidExpression($task->getCronExpression()) ) {
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
        
        return $this;
    }
    
    /**
     * Remove a task from the scheduler
     * @param string $taskName
     * @return void
     */
    public function removeTask(string $taskName): void
    {
        $this->taskRegistry->remove($taskName);
        // Note: Symfony Scheduler doesn't support removing individual messages
        // The schedule would need to be rebuilt for this to work fully
    }
    
    /**
     * Get the task registry
     * @return TaskRegistry
     */
    public function getTaskRegistry(): TaskRegistry
    {
        return $this->taskRegistry;
    }
    
    /**
     * Get all registered tasks
     * @return TaskInterface[]
     */
    public function getTasks(): array
    {
        return $this->taskRegistry->all();
    }
    
    /**
     * Get a specific task by name
     * @param string $name
     * @return TaskInterface
     */
    public function getTask(string $name): TaskInterface
    {
        return $this->taskRegistry->get($name);
    }
    
    /**
     * Check if a task is due to run based on its cron expression
     * @param TaskInterface $task
     * @param DateTimeImmutable|null $currentTime
     * @return bool
     * @throws Exception
     */
    public function isTaskDue(TaskInterface $task, ?DateTimeImmutable $currentTime = null): bool
    {
        $currentTime = $currentTime ?? new DateTimeImmutable();
        $cron = new CronExpression($task->getCronExpression());
        
        $lastRun = $task->getLastExecutedAt();
        if ( null === $lastRun ) {
            return $cron->isDue($currentTime);
        }
        
        $nextRun = $cron->getNextRunDate($lastRun);
        return $nextRun <= $currentTime;
    }
    
    /**
     * Get the next run time for a task
     * @param TaskInterface $task
     * @param DateTimeImmutable|null $currentTime
     * @return DateTimeImmutable
     * @throws Exception
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
     * @param DateTimeImmutable|null $currentTime
     * @return array
     * @throws Exception
     */
    public function runDueTasks(?DateTimeImmutable $currentTime = null): array
    {
        $currentTime = $currentTime ?? new DateTimeImmutable();
        $executedTasks = [];
        
        foreach ( $this->taskRegistry->getEnabledTasks() as $task ) {
            if ( $this->isTaskDue($task, $currentTime) ) {
                try {
                    $startTime = microtime(true);
                    $this->logger?->debug('[' . date('Y.m.d H:i:s') . '] ' . "Executing task: {$task->getName()}" . PHP_EOL);
                    $task->execute();
                    $this->logger?->debug('[' . date('Y.m.d H:i:s') . '] ' . "Task executed successfully: {$task->getName()} in " . (microtime(true) - $startTime) . " seconds" . PHP_EOL . PHP_EOL);
                    $task->setLastExecutedAt($currentTime);
                    $executedTasks[] = $task->getName();
                } catch ( Exception $e ) {
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
     * @return void
     * @throws Throwable
     */
    public function run(): void
    {
        if ( empty($this->taskRegistry->all()) ) {
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
     * @return array
     * @throws Exception
     */
    public function getScheduleInfo(): array
    {
        $info = [];
        
        foreach ( $this->taskRegistry->all() as $task ) {
            $info[] = [
                'name'          => $task->getName(),
                'cron'          => $task->getCronExpression(),
                'enabled'       => $task->isEnabled(),
                'priority'      => $task->getPriority(),
                'last_executed' => $task->getLastExecutedAt()?->format('Y-m-d H:i:s'),
                'next_run'      => $this->getNextRunTime($task)->format('Y-m-d H:i:s'),
                'is_due'        => $this->isTaskDue($task)
            ];
        }
        
        return $info;
    }
    
    /**
     * Prevent cloning of singleton
     * @return void
     */
    private function __clone(): void
    {
    }
    
    /**
     * Prevent unserialization of singleton
     * @return void
     * @throws Exception
     */
    public function __wakeup(): void
    {
        throw new Exception("Cannot unserialize singleton");
    }
}