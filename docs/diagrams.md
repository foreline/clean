# Диаграммы архитектуры

> Визуальное представление архитектуры Pristine Framework

## 📋 Содержание

- [Общая архитектура](#общая-архитектура)
- [Доменный слой](#доменный-слой)
- [Поток данных](#поток-данных)
- [Жизненный цикл запроса](#жизненный-цикл-запроса)
- [Система событий](#система-событий)
- [Структура проекта](#структура-проекта)

---

## 🏗️ Общая архитектура

### Clean Architecture слои

```mermaid
graph TB
    subgraph "🎨 Presentation Layer"
        C[Controllers]
        V[Views]
        M[Middleware]
    end
    
    subgraph "💼 Application Layer"
        UC[Use Cases]
        S[Services]
        I[Interfaces]
    end
    
    subgraph "🏛️ Domain Layer"
        E[Entities]
        A[Aggregates]
        VO[Value Objects]
        R[Repository Interfaces]
        EV[Domain Events]
    end
    
    subgraph "🔧 Infrastructure Layer"
        DB[Database]
        RI[Repository Implementations]
        EXT[External Services]
        Q[Queues]
    end
    
    C --> UC
    UC --> A
    UC --> R
    R --> RI
    RI --> DB
    UC --> EV
    EV --> Q
    
    style C fill:#e1f5fe
    style UC fill:#f3e5f5
    style A fill:#e8f5e8
    style RI fill:#fff3e0
```

### Зависимости между слоями

```mermaid
graph LR
    P[Presentation] --> A[Application]
    A --> D[Domain]
    I[Infrastructure] --> D
    I --> A
    
    style P fill:#e1f5fe
    style A fill:#f3e5f5
    style D fill:#e8f5e8
    style I fill:#fff3e0
```

## 🏛️ Доменный слой

### Структура доменных объектов

```mermaid
classDiagram
    class EntityInterface {
        +getId() int|null
        +setId(id: int) self
    }
    
    class AggregateInterface {
        <<interface>>
    }
    
    class ValueObjectInterface {
        +equals(other: ValueObjectInterface) bool
    }
    
    class CollectionInterface {
        +addItem(item: object) self
        +removeItem(item: object) self
        +contains(item: object) bool
        +count() int
    }
    
    class User {
        -id: int
        -email: Email
        -name: string
        -roles: RoleCollection
        +setEmail(email: Email)
        +addRole(role: Role)
    }
    
    class Email {
        -value: string
        +__construct(value: string)
        +getValue() string
        +equals(other: ValueObjectInterface) bool
    }
    
    class Post {
        -id: int
        -title: string
        -content: string
        -comments: CommentCollection
        -status: PostStatus
        +addComment(comment: Comment)
        +publish()
    }
    
    EntityInterface <|-- User
    AggregateInterface <|-- User
    AggregateInterface <|-- Post
    ValueObjectInterface <|-- Email
    ValueObjectInterface <|-- PostStatus
    
    User --> Email
    User --> RoleCollection
    Post --> CommentCollection
    Post --> PostStatus
```

### Value Objects иерархия

```mermaid
graph TD
    VO[ValueObjectInterface] --> SVO[StringValueObjectInterface]
    VO --> IVO[IntValueObjectInterface]
    VO --> FVO[FloatValueObjectInterface]
    VO --> EVO[EnumValueObjectInterface]
    VO --> MVO[MixedValueObjectInterface]
    
    SVO --> Email
    SVO --> PhoneNumber
    SVO --> Address
    
    IVO --> UserId
    IVO --> Quantity
    
    FVO --> Price
    FVO --> Weight
    
    EVO --> PostStatus
    EVO --> UserRole
    EVO --> OrderStatus
    
    MVO --> Coordinates
    MVO --> FullName
    
    style VO fill:#e8f5e8
    style SVO fill:#f0f8e8
    style IVO fill:#f0f8e8
    style FVO fill:#f0f8e8
    style EVO fill:#f0f8e8
    style MVO fill:#f0f8e8
```

## 🔄 Поток данных

### Типичный запрос

```mermaid
sequenceDiagram
    participant C as Controller
    participant UC as Use Case
    participant A as Aggregate
    participant R as Repository
    participant DB as Database
    participant E as Event Publisher
    
    C->>UC: execute(data)
    UC->>R: findById(id)
    R->>DB: SELECT query
    DB-->>R: raw data
    R-->>UC: Aggregate
    UC->>A: businessMethod()
    A-->>UC: updated state
    UC->>R: persist(aggregate)
    R->>DB: INSERT/UPDATE
    UC->>E: publish(event)
    E-->>UC: event published
    UC-->>C: result
```

### CRUD операции

```mermaid
graph TD
    subgraph "Create"
        C1[Controller] --> UC1[CreateUseCase]
        UC1 --> VAL1[Validation]
        VAL1 --> AGG1[Create Aggregate]
        AGG1 --> REP1[Repository.persist]
        REP1 --> EVT1[Publish Event]
    end
    
    subgraph "Read"
        C2[Controller] --> UC2[ReadUseCase]
        UC2 --> REP2[Repository.find]
        REP2 --> MAP2[Map to DTO]
    end
    
    subgraph "Update"
        C3[Controller] --> UC3[UpdateUseCase]
        UC3 --> REP3[Repository.find]
        REP3 --> UPD3[Update Aggregate]
        UPD3 --> REP4[Repository.persist]
        REP4 --> EVT3[Publish Event]
    end
    
    subgraph "Delete"
        C4[Controller] --> UC4[DeleteUseCase]
        UC4 --> REP5[Repository.find]
        REP5 --> VAL4[Validate Deletion]
        VAL4 --> REP6[Repository.delete]
        REP6 --> EVT4[Publish Event]
    end
```

## 🚀 Жизненный цикл запроса

```mermaid
flowchart TD
    Start([HTTP Request]) --> MW[Middleware]
    MW --> R[Routing]
    R --> C[Controller]
    C --> V[Validation]
    V --> UC[Use Case]
    UC --> BR[Business Rules]
    BR --> RP[Repository]
    RP --> DB[(Database)]
    DB --> RP
    RP --> UC
    UC --> EV[Events]
    EV --> Q[Queue]
    UC --> C
    C --> RES[Response]
    RES --> End([HTTP Response])
    
    subgraph "Domain Layer"
        BR
        EV
    end
    
    subgraph "Infrastructure Layer"
        RP
        DB
        Q
    end
    
    style Start fill:#e1f5fe
    style End fill:#e1f5fe
    style UC fill:#f3e5f5
    style BR fill:#e8f5e8
    style RP fill:#fff3e0
```

## 🔔 Система событий

### Event-Driven архитектура

```mermaid
graph TB
    subgraph "Event Sources"
        UC1[User Use Case]
        UC2[Order Use Case]
        UC3[Product Use Case]
    end
    
    subgraph "Event Bus"
        P[Publisher]
        D[Dispatcher]
    end
    
    subgraph "Event Handlers"
        EH1[Email Handler]
        EH2[Notification Handler]
        EH3[Analytics Handler]
        EH4[Audit Handler]
    end
    
    subgraph "External Systems"
        EMAIL[Email Service]
        SMS[SMS Service]
        ANALYTICS[Analytics]
        LOG[Audit Log]
    end
    
    UC1 --> P
    UC2 --> P
    UC3 --> P
    
    P --> D
    
    D --> EH1
    D --> EH2
    D --> EH3
    D --> EH4
    
    EH1 --> EMAIL
    EH2 --> SMS
    EH3 --> ANALYTICS
    EH4 --> LOG
    
    style P fill:#f3e5f5
    style D fill:#f3e5f5
```

### Асинхронная обработка

```mermaid
sequenceDiagram
    participant UC as Use Case
    participant P as Publisher
    participant Q as Queue
    participant W as Worker
    participant H as Handler
    participant ES as External Service
    
    UC->>P: publish(event)
    P->>Q: push to queue
    P-->>UC: confirmed
    
    Note over Q,W: Асинхронно
    
    Q->>W: pop event
    W->>H: handle(event)
    H->>ES: external call
    ES-->>H: response
    H-->>W: completed
    W-->>Q: ack
```

## 📁 Структура проекта

```mermaid
graph TD
    ROOT[Project Root] --> SRC[src/]
    ROOT --> DOCS[docs/]
    ROOT --> TESTS[tests/]
    ROOT --> VENDOR[vendor/]
    
    SRC --> DOMAIN[Domain/]
    SRC --> INFRA[Infrastructure/]
    SRC --> PRES[Presentation/]
    
    DOMAIN --> ENTITIES[Entity/]
    DOMAIN --> AGGREGATES[Aggregate/]
    DOMAIN --> VALUES[ValueObject/]
    DOMAIN --> REPOS[Repository/]
    DOMAIN --> EVENTS[Event/]
    DOMAIN --> SERVICES[Service/]
    DOMAIN --> USECASES[UseCase/]
    DOMAIN --> USERS[User/]
    
    INFRA --> DI[DI/]
    INFRA --> REPO_IMPL[Repository/]
    INFRA --> MAIL[Mailer/]
    INFRA --> MIGRATION[Migration/]
    
    PRES --> HTTP[HTTP/]
    PRES --> RESPONSE[Response/]
    PRES --> HELPERS[Helpers/]
    
    TESTS --> UNIT[Unit/]
    TESTS --> INTEGRATION[Integration/]
    TESTS --> FEATURE[Feature/]
    
    style DOMAIN fill:#e8f5e8
    style INFRA fill:#fff3e0
    style PRES fill:#e1f5fe
    style TESTS fill:#f3e5f5
```

## 🔗 Взаимодействие компонентов

### Dependency Injection

```mermaid
graph TD
    subgraph "Application Bootstrap"
        CONTAINER[DI Container]
        PROVIDERS[Service Providers]
    end
    
    subgraph "Controllers"
        CTRL1[UserController]
        CTRL2[ProductController]
    end
    
    subgraph "Use Cases"
        UC1[CreateUser]
        UC2[CreateProduct]
    end
    
    subgraph "Repositories"
        REPO1[UserRepository]
        REPO2[ProductRepository]
    end
    
    subgraph "Infrastructure"
        DB[Database]
        MAILER[Email Service]
        QUEUE[Queue Service]
    end
    
    PROVIDERS --> CONTAINER
    CONTAINER --> CTRL1
    CONTAINER --> CTRL2
    
    CTRL1 --> UC1
    CTRL2 --> UC2
    
    UC1 --> REPO1
    UC1 --> MAILER
    UC2 --> REPO2
    UC2 --> QUEUE
    
    REPO1 --> DB
    REPO2 --> DB
    
    style CONTAINER fill:#f3e5f5
    style PROVIDERS fill:#f3e5f5
```

### Repository Pattern

```mermaid
classDiagram
    direction TB
    
    class UserRepositoryInterface {
        <<interface>>
        +persist(user: User) User
        +findById(id: int) User
        +findByEmail(email: Email) User
        +delete(user: User) void
    }
    
    class DoctrineUserRepository {
        -entityManager: EntityManager
        +persist(user: User) User
        +findById(id: int) User
        +findByEmail(email: Email) User
        +delete(user: User) void
    }
    
    class EloquentUserRepository {
        +persist(user: User) User
        +findById(id: int) User
        +findByEmail(email: Email) User
        +delete(user: User) void
    }
    
    class InMemoryUserRepository {
        -users: array
        +persist(user: User) User
        +findById(id: int) User
        +findByEmail(email: Email) User
        +delete(user: User) void
    }
    
    UserRepositoryInterface <|-- DoctrineUserRepository
    UserRepositoryInterface <|-- EloquentUserRepository
    UserRepositoryInterface <|-- InMemoryUserRepository
    
    class CreateUser {
        -repository: UserRepositoryInterface
        +execute(data: array) User
    }
    
    CreateUser --> UserRepositoryInterface
```

---

## 📊 Диаграммы для копирования

Все диаграммы выше написаны в формате Mermaid и могут быть легко вставлены в:

- **GitHub/GitLab README** - поддерживают Mermaid нативно
- **Notion** - через блок Mermaid
- **Obsidian** - с плагином Mermaid
- **VS Code** - с расширением Mermaid Preview

### Пример использования в Markdown:

```markdown
```mermaid
graph TB
    A[Start] --> B[Process]
    B --> C[End]
```

### Интерактивные диаграммы

Для более интерактивного опыта можно использовать:

1. **Mermaid Live Editor**: https://mermaid.live/
2. **Draw.io**: https://app.diagrams.net/
3. **PlantUML**: https://plantuml.com/

### Экспорт в другие форматы

```bash
# Установка Mermaid CLI
npm install -g @mermaid-js/mermaid-cli

# Экспорт в PNG
mmdc -i diagram.mmd -o diagram.png

# Экспорт в SVG
mmdc -i diagram.mmd -o diagram.svg

# Экспорт в PDF
mmdc -i diagram.mmd -o diagram.pdf
```
