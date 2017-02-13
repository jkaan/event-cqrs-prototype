<?php
declare(strict_types = 1);

namespace Jk\Cqrs\Domain\Aggregate;

use Prooph\EventSourcing\AggregateRoot;
use Rhumsaa\Uuid\Uuid;
use Jk\Cqrs\Domain\Event\UserWasRegistered;

class User extends AggregateRoot
{
    private $uuid;

    private $username;

    public static function new(string $name): self
    {
        $self = new self();
        $self->recordThat(UserWasRegistered::occur(
            (string) Uuid::uuid4(),
            [
                'username' => $name
            ]
        ));

        return $self;
    }

    public function whenUserWasRegistered(UserWasRegistered $event)
    {
        $this->uuid = $event->uuid();
        $this->username = $event->username();
    }

    /**
     * @return string representation of the unique identifier of the aggregate root
     */
    protected function aggregateId(): string
    {
        return (string) $this->uuid;
    }
}
