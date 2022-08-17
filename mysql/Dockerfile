FROM mysql:8.0.30

ENV MYSQL_ALLOW_EMPTY_PASSWORD=yes \
    MYSQL_DATABASE=cities \
    MYSQL_USER=shipping \
    MYSQL_PASSWORD=secret

COPY docker-entrypoint-initdb.d/* /docker-entrypoint-initdb.d/
