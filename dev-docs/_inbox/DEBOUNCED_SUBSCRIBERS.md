# Debounced Subscribers proposal

This document outlines a proposal for implementing debounced subscribers in the context of event handling. The goal is to prevent multiple rapid-fire events from triggering the subscriber function multiple times, which can lead to performance issues or unintended consequences.

## Problem Statement
In event-driven architectures, it's common for certain events to be emitted in quick succession. This can lead to a situation where a subscriber function is called multiple times in a short period, which may not be desirable.

## Scenarios
### Badge Notification updates
When some batch operation is performed, it may trigger multiple events that update the badge notification count in short period of time. But the UI only needs to update the badge count once after all events have been processed, instead of updating it multiple times for each event. There is absolutly no need to update the badge count 100 times per second, and it can lead to performance issues and a bad user experience. Neither we need to count badge notifications 100 times per second. Instead we can debounce the subscriber function that updates the badge count, so that it only runs once after a certain period of time has passed since the last event was emitted. This way, we can ensure that the badge count is updated efficiently without overwhelming the system or the user interface. Also badge notifications are not expected to be updated in real time, so a small delay of a few seconds is acceptable.

## Implementation details
Due to PHP nature we need to implement a reliable way to debounce subscribers accross multiple independent requests, while maintaining the performance and scalability of the system. So we think that inmemory debouncing is not a good solution, and we need to implement a more robust solution that can work across multiple requests. We are thinking about storing the debouncing state in a shared storage, such as Redis, Memcached, and regular files. The storage should be configurable via `.env` file, so that it can be easily switched between different storage options.
We already have multiple Interfaces for different types of Subscribers, e.g. `AsyncSubscriberInterface`, `IndexedSubscriberInterface`, `RetryableSubscriberInterface`, so we can create a new `DebouncedSubscriberInterface` that extends the base `SubscriberInterface` and adds the necessary methods for debouncing. The implementation of the debouncing logic can be handled in a separate class, such as `DebouncedSubscriberHandler`, which will be responsible for managing the debouncing state and ensuring that the subscriber function is called only once after the specified debounce period. The debounce period should be configurable per subscriber, so that it can be adjusted based on the specific use case.

## References
- (Domain Events)[`src\Domain\Event`]

## Tasks
- [x] Get a good understanding of the Event system and how subscribers are currently managed.
- [x] Design the `DebouncedSubscriberInterface` and the `DebouncedSubscriberHandler` classes.
- [x] Implement the debouncing logic using different storage options (Redis, Memcached, files) and make it configurable via `.env` file.
- [x] Write tests to ensure that the debouncing logic works correctly and that the subscriber function is called only once after the specified debounce period.
- [x] Update documentation to include information about the new `DebouncedSubscriberInterface` and how to use it in the context of event handling.

## Status: Implemented

Delivered components:

- `Domain\Event\DebouncedSubscriberInterface` — subscriber contract (`getDebounceKey()`, `getDebounceMilliseconds()`, `getMaxWaitMilliseconds()`).
- `Domain\Event\DebounceHandlerInterface` + `Domain\Event\DebouncedSubscriberHandler` — scheduling and `flush()` of deferred invocations.
- `Domain\Event\DebounceStorageInterface` + `Domain\Event\DebounceState` — transport-agnostic storage abstraction.
- `Infrastructure\Event\Debounce\{FileDebounceStorage, RedisDebounceStorage, MemcachedDebounceStorage, DebounceStorageFactory}` — backends configurable via `EVENT_DEBOUNCE_STORAGE`.
- `Publisher::setDebounceHandler()` integration (synchronous fallback when unset).
- Tests under `tests/Domain/Event/` and `tests/Infrastructure/Event/Debounce/`.
- Documentation: `docs/domain/event/debounced-subscribers.md`.