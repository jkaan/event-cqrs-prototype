<?php
// DIC configuration

/** @var ContainerInterface $container */
use Interop\Container\ContainerInterface;
use Jk\Cqrs\Domain\Event\EchoText;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\Plugin\Router\CommandRouter;

$container = $app->getContainer();

// Twig view layer
$container['view'] = function (ContainerInterface $container) {
    $view = new \Slim\Views\Twig('src/Presentation');

    $basePath = rtrim(str_ireplace(
        'index.php',
        '',
        $container['request']->getUri()->getBasePath()),
        '/'
    );
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));

    return $view;
};

// Monolog
$container['logger'] = function (ContainerInterface $container) {
    $settings = $container->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

$container['commandBus'] = function (ContainerInterface $container) {
    $commandBus = new CommandBus();
    $router = new CommandRouter();

    $router->route(\Jk\Cqrs\Domain\Event\CreateUser::class)
        ->to(function (\Jk\Cqrs\Domain\Event\CreateUser $command) {
            echo 'User' . $command->payload()['username'] . ' has been created. Now persisting to database';
        });

    $commandBus->utilize($router);
    return $commandBus;
};

$container['action.createUser'] = function (ContainerInterface $container) {
    return new Jk\Cqrs\Infrastructure\Action\CreateAccount(
        $container->get('commandBus')
    );
};
