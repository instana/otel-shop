'use strict';

const opentelemetry = require('@opentelemetry/api');
const { NodeTracerProvider } = require('@opentelemetry/sdk-trace-node');
const { getNodeAutoInstrumentations } = require('@opentelemetry/auto-instrumentations-node');
const { ConsoleSpanExporter, SimpleSpanProcessor } = require('@opentelemetry/sdk-trace-base');
const { registerInstrumentations } = require('@opentelemetry/instrumentation');
const { OTLPTraceExporter } = require('@opentelemetry/exporter-trace-otlp-grpc');

module.exports = (serviceName) => {
  const provider = new NodeTracerProvider();

  const otlpExporter = new OTLPTraceExporter({
    // todo: switch to environment smh?
    url: 'grpc://localhost:4317',

    // optional - collection of custom headers to be sent with each request, empty by default
    headers: {},
  });

  provider.addSpanProcessor(new SimpleSpanProcessor(new ConsoleSpanExporter()));
  provider.addSpanProcessor(new SimpleSpanProcessor(otlpExporter));

  // Initialize the OpenTelemetry APIs to use the NodeTracerProvider bindings
  provider.register();

  registerInstrumentations({
    instrumentations: [
      getNodeAutoInstrumentations()
    ],
    tracerProvider: provider,
  });

  return opentelemetry.trace.getTracer('user-service');
};
