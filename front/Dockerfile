ARG image=ubuntu:20.04
FROM $image AS build

RUN apt-get update \
&& DEBIAN_FRONTEND=noninteractive TZ="Europe/London" \
   apt-get install --no-install-recommends --no-install-suggests -y \
   build-essential autoconf libtool pkg-config ca-certificates gcc g++ git libcurl4-openssl-dev libpcre3-dev gnupg2 lsb-release curl apt-transport-https software-properties-common zlib1g-dev cmake


RUN curl -o /etc/apt/trusted.gpg.d/nginx_signing.asc https://nginx.org/keys/nginx_signing.key \
    && apt-add-repository "deb http://nginx.org/packages/mainline/ubuntu `lsb_release -cs` nginx" \
    && /bin/bash -c 'echo -e "Package: *\nPin: origin nginx.org\nPin: release o=nginx\nPin-Priority: 900"' | tee /etc/apt/preferences.d/99nginx

RUN apt-get update \
&& DEBIAN_FRONTEND=noninteractive TZ="Europe/London" \
   apt-get install --no-install-recommends --no-install-suggests -y \
   nginx

RUN git clone --shallow-submodules --depth 1 --recurse-submodules -b v1.36.4 \
  https://github.com/grpc/grpc \
  && cd grpc \
  && mkdir -p cmake/build \
  && cd cmake/build \
  && cmake \
    -DgRPC_INSTALL=ON \
    -DgRPC_BUILD_TESTS=OFF \
    -DCMAKE_INSTALL_PREFIX=/install \
    -DCMAKE_BUILD_TYPE=Release \
    -DgRPC_BUILD_GRPC_NODE_PLUGIN=OFF \
    -DgRPC_BUILD_GRPC_OBJECTIVE_C_PLUGIN=OFF \
    -DgRPC_BUILD_GRPC_PHP_PLUGIN=OFF \
    -DgRPC_BUILD_GRPC_PHP_PLUGIN=OFF \
    -DgRPC_BUILD_GRPC_PYTHON_PLUGIN=OFF \
    -DgRPC_BUILD_GRPC_RUBY_PLUGIN=OFF \
    ../.. \
  && make -j2 \
  && make install

RUN git clone --shallow-submodules --depth 1 --recurse-submodules -b v0.7.0 \
  https://github.com/open-telemetry/opentelemetry-cpp.git \
  && cd opentelemetry-cpp \
  && mkdir build \
  && cd build \
  && cmake -DCMAKE_BUILD_TYPE=Release \
    -DCMAKE_INSTALL_PREFIX=/install \
    -DCMAKE_PREFIX_PATH=/install \
    -DWITH_OTLP=ON \
    -DBUILD_TESTING=OFF \
    -DWITH_EXAMPLES=OFF \
    -DCMAKE_POSITION_INDEPENDENT_CODE=ON \
    .. \
  && make -j2 \
  && make install

RUN git clone --depth 1 https://github.com/open-telemetry/opentelemetry-cpp-contrib.git

RUN mkdir -p opentelemetry-cpp-contrib/instrumentation/nginx/build
RUN cd opentelemetry-cpp-contrib/instrumentation/nginx/build \
  && cmake -DCMAKE_BUILD_TYPE=Release \
    -DCMAKE_PREFIX_PATH=/install \
    -DCMAKE_INSTALL_PREFIX=/usr/share/nginx/modules \
    .. \
  && make -j2 \
  && make install

FROM scratch AS export
COPY --from=build /opentelemetry-cpp-contrib/instrumentation/nginx/build/otel_ngx_module.so .

FROM build AS run
CMD ["/usr/sbin/nginx", "-g", "daemon off;"]
