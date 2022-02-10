<?php

declare(strict_types=1);

namespace Instana\RobotShop\Ratings\EventSubscriber;

use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\SDK\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\SDK\Trace\Tracer;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SemConv\TraceAttributes;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

class TracingKernelSubscriber implements EventSubscriberInterface
{
    private TracerProvider $tracerProvider;
    private Tracer $tracer;
    private ?SpanInterface $mainSpan = null;

    public function __construct(Tracer $tracer, TracerProvider $tracerProvider)
    {
        $this->tracer = $tracer;
        $this->tracerProvider = $tracerProvider;
    }

    public function onTerminateEvent(TerminateEvent $event): void
    {
        if ($this->mainSpan === null) {
            return;
        }

        $this->mainSpan->end();

        $this->tracerProvider->shutdown();
    }

    public function onKernelRequestEvent(RequestEvent $requestEvent): void
    {
        if ($requestEvent->isMainRequest() === false) {
            return;
        }

        $request = $requestEvent->getRequest();

        $carrier = TraceContextPropagator::getInstance()->extract($request->headers->all());

        // Create our main span and activate it
        $this->mainSpan = $this->tracer->spanBuilder(sprintf('%s %s', $request->getMethod(), $request->getPathInfo()))
            ->setSpanKind(SpanKind::KIND_SERVER)
            ->setParent($carrier)
            ->setAttribute(TraceAttributes::HTTP_METHOD, $request->getMethod())
            ->setAttribute(TraceAttributes::HTTP_URL, $request->getUri())
            ->setAttribute(TraceAttributes::HTTP_TARGET, $request->getPathInfo())
            ->setAttribute(TraceAttributes::HTTP_HOST, $request->getHost())
            ->setAttribute(TraceAttributes::HTTP_SCHEME, $request->getScheme())
            ->setAttribute(TraceAttributes::NET_PEER_IP, $request->getClientIp())
            ->startSpan();

        $this->mainSpan->activate();
    }

    public static function getSubscribedEvents(): array
    {
        // return the subscribed events, their methods and priorities
        // use a very low negative integer for the priority, so the listener
        // will be the last one to be called.
        return [
            KernelEvents::REQUEST => 'onKernelRequestEvent',
            KernelEvents::TERMINATE => [
                ['onTerminateEvent', -10000]
            ],
        ];
    }
}
