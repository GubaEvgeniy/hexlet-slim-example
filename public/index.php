<?php

// Подключение автозагрузки через composer
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;


$companies = \App\Generator::generate(100);

$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);

$app->get('/', function ($request, $response) {
    return $response->write('Welcome to Slim!');
});
/**
 *  Request handler
 */
$domains = [];
for ($i = 0; $i < 10; $i++) {
    $domains[] = $companies[$i]['name'];
}

$phones = [];
for ($i = 0; $i < 10; $i++) {
    $phones[] = $companies[$i]['phone'];
}
$app->get('/phones', function ($request, $response) use ($phones) {
    return $response->write(json_encode($phones));
});
$app->get('/domains', function ($request, $response) use ($domains) {
    return $response->write(json_encode($domains));
});

/**
 * HTTP session
 */
$app->get('/companies', function ($request, $response) use ($companies) {

    $page = $request->getQueryParam('page', 1);
    $per = $request->getQueryParam('per', 5);

    $company_per_page = array_slice($companies, $per * ($page - 1), $per);

    return $response->write(json_encode($company_per_page));
});

/**
 * Router https://www.slimframework.com/docs/v3/objects/router.html
 */
$app->get('/companies/{id}', function ($request, $response, $args) use ($companies) {

    $id = $args['id'];
    $company = collect($companies)->firstWhere('id', '=', $id);

    if (empty($company)) {
        return $response->withStatus(404)->write('Page not found');
    }

    return $response->write(json_encode($company));
});


$app->run();
