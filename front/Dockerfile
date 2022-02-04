FROM ubuntu:20.04

RUN apt-get update \
    && DEBIAN_FRONTEND=noninteractive TZ="Europe/London" \
    apt-get install --no-install-recommends --no-install-suggests -y \
    curl apt-transport-https software-properties-common

RUN curl -o /etc/apt/trusted.gpg.d/nginx_signing.asc https://nginx.org/keys/nginx_signing.key \
    && apt-add-repository "deb http://nginx.org/packages/mainline/ubuntu `lsb_release -cs` nginx" \
    && /bin/bash -c 'echo -e "Package: *\nPin: origin nginx.org\nPin: release o=nginx\nPin-Priority: 900"' | tee /etc/apt/preferences.d/99nginx

RUN apt-get update \
    && DEBIAN_FRONTEND=noninteractive TZ="Europe/London" \
    apt-get install --no-install-recommends --no-install-suggests -y \
    nginx

ADD otel_ngx_module.so /usr/share/nginx/modules/otel_ngx_module.so

CMD ["/usr/sbin/nginx", "-g", "daemon off;"]
