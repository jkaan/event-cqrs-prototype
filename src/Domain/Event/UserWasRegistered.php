<?php
declare(strict_types=1);

namespace Jk\Cqrs\Domain\Event;

use Prooph\EventSourcing\AggregateChanged;

class UserWasRegistered extends AggregateChanged
{
    public function username(): string
    {
        return $this->payload['username'];
    }
}
