<?php

/**
 * Instana SDK extension stubs
 *
 * Example:
 * <code>
 * <?php
 *     $tracer = new Instana\Tracer();
 *     $span = $tracer->createSpan('foo');
 *     $span->annotate('function', 'doSomething');
 *     try {
 *         doSomething();
 *     } catch (\Exception $e) {
 *         $tracer->logException($e);
 *         $span->markError();
 *     } finally {
 *        $span->stop();
 *     }
 * </code>
 *
 * @link https://docs.instana.io/ecosystem/php/#php-sdk
 * @package Instana
 */

namespace Instana;

use Exception;

if (false === extension_loaded('instana')) {

    if (false === defined("Instana\SPAN_ENTRY")) {
        define('Instana\SPAN_ENTRY', 1);
    }

    if (false === defined("Instana\SPAN_EXIT")) {
        define('Instana\SPAN_EXIT', 2);
    }

    if (false === defined("Instana\SPAN_INTERMEDIATE")) {
        define('Instana\SPAN_INTERMEDIATE', 3);
    }

    if (false === class_exists('Instana\Tracer')) {
        /**
         * Class Instana\Tracer
         *
         * @package Instana
         */
        class Tracer
        {
            /**
             * Tracer constructor.
             */
            public function __construct()
            {}

            /**
             * Starts a new SDK Trace
             *
             * Starting a new trace will discard any traces started by Instana's AutoTrace
             *
             * By default, Instana's AutoTrace will create a trace for you when a request
             * is received. This method is only needed if you want to create a trace that
             * deviates from the regular PHP lifecycle that starts with RINIT and ends in
             * RSHUTDOWN.
             */
            public function createNewTrace()
            {}

            /**
             * Continue the Trace
             *
             * The SDK allows to continue the trace passed through some message system
             * like Google PubSub or RabbitMQ
             *
             * Example:
             * <code>
             * <?php
             *     $message = $queue->pull();
             *     $messageContext = new \Instana\TraceContext($message['X-INSTANA-T'], $message['X-INSTANA-S']);
             *     $tracer->continueTrace($messageContext);
             * </code>
             *
             * @param TraceContext $context
             * @throws InstanaRuntimeException when TraceContext is not provided
             * @throws InstanaRuntimeException when TraceContext has incorrect trace id or span id
             */
            public function continueTrace($context)
            {}

            /**
             * Get current tracing context
             *
             * @return TraceContext
             */
            public function getActiveContext()
            {
                return new TraceContext('', '');
            }

            /**
             * Creates a new SDK Span with the name set to $category
             *
             * @param string $category
             * @param int $type - optional span type one of Instana\SPAN_ENTRY, Instana\SPAN_EXIT or Instana\SPAN_LOCAL
             * @param string $parentId - optional ID of parent span
             * @return Span
             */
            public function createSpan($category, $type = \Instana\SPAN_INTERMEDIATE, $parentId = '')
            {
                return new Span();
            }

            /**
             * Logs an exception or throwable
             *
             * @param Exception|\Throwable $e
             * @return void
             */
            public function logException($e)
            {}

            /**
             * Returns a reference to the root Span
             *
             * @return Span
             */
            public static function getEntrySpan()
            {
                return new Span();
            }

            /**
             * Sends collected instrumentation data
             */
            public function flush()
            {}

            /**
             * Sets the Service Name
             *
             * @param string $serviceName
             * return void
             */
            public static function setServiceName($serviceName)
            {}
        }
    }

    if (false === class_exists('Instana\TraceContext')) {
        /**
         * Class Instana\TraceContext
         *
         * Object to represent tracing context
         *
         * @package Instana
         */
        class TraceContext
        {
            /**
             * TraceContext constructor
             *
             * @param string $traceId
             * @param string $spanId
             */
            public function __construct($traceId, $spanId)
            {}

            /**
             * Trace ID of the context
             *
             * @return string
             */
            public function getTraceId()
            {
                return "";
            }

            /**
             * Span ID of the context
             *
             * @return string
             */
            public function getSpanId()
            {
                return "";
            }
        }
    }

    if (false === class_exists('Instana\Span')) {
        /**
         * Class Instana\Span
         *
         * An individual Span in a Trace
         *
         * @package Instana
         */
        class Span
        {
            /**
             * Span constructor.
             *
             * Creating Spans directly through this constructor will create orphaned Spans that won't show up in a trace.
             * Use <code>Tracer::createSpan()</code> instead.
             *
             * @see Tracer::createSpan()
             */
            public function __construct()
            {}

            /**
             * Annotates the Span with a key and a value
             *
             * Setting the same key multiple times will overwrite any previously set value.
             *
             * @param string $key
             * @param string|int $val
             * @throws InstanaRuntimeException when Span was already stopped
             * @throws InstanaRuntimeException when $key is not a string
             * @throws InstanaRuntimeException when $value is not a string or integer
             * @return void
             */
            public function annotate($key, $val)
            {}

            /**
             * Marks the Span as erroneous
             *
             * @throws InstanaRuntimeException when Span was already stopped
             * @return void
             */
            public function markError()
            {}

            /**
             * Closes the Span
             *
             * @throws InstanaRuntimeException when Span was not created through Span::__construct
             * @return void
             */
            public function stop()
            {}

            /**
             * Convert the span to Google PubSub Receive
             *
             * Example:
             * <code>
             * <?php
             *     $message = $queue->pull();
             *     $attributes = $message->attributes();
             *     $messageContext = new \Instana\TraceContext($attributes['X-INSTANA-T'], $attributes['X-INSTANA-S']);
             *     $tracer->continueTrace($messageContext);
             *     $span = $tracer->getEntrySpan();
             *     $span->asGCPubSubReceive($project, 'php-consumer');
             * </code>
             *
             * @param string $projectId
             * @param string $subscriptionId
             */
            public function asGCPubSubReceive($projectId, $subscriptionId)
            {}
        }
    }

    if (false === class_exists('Instana\InstanaRuntimeException')) {
        /**
         * Class InstanaRuntimeException
         *
         * @package Instana
         */
        class InstanaRuntimeException extends \Exception
        {}
    }
}
