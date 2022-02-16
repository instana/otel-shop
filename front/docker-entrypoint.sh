#!/usr/bin/env sh
set -eu

envsubst '${WEB_HOST} ${CATALOGUE_HOST} ${CART_HOST} ${PAYMENT_HOST} ${SHIPPING_HOST} ${RATINGS_HOST} ${USER_HOST}' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf

exec "$@"
