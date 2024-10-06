<?php
declare(strict_types=1);

namespace Presentation\UI\Dropdown;

/**
 * Dropdown Action
 */
class Action
{
    /** @var string  */
    private string $name = '';
    
    /** @var string[] class attribute */
    private array $classes = [];
    
    /** @var array data attributes */
    private array $data = [];
    
    /** @var string href attribute */
    private string $link = '';
    
    /** @var bool condition for displaying item (based on access) */
    private bool $access = true;
    
    /** @var bool condition for displaying item (based on logic)  */
    private bool $visible = true;
    
    /** @var bool  */
    private bool $disabled = false;

    /**
     *
     */
    public function __construct(string $name = '')
    {
        $this->setName($name);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Action
     */
    public function setName(string $name): Action
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return array
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

    /**
     * @param array $classes
     * @return Action
     */
    public function setClasses(array $classes): Action
    {
        foreach ( $classes as $class ) {
            $this->addClass($class);
        }
        return $this;
    }

    /**
     * @param string $className
     * @return $this
     */
    public function addClass(string $className): self
    {
        if ( !in_array($className, $this->classes) ) {
            $this->classes[] = $className;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array<string, mixed> $data
     * @return Action
     */
    public function setData(array $data): Action
    {
        foreach ( $data as $attributeName => $attributeValue ) {
            $this->addData($attributeName, $attributeValue);
        }
        return $this;
    }

    /**
     * @param string $attributeName
     * @param mixed $value
     * @return $this
     */
    public function addData(string $attributeName, mixed $value): self
    {
        $this->data[$attributeName] = (string)$value;
        return $this;
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @param string $link
     * @return Action
     */
    public function setLink(string $link): Action
    {
        $this->link = $link;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAccess(): bool
    {
        return $this->access;
    }

    /**
     * @param bool $access
     * @return Action
     */
    public function setAccess(bool $access): Action
    {
        $this->access = $access;
        return $this;
    }

    /**
     * @return bool
     */
    public function isVisible(): bool
    {
        return $this->visible;
    }

    /**
     * @param bool $visible
     * @return Action
     */
    public function setVisible(bool $visible): Action
    {
        $this->visible = $visible;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    /**
     * @param bool $disabled
     * @return Action
     */
    public function setDisabled(bool $disabled = true): Action
    {
        $this->disabled = $disabled;
        return $this;
    }
}