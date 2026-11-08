<?php
declare(strict_types=1);

namespace Domain\Event;

/** Публикует событие и полный набор его доставок атомарно. */
interface BatchAsyncDispatcherInterface extends AsyncDispatcherInterface
{
    /** @param list<AsyncSubscriberInterface> $subscribers */
    public function dispatchBatch(EventInterface $event, array $subscribers): void;
}
