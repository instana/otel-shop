'use strict';

const path = require('path');
const process = require('process');
const logger = require('./logger');

require('dotenv').config({ path: path.resolve(process.cwd(), '..', '.env') });

require('@opentelemetry/api');
const opentelemetry = require('@opentelemetry/sdk-node');
const {
  getNodeAutoInstrumentations,
} = require('@opentelemetry/auto-instrumentations-node');
const { Resource } = require('@opentelemetry/resources');
const {
  SemanticResourceAttributes,
} = require('@opentelemetry/semantic-conventions');
const { InstanaExporter } = require('@instana/opentelemetry-exporter');

const instanaTraceExporter = new InstanaExporter({
  agentKey: process.env.INSTANA_AGENT_KEY,
  endpointUrl: process.env.INSTANA_ENDPOINT_URL,
});

const nodeAutoInstrumentations = getNodeAutoInstrumentations({
  // fs is creating lot's of noise, disable
  '@opentelemetry/instrumentation-fs': {
    enabled: false,
  },
});

const sdk = new opentelemetry.NodeSDK({
  resource: new Resource({
    [SemanticResourceAttributes.SERVICE_NAME]: 'Instana Exporter Demo',
  }),
  traceExporter: instanaTraceExporter,
  instrumentations: [nodeAutoInstrumentations],
});

sdk.start();
