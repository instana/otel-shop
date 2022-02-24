module github.ibm.com/instana/otel-demo-app/dispatch

go 1.16

require (
	github.com/streadway/amqp v1.0.0
	go.opentelemetry.io/otel v1.4.1
	go.opentelemetry.io/otel/exporters/otlp/otlptrace/otlptracegrpc v1.3.0
	go.opentelemetry.io/otel/sdk v1.4.1
	go.opentelemetry.io/otel/trace v1.4.1
	google.golang.org/grpc v1.44.0
)
