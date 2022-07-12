"use strict";

const { detectResources } = require("@opentelemetry/resources");
const opentelemetry = require("@opentelemetry/sdk-node");
const { SimpleSpanProcessor } = require("@opentelemetry/sdk-trace-base");

const { NodeTracerProvider } = require("@opentelemetry/sdk-trace-node");
const { AlwaysOnSampler } = require("@opentelemetry/core");
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
} = require("../../opentelemetry-js-contrib/detectors/node/opentelemetry-resource-detector-instana/build/src/");

const {
  Resource,
  envDetector,
  processDetector,
} = require("@opentelemetry/resources");
const { trace } = require("@opentelemetry/api");
const {
  OTLPTraceExporter,
} = require("@opentelemetry/exporter-trace-otlp-grpc");
const {
  AsyncLocalStorageContextManager,
} = require("@opentelemetry/context-async-hooks");
/*
exports.init = async () => {
  const serviceName = "LOL";
  const resource = await detectResources({
    detectors: [instanaAgentDetector],
  });

  const resource2 = new Resource({
    [SemanticResourceAttributes.SERVICE_NAME]: serviceName,
  });
  const mergedResource = resource2.merge(resource);
  const tracerProvider = new NodeTracerProvider({ resource: mergedResource });
  console.log(process.env.OTEL_EXPORTER_OTLP_ENDPOINT);
  const traceOtlpExporter = new OTLPTraceExporter({
    url: process.env.OTEL_EXPORTER_OTLP_ENDPOINT,
  });

  const metricOtlpExporter = new OTLPMetricExporter({
    url: process.env.OTEL_EXPORTER_OTLP_ENDPOINT,
  });

  tracerProvider.addSpanProcessor(new SimpleSpanProcessor(traceOtlpExporter));
  tracerProvider.addSpanProcessor(new SimpleSpanProcessor(metricOtlpExporter));

  tracerProvider.register();

  registerInstrumentations({
    instrumentations: [getNodeAutoInstrumentations({})],
    tracerProvider,
  });

  return trace.getTracer(serviceName);
};
*/

exports.init = async function () {
  const serviceName = "LOL";
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
    sampler: new AlwaysOnSampler(),
    // contextManager: new AsyncLocalStorageContextManager(),
  });

  // attributes are automatically merged!
  await sdk.detectResources({
    detectors: [envDetector, processDetector, instanaAgentDetector],
  });

  const resource = sdk["_resource"];
  console.log("final resource", resource);
  // const provider = new NodeTracerProvider({ resource });
  // trace.setGlobalTracerProvider(provider);
  console.log(sdk);
  sdk.start();
};
