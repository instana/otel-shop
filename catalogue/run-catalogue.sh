#!/usr/bin/env bash

set -eo pipefail

cd `dirname $BASH_SOURCE`


export MONGO_URL=mongodb://localhost:27017/catalogue
export INSTANA_LOG_LEVEL=debug
export INSTANA_DISABLED_TRACERS=mongodb
export OTEL_EXPORTER_OTLP_ENDPOINT=localhost:55680
export OTEL_EXPORTER_OTLP_INSECURE=true
export OTEL_RESOURCE_ATTRIBUTES=service.name=catalogue


# BOTH, OTEL first
node -r ./tracer.js -r ./node_modules/@instana/collector/src/immediate server.js

# BOTH, Instana first
# node -r ./node_modules/@instana/collector/src/immediate -r ./tracer.js server.js

# JUST OTEL
# node -r ./tracer.js server.js

# JUST INSTANA
# node -r ./node_modules/@instana/collector/src/immediate server.js


# -r instana -r otel or vice versa
# no original function query to wrap
# no function to unwrap.
# Error
#     at MongoDBInstrumentation.unwrap [as _unwrap] (/Users/bastian/instana/code/otel-shop/catalogue/node_modules/shimmer/index.js:84:13)
#     at InstrumentationNodeModuleFile.v4Patch [as patch] (/Users/bastian/instana/code/otel-shop/catalogue/node_modules/@opentelemetry/instrumentation-mongodb/build/src/instrumentation.js:98:26)
#     at MongoDBInstrumentation._onRequire (/Users/bastian/instana/code/otel-shop/catalogue/node_modules/@opentelemetry/instrumentation/build/src/platform/node/instrumentation.js:85:33)
#     at /Users/bastian/instana/code/otel-shop/catalogue/node_modules/@opentelemetry/instrumentation/build/src/platform/node/instrumentation.js:112:29
#     at Module.Hook._require.Module.require (/Users/bastian/instana/code/otel-shop/catalogue/node_modules/require-in-the-middle/index.js:154:32)
#     at Module.Hook._require.Module.require (/Users/bastian/instana/code/otel-shop/catalogue/node_modules/require-in-the-middle/index.js:80:39)
#     at Module.Hook._require.Module.require (/Users/bastian/instana/code/otel-shop/catalogue/node_modules/require-in-the-middle/index.js:80:39)
#     at Module.Hook._require.Module.require (/Users/bastian/instana/code/otel-shop/catalogue/node_modules/require-in-the-middle/index.js:80:39)
#     at Module.Hook._require.Module.require (/Users/bastian/instana/code/otel-shop/catalogue/node_modules/require-in-the-middle/index.js:80:39)
#     at Module.Hook._require.Module.require (/Users/bastian/instana/code/otel-shop/catalogue/node_modules/require-in-the-middle/index.js:80:39)
# {"level
