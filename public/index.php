<?php

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Slim\Middleware\MethodOverrideMiddleware;
use DI\Container;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use App\Validator;

session_start();
$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});
$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});
$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);
$app->add(MethodOverrideMiddleware::class);
$router = $app->getRouteCollector()->getRouteParser();
$validator = new Validator();

$app->get('/', function ($req, $res) {
    $flash = $this->get('flash')->getMessages();
    $params = ['flash' => $flash];

    return $this->get('renderer')->render($res, 'index.phtml', $params);
})->setName('root');

$app->post('/', function ($req, $res) use ($router) {
    $users = $_SESSION['users'] ?? [];
    $user = $req->getParsedBodyParam('user');
    $collection = collect($users)->where('email', $user['email']);
    if (count($collection) > 0) {
        $this->get('flash')->addMessage('success', "Вхід виконано успішно");
        $_SESSION['login'] = true;

        return $res->withRedirect($router->urlFor('users'));
    }
    $this->get('flash')->addMessage('error', "Користувача з таким email не знайдено");

    return $res->withRedirect($router->urlFor('root'));
    
})->setName('login');

$app->get('/users', function ($req, $res) use ($router) {
    $users = $_SESSION['users'] ?? [];
    if ($_SESSION['login'] !== true) {
        $this->get('flash')->addMessage('error', "Авторизутесь");

        return $res->withStatus(401)->withRedirect($router->urlFor('root'));
    }
    $search = $req->getQueryParam('search');
    $filterdUsers = array_filter($users, fn($user) => str_contains($user['name'], $search));
    $flash = $this->get('flash')->getMessages();
    $params = ['users' => $filterdUsers, 'search' => $search, 'flash' => $flash];

    return $this->get('renderer')->render($res, 'users/users.phtml', $params);
})->setname('users');

$app->get('/users/new', function ($req, $res) use ($router) {
    $params = ['user' => ['name' => '', 'email' => '', 'id' => ''], 'errors' => []];

    return $this->get('renderer')->render($res, 'users/newUser.phtml', $params);
})->setName('newUser');

$app->post('/users', function ($req, $res) use ($router, $validator) {
    $users = $_SESSION['users'] ?? [];
    $user = $req->getParsedBodyParam('user');
    $usersCount = count($users);
    $usersKeys = array_keys($users);
    $id = $usersCount > 0 ? $usersKeys[$usersCount - 1] + 1 : 1;
    $user['id'] = $id;
    $users[$id] = $user;
    $errors = $validator->validate($user);
    if (count($errors) === 0) {
        $_SESSION['users'] = $users;
        $this->get('flash')->addMessage('success', "Новий користувач доданий успішно");

        return $res->withRedirect($router->urlFor('users'), 302);
    }
    $params = ['user' => $user, 'errors' => $errors];
    $res = $res->withStatus(422);

    return $this->get('renderer')->render($res, 'users/newUser.phtml', $params);

})->setName('saveNewUser');

$app->get('/users/{id}', function ($req, $res, $args) use ($router) {
    if ($_SESSION['login'] !== true) {
        $this->get('flash')->addMessage('error', "Авторизуйтесь");

        return $res->withStatus(401)->withRedirect($router->urlFor('root'));
    }
    $id = $args['id'];
    $users = $_SESSION['users'] ?? [];
    $user = collect($users)->where('id', $id)->all();
    $flash = $this->get('flash')->getMessages();
    $params = ['user' => $user, 'flash' => $flash];
    if (isset($user[$id]['id'])) {
        return $this->get('renderer')->render($res, 'users/user.phtml', $params);
    }
    $this->get('flash')->addMessage('error', "Користувач з id: {$id} не існує");
    $res = $res->withStatus(404);

    return $res->withRedirect($router->urlFor('users'));
})->setName('user');

$app->patch('/users/{id}', function ($req, $res, $args) use ($router, $validator) {
    $id = $args['id'];
    $users = $_SESSION['users'] ?? [];
    $user = $users[$id];
    $data = $req->getParsedBodyParam('user');
    $errors = $validator->validate($data);
    if (count($errors) === 0) {
        $user['name'] = $data['name'] !== '' ? $data['name'] : $user['name'];
        $user['email'] = $data['email'] !== '' ? $data['email'] : $user['email'];
        $users[$id] = $user;
        $this->get('flash')->addMessage('success', 'Дані користувача оновлено успішно');
        $_SESSION['users'] = $users;

        return $res->withRedirect($router->urlFor('user', ['id' => $id]));
    }
    $params = ['user' => $user, 'errors' => $errors];
    $res = $res->withStatus(422);

    return $this->get('renderer')->render($res, 'users/editUser.phtml', $params);
});

$app->get('/users/{id}/edit', function ($req, $res, $args) use ($router) {
    if ($_SESSION['login'] !== true) {
        $this->get('flash')->addMessage('error', "Авторизуйтесь");

        return $res->withStatus(401)->withRedirect($router->urlFor('root'));
    }
    $id = $args['id'];
    $users = $_SESSION['users'] ?? [];
    $user = $users[$id];
    $params = ['user' => $user];

    return $this->get('renderer')->render($res, 'users/editUser.phtml', $params);
});

$app->delete('/users/{id}', function ($req, $res, $args) use ($router) {
    $users = $_SESSION['users'] ?? [];
    unset($users[$args['id']]);
    $this->get('flash')->addMessage('success', 'Користувач видалений успішно');
    $_SESSION['users'] = $users;

    return $res->withRedirect($router->urlFor('users'));
});

$app->post('/logout', function ($req, $res) use ($router) {
    $_SESSION['login'] = false;
    return $res->withRedirect($router->urlFor('root'));
});

$app->run();
