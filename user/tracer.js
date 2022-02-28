'use strict';

const opentelemetry = require("@opentelemetry/sdk-node");
const api = require('@opentelemetry/api');
const { getNodeAutoInstrumentations } = require('@opentelemetry/auto-instrumentations-node');
const { OTLPTraceExporter } = require('@opentelemetry/exporter-trace-otlp-grpc');
const { OTLPMetricExporter } = require("@opentelemetry/exporter-metrics-otlp-grpc");

const traceOtlpExporter = new OTLPTraceExporter({
  url: process.env.OTEL_EXPORTER_OTLP_ENDPOINT
});

const metricOtlpExporter = new OTLPMetricExporter({
  url: process.env.OTEL_EXPORTER_OTLP_ENDPOINT
});

const sdk = new opentelemetry.NodeSDK({
  traceExporter: traceOtlpExporter,
  metricExporter: metricOtlpExporter,
  autoDetectResources: true,
  instrumentations: [getNodeAutoInstrumentations()],
});

sdk.start()
