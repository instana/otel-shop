'use strict';

const { MeterProvider, ConsoleMetricExporter } = require('@opentelemetry/sdk-metrics-base');
const { OTLPMetricExporter } = require('@opentelemetry/exporter-metrics-otlp-grpc');

const otlpExporter = new OTLPMetricExporter({
  url: process.env.OTEL_EXPORTER_OTLP_ENDPOINT
});

const meter = new MeterProvider({
  exporter: otlpExporter,
  interval: 1000,
}).getMeter('your-meter-name');

const requestCount = meter.createCounter("requests", {
  description: "Count all incoming requests"
});

module.exports.countAllRequests = () => {
  return (req, res, next) => {

    requestCount.add(1, { route: req.path })

    next();
  };
};
