<?php
declare(strict_types=1);

namespace Domain\Scheduler\Tasks;

use Domain\Scheduler\AbstractTask;

/**
 * Example task that sends periodic notifications
 */
class NotificationTask extends AbstractTask
{
    public function __construct()
    {
        parent::__construct(
            name: 'notifications',
            cronExpression: '*/15 * * * *', // Every 15 minutes
            enabled: true,
            priority: 5
        );
    }
    
    public function execute(): void
    {
        // Implementation for sending notifications
        echo "Checking for pending notifications...\n";
        
        // Example: Check for pending emails, SMS, push notifications
        // $this->processPendingEmails();
        // $this->processPendingSms();
        
        echo "Notification task completed.\n";
    }
}
