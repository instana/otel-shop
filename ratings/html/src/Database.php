<?php

declare(strict_types=1);

namespace Instana\RobotShop\Ratings;

use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\SDK\Trace\Tracer;
use OpenTelemetry\SemConv\TraceAttributes;
use PDO;
use PDOException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class Database implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var string
     */
    private $dsn;

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $password;

    private Tracer $tracer;

    public function __construct(string $dsn, string $user, string $password, Tracer $tracer)
    {
        $this->dsn = $dsn;
        $this->user = $user;
        $this->password = $password;
        $this->tracer = $tracer;
    }

    public function getConnection(): ?PDO
    {
        $opt = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];


        $span = $this->tracer->spanBuilder('CONNECT')
            ->setSpanKind(SpanKind::KIND_CLIENT)
            ->setAttribute(TraceAttributes::DB_SYSTEM, 'mysql')
            ->setAttribute(TraceAttributes::DB_NAME, 'ratings')
            ->setAttribute(TraceAttributes::DB_CONNECTION_STRING, 'mysql:host=mysql;dbname=ratings;charset=utf8mb4')
            ->setAttribute(TraceAttributes::DB_OPERATION, 'CONNECT')
            ->setAttribute(TraceAttributes::DB_STATEMENT, 'CONNECT')
            ->startSpan();
        try {
            return new PDO($this->dsn, $this->user, $this->password, $opt);
        } catch (PDOException $e) {
            $msg = $e->getMessage();
            $this->logger->error("Database error $msg");

            $span->recordException($e);

            return null;
        } finally {
            $span->end();
        }
    }
}
