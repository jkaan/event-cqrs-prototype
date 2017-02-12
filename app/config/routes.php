<?php
// Routes

$container = $app->getContainer();
$commandBus = $container->get('commandBus');

$app->get('/', function ($request, $response, $args) {
    return $this->view->render($response, 'index.twig');
});

$app->post('/createUser', 'action.createUser');
