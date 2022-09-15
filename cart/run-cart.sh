#!/usr/bin/env bash

set -eo pipefail

cd `dirname $BASH_SOURCE`

OTEL_EXPORTER_OTLP_ENDPOINT=localhost:55680 OTEL_EXPORTER_OTLP_INSECURE=true OTEL_RESOURCE_ATTRIBUTES=service.name=cart REDIS_URL=redis://localhost:6379 node -r ./node_modules/@instana/collector/src/immediate -r ./tracer.js server.js


