# Application development using Framework

## Preamble
This project is a PHP Clean Architecture Framework. It's goal is to set up boundaries (i.e. using interfaces) for Clean Architecture development. Important: it is not a Framework like Symfony or Laravel, which gives a complete solution for application development, but rather a foundation/skeleton that helps developers structure their applications following Clean Architecture patterns.

## Task
Your task is to test this framework by creating a test application so I can see how developers may interpret a framework.

## Instructions
- Read the documentation section under `docs` directory to get the understanding of the framework
- Create the simple Blog application:
    - Simple `User` system (for posts and comments authors). Do not implement registration/authorization part
    - A blog must have a `Post` consisting of title, post content, author and post date information
    - A `Post` can have one or several `Categories`
    - A `Post` can have `Tags` which are independent `Entities`
    - A `Post` can have `Comments` with properties: author, comment, date
    - Implement UseCases on your discretion
- Do not implement UI part. Only core domain logic, UseCases and Services
- Create application under `examples` directory
- Do not overengineer the application. It is just a demo
- Do not create tests
