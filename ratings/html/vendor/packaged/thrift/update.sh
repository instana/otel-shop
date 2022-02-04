#!/bin/bash

TAG=$1

if [[ "$TAG" == "" ]]; then
  echo "no branch or tag specified"
  exit 1
fi

cd "$(dirname $0)"
git clone --depth=1 --branch=$TAG git@github.com:apache/thrift tmp

# Move old file to an upper level to fit the new requirements of psr-4
if [ -d "src/Thrift" ]
then
  mv src/Thrift/* src/ && rm -Rf src/Thrift
fi

rm -Rf src/Thrift
cp -R tmp/lib/php/lib/* src/
rm -Rf tmp
cd -
