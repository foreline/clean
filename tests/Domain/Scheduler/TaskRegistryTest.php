<?php
declare(strict_types=1);

namespace Tests\Domain\Scheduler;

use Domain\Scheduler\TaskRegistry;
use Domain\Scheduler\AbstractTask;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

/**
 * @covers \Domain\Scheduler\TaskRegistry
 */
class TaskRegistryTest extends TestCase
{
    private TaskRegistry $registry;
    
    protected function setUp(): void
    {
        $this->registry = new TaskRegistry();
    }
    
    public function testRegisterAndGet(): void
    {
        $task = new TestRegistryTask();
        
        $this->registry->register($task);
        
        $this->assertTrue($this->registry->has('test-registry-task'));
        $this->assertSame($task, $this->registry->get('test-registry-task'));
    }
    
    public function testGetNonExistentTask(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Task 'non-existent' not found");
        
        $this->registry->get('non-existent');
    }
    
    public function testHas(): void
    {
        $task = new TestRegistryTask();
        
        $this->assertFalse($this->registry->has('test-registry-task'));
        
        $this->registry->register($task);
        
        $this->assertTrue($this->registry->has('test-registry-task'));
    }
    
    public function testAll(): void
    {
        $task1 = new TestRegistryTask();
        $task2 = new class extends AbstractTask {
            public function __construct()
            {
                parent::__construct('task2', '0 0 * * *');
            }
            
            public function execute(): void
            {
            }
        };
        
        $this->registry->register($task1);
        $this->registry->register($task2);
        
        $all = $this->registry->all();
        
        $this->assertCount(2, $all);
        $this->assertSame($task1, $all['test-registry-task']);
        $this->assertSame($task2, $all['task2']);
    }
    
    public function testGetEnabledTasks(): void
    {
        $enabledTask = new TestRegistryTask();
        $disabledTask = new class extends AbstractTask {
            public function __construct()
            {
                parent::__construct('disabled', '0 0 * * *', false); // disabled
            }
            
            public function execute(): void
            {
            }
        };
        
        $this->registry->register($enabledTask);
        $this->registry->register($disabledTask);
        
        $enabled = $this->registry->getEnabledTasks();
        
        $this->assertCount(1, $enabled);
        $this->assertSame($enabledTask, reset($enabled));
    }
    
    public function testGetEnabledTasksSortedByPriority(): void
    {
        $lowPriority = new class extends AbstractTask {
            public function __construct()
            {
                parent::__construct('low', '0 0 * * *', true, 1);
            }
            
            public function execute(): void
            {
            }
        };
        
        $highPriority = new class extends AbstractTask {
            public function __construct()
            {
                parent::__construct('high', '0 0 * * *', true, 10);
            }
            
            public function execute(): void
            {
            }
        };
        
        $this->registry->register($lowPriority);
        $this->registry->register($highPriority);
        
        $enabled = $this->registry->getEnabledTasks();
        $names = array_map(fn($task) => $task->getName(), array_values($enabled));
        
        $this->assertEquals(['high', 'low'], $names);
    }
    
    public function testRemove(): void
    {
        $task = new TestRegistryTask();
        
        $this->registry->register($task);
        $this->assertTrue($this->registry->has('test-registry-task'));
        
        $this->registry->remove('test-registry-task');
        $this->assertFalse($this->registry->has('test-registry-task'));
    }
    
    public function testClear(): void
    {
        $task1 = new TestRegistryTask();
        $task2 = new class extends AbstractTask {
            public function __construct()
            {
                parent::__construct('task2', '0 0 * * *');
            }
            
            public function execute(): void
            {
            }
        };
        
        $this->registry->register($task1);
        $this->registry->register($task2);
        
        $this->assertCount(2, $this->registry->all());
        
        $this->registry->clear();
        
        $this->assertCount(0, $this->registry->all());
    }
}

/**
 * Test task for registry tests
 */
class TestRegistryTask extends AbstractTask
{
    public function __construct()
    {
        parent::__construct(
            name: 'test-registry-task',
            cronExpression: '*/10 * * * *',
            enabled: true,
            priority: 5
        );
    }
    
    public function execute(): void
    {
        // Test implementation
    }
}
