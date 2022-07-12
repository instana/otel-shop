## Opentelemetry Cart service

```
CART_SERVER_PORT=44553 OTEL_EXPORTER_OTLP_ENDPOINT=localhost:55680 OTEL_EXPORTER_OTLP_INSECURE=true REDIS_URL=redis://localhost:6379 node -r ./tracer.js server.js
```