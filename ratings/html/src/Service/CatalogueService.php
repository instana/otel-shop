<?php

declare(strict_types=1);

namespace Instana\RobotShop\Ratings\Service;

use Exception;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\SDK\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\SDK\Trace\Tracer;
use OpenTelemetry\SemConv\TraceAttributes;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class CatalogueService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var string
     */
    private $catalogueUrl;

    private Tracer $tracer;

    public function __construct(string $catalogueUrl, Tracer $tracer)
    {
        $this->catalogueUrl = $catalogueUrl;
        $this->tracer = $tracer;
    }

    public function checkSKU(string $sku): bool
    {
        $url = sprintf('%s/product/%s', $this->catalogueUrl, $sku);
        $span = $this->startSpan('GET', $url);

        $carrier = [];
        TraceContextPropagator::getInstance()->inject($carrier);

        $opt = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => $carrier,
        ];

        $curl = curl_init($url);
        curl_setopt_array($curl, $opt);

        $data = curl_exec($curl);
        if (!$data) {
            $this->logger->error('failed to connect to catalogue');
            $exception = new Exception('Failed to connect to catalogue');
            $span->recordException($exception);
            throw $exception;
        }

        $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $this->logger->info("catalogue status $status");

        $span->setAttribute(TraceAttributes::HTTP_STATUS_CODE, $status);
        $span->end();

        curl_close($curl);

        return 200 === $status;
    }

    private function startSpan(string $method, string $url)
    {
        return $this->tracer->spanBuilder(sprintf('%s %s', $method, $url))
            ->setSpanKind(SpanKind::KIND_CLIENT)
            ->setAttribute(TraceAttributes::HTTP_URL, $url)
            ->setAttribute(TraceAttributes::HTTP_METHOD, $method)
            ->startSpan();
    }
}
