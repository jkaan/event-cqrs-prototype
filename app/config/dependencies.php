<?php
// DIC configuration

/** @var ContainerInterface $container */
use Doctrine\DBAL\Driver\PDOSqlite\Driver;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\SchemaException;
use Interop\Container\ContainerInterface;
use Jk\Cqrs\Domain\Aggregate\User;
use Jk\Cqrs\Domain\Command\CreateUser;
use Prooph\EventStore\Adapter\Doctrine\Schema\EventStoreSchema;
use Prooph\EventStoreBusBridge\EventPublisher;
use Prooph\EventStoreBusBridge\TransactionManager;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\Plugin\Router\CommandRouter;
use Jk\Cqrs\Domain\Repository\UserRepositoryInterface;
use Jk\Cqrs\Infrastructure\Repository\UserRepository;

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

    $router->route(CreateUser::class)
        ->to(function (CreateUser $command) use ($container) {
            /** @var UserRepositoryInterface $userRepo */
            $userRepo = $container->get('repo.user');
            $userRepo->add(User::new($command->payload()['username']));
        });

    $commandBus->utilize($router);
    // TransactionManager starts a new transaction every time a command is dispatched
    // so it can be persisted to the event store
    /** @var TransactionManager $transactionManager */
    $transactionManager = $container->get('transactionManager');
    $transactionManager->setUp($container->get('eventStore'));
    $commandBus->utilize($transactionManager);

    return $commandBus;
};

$container['transactionManager'] = function () {
    return new TransactionManager();
};

$container['repo.user'] = function (ContainerInterface $container) {
    return new UserRepository($container->get('eventStore'));
};

$container['eventStore'] = function (ContainerInterface $container) {
    $connection = DriverManager::getConnection([
        'driverClass' => Driver::class,
        'path'        => __DIR__ . '/../data/db.sqlite3'
    ]);

    try {
        $schema = $connection->getSchemaManager()->createSchema();

        EventStoreSchema::createSingleStream($schema, 'event_stream', true);

        foreach ($schema->toSql($connection->getDatabasePlatform()) as $sql) {
            $connection->exec($sql);
        }
    } catch (SchemaException $ignored) {
    }

    $eventBus = new \Prooph\ServiceBus\EventBus();
    $eventStore = new \Prooph\EventStore\EventStore(
        new \Prooph\EventStore\Adapter\Doctrine\DoctrineEventStoreAdapter(
            $connection,
            new \Prooph\Common\Messaging\FQCNMessageFactory(),
            new \Prooph\Common\Messaging\NoOpMessageConverter(),
            new \Prooph\EventStore\Adapter\PayloadSerializer\JsonPayloadSerializer()
        ),
        new \Prooph\Common\Event\ProophActionEventEmitter()
    );

    (new EventPublisher($eventBus))->setUp($eventStore);

    return $eventStore;
};

$container['action.createUser'] = function (ContainerInterface $container) {
    return new Jk\Cqrs\Infrastructure\Action\CreateAccount(
        $container->get('commandBus')
    );
};
