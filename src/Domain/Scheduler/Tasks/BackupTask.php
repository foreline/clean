<?php
declare(strict_types=1);

namespace Domain\Scheduler\Tasks;

use Domain\Scheduler\AbstractTask;

/**
 * Example task for data backup operations
 */
class BackupTask extends AbstractTask
{
    public function __construct()
    {
        parent::__construct(
            name: 'backup',
            cronExpression: '0 1 * * 0', // Weekly on Sunday at 1 AM
            enabled: true,
            priority: 20
        );
    }
    
    public function execute(): void
    {
        // Implementation for backup operations
        echo "Starting backup task...\n";
        
        // Example: Backup database, files, configurations
        // $this->backupDatabase();
        // $this->backupFiles();
        // $this->backupConfigurations();
        
        echo "Backup task completed.\n";
    }
}
