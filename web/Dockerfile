FROM ubuntu/apache2:2.4-20.04_beta

RUN apt-get update && apt-get install -y \
  curl \
  && rm -rf /var/lib/apt/lists/*

COPY mod_otel.so /usr/lib/apache2/modules/
COPY conf/opentelemetry.load /etc/apache2/mods-available/opentelemetry.load
COPY conf/opentelemetry.conf /etc/apache2/mods-available/opentelemetry.conf

COPY conf/status.conf /etc/apache2/mods-available/status.conf

RUN a2enmod opentelemetry
RUN a2enmod status
