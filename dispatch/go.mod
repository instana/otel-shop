module github.ibm.com/instana/otel-demo-app/dispatch

go 1.16

require (
	github.com/opentracing/opentracing-go v1.2.0
	github.com/streadway/amqp v1.0.0
	go.opentelemetry.io/otel v1.3.0
	go.opentelemetry.io/otel/exporters/otlp/otlptrace/otlptracegrpc v1.3.0
	go.opentelemetry.io/otel/sdk v1.3.0
	go.opentelemetry.io/otel/trace v1.3.0
	google.golang.org/grpc v1.44.0
)
