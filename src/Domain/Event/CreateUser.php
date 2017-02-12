<?php
declare(strict_types=1);

namespace Jk\Cqrs\Domain\Event;

use Prooph\Common\Messaging\Command;

class CreateUser extends Command
{
    private $username;

    public function __construct(string $username)
    {
        $this->username = $username;
    }

    /**
     * Return message payload as array
     *
     * The payload should only contain scalar types and sub arrays.
     * The payload is normally passed to json_encode to persist the message or
     * push it into a message queue.
     *
     * @return array
     */
    public function payload(): array
    {
        return [
            'username' => $this->username,
        ];
    }

    /**
     * This method is called when message is instantiated named constructor fromArray
     *
     * @param array $payload
     * @return void
     */
    protected function setPayload(array $payload)
    {
        $this->username = $payload['username'];
    }
}
