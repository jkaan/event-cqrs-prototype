<?php
declare(strict_types = 1);

namespace Jk\Cqrs\Domain\Aggregate;

use Prooph\EventSourcing\AggregateRoot;

class User extends AggregateRoot
{
    /**
     * @return string representation of the unique identifier of the aggregate root
     */
    protected function aggregateId()
    {
        return $this->id;
    }
}
