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
$users = ['mike', 'mishel', 'adel', 'keks', 'kamila'];

$app->get('/users', function ($request, $response, $args) use ($users) {
    $term = $request->getQueryParam('term');
    $filteredUsers = array_filter($users, fn ($user) => str_contains($user, $term));
    $params = ['users' => $filteredUsers, 'term' => $term];
    return $this->get('renderer')->render($response, '../templates/users/index.phtml', $params);
});

$app->run();