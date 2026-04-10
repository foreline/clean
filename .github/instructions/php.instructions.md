---
applyTo: '**/*.php'
---

# PHP Coding Standards

- Always start with `<?php` followed by `declare(strict_types=1);`
- Use PSR-4 namespaces matching the directory structure (`Domain\`, `Infrastructure\`, `Presentation\`)
- One class/interface/trait/enum per file
- Add PHPDoc blocks on all public and protected methods
- Use typed properties and return types — avoid `mixed` when a concrete type is possible
- Use Yoda conditions: `if ( null === $value )`
- Fluent setters must return `self`, not `static`
- Mark deprecated code with `@deprecated` PHPDoc tag
- Use `readonly` properties where appropriate (PHP 8.1+)
- Prefer constructor promotion for simple DTOs
- Import classes with `use` statements — no inline fully-qualified names
- Validate inputs at system boundaries with `webmozart/assert` or typed exceptions
- Never use `@suppress` or silence errors with `@` operator without justification
