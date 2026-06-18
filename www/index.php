<?php

use Meritum\Http\HttpKernel;
use Georgeff\Kernel\Support\Env;
use Georgeff\Kernel\Environment;
use Junction\Api\ModuleRepository;

require_once __DIR__ . '/../vendor/autoload.php';

$environment = match (Env::get('APP_ENV', 'production')) {
    'local'       => Environment::Local,
    'test',
    'testing'     => Environment::Testing,
    'dev',
    'develop',
    'development' => Environment::Development,
    'stage',
    'staging'     => Environment::Staging,
    default       => Environment::Production
};

$kernel = new HttpKernel($environment, debug: Env::get('APP_DEBUG', false));

$kernel->addRepository(new ModuleRepository());

$kernel->boot();

$kernel->run();
