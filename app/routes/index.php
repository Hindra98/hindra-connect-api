<?php

use Slim\App;

return function (App $app) {

  (require __DIR__ . '/routes.php')($app);
  (require __DIR__ . '/authRoutes.php')($app);
  (require __DIR__ . '/userRoutes.php')($app);
  (require __DIR__ . '/benefitRoutes.php')($app);
};
