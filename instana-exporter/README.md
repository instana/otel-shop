## Opentelemetry Instana Exporter Demo

https://www.ibm.com/docs/en/instana-observability/243?topic=nodejs-opentelemetry-integration#serverless-opentelemetry-exporter

This demo will transform Otel spans into Instana spans using the Instana exporter. The spans are send directly to the serverless acceptor in our backend. Data is generated every 5s.

1. Copy env.example to .env
2. Add APP_PORT, INSTANA_AGENT_KEY and INSTANA_ENDPOINT_URL. (You need to use the serverless endpoint)
3. `npm run start`
4. Go to services and look for "Instana Exporter Demo"

