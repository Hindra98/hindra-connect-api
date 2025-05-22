<?php

use Slim\App;

return function (App $app) {

  (require __DIR__ . '/routes.php')($app);
  (require __DIR__ . '/authRoutes.php')($app);
  (require __DIR__ . '/userRoutes.php')($app);
  (require __DIR__ . '/paramsRoutes.php')($app);
  (require __DIR__ . '/profileRoutes.php')($app);
  (require __DIR__ . '/categoryRoutes.php')($app);
  (require __DIR__ . '/fileRoutes.php')($app);
  (require __DIR__ . '/benefitRoutes.php')($app);
};
