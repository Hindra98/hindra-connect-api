<?php


require __DIR__ . '/vendor/autoload.php';

use App\Middlewares\CorsMiddleware;
use Slim\Factory\AppFactory;


$app ??= AppFactory::create();
// Appliquer le middleware CORS globalement
$app->add(new CorsMiddleware());
$app->setBasePath("/api-exchange");

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

(require __DIR__ . '/app/routes/index.php')($app);
require __DIR__ . '/app/core/constants/auth-constants.php';

// Erreur Middleware (A supprimer)
// $errorMiddleware = $app->addErrorMiddleware(true, true, true);
// $app->add(new RateLimitMiddleware(['requests_per_minute' => 60,]));

$app->run();
