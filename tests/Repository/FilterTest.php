<?php
declare(strict_types=1);

namespace Tests\Repository;

use Domain\Repository\ConditionFilterInterface;
use Domain\Repository\Filter;
use Domain\Service\ServiceInterface;
use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{
    private ?ServiceInterface $mockService;
    private Filter $filter;
    
    /**
     * Sets up the test environment with a mock service.
     */
    protected function setUp(): void
    {
        $this->mockService = $this->createMock(ServiceInterface::class);
        $this->filter = new Filter($this->mockService);
    }
    
    /**
     * Tests the get() method for retrieving filter parameters.
     */
    public function testGetReturnsEmptyArrayInitially(): void
    {
        // Arrange
        // No setup needed as filter is initialized empty
        
        // Act
        $result = $this->filter->get();
        
        // Assert
        $this->assertSame([], $result, 'Expected an empty array when no filters are set.');
    }
    
    /**
     * Tests the set() method for overwriting all filter parameters.
     */
    public function testSetOverwritesAllFilterParameters(): void
    {
        // Arrange
        $filterData = ['key1' => 'value1', 'key2' => 'value2'];
        
        // Act
        $this->filter->set($filterData);
        $result = $this->filter->get();
        
        // Assert
        $this->assertSame($filterData, $result, 'Expected filter parameters to be overwritten.');
    }
    
    /**
     * Tests the add() method for adding a new filter parameter.
     */
    public function testAddAddsNewFilterParameter(): void
    {
        // Arrange
        $field = 'key';
        $value = 'value';
        
        // Act
        $this->filter->add($field, $value);
        $result = $this->filter->get();
        
        // Assert
        $this->assertArrayHasKey($field, $result, 'Expected the filter to contain the added key.');
        $this->assertSame($value, $result[$field], 'Expected the filter value to match the added value.');
    }
    
    /**
     * Tests the add() method for adding a new filter parameter.
     */
    public function testAddAddsNewFilterParameters(): void
    {
        // Arrange
        $field = 'id';
        $value = [15, 20, 35];
        
        // Act
        $this->filter->add($field, $value);
        $result = $this->filter->get();
        
        // Assert
        $this->assertArrayHasKey($field, $result, 'Expected the filter to contain the added key.');
        $this->assertSame($value, $result[$field], 'Expected the filter value to match the added value.');
    }
    
    /**
     * Tests the restrict() method for restricting a filter parameter.
     */
    public function testRestrictRestrictsManyFilterParameter(): void
    {
        // Arrange
        $field = 'id';
        
        $addValue = [15];
        $restrictValue = [10, 15, 20];
        
        // Act
        $this->filter->add($field, $addValue);
        $this->filter->restrict($field, $restrictValue);
        
        $result = $this->filter->get();
        
        // Assert
        $this->assertArrayHasKey($field, $result, 'Expected the filter to contain the restricted key.');
        $this->assertSame(array_values($addValue), array_values($result[$field]), 'Expected the filter value to match the restricted value.');
    }
    
    /**
     * Tests the restrict() method for restricting a filter parameter.
     */
    public function testRestrictRestrictsAddedFilterParameter(): void
    {
        // Arrange
        $field = 'id';
        
        $addValue = [12];
        $restrictValue = [10, 15, 20];
        
        // Act
        $this->filter->add($field, $addValue);
        $this->filter->restrict($field, $restrictValue);
        
        $result = $this->filter->get();
        
        // Assert
        $this->assertArrayHasKey($field, $result, 'Expected the filter to contain the restricted key.');
        $this->assertSame(array_values($restrictValue), array_values($result[$field]), 'Expected the filter value to match the restricted value.');
    }
    
    /**
     * Tests the restrict() method for restricting a filter parameter.
     */
    public function testRestrictRestrictsOneFilterParameter(): void
    {
        // Arrange
        $field = 'id';
        
        $addValue = [10, 15, 20];
        $restrictValue = [15];
        
        // Act
        $this->filter->add($field, $addValue);
        $this->filter->restrict($field, $restrictValue);
        
        $result = $this->filter->get();
        
        // Assert
        $this->assertArrayHasKey($field, $result, 'Expected the filter to contain the restricted key.');
        $this->assertSame(array_values($restrictValue), array_values($result[$field]), 'Expected the filter value to match the restricted value.');
    }
    
    /**
     * Tests the restrict() method for restricting a filter parameter.
     */
    public function testRestrictWithoutAddFilterParameter(): void
    {
        // Arrange
        $field = 'id';
        $restrictValue = [15, 19, 99];
        
        // Act
        $this->filter->restrict($field, $restrictValue);
        
        $result = $this->filter->get();
        
        // Assert
        $this->assertArrayHasKey($field, $result, 'Expected the filter to contain the restricted key.');
        $this->assertSame(array_values($restrictValue), array_values($result[$field]), 'Expected the filter value to match the restricted value.');
    }
    
    /**
     * Tests the remove() method for unsetting a filter parameter.
     */
    public function testRemoveUnsetsFilterParameter(): void
    {
        // Arrange
        $field = 'key';
        $this->filter->add($field, 'value');
        
        // Act
        $this->filter->remove($field);
        $result = $this->filter->get();
        
        // Assert
        $this->assertArrayNotHasKey($field, $result, 'Expected the filter to not contain the removed key.');
    }
    
    /**
     * Tests the not() method for inversing a filter parameter.
     */
    public function testNotInversesFilterParameter(): void
    {
        // Arrange
        $field = 'key';
        $value = 'value';
        
        // Act
        $this->filter->not($field, $value);
        $result = $this->filter->get();
        
        // Assert
        $this->assertArrayHasKey('!' . $field, $result, 'Expected the filter to contain the inversed key.');
        $this->assertSame($value, $result['!' . $field], 'Expected the filter value to match the inversed value.');
    }
    
    /**
     * Tests the reset() method for clearing all filter parameters.
     */
    public function testResetClearsAllFilterParameters(): void
    {
        // Arrange
        $this->filter->set(['key1' => 'value1', 'key2' => 'value2']);
        
        // Act
        $this->filter->reset();
        $result = $this->filter->get();
        
        // Assert
        $this->assertSame([], $result, 'Expected the filter to be reset to an empty array.');
    }
    
    /**
     * Tests the endFilter() method for returning the service.
     */
    public function testEndFilterReturnsService(): void
    {
        // Act
        $result = $this->filter->endFilter();
        
        // Assert
        $this->assertSame($this->mockService, $result, 'Expected the service to be returned.');
    }
    
    /**
     * Tests the byCondition() method for returning a ConditionFilterInterface instance.
     */
    public function testByConditionReturnsConditionFilterInstance(): void
    {
        // Act
        $result = $this->filter->byCondition();
        
        // Assert
        $this->assertNotNull($result, 'Expected a ConditionFilterInterface instance to be returned.');
        $this->assertInstanceOf(ConditionFilterInterface::class, $result, 'Expected the returned instance to implement ConditionFilterInterface.');
    }
}
