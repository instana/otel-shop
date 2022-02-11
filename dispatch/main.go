package main

import (
	"context"
	"encoding/json"
	"errors"
	"fmt"
	"log"
	"math/rand"
	"os"
	"strconv"
	"time"

	"google.golang.org/grpc"
	"google.golang.org/grpc/credentials/insecure"

	"go.opentelemetry.io/otel"
	"go.opentelemetry.io/otel/attribute"
	"go.opentelemetry.io/otel/exporters/otlp/otlptrace/otlptracegrpc"
	"go.opentelemetry.io/otel/propagation"
	"go.opentelemetry.io/otel/sdk/resource"
	sdktrace "go.opentelemetry.io/otel/sdk/trace"
	semconv "go.opentelemetry.io/otel/semconv/v1.7.0"
	oteltrace "go.opentelemetry.io/otel/trace"

	"github.com/streadway/amqp"
)

var (
	amqpUri          string
	rabbitChan       *amqp.Channel
	rabbitCloseError chan *amqp.Error
	rabbitReady      chan bool
	errorPercent     int

	dataCenters = []string{
		"asia-northeast2",
		"asia-south1",
		"europe-west3",
		"us-east1",
		"us-west1",
	}
)

func connectToRabbitMQ(uri string) *amqp.Connection {
	for {
		conn, err := amqp.Dial(uri)
		if err == nil {
			return conn
		}

		log.Println(err)
		log.Printf("Reconnecting to %s\n", uri)
		time.Sleep(1 * time.Second)
	}
}

func rabbitConnector(uri string) {
	var rabbitErr *amqp.Error

	for {
		rabbitErr = <-rabbitCloseError
		if rabbitErr == nil {
			return
		}

		log.Printf("Connecting to %s\n", amqpUri)
		rabbitConn := connectToRabbitMQ(uri)
		rabbitConn.NotifyClose(rabbitCloseError)

		var err error

		// create mappings here
		rabbitChan, err = rabbitConn.Channel()
		handleErr(err, "Failed to create channel")

		// create exchange
		err = rabbitChan.ExchangeDeclare("otel-shop", "direct", true, false, false, false, nil)
		handleErr(err, "Failed to create exchange")

		// create queue
		queue, err := rabbitChan.QueueDeclare("orders", true, false, false, false, nil)
		handleErr(err, "Failed to create queue")

		// bind queue to exchange
		err = rabbitChan.QueueBind(queue.Name, "orders", "otel-shop", false, nil)
		handleErr(err, "Failed to bind queue")

		// signal ready
		rabbitReady <- true
	}
}

func handleErr(err error, msg string) {
	if err != nil {
		log.Fatalf("%s : %s", msg, err)
	}
}

func getOrderId(order []byte) string {
	id := "unknown"
	var f interface{}
	err := json.Unmarshal(order, &f)
	if err == nil {
		m := f.(map[string]interface{})
		id = m["orderid"].(string)
	}

	return id
}

func createSpan(headers map[string]interface{}, order string) {
	tracer := otel.Tracer("dispatcher-tracer")

	//ctx, span := tracer.Start(context.Background(), "CollectorExporter-Example")
	//defer span.End()

	//fmt.Fprintln(headers)

	// headers is map[string]interface{}
	// carrier is map[string]string
	carrier := make(propagation.MapCarrier)
	// convert by copying k, v
	for k, v := range headers {
		carrier[k] = v.(string)
	}

	// get the order id
	log.Printf("order %s\n", order)

	ctx := otel.GetTextMapPropagator().Extract(context.Background(), carrier)
	log.Println("Creating child span")

	opts := []oteltrace.SpanStartOption{
		oteltrace.WithSpanKind(oteltrace.SpanKindConsumer),
	}
	ctx, span := tracer.Start(ctx, "getOrder", opts...)
	defer span.End()

	fakeDataCenter := dataCenters[rand.Intn(len(dataCenters))]

	span.SetAttributes(
		semconv.MessagingDestinationKindQueue,
		attribute.KeyValue{Key: semconv.MessagingRabbitmqRoutingKeyKey, Value: attribute.StringValue("otel-shop")},
		attribute.KeyValue{Key: semconv.MessagingSystemKey, Value: attribute.StringValue("rabbitmq")},
		attribute.KeyValue{Key: semconv.MessagingDestinationKey, Value: attribute.StringValue("otel-shop")},
		attribute.KeyValue{Key: semconv.MessagingProtocolKey, Value: attribute.StringValue("AMQP")},

		attribute.KeyValue{Key: "datacenter", Value: attribute.StringValue(fakeDataCenter)},
	)

	time.Sleep(time.Duration(42+rand.Int63n(42)) * time.Millisecond)
	if rand.Intn(100) < errorPercent {
		span.RecordError(errors.New("Failed to dispatch to SOP"))
	}

	processSale(ctx, span)
}

