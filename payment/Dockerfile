FROM python:3.11.0

EXPOSE 8080
USER root

ENV OTEL_SERVICE_NAME=otel-shop-payment

WORKDIR /app

COPY requirements.txt /app/

RUN pip install -r requirements.txt

COPY *.py /app/

CMD ["opentelemetry-instrument", "--log_level", "debug", "python", "app.py"]
