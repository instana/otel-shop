FROM golang:1.18.1

WORKDIR /go/src/app

COPY *.go .
COPY go.mod .

RUN go get
RUN go install

ENV OTEL_SERVICE_NAME=dispatch

CMD dispatch
