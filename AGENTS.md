# Pristine Framework / foreline/clean

Файл для AI-агентов, работающих с проектом. Здесь собраны фактические сведения об архитектуре, сборке, тестировании и соглашениях. Все пути и команды указаны так, как они есть в репозитории.

---

## Обзор проекта

**Название:** Pristine Framework  
**Пакет Composer:** `foreline/clean`  
**Тип:** PHP-фреймворк для построения приложений по принципам Clean Architecture / Domain-Driven Design.  
**Лицензия:** MIT.  
**PHP:** `>=8.1`. Рабочее окружение, в котором запускались тесты, — PHP 8.2.

Фреймворк предоставляет базовые абстракции (сущности, агрегаты, объекты-значения, репозитории, сценарии использования, доменные события, DI-контейнер, планировщик и асинхронную доставку событий). Прикладной код строится поверх этих абстракций, как показано в `examples/Blog/`.

---

## Технологический стек

### Runtime / production
- **PHP** `>=8.1` с `declare(strict_types=1)` повсеместно.
- **Symfony components:**
  - `symfony/http-foundation` (>6.3)
  - `symfony/scheduler` (^7.3)
  - `symfony/messenger` (^7.4)
- `psr/log` (^2.0 || ^3.0)
- `webmozart/assert` (^1.11) — валидация на границах.
- `ramsey/uuid` (^4.8)
- `dragonmantank/cron-expression` (^3.4)
- `ext-curl`

### Разработка
- **PHPUnit** 9.6 (`phpunit/phpunit: ^9.6`)
- **PHPStan** (установлен через `*`, конфиг `phpstan.neon`, уровень `3`)
- `jetbrains/phpstorm-attributes` (опциональные аннотации)

---

## Архитектура

### Трёхслойная Clean Architecture

Код разделён на три слоя, зависимости направлены внутрь:

```
Presentation → Infrastructure → Domain
```

