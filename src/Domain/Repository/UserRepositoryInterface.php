<?php
declare(strict_types=1);

namespace Jk\Cqrs\Domain\Repository;

use Jk\Cqrs\Domain\Aggregate\User;
use Rhumsaa\Uuid\Uuid;

interface UserRepositoryInterface
{
    public function add(User $user);
    public function get(Uuid $uuid);
}
