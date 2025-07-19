<?php
declare(strict_types=1);

namespace Tests\Domain\Scheduler;

use Domain\Scheduler\TaskScheduler;
use Domain\Scheduler\AbstractTask;
use Domain\Scheduler\TaskRegistry;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Exception;

/**
 * @covers \Domain\Scheduler\TaskScheduler
 */
class TaskSchedulerTest extends TestCase
{
    private TaskScheduler $scheduler;
    
    protected function setUp(): void
    {
        // Reset singleton for testing
        $reflection = new \ReflectionClass(TaskScheduler::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);
        
        $this->scheduler = TaskScheduler::getInstance();
    }
    
    public function testSingletonPattern(): void
    {
        $scheduler1 = TaskScheduler::getInstance();
        $scheduler2 = TaskScheduler::getInstance();
        
        $this->assertSame($scheduler1, $scheduler2);
    }
    
    public function testAddTask(): void
    {
        $task = new TestTask();
        
        $this->scheduler->addTask($task);
        
        $this->assertTrue($this->scheduler->getTaskRegistry()->has('test-task'));
        $this->assertSame($task, $this->scheduler->getTask('test-task'));
    }
    
    public function testAddTaskWithInvalidCronExpression(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid cron expression');
        
        $task = new class extends AbstractTask {
            public function __construct()
            {
                parent::__construct('invalid', 'invalid-cron');
            }
            
            public function execute(): void
            {
            }
        };
        
        $this->scheduler->addTask($task);
    }
    
    public function testRemoveTask(): void
    {
        $task = new TestTask();
        
        $this->scheduler->addTask($task);
        $this->assertTrue($this->scheduler->getTaskRegistry()->has('test-task'));
        
        $this->scheduler->removeTask('test-task');
        $this->assertFalse($this->scheduler->getTaskRegistry()->has('test-task'));
    }
    
    public function testIsTaskDue(): void
    {
        $task = new TestTask();
        $currentTime = new DateTimeImmutable('2023-01-01 12:00:00');
        
        // Task should be due if never executed
        $this->assertTrue($this->scheduler->isTaskDue($task, $currentTime));
        
        // Set last execution to recent time (same time as current)
        $task->setLastExecutedAt(new DateTimeImmutable('2023-01-01 12:00:00'));
        $this->assertFalse($this->scheduler->isTaskDue($task, $currentTime));
        
        // Set last execution to old time (more than 5 minutes ago)
        $task->setLastExecutedAt(new DateTimeImmutable('2023-01-01 10:00:00'));
        $this->assertTrue($this->scheduler->isTaskDue($task, $currentTime));
    }
    
    public function testGetNextRunTime(): void
    {
        $task = new TestTask();
        $currentTime = new DateTimeImmutable('2023-01-01 12:00:00');
        
        $nextRun = $this->scheduler->getNextRunTime($task, $currentTime);
        
        // Should be next 5-minute interval
        $this->assertEquals('2023-01-01 12:05:00', $nextRun->format('Y-m-d H:i:s'));
    }
    
    public function testRunDueTasks(): void
    {
        $task = new TestTask();
        $this->scheduler->addTask($task);
        
        $currentTime = new DateTimeImmutable('2023-01-01 12:00:00');
        $executedTasks = $this->scheduler->runDueTasks($currentTime);
        
        $this->assertContains('test-task', $executedTasks);
        $this->assertNotNull($task->getLastExecutedAt());
    }
    
    public function testRunDueTasksWithDisabledTask(): void
    {
        $task = new class extends AbstractTask {
            public function __construct()
            {
                parent::__construct('disabled-task', '*/5 * * * *', false); // disabled
            }
            
            public function execute(): void
            {
                throw new Exception('This should not be called');
            }
        };
        
        $this->scheduler->addTask($task);
        
        $currentTime = new DateTimeImmutable('2023-01-01 12:00:00');
        $executedTasks = $this->scheduler->runDueTasks($currentTime);
        
        $this->assertNotContains('disabled-task', $executedTasks);
    }
    
    public function testGetScheduleInfo(): void
    {
        $task = new TestTask();
        $this->scheduler->addTask($task);
        
        $info = $this->scheduler->getScheduleInfo();
        
        $this->assertCount(1, $info);
        $this->assertEquals('test-task', $info[0]['name']);
        $this->assertEquals('*/5 * * * *', $info[0]['cron']);
        $this->assertTrue($info[0]['enabled']);
    }
}

/**
 * Test task for unit tests
 */
class TestTask extends AbstractTask
{
    public bool $executed = false;
    
    public function __construct()
    {
        parent::__construct(
            name: 'test-task',
            cronExpression: '*/5 * * * *', // Every 5 minutes
            enabled: true,
            priority: 1
        );
    }
    
    public function execute(): void
    {
        $this->executed = true;
    }
}