| Слой | Корневой namespace | Путь |
|------|-------------------|------|
| Domain | `Domain\` | `src/Domain/` |
| Infrastructure | `Infrastructure\` | `src/Infrastructure/` |
| Presentation | `Presentation\` | `src/Presentation/` |

**Правило:** классы доменного слоя не должны импортировать `Infrastructure\*` или `Presentation\*`.

### Анемичная модель + Use Cases

- Сущности (`Entity`) — простые контейнеры данных с геттерами/сеттерами.
- Агрегаты (`Aggregate`) — расширяют сущности и добавляют бизнес-правила, связи с другими агрегатами и коллекциями.
- Бизнес-логика сложных операций живёт в **Use Case** / **Manager** классах.

### Ключевые абстракции

| Абстракция | Где искать | Назначение |
|------------|-----------|------------|
| `EntityInterface` | `src/Domain/Entity/EntityInterface.php` | Контракт сущности с `getId(): null\|int\|string` |
| `AbstractEntity` | `src/Domain/Entity/AbstractEntity.php` | Базовая сущность: `id`, `name`, `slug`, `extId`, `aggregatedCount` и т.д. |
| `AggregateInterface` | `src/Domain/Aggregate/AggregateInterface.php` | Маркер агрегата, расширяет `EntityInterface` |
| `AbstractAggregate` | `src/Domain/Aggregate/AbstractAggregate.php` | Базовый абстрактный агрегат |
| `CollectionInterface` / `CollectionTrait` | `src/Domain/Aggregate/` | Типизированные коллекции агрегатов |
| `RepositoryInterface` | `src/Domain/Repository/RepositoryInterface.php` | Контракт репозитория (`find`, `findById`, `delete`, транзакции, счётчики). Метод `persist` закомментирован |
| `AbstractManager` | `src/Domain/UseCase/AbstractManager.php` | Базовый менеджер с `filter`, `sort`, `limit`, `fields`, `group` |
| `ValueObjectInterface` | `src/Domain/ValueObject/` | Базовый + типизированные интерфейсы VO (`String`, `Int`, `Float`, `Enum`, `Array`, `Mixed`) |
| `EventInterface` / `Event` | `src/Domain/Event/` | Базовое доменное событие с `occurredOn()` и текущим пользователем |
| `Publisher` | `src/Domain/Event/Publisher.php` | Singleton-подписчик/издатель. Поддерживает приоритеты, индексированную маршрутизацию, async и debounce |
| `SubscriberInterface` | `src/Domain/Event/SubscriberInterface.php` | Обработчик событий |
| `IndexedSubscriberInterface` | `src/Domain/Event/IndexedSubscriberInterface.php` | Подписчик, декларирующий события для O(1)-диспатча |
| `AsyncSubscriberInterface` | `src/Domain/Event/AsyncSubscriberInterface.php` | Асинхронный подписчик |
| `DebouncedSubscriberInterface` | `src/Domain/Event/DebouncedSubscriberInterface.php` | Подписчик с дебаунсом |
| `TaskScheduler` / `TaskRegistry` | `src/Domain/Scheduler/` | Планировщик задач на базе Symfony Scheduler |
| `Container` | `src/Infrastructure/DI/Container.php` | DI-контейнер с auto-wiring и singleton |

### Event-driven

- `Publisher::getInstance()->publish(...)` рассылает события подписчикам.
- Есть два режима подписчиков: устаревший (legacy) — линейный перебор с `isSubscribedTo()`, и индексированный — O(1) по классу события.
- Асинхронные подписчики маршрутизируются через `AsyncDispatcherInterface` → Symfony Messenger.
- Debounced-подписчики обрабатываются через `DebounceHandlerInterface`.
- Подготовлены CLI-скрипты:
  - `bin/messenger-worker.php` — consumer для async-событий (требует `--bootstrap=...`).
  - `bin/event-monitor.php` — мониторинг Event Store (требует `--bootstrap=...`).

### Планировщик

- `TaskScheduler` — singleton на основе `symfony/scheduler`.
- Задачи регистрируются через cron-выражения (`dragonmantank/cron-expression`).
- `SchedulerHelper` предоставляет отчёты и вспомогательные методы.

---

## Организация кода

```
src/
├── Domain/                 # Бизнес-логика, нет зависимостей от Infrastructure/Presentation
│   ├── Aggregate/          # AggregateInterface, AbstractAggregate, CollectionInterface, CollectionTrait, IteratorInterface, IteratorTrait
│   ├── Entity/             # EntityInterface, AbstractEntity, AbstractTrackableEntity
│   ├── Enum/
│   ├── Event/              # EventInterface, Event, Publisher, Subscriber*, Async*, Debounce*, StoredEvent, EventStore, Monitoring/*
│   ├── Events/             # Конкретные события (ExceptionOccurredEvent и др.)
│   ├── Exception/
│   ├── File/               # File aggregate, use cases, repository interface, events
│   ├── Helpers/
│   ├── Lifecycle/          # Статусы жизненного цикла, переходы
│   ├── Repository/         # RepositoryInterface, Filter/Sort/Limit/Fields/Group и их реализации
│   ├── Scheduler/          # TaskScheduler, TaskRegistry, TaskHandler, TaskInterface, TaskMessage
│   ├── Service/
│   ├── Subscriber/         # Подписчики для email/Telegram-уведомлений об ошибках/исключениях
│   ├── UseCase/            # AbstractManager, AbstractEntityPermissions, GetCollectionInterface
│   ├── User/               # User, Group, Role, Permission aggregates и value objects
│   └── ValueObject/        # Базовые и типизированные интерфейсы VO, Color, Aggregation
├── Infrastructure/         # Технические детали
│   ├── DI/                 # Container, ContainerInterface, ServiceProviderInterface, Configuration, Bridge
│   ├── Event/              # Debounce-реализации, MessengerFactory, AsyncEventMessage
│   ├── Helpers/
│   ├── Mailer/             # MailerManager, EmailMessage, MessageInterface, MailerInterface
│   └── Migration/
└── Presentation/           # Ввод/вывод
    ├── Form/               # InputCoercer — толерантное преобразование HTML-форм
    ├── Helpers/
    ├── HTTP/
    └── Response/           # ResponseMetaHelper

