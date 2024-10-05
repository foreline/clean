## Доменный слой / Domain Layer

### Сущности / Entities

Используется модель анемичных сущностей (Anemic Entities). За поведение отвечают сервисы.

### Агрегаты / Aggregate Root

Содержит в себе наборы вложенных сущностей и объектов-значений.

### Репозитории / Repositories

Внешний объект хранилища, который сохраняет сущности. 
Репозиторий это слой, находящийся между доменным слоем и инфраструктурным слоем базы данных.
В доменном слое находятся только интерфейсы репозиториев. Сами репозитории находятся в Инфраструктурном слое.

### Сервисы / Service

Доменные сервисы. Содержат в себе бизнес логику приложения.

### Сервисы / UseCase

Простые сервисы для сохранения и извлечения сущностей из базы данных.

### Доменные события / Domain Events

События это объекты, созданные и инициализируемые (raised) из сущности, либо из сервиса.
Domain events can make the system more scalable and avoid any coupling - one aggregate should not determine what the other aggregates should do, and temporal coupling - the successful completion of payment doesn't depend on all the processes to be available at the same time.

### Объекты-значения / ValueObjects

VOs have no conceptual identity. 
That doesn't mean that they shouldn't have persistence identity. 
Don't let persistence implementation cloud your understanding of Entities vs VOs.

Value objects are accessible by their value rather than identity. They are immutable objects. Their values don't change (or change rarely) and have no lifecycle.
- не имеет идентификатора
- не сохраняется никуда отдельно
- не содержит сеттеров, задается через конструктор.
  
Например, статус у какого-либо объекта, валюты, страны, даты.

Предоставляются интерфейсы для следующих видов Объектов-значений:
- `FloatValueObjectInterface` - для VO с типом значения `float`
- `IntValueObjectInterface` - для VO с типом значения `int`
- `StringValueObjectInterface` - для VO с типом значения `string`
