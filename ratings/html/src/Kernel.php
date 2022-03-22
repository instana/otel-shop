<?php

declare(strict_types=1);

namespace Instana\RobotShop\Ratings;

use Instana\RobotShop\Ratings\Controller\HealthController;
use Instana\RobotShop\Ratings\Controller\RatingsApiController;
use Instana\RobotShop\Ratings\EventSubscriber\TracingKernelSubscriber;
use Instana\RobotShop\Ratings\Integration\InstanaHeadersLoggingProcessor;
use Instana\RobotShop\Ratings\Service\CatalogueService;
use Instana\RobotShop\Ratings\Service\HealthCheckService;
use Instana\RobotShop\Ratings\Service\RatingsService;
use Monolog\Formatter\LineFormatter;
use OpenTelemetry\SDK\Trace\Tracer;
use OpenTelemetry\Symfony\OtelSdkBundle\OtelSdkBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\RouteCollectionBuilder;

class Kernel extends BaseKernel implements EventSubscriberInterface
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new MonologBundle(),
            new OtelSdkBundle(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => 'corsResponseFilter',
        ];
    }

    public function corsResponseFilter(ResponseEvent $event)
    {
        $response = $event->getResponse();

        $response->headers->add([
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => '*',
        ]);
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader): void
    {
        $c->loadFromExtension('framework', [
            'secret' => 'S0ME_SECRET',
        ]);

        $c->loadFromExtension('monolog', [
            'handlers' => [
                'stdout' => [
                    'type' => 'stream',
                    'level' => 'info',
                    'path' => 'php://stdout',
                    'channels' => ['!request'],
                ],
            ],
        ]);

        $c->loadFromExtension('otel_sdk', [
            'resource' => [
                'attributes' => [
                    'service.name' => getenv('OTEL_SERVICE_NAME') ?:  'unknown-service',
                ]
            ],
            'trace' => [
                'sampler' => 'always_on',
                'exporters' => [
                    'otlpgrpc' => [
                        'type' => 'otlpgrpc',
                        'url' => getenv('OTEL_EXPORTER_OTLP_ENDPOINT') ?: 'localhost:4137',
                    ]
                ]
            ],
        ]);

        $c->setParameter('catalogueUrl', '%env(CATALOGUE_URL)%');
        $c->setParameter('pdo_dsn', '%env(PDO_URL)%');
        $c->setParameter('pdo_user', 'ratings');
        $c->setParameter('pdo_password', 'iloveit');
        $c->setParameter('logger.name', 'RatingsAPI');

        $c->register(InstanaHeadersLoggingProcessor::class)
            ->addTag('kernel.event_subscriber')
            ->addTag('monolog.processor');

        $c->register('monolog.formatter.instana_headers', LineFormatter::class)
            ->addArgument('[%%datetime%%] [%%extra.token%%] %%channel%%.%%level_name%%: %%message%% %%context%% %%extra%%\n');

        $c->register(Database::class)
            ->addArgument($c->getParameter('pdo_dsn'))
            ->addArgument($c->getParameter('pdo_user'))
            ->addArgument($c->getParameter('pdo_password'))
            ->addArgument(new Reference(Tracer::class))
            ->addMethodCall('setLogger', [new Reference('logger')])
            ->setAutowired(true);

        $c->register(CatalogueService::class)
            ->addArgument($c->getParameter('catalogueUrl'))
            ->addMethodCall('setLogger', [new Reference('logger')])
            ->setAutowired(true);

        $c->register(HealthCheckService::class)
            ->addArgument(new Reference('database.connection'))
            ->addMethodCall('setLogger', [new Reference('logger')])
            ->setAutowired(true);

        $c->register('database.connection', \PDO::class)
            ->setFactory([new Reference(Database::class), 'getConnection']);

        $c->setAlias(\PDO::class, 'database.connection');

        $c->register(RatingsService::class)
            ->addMethodCall('setLogger', [new Reference('logger')])
            ->setAutowired(true);

        $c->register(HealthController::class)
            ->addMethodCall('setLogger', [new Reference('logger')])
            ->addTag('controller.service_arguments')
            ->setAutowired(true);

        $c->register(RatingsApiController::class)
            ->addMethodCall('setLogger', [new Reference('logger')])
            ->addTag('controller.service_arguments')
            ->setAutowired(true);

        //$c->register(DataCenterListener::class)
        //    ->addTag('kernel.event_listener', [
        //        'event' => 'kernel.request'
        //    ])
        //    ->setAutowired(true);

        $c->register(TracingKernelSubscriber::class)
            ->addTag('kernel.event_subscriber')
            ->setAutowired(true);
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import(__DIR__.'/Controller/', 'annotation');
    }
}
