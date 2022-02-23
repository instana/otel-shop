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

const boundInstruments = new Map();

module.exports.countAllRequests = () => {
  return (req, res, next) => {
    if (!boundInstruments.has(req.path)) {
      const labels = { route: req.path };
      const boundCounter = requestCount.bind(labels);
      boundInstruments.set(req.path, boundCounter);
    }

    boundInstruments.get(req.path).add(1);
    next();
  };
};
