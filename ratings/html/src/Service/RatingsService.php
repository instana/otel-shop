<?php

declare(strict_types=1);

namespace Instana\RobotShop\Ratings\Service;

use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\SDK\Trace\Tracer;
use OpenTelemetry\SemConv\TraceAttributes;
use PDO;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

class RatingsService implements LoggerAwareInterface
{
    private const QUERY_RATINGS_BY_SKU = 'select avg_rating, rating_count from ratings where sku = ?';
    private const QUERY_UPDATE_RATINGS_BY_SKU = 'update ratings set avg_rating = ?, rating_count = ? where sku = ?';
    private const QUERY_INSERT_RATING = 'insert into ratings(sku, avg_rating, rating_count) values(?, ?, ?)';

    use LoggerAwareTrait;

    /**
     * @var PDO
     */
    private $connection;

    private Tracer $tracer;

    public function __construct(PDO $connection, Tracer $tracer)
    {
        $this->connection = $connection;
        $this->tracer = $tracer;
    }

    public function ratingBySku(string $sku): array
    {
        $stmt = $this->traceQuery(self::QUERY_RATINGS_BY_SKU, 'failed to query data', [$sku]);

        $data = $stmt->fetch();
        if ($data) {
            // for some reason avg_rating is return as a string
            $data['avg_rating'] = (float) $data['avg_rating'];

            return $data;
        }

        // nicer to return an empty record than throw 404
        return ['avg_rating' => 0, 'rating_count' => 0];
    }

    public function updateRatingForSKU(string $sku, $score, int $count): void
    {
        $this->traceQuery(self::QUERY_UPDATE_RATINGS_BY_SKU, 'failed to update rating', [$score, $count, $sku]);
    }

    public function addRatingForSKU($sku, $rating): void
    {
        $this->traceQuery(self::QUERY_INSERT_RATING, 'failed to insert data', [$sku, $rating, 1]);
    }

    protected function traceQuery($statement, $error, $parameters = []): ?\PDOStatement
    {
        $prepared = $this->connection->prepare($statement);
        $span = $this->tracer->spanBuilder($prepared->queryString)
            ->setSpanKind(SpanKind::KIND_CLIENT)
            ->setAttribute(TraceAttributes::DB_SYSTEM, 'mysql')
            ->setAttribute(TraceAttributes::DB_NAME, 'ratings')
            ->setAttribute(TraceAttributes::DB_CONNECTION_STRING, 'mysql:host=mysql;dbname=ratings;charset=utf8mb4')
            ->setAttribute(TraceAttributes::DB_OPERATION, 'SELECT')
            ->setAttribute(TraceAttributes::DB_STATEMENT, $prepared->queryString)
            ->startSpan();

        if (true === empty($parameters)){
            $res = $prepared->execute();
        } else {
            $res = $prepared->execute($parameters);
        }

        if (!$res) {
            $exception = new \Exception($error, 500);

            $this->logger->error($error);
            $span->recordException($exception);

            throw $exception;
        }

        $span->end();

        return $prepared;
    }
}
