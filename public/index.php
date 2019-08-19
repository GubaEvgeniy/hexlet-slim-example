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
 *  Обработчик запросов
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


$app->run();
