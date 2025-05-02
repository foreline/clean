# Объекты-значения - ValueObjects

Доступные интерфейсы для объектов-значений:
- `ValueObjectInterface` - базовый интерфейс для объектов-значений
- `FloatValueObjectInterface` - с типом значения `float`
- `IntValueObjectInterface` - с типом значения `int`
- `StringValueObjectInterface` - с типом значения `string`
- `MultipleValueObjectInterface` - состоящих из других объектов-значений и скалярных типов
- `EnumValueObjectInterface` - для списочных объектов-значений, которые могут быть перечислены
- `PersistableValueObjectInterface` - для объектов-значений, которые могут быть сохранены в БД

## ValueObjectInterface
Базовый интерфейс для объектов-значений.
```php
public static function getAll(): array;
```

## MultipleValueObjectInterface
Интерфейс для объектов-значений, состоящих из других объектов-значений и скалярных типов.
Интерфейс наследует интерфейс `StringValueObjectInterface` и должен приводиться к строковому значению
(реализовать метод `__toString()`).

## PersistableValueObjectInterface
Хотя Value-Objects являются неизменяемыми и не имеют идентификатора, в некоторых случаях нам нужно где-то хранить значения.
В этом случае мы можем использовать PersistableValueObjectInterface.
```php
public function getId(): int|string;
```

## EnumValueObjectInterface
Интерфейс для объектов-значений, которые могут быть перечислены.
```php