<?php
declare(strict_types=1);

namespace Domain\Scheduler\Tasks;

use Domain\Scheduler\AbstractTask;

/**
 * Example task that runs daily to clean up old files
 */
class CleanupTask extends AbstractTask
{
    public function __construct()
    {
        parent::__construct(
            name: 'cleanup',
            cronExpression: '0 2 * * *', // Daily at 2 AM
            enabled: true,
            priority: 10
        );
    }
    
    public function execute(): void
    {
        // Implementation for cleaning up old files
        // This is just an example
        echo "Running daily cleanup task...\n";
        
        // Example: Clean up old log files, temporary files, etc.
        // $this->cleanupOldLogFiles();
        // $this->cleanupTempFiles();
        
        echo "Cleanup task completed.\n";
    }
}
