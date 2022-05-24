<?php

declare(strict_types=1);

require __DIR__.'/vendor/autoload.php';

use Instana\RobotShop\Ratings\Kernel;
use Symfony\Component\HttpFoundation\Request;

# Turn off deprecation messages as per https://github.com/open-telemetry/opentelemetry-php/commit/99fba0d720785dfe9faba310e6e2e78fae9c075d
OpenTelemetry\SDK\Common\Dev\Compatibility\Util::setErrorLevel(0)

$env = getenv('APP_ENV') ?: 'dev';
$kernel = new Kernel($env, true);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
