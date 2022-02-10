<?php

declare(strict_types=1);

namespace Instana\RobotShop\Ratings\Service;

use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\SDK\Trace\Tracer;
use OpenTelemetry\SemConv\TraceAttributes;
use PDO;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class HealthCheckService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var PDO
     */
    private $pdo;

    private Tracer $tracer;

    public function __construct(PDO $pdo, Tracer $tracer)
    {
        $this->pdo = $pdo;
        $this->tracer = $tracer;
    }

    public function checkConnectivity(): bool
    {
        $prepared = $this->pdo->prepare('SELECT 1 + 1 FROM DUAL;');
        $span = $this->tracer->spanBuilder($prepared->queryString)
            ->setSpanKind(SpanKind::KIND_CLIENT)
            ->setAttribute(TraceAttributes::DB_SYSTEM, 'mysql')
            ->setAttribute(TraceAttributes::DB_NAME, 'ratings')
            ->setAttribute(TraceAttributes::DB_CONNECTION_STRING, 'mysql:host=mysql;dbname=ratings;charset=utf8mb4')
            ->setAttribute(TraceAttributes::DB_OPERATION, 'SELECT')
            ->setAttribute(TraceAttributes::DB_STATEMENT, 'SELECT 1 + 1 FROM DUAL;')
            ->startSpan();
        $res = $prepared->execute();

        $span->end();

        return $res;
    }
}
