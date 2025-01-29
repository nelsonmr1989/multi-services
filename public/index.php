<?php

use App\Kernel;

require_once dirname(__DIR__) . '/vendor/autoload_runtime.php';

return function (array $context) {
    $e= 1000;
    xdebug_info();
    xdebug_break();
//    var_dump('test');
//    die();
    phpinfo();
    die();
    $a = 200;
    $b = 300;
    return new Kernel($context['APP_ENV'], (bool)$context['APP_DEBUG']);
};
