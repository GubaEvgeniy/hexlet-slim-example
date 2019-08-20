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
$repo = new App\Repository();

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

$app->get('/courses', function ($request, $response) use ($repo) {
    $params = [
        'courses' => $repo->all()
    ];
    return $this->get('renderer')->render($response, 'courses/index.phtml', $params);
});

$app->get('/courses/new', function ($request, $response) use ($repo) {
    $params = [
        'course' => [],
        'errors' => []
    ];
    return $this->get('renderer')->render($response, 'courses/new.phtml', $params);
});

$app->post('/courses', function ($request, $response) use ($repo) {
    $course = $request->getParsedBodyParam('course');

    $validator = new App\Validator();
    $errors = $validator->validate($course);

    if (count($errors) === 0) {
        $repo->save($course);
        return $response->withHeader('Location', '/courses')
            ->withStatus(302);
    }

    $params = [
        'course' => $course,
        'errors' => $errors
    ];

    return $this->get('renderer')->render($response->withStatus(422), 'courses/new.phtml', $params);
});

$app->run();
