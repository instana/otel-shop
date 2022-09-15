"use strict";

const opentelemetry = require("@opentelemetry/sdk-node");
const api = require("@opentelemetry/api");
const {
  getNodeAutoInstrumentations,
} = require("@opentelemetry/auto-instrumentations-node");
const {
  OTLPMetricExporter,
} = require("@opentelemetry/exporter-metrics-otlp-grpc");
const {
  SemanticResourceAttributes,
} = require("@opentelemetry/semantic-conventions");

const {
  instanaAgentDetector,
} = require("@opentelemetry/resource-detector-instana");

const {
  Resource,
  envDetector,
  processDetector,
} = require("@opentelemetry/resources");
const {
  OTLPTraceExporter,
} = require("@opentelemetry/exporter-trace-otlp-grpc");

api.diag.setLogger(new api.DiagConsoleLogger(), api.DiagLogLevel.DEBUG);

(async function () {
  const serviceName = "Cart Otel Service";
  const globalResource = new Resource({
    [SemanticResourceAttributes.SERVICE_NAME]: serviceName,
  });

  const traceOtlpExporter = new OTLPTraceExporter({
    url: process.env.OTEL_EXPORTER_OTLP_ENDPOINT,
  });

  const metricOtlpExporter = new OTLPMetricExporter({
    url: process.env.OTEL_EXPORTER_OTLP_ENDPOINT,
  });

  const sdk = new opentelemetry.NodeSDK({
    traceExporter: traceOtlpExporter,
    metricExporter: metricOtlpExporter,
    instrumentations: [getNodeAutoInstrumentations()],
    autoDetectResources: false,
    resource: globalResource,
  });

  // attributes are automatically merged!
  await sdk.detectResources({
    detectors: [envDetector, processDetector, instanaAgentDetector],
  });

  api.diag.debug(`Merged resource ${JSON.stringify(sdk["_resource"])}`);
  await sdk.start();
})();
