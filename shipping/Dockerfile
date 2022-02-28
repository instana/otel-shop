#
# Build
#
FROM debian:11 AS build

RUN apt-get update && apt-get -y install maven wget

WORKDIR /opt/shipping

COPY pom.xml /opt/shipping/
RUN mvn dependency:resolve -fail-never
COPY src /opt/shipping/src/
RUN mvn package

RUN wget https://github.com/open-telemetry/opentelemetry-java-instrumentation/releases/download/v1.11.1/opentelemetry-javaagent.jar -O /opt/shipping/opentelemetry-javaagent-all.jar

#
# Run
#
FROM openjdk:17-jdk

EXPOSE 8080

WORKDIR /opt/shipping

ENV CART_ENDPOINT=cart:8080
ENV DB_HOST=mysql

# [bump]
COPY --from=build /opt/shipping/target/shipping-0.1.67.jar shipping.jar
COPY --from=build /opt/shipping/opentelemetry-javaagent-all.jar opentelemetry-javaagent-all.jar

CMD [ "java", "-Xmn256m", "-Xmx768m", "-javaagent:opentelemetry-javaagent-all.jar", "-jar", "shipping.jar" ]