func processSale(ctx context.Context, parentSpan oteltrace.Span) {
	tracer := otel.Tracer("dispatcher-tracer")
	_, span := tracer.Start(ctx, "processSale")
	defer span.End()

	time.Sleep(time.Duration(42+rand.Int63n(42)) * time.Millisecond)
}

// Initializes an OTLP exporter, and configures the corresponding trace and
// metric providers.
func initProvider() func() {
	ctx := context.Background()

	res, err := resource.New(ctx,
		resource.WithAttributes(
			// the service name used to display traces in backends
			semconv.ServiceNameKey.String(os.Getenv("OTEL_SERVICE_NAME")),
		),
	)
	handleErr(err, "failed to create resource")

	// If the OpenTelemetry Collector is running on a local cluster (minikube or
	// microk8s), it should be accessible through the NodePort service at the
	// `localhost:30080` endpoint. Otherwise, replace `localhost` with the
	// endpoint of your cluster. If you run the app inside k8s, then you can
	// probably connect directly to the service through dns
	conn, err := grpc.DialContext(ctx, os.Getenv("OTEL_EXPORTER_OTLP_ENDPOINT"), grpc.WithTransportCredentials(insecure.NewCredentials()), grpc.WithBlock())
	handleErr(err, "failed to create gRPC connection to collector")

	// Set up a trace exporter
	traceExporter, err := otlptracegrpc.New(ctx, otlptracegrpc.WithGRPCConn(conn))
	handleErr(err, "failed to create trace exporter")

	// Register the trace exporter with a TracerProvider, using a batch
	// span processor to aggregate spans before export.
	bsp := sdktrace.NewBatchSpanProcessor(traceExporter)
	tracerProvider := sdktrace.NewTracerProvider(
		sdktrace.WithSampler(sdktrace.AlwaysSample()),
		sdktrace.WithResource(res),
		sdktrace.WithSpanProcessor(bsp),
	)
	otel.SetTracerProvider(tracerProvider)

	// set global propagator to tracecontext (the default is no-op).
	otel.SetTextMapPropagator(propagation.TraceContext{})

	return func() {
		// Shutdown will flush any remaining spans and shut down the exporter.
		handleErr(tracerProvider.Shutdown(ctx), "failed to shutdown TracerProvider")
	}
}

func main() {
	rand.Seed(time.Now().Unix())

	shutdown := initProvider()
	defer shutdown()

	// Init amqpUri
	// get host from environment
	amqpHost, ok := os.LookupEnv("AMQP_HOST")
	if !ok {
		amqpHost = "rabbitmq"
	}
	amqpUri = fmt.Sprintf("amqp://guest:guest@%s:5672/", amqpHost)

	// get error threshold from environment
	errorPercent = 0
	epct, ok := os.LookupEnv("DISPATCH_ERROR_PERCENT")
	if ok {
		epcti, err := strconv.Atoi(epct)
		if err == nil {
			if epcti > 100 {
				epcti = 100
			}
			if epcti < 0 {
				epcti = 0
			}
			errorPercent = epcti
		}
	}
	log.Printf("Error Percent is %d\n", errorPercent)

	// MQ error channel
	rabbitCloseError = make(chan *amqp.Error)

	// MQ ready channel
	rabbitReady = make(chan bool)

	go rabbitConnector(amqpUri)

	rabbitCloseError <- amqp.ErrClosed

	go func() {
		for {
			// wait for rabbit to be ready
			ready := <-rabbitReady
			log.Printf("Rabbit MQ ready %v\n", ready)

			// subscribe to bound queue
			msgs, err := rabbitChan.Consume("orders", "", true, false, false, false, nil)
			handleErr(err, "Failed to consume")

			for d := range msgs {
				log.Printf("Order %s\n", d.Body)
				log.Printf("Headers %v\n", d.Headers)
				id := getOrderId(d.Body)
				go createSpan(d.Headers, id)
			}
		}
	}()

	log.Println("Waiting for messages")
	select {}
}