tests/                      # Зеркало src/ под namespace Tests\
examples/Blog/              # Пример приложения-блога
bin/                        # CLI-скрипты
```

---

## Конфигурационные файлы

| Файл | Назначение |
|------|-----------|
| `composer.json` | Зависимости, PSR-4 автозагрузка, скрипты, бинарники |
| `phpunit.xml` | Конфигурация PHPUnit 9.6: bootstrap `vendor/autoload.php`, suite `tests/`, покрытие `src/`, `forceCoversAnnotation="true"` |
| `phpstan.neon` | PHPStan уровень 3, анализ `src/`, игнорирование `\Redis`/`\Memcached` в debounce-реализациях |
| `.gitlab-ci.yml` | CI/CD: composer, syntax check, PHPStan, PHPUnit с покрытием |
| `.env.example` | Переменные окружения для уведомлений и async-событий |
| `.github/copilot-instructions.md` | Краткая сводка для Copilot |
| `.github/instructions/*.md` | Правила для PHP, тестов и документации |
| `.github/agents/*.md` | Роли агентов: architect, reviewer, test-writer |
| `.github/prompts/*.md` | Промпты для генерации сущностей, VO, тестов, ревью |

---

## Команды сборки и проверки

```bash
# Установка зависимостей
composer install

# Запуск всех тестов
vendor/bin/phpunit

# Запуск отдельного тест-класса
vendor/bin/phpunit --filter ContainerTest

# Статический анализ (скрипт composer)
composer phpstan
# эквивалентно:
vendor/bin/phpstan analyse -c phpstan.neon

# PHP syntax check (как в GitLab CI)
find . -name "*.php" -exec php -l {} \;

# Async worker (требует bootstrap-файла)
php bin/messenger-worker.php --bootstrap=path/to/bootstrap.php

# Event monitor (требует bootstrap-файла)
php bin/event-monitor.php --bootstrap=path/to/bootstrap.php dashboard
```

**Важно:** в `composer.json` зарегистрирован только скрипт `phpstan`. Скрипты `composer test` и `composer analyse`, упомянутые в `README.md`, в конфиге отсутствуют.

---

## Тестирование

### Конвенции

- Namespace: `Tests\` для всего в `tests/`.
- Класс теста: `{ClassName}Test extends PHPUnit\Framework\TestCase`.
- Методы: `test{Behavior}` в camelCase.
- Структура теста с комментариями AAA: `// Arrange`, `// Act`, `// Assert`.
- Общие фикстуры — в `setUp()`, очистка — в `tearDown()`.
- Для зависимостей по интерфейсам использовать `createMock()`.
- Предпочитать `assertSame()` вместо `assertEquals()`.
- Сообщение об ошибке передавать последним параметром ассерта.
- Покрытие класса аннотацией `@covers` обязательно, потому что в `phpunit.xml` включён `forceCoversAnnotation="true"`.

### Текущее состояние тестов

```
Tests: 181, Assertions: 262, Errors: 18, Failures: 1, Risky: 28.
```

Известные проблемы:
- **18 errors** в DI-тестах: `Interface "Psr\Container\ContainerInterface" not found`. В `composer.json` не указана зависимость `psr/container`, хотя `Infrastructure\DI\ContainerInterface` расширяет `Psr\Container\ContainerInterface`.
- **1 failure** в `Tests\Domain\Helpers\UtilsTest::testLcfirst` — тест ожидает lowercase кириллической строки, но получает исходный регистр.
- **28 risky** — отсутствуют аннотации `@covers` у многих тестов (`UtilsTest`, `GroupCollectionTest`, `UserCollectionTest`, `FileCollectionTest`, `DatesTest`, `FilterTest`).

### Покрытие

PHPUnit настроен на сбор покрытия для `src/` в `.phpunit.cache/code-coverage`.

---

## Стиль кода

Основные требования (из `.github/instructions/php.instructions.md` и фактического кода):

- Каждый файл PHP начинается с:
  ```php
  <?php
  declare(strict_types=1);
  ```
- PSR-4 неймспейсы: `Domain\`, `Infrastructure\`, `Presentation\` соответствуют директориям.
- Один класс / интерфейс / трейт / enum в файле; имя файла совпадает с именем класса.
- PHPDoc на всех `public` и `protected` методах (желательно с `@param`, `@return`, `@throws`).
- Типизированные свойства и возвращаемые типы; избегать `mixed`, если возможен конкретный тип.
- **Yoda-условия:** `if ( null === $value )`.
- Пробелы внутри скобок условий: `if ( null === $id )`.
- Fluent-сеттеры возвращают `self`, не `static` (кроме унаследованных/устаревших мест).
- Именование:
  - интерфейсы — суффикс `Interface` (`RepositoryInterface`);
  - абстрактные классы — префикс `Abstract` (`AbstractEntity`);
  - трейты — суффикс `Trait`.
- Использовать `use`-импорты, не inline FQN.
- Валидация на границах системы через `webmozart/assert` или типизированные исключения.
- Не использовать `@suppress` и `@`-оператор без обоснования.
- `readonly` свойства и constructor promotion применяются для простых DTO.
- Устаревший код помечать `@deprecated`.

---

## Безопасность

- Ввод валидируется на границах (VO, Form, Assert).
- Используются специфичные исключения (`Domain\Exception\*`, `Infrastructure\DI\Exception\*`).
- Не следует заглушать исключения пустыми `catch` без явного обоснования.
- Async-worker и event-monitor требуют `--bootstrap` — не запускают произвольный код, только загружают пользовательский bootstrap-файл.
- Подписчики уведомлений (`Domain\Subscriber\*`) читают токены и email из `$_ENV`. Для production нужно настроить `.env` на основе `.env.example`.
- Расширения `redis` и `memcached` являются опциональными runtime-зависимостями; использование защищено проверками.

---

## Известные несоответствия и нюансы

- **PHPStan:** README бейдж заявляет level 8, но `phpstan.neon` использует `level: 3`. При запуске PHPStan выдаёт ошибки (например, в `IteratorTrait`, `EntityInterface.phpDoc`, `Event.php` из-за `UserInterface`, `EventStore::$result`).
- **composer.json:** отсутствует `psr/container`, но DI-контейнер расширяет PSR-11. Из-за этого тесты DI падают с `Interface not found`.
- **phpunit.xml:** `forceCoversAnnotation="true"` делает большую часть тестов risky из-за отсутствия `@covers`.
- **README:** упоминает `composer test` и `composer analyse`, которых нет в `composer.json`.
- **GitHub Actions:** в `.github/workflows/` нет файлов; CI описан только в `.gitlab-ci.yml`. В README присутствует бейдж GitHub Actions, но workflow-файла нет.
- **`tasks.php` и `scheduler.php`** в корне пустые (0 байт).
- **`RepositoryInterface::persist` закомментирован** — реализации репозиториев не обязаны его реализовывать через интерфейс.
- **`EventStore::fetch()`** обращается к несуществующему свойству `$this->result` — есть PHPStan-ошибка и, вероятно, runtime-ошибка.

---

## Ресурсы для агентов

Перед генерацией нового кода полезно сверяться с:

- `.github/copilot-instructions.md` — краткая сводка.
- `.github/instructions/php.instructions.md` — стандарты PHP.
- `.github/instructions/tests.instructions.md` — стандарты тестов.
- `.github/instructions/docs.instructions.md` — стандарты документации.
- `.github/agents/architect.agent.md` — роль архитектора.
- `.github/agents/reviewer.agent.md` — роль ревьюера.
- `.github/agents/test-writer.agent.md` — роль тестировщика.
- `.github/prompts/create-entity.prompt.md` — как скелетить сущность.
- `.github/prompts/create-value-object.prompt.md` — как создавать VO.
- `.github/prompts/add-tests.prompt.md` — как писать тесты.
- `.agents/skills/git-commit/SKILL.md` — навык для коммитов по Conventional Commits.

---

## Быстрый старт для новой сущности

Типичная структура прикладной сущности (см. `examples/Blog/` и `docs/architecture/index.md`):

```
src/Domain/{Entity}/
├── Entity/{Entity}Entity.php          # простой DTO
├── Aggregate/{Entity}.php             # агрегат с бизнес-правилами
├── Aggregate/{Entity}Collection.php   # типизированная коллекция
├── Repository/{Entity}RepositoryInterface.php
├── UseCase/{Entity}Manager.php        # CRUD через репозиторий
├── UseCase/Create{Entity}.php         # сценарий создания
├── UseCase/{Entity}Permissions.php    # проверка прав
├── Event/{Entity}CreatedEvent.php
├── ValueObject/...
└── Exception/...
```

---

## Контакты

Автор: Simakin Dima <dima@foreline.ru>.
