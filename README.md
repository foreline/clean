# Pristine - PHP Clean Architecture framework

Core Domain, Infrastructure and Presentation Layers interfaces and classes for building Clean Architecture applications.

## Installation

composer.json:
```json
{
  "require": {
    "php": ">=8.0",
    "foreline/clean": "dev-main"
  },
  "repositories": [
    {
      "type": "git",
      "url": "https://gitlab.foreline.ru/foreline/clean.git"
    }
  ]
}
```

## Documentation
Documentation is available at [docs](./docs/index.md) section.

- [Domain Layer](./docs/domain/index.md)
- [Infrastructure Layer](./docs/infrastructure/index.md)
- [Presentation Layer](./docs/presentation/index.md)

## Release cycle
The current releases are numbered 0.x.y. When a non-breaking change is introduced (adding new methods, optimizing existing code, etc.), y is incremented.
When a breaking change is introduced, a new 0.x version cycle is always started.