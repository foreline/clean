<?php
declare(strict_types=1);

namespace Domain\Scheduler;

use RuntimeException;

/**
 * File-based lock for scheduled tasks.
 * Uses flock() for non-blocking exclusive locking.
 * Crash-safe: OS releases the lock when the process terminates.
 */
class TaskLock
{
    /** @var resource|null File handle for the lock file */
    private $fileHandle = null;
    
    private bool $acquired = false;
    
    /**
     * @param string $taskName Task identifier used to derive lock file name
     * @param string $lockDirectory Directory where lock files are stored
     */
    public function __construct(
        private readonly string $taskName,
        private readonly string $lockDirectory
    ) {
    }
    
    /**
     * Attempt to acquire an exclusive non-blocking lock.
     *
     * @return bool True if the lock was acquired, false if the task is already running
     * @throws RuntimeException If the lock file cannot be created or opened
     */
    public function acquire(): bool
    {
        if ( $this->acquired ) {
            return true;
        }
        
        $this->ensureLockDirectory();
        
        $lockFile = $this->getLockFilePath();
        
        $handle = @fopen($lockFile, 'c+');
        if ( false === $handle ) {
            throw new RuntimeException("Cannot open lock file: {$lockFile}");
        }
        
        // Non-blocking exclusive lock
        if ( !flock($handle, LOCK_EX | LOCK_NB) ) {
            fclose($handle);
            return false;
        }
        
        // Write debug info (PID + timestamp) into the lock file
        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, json_encode([
            'pid'       => getmypid(),
            'task'      => $this->taskName,
            'locked_at' => date('Y-m-d H:i:s'),
        ], JSON_PRETTY_PRINT));
        fflush($handle);
        
        $this->fileHandle = $handle;
        $this->acquired = true;
        
        return true;
    }
    
    /**
     * Release the lock.
     *
     * @return void
     */
    public function release(): void
    {
        if ( null === $this->fileHandle ) {
            return;
        }
        
        flock($this->fileHandle, LOCK_UN);
        fclose($this->fileHandle);
        
        $this->fileHandle = null;
        $this->acquired = false;
    }
    
    /**
     * Check if the lock is currently held by this instance.
     *
     * @return bool
     */
    public function isAcquired(): bool
    {
        return $this->acquired;
    }
    
    /**
     * Get the lock file path for this task.
     *
     * @return string
     */
    public function getLockFilePath(): string
    {
        // Sanitize task name for safe use as filename
        $safeTaskName = preg_replace('/[^a-zA-Z0-9_\-.]/', '_', $this->taskName);
        
        return $this->lockDirectory . DIRECTORY_SEPARATOR . $safeTaskName . '.lock';
    }
    
    /**
     * Ensure the lock directory exists.
     *
     * @return void
     * @throws RuntimeException If the directory cannot be created
     */
    private function ensureLockDirectory(): void
    {
        if ( is_dir($this->lockDirectory) ) {
            return;
        }
        
        if ( !mkdir($this->lockDirectory, 0775, true) && !is_dir($this->lockDirectory) ) {
            throw new RuntimeException("Cannot create lock directory: {$this->lockDirectory}");
        }
    }
    
    /**
     * Release the lock on destruction to prevent leaks.
     */
    public function __destruct()
    {
        $this->release();
    }
}
