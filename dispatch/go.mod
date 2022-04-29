module github.com/instana/otel-shop/dispatch

go 1.16

require (
	github.com/streadway/amqp v1.0.0
	go.opentelemetry.io/contrib/detectors/aws/ec2 v1.6.0
	go.opentelemetry.io/contrib/detectors/aws/ecs v1.6.0
	go.opentelemetry.io/contrib/detectors/aws/eks v1.6.0
	go.opentelemetry.io/contrib/detectors/aws/lambda v0.31.0
	go.opentelemetry.io/contrib/detectors/gcp v1.6.0
	go.opentelemetry.io/contrib/instrumentation/host v0.31.0
	go.opentelemetry.io/contrib/instrumentation/runtime v0.31.0
	go.opentelemetry.io/otel v1.7.0
	go.opentelemetry.io/otel/exporters/otlp/otlpmetric/otlpmetricgrpc v0.29.0
	go.opentelemetry.io/otel/exporters/otlp/otlptrace/otlptracegrpc v1.6.3
	go.opentelemetry.io/otel/metric v0.30.0
	go.opentelemetry.io/otel/sdk v1.7.0
	go.opentelemetry.io/otel/sdk/metric v0.30.0
	go.opentelemetry.io/otel/trace v1.7.0
	google.golang.org/grpc v1.46.0
)
