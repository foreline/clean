<?php
declare(strict_types=1);

namespace Domain\Scheduler;

use DateTimeImmutable;
use Exception;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Handles task execution from the scheduler
 */
#[AsMessageHandler]
class TaskHandler
{
    public function __construct(
        private readonly TaskRegistry $taskRegistry
    ) {
    }
    
    /**
     * Handle task message execution
     *
     * @param TaskMessage $message
     * @return void
     * @throws Exception
     */
    public function __invoke(TaskMessage $message): void
    {
        $taskName = $message->getTaskName();
        
        if (!$this->taskRegistry->has($taskName)) {
            throw new Exception("Task '{$taskName}' not found in registry");
        }
        
        $task = $this->taskRegistry->get($taskName);
        
        if (!$task->isEnabled()) {
            return; // Skip disabled tasks
        }
        
        try {
            $task->execute();
            $task->setLastExecutedAt(new DateTimeImmutable());
        } catch (Exception $e) {
            // Log error or handle task failure
            throw new Exception("Failed to execute task '{$taskName}': " . $e->getMessage(), 0, $e);
        }
    }
}