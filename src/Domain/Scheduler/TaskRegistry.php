<?php
declare(strict_types=1);

namespace Domain\Scheduler;

use InvalidArgumentException;

/**
 * Registry for managing scheduled tasks
 */
class TaskRegistry
{
    /** @var TaskInterface[] */
    private array $tasks = [];
    
    /**
     * Register a task
     */
    public function register(TaskInterface $task): void
    {
        $this->tasks[$task->getName()] = $task;
    }
    
    /**
     * Get a task by name
     */
    public function get(string $name): TaskInterface
    {
        if (!isset($this->tasks[$name])) {
            throw new InvalidArgumentException("Task '{$name}' not found");
        }
        
        return $this->tasks[$name];
    }
    
    /**
     * Check if a task exists
     */
    public function has(string $name): bool
    {
        return isset($this->tasks[$name]);
    }
    
    /**
     * Get all registered tasks
     * 
     * @return TaskInterface[]
     */
    public function all(): array
    {
        return $this->tasks;
    }
    
    /**
     * Get enabled tasks sorted by priority
     * 
     * @return TaskInterface[]
     */
    public function getEnabledTasks(): array
    {
        $enabled = array_filter($this->tasks, fn(TaskInterface $task) => $task->isEnabled());
        
        // Sort by priority (higher priority first)
        uasort($enabled, fn(TaskInterface $a, TaskInterface $b) => $b->getPriority() <=> $a->getPriority());
        
        return $enabled;
    }
    
    /**
     * Remove a task
     */
    public function remove(string $name): void
    {
        unset($this->tasks[$name]);
    }
    
    /**
     * Clear all tasks
     */
    public function clear(): void
    {
        $this->tasks = [];
    }
}
