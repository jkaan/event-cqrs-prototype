<?php
declare(strict_types=1);

namespace Jk\Cqrs\Infrastructure\Repository;

use Jk\Cqrs\Domain\Aggregate\User;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Prooph\EventStore\Aggregate\AggregateRepository;
use Prooph\EventStore\Aggregate\AggregateType;
use Prooph\EventStore\EventStore;
use Rhumsaa\Uuid\Uuid;
use Jk\Cqrs\Domain\Repository\UserRepositoryInterface;

class UserRepository extends AggregateRepository implements UserRepositoryInterface
{

    public function __construct(EventStore $eventStore)
    {
        parent::__construct(
            $eventStore,
            AggregateType::fromAggregateRootClass(User::class),
            new AggregateTranslator(),
            null,
            null,
            true
        );
    }

    /**
     * @param User $user
     */
    public function add(User $user)
    {
        $this->addAggregateRoot($user);
    }

    /**
     * @param Uuid $uuid
     * @return User
     */
    public function get(Uuid $uuid): User
    {
        return $this->getAggregateRoot($uuid->toString());
    }
}
