<?php
declare(strict_types=1);

namespace Jk\Cqrs\Infrastructure\Action;

use Assert\Assertion;
use Prooph\ServiceBus\CommandBus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Jk\Cqrs\Domain\Command\CreateUser;

class CreateAccount implements ActionInterface
{
    /** @var CommandBus */
    private $commandBus;

    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $body = $request->getParsedBody();
        Assertion::keyExists($body, 'username');

        $createUserCommand = new CreateUser($body['username']);
        $this->commandBus->dispatch($createUserCommand);

        return $response->withHeader('Location', '/');
    }
}
