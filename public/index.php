<?php

use function Stringy\create as s;
use Slim\Factory\AppFactory;
use DI\Container;
use Slim\Views\PhpRenderer;

// Подключение автозагрузки через composer
require __DIR__ . '/../vendor/autoload.php';

$container = new Container();
$container->set('renderer', function () {
    // Параметром передается базовая директория в которой будут храниться шаблоны
    return new PhpRenderer(__DIR__ . '/../templates');
});

AppFactory::setContainer($container);
$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);

$users = App\Generator::generateUsers(100);

$app->get('/', function ($request, $response, $args) {
    $title = 'Добро пожаловать';
    $params = [
        'title' => $title
    ];
    return $this->get('renderer')->render($response, 'homepage.phtml', $params);
});

$app->get('/users', function ($request, $response) use ($users) {

    $term = $request->getQueryParam('term');

    if (!$term) {
        $userResult = $users;
    } else {
        $userResult = array_filter($users, function ($user) use ($term) {
            return s($user['firstName'])->contains($term, false);
        });
    }

    $args = [
        'users' => $userResult,
        'term' => $term
    ];

    return $this->get('renderer')->render($response, 'users/index.phtml', $args);
});

$app->get('/users/{id}', function ($request, $response, $args) use ($users) {

    $user = collect($users)->firstWhere('id', '=', $args['id']);

    $params = [
        'user' => $user
    ];

    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
});

$app->run();
