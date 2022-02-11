#
# Build
#
FROM debian:10 AS build

RUN apt-get update && apt-get -y install maven wget

WORKDIR /opt/shipping

COPY pom.xml /opt/shipping/
RUN mvn dependency:resolve
COPY src /opt/shipping/src/
RUN mvn package

RUN wget https://github.com/open-telemetry/opentelemetry-java-instrumentation/releases/download/v1.10.1/opentelemetry-javaagent.jar -O /opt/shipping/opentelemetry-javaagent.jar

#
# Run
#
FROM openjdk:8-jdk

EXPOSE 8080

WORKDIR /opt/shipping

ENV CART_ENDPOINT=cart:8080
ENV DB_HOST=mysql

COPY --from=build /opt/shipping/target/shipping-1.0.jar shipping.jar
COPY --from=build /opt/shipping/opentelemetry-javaagent.jar opentelemetry-javaagent.jar

CMD [ "java", "-Xmn256m", "-Xmx768m", "-javaagent:opentelemetry-javaagent.jar", "-jar", "shipping.jar" ]