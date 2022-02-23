FROM php:8.1-apache as extension-build

ARG grpc_version

RUN apt-get update && apt-get install -yqq unzip libzip-dev autoconf zlib1g-dev \
    && pecl install grpc-$grpc_version

RUN strip --strip-debug /usr/local/lib/php/extensions/no-debug-non-zts-20210902/grpc.so

FROM scratch

COPY --from=extension-build /usr/local/lib/php/extensions/no-debug-non-zts-20210902/grpc.so /
