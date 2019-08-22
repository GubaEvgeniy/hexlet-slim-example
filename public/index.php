<?php

use Slim\Flash\Messages;
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
$container->set('flash', function () {
    return new Messages();
});


AppFactory::setContainer($container);
$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);
$router = $app->getRouteCollector()->getRouteParser();


$users = App\Generator::generateUsers(100);
$posts = App\Generator::generatePosts(100);

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

/**
 * CREATE POSTS
 */
$app->get('/posts/new', function ($request, $response) {
    $params = [
        'postsData' => [],
        'errors' => []
    ];
    return $this->get('renderer')->render($response, 'posts/new.phtml', $params);
})->setName('newPosts');

$app->post('/posts', function ($request, $response) use ($router, $repo) {
    // Извлекаем данные формы
    $postsData = $request->getParsedBodyParam('post');

    $validator = new App\Validator();
    // Проверяем корректность данных
    $errors = $validator->validate($postsData);

    if (count($errors) === 0) {
        // Если данные корректны, то сохраняем, добавляем флеш и выполняем редирект
        $repo->savePost($postsData);
        $this->get('flash')->addMessage('success', 'Post has been created');
        // Обратите внимание на использование именованного роутинга
        $url = $router->urlFor('posts');

        return $response->withRedirect($url);
    }

    $params = [
        'postsData' => $postsData,
        'errors' => $errors
    ];

    // Если возникли ошибки, то устанавливаем код ответа в 422 и рендерим форму с указанием ошибок
    $response = $response->withStatus(422);
    return $this->get('renderer')->render($response, 'posts/new.phtml', $params);
});

/**
 * READ POSTS
 */

$app->get('/posts', function ($request, $response) use ($repo) {
    $flash = $this->get('flash')->getMessages();



    $per = 5;
    $page = $request->getQueryParams()['page'] ?? 1;
    $offset = ($page - 1) * $per;

    $sliceOfPosts = array_slice($repo->all()[0], $offset, $per);
    $params = [
        'flash' => $flash,
        'page' => $page,
        'posts' => $sliceOfPosts
    ];
    return $this->get('renderer')->render($response, 'posts/index.phtml', $params);
})->setName('posts');

$app->get('/posts/{id}', function ($request, $response, array $args) use ($repo) {
    $id = $args['id'];
    $post = collect($repo)->firstWhere('slug', $id);
    if (!$post) {
        return $response->withStatus(404)->write('Page not found');
    }
    $params = [
        'post' => $post,
    ];
    return $this->get('renderer')->render($response, 'posts/show.phtml', $params);
})->setName('post');





$app->run();
