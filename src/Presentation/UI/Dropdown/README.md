# Выпадающий список действий (dropdown).

## Концепция

Этот UI модуль реализует функциональность выпадающего списка действий (dropdown). Он состоит из трех основных компонентов:

- `Dropdown` - основной класс, который представляет собой сам выпадающий список. 
- `Action` - класс, описывающий отдельное действие в списке. 
- `ActionCollection` - коллекция действий для Dropdown.

## Примеры использования


### Создание простого Dropdown:

```php
$dropdown = (new Dropdown())
    ->setLabel("Выберите действие")
    ->addAction(new Action("Просмотр"))
    ->addAction(new Action("Редактирование"))
    ->addAction(new Action("Удаление"));

echo $dropdown->get();
```

### Настройка стилей и классов:

```php
$dropdown = (new Dropdown())
    ->setLabel("Выберите действие")
    ->addClass("btn btn-secondary")
    ->addButtonClass("btn-sm");

$action1 = new Action("Просмотр");
$action1->addClass("text-success");

$action2 = new Action("Редактирование");
$action2->addClass("text-warning");

$action3 = new Action("Удаление");
$action3->addClass("text-danger");

$dropdown
    ->addAction($action1)
    ->addAction($action2)
    ->addAction($action3);

echo $dropdown->get();
```

### Использование разделенного типа (split):

```php
$dropdown = (new Dropdown())
    ->setType(Dropdown::TYPE_SPLIT)
    ->setLabel("Выберите действие")
    ->setLink("#");

$action1 = new Action("Просмотр");
$action1->setLink("#view");

$action2 = new Action("Редактирование");
$action2->setLink("#edit");

$dropdown
    ->addAction($action1)
    ->addAction($action2);

echo $dropdown->get();
```

### Добавление дополнительных данных:

```php
$dropdown = (new Dropdown())
    ->setLabel("Выберите действие");

$action = (new Action("Просмотр"))
    ->setData([
        "data-toggle" => "modal",
        "data-target" => "#viewModal"
    ]);

$dropdown->addAction($action);

echo $dropdown->get();
```

### Управление доступностью и видимостью:

```php
$dropdown = new Dropdown();
$dropdown->setLabel("Выберите действие");

$action1 = new Action("Просмотр");
$action1->setAccess(true)->setVisible(true);

$action2 = new Action("Редактирование");
$action2->setAccess(false)->setVisible(true);

$action3 = new Action("Удаление");
$action3->setAccess(true)->setVisible(false);

$dropdown
    ->addAction($action1)
    ->addAction($action2)
    ->addAction($action3);

echo $dropdown->get();
```