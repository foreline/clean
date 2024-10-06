<?php
declare(strict_types=1);

namespace Presentation\UI\Dropdown;

/**
 *
 */
class Dropdown
{
    public const STYLE_LINK = 'link';
    public const STYLE_BUTTON = 'button';
    
    public const TYPE_DEFAULT = 'default';
    public const TYPE_SPLIT = 'split';
    
    /** @var string  */
    private string $id = '';
    
    /** @var string  */
    private string $label = 'Действия';
    
    /** @var string  */
    private string $link = '#';
    
    /** @var string[]  */
    private array $classes = [];
    
    /** @var string[] */
    private array $buttonClasses = [];
    
    /** @var ActionCollection|null  */
    private ?ActionCollection $actions = null;
    
    /** @var bool Condition for displaying Dropdown (based on access) */
    private ?bool $access = true;
    
    /** @var bool Condition for displaying Dropdown (based on logic) */
    private bool $visible = true;
    
    /** @var string  */
    private string $type = self::TYPE_DEFAULT;
    
    /** @var string  */
    private string $style = self::STYLE_BUTTON;

    /**
     *
     */
    public function __construct()
    {
        $this->id = 'dropdown-' . mt_rand(1000,9999);
        $this->buttonClasses[] = 'btn-primary';
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return Dropdown
     */
    public function setId(string $id): Dropdown
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return Dropdown
     */
    public function setLabel(string $label): Dropdown
    {
        $this->label = $label;
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
     * @return Dropdown
     */
    public function setLink(string $link): Dropdown
    {
        $this->link = $link;
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
     * @return Dropdown
     */
    public function setClasses(array $classes): Dropdown
    {
        $this->classes = $classes;
        return $this;
    }

    /**
     * @param string $class
     * @return $this
     */
    public function addClass(string $class): self
    {
        if ( !in_array($class, $this->classes) ) {
            $this->classes[] = $class;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getButtonClasses(): array
    {
        return $this->buttonClasses;
    }

    /**
     * @param array $buttonClasses
     * @return Dropdown
     */
    public function setButtonClasses(array $buttonClasses): Dropdown
    {
        foreach ( $buttonClasses as $buttonClass ) {
            $this->addButtonClass($buttonClass);
        }
        return $this;
    }

    /**
     * @param string $class
     * @return $this
     */
    public function addButtonClass(string $class): self
    {
        if ( !in_array($class, $this->buttonClasses) ) {
            $this->buttonClasses[] = $class;
        }
        return $this;
    }

    /**
     * @return ActionCollection|null
     */
    public function getActions(): ?ActionCollection
    {
        return $this->actions;
    }

    /**
     * @param ActionCollection|null $actions
     * @return Dropdown
     */
    public function setActions(?ActionCollection $actions): Dropdown
    {
        foreach ( $actions as $action ) {
            $this->addAction($action);
        }
        return $this;
    }

    /**
     * @param Action $action
     * @return $this
     */
    public function addAction(Action $action): self
    {
        if ( !$this->actions ) {
            $this->actions = new ActionCollection();
        }
        $this->actions->addItem($action);
        return $this;
    }

    /**
     * @return bool
     */
    public function isAccess(): bool
    {
        return (bool)$this->access;
    }

    /**
     * @param ?bool $access
     * @return Dropdown
     */
    public function setAccess(?bool $access): Dropdown
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
     * @return Dropdown
     */
    public function setVisible(bool $visible): Dropdown
    {
        $this->visible = $visible;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Dropdown
     */
    public function setType(string $type): Dropdown
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getStyle(): string
    {
        return $this->style;
    }

    /**
     * Задает стиль (кнопка, ссылка и т.п. см. Dropdown::STYLE_)
     * @param string $style
     * @return Dropdown
     */
    public function setStyle(string $style): Dropdown
    {
        $this->style = $style;
        return $this;
    }

    /**
     * Возвращает html-код для отображения выпадающего список действий (bootstrap5)
     * @return string
     */
    public function get(): string
    {
        if ( !$this->isAccess() || !$this->isVisible() ) {
            return '';
        }
        
        if ( empty($this->getClasses()) ) {
            $this->addClass('btn-primary');
        }
        
        $dropdownClasses = implode(' ', $this->getClasses());

        $output = '
        <div class="btn-group mb-2 ' . $dropdownClasses . '" role="group">
            <div class="btn-group" role="group">
        ';

        $btnClass = 'btn-primary';

        foreach ( $this->getButtonClasses() as $class ) {
            if ( 0 <= strpos($class, 'btn-') ) {
                $btnClass = $class;
                break;
            }
        }

        if ( self::TYPE_SPLIT === $this->getType() ) {
    
            if ( 0 < count((array)$this->getActions()?->current()?->getData()) ) {
                $arData = [];
                foreach ( $this->getActions()->current()->getData() as $dataAttr => $dataValue) {
                    if ( !str_starts_with($dataAttr, 'data-') ) {
                        $dataAttr = 'data-' . $dataAttr;
                    }
                    $arData[] = $dataAttr . '=' . $dataValue;
                }
                $data = implode(' ', $arData);
            } else {
                $data = '';
            }
    
            $output .= '
                <a
                    href="' . $this->getActions()?->current()?->getLink() . '"
                    type="button"
                    class="btn ' . $btnClass . ' ' . implode(' ', (array)$this->getActions()?->current()?->getClasses()) . '" ' .
                    $data . '
                >
                    ' . $this->getLabel() . '
                </a>
            ';
        }

        if ( self::STYLE_BUTTON === $this->getStyle() ) {
            $output .= '
                <button
                    id="' . $this->getId() . '"
                    type="button"
                    class="btn ' . implode(' ', $this->getButtonClasses()) . ' dropdown-toggle ' . ( self::TYPE_SPLIT === $this->getType() ? 'dropdown-toggle-split border-start' : '' ) . '"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    data-bs-reference="parent"
                >
                    ' . ( self::TYPE_SPLIT === $this->getType() ? '<span class="visually-hidden">Toggle Dropdown</span>' : $this->getLabel() ) . '
                </button>
            ';
        } elseif ( self::STYLE_LINK === $this->getStyle() ) {
            $output .= '
                <a
                    href="' . $this->getLink() . '"
                    class="' . implode(' ', $this->getClasses()) . '"
                    role="button"
                    id="' . $this->getId() . '"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                >
                    ' . $this->getLabel() . '
                </a>
            ';
        }
        
        $output .= '<ul class="dropdown-menu" aria-labelledby="' . $this->getId() . '">';

        foreach ( $this->getActions() as $action ) {
    
            if ( !$action->isAccess() || !$action->isVisible() ) {
                continue;
            }
    
            if ( 0 < count($action->getData()) ) {
                $arData = [];
                foreach ($action->getData() as $dataAttr => $dataValue) {
                    if ( !str_starts_with($dataAttr, 'data-') ) {
                        $dataAttr = 'data-' . $dataAttr;
                    }
                    $arData[] = $dataAttr . '=' . $dataValue;
                }
                $data = implode(' ', $arData);
            } else {
                $data = '';
            }
    
            if ( empty($action->getName()) ) {
                $output .= '<li class="border-bottom"></li>';
            } else {
                
                $action->isDisabled() && $action->addClass('disabled');
                
                $output .= '
                    <li>
                        <a
                            class="dropdown-item ' . implode(' ', $action->getClasses()) . '"
                            href="' . (!empty($action->getLink()) ? $action->getLink() : '#') . '" ' .
                            ($action->isDisabled() ? 'aria-disabled="true"' : '') .
                            $data . '
                        >' . $action->getName() . '</a>
                    </li>
                ';
            }
        }

        $output .= '
                </ul>
            </div>
        </div>
        ';

        return $output;
    }

    /**
     * @return void
     */
    public function show(): void
    {
        echo $this->get();
    }
}