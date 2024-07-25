<?php

namespace App\Index;

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;

$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});
$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);

$app->get('/users/new', function ($request, $response) {
    return $this->get('renderer')->render($response, "../templates/users/users-new.phtml");
});

$app->post('/users', function ($request, $response) {
    $user = $request->getParsedBodyParam('user');
    $user['id'] = uniqid();
    file_put_contents('user-data', json_encode($user));
    $params = ['name' => $user['name'], 'email' => $user['email']];
    return $this->get('renderer')->render($response, "../templates/users/users.phtml", $params);
});

$app->run();