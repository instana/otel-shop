# Instana OpenTelemetry Demo

This OpenTelemetry demo consists of the following services:

Application:

* **cart**: (Auto-Instrumented) NodeJS application talking to MongoDB and Redis
* **catalogue**: (Auto-Instrumented) NodeJS application talking to MongoDB
* **dispatch**: A golang application consuming from RabbitMQ
* **front**: An nginx frontend proxy server
* **payment**: (Auto-Instrumented) Python application talking to RabbitMQ, receiving and calling via HTTP
* **ratings**: PHP/Symfony Application reading and writing to MySQL
* **shipping**: (Auto-Instrumented) Java Application
* **user**: (Auto-Instrumented) NodeJS application reading and writing to MongoDB
* **web**: Apache web server for static assets

Infrastructure:

* **mongodb**
* **redis**
* **rabbitmq**
* **mysql**

(Kinda) Optional:

* **agent**: Instana Agent configured to receive OpenTelemetry via OTLP
* **load-gen**: Load Generator for the whole thing

## Usage

The Otel-Shop can be deployed to Kubernetes or plain docker-compose.
### `docker-compose`

1. Copy `.env.template` to `.env` and set the values straight in there.
2. `docker-compose -f docker-compose.yaml -f docker-compose-agent.yaml -f docker-compose-load.yaml pull`
3. `docker-compose -f docker-compose.yaml -f docker-compose-agent.yaml -f docker-compose-load.yaml up`

### Kubernetes

Deploy the Instana Agent via our helm chart or operator and enable the OpenTelemetry collector endpoint:

Helm Chart:

```shell
helm install --create-namespace instana-agent --namespace instana-agent \
    --repo https://agents.instana.io/helm \
    --set agent.key='<your agent key>' \
    --set agent.endpointHost='<your host agent endpoint>' \
    --set cluster.name='<your-cluster-name>' \
    --set zone.name='<your-zone-name>' \
    --set opentelemetry.enable=true \ # this is the setting
    instana-agent
```

Operator:

**Note**: You need `cert-manager` in your cluster. You can install it as follows, if you dont have it:

```shell
kubectl apply -f https://github.com/cert-manager/cert-manager/releases/download/v1.7.1/cert-manager.yaml
```

Install the Instana Agent operator

```shell
# Install the operator
kubectl apply -f https://github.com/instana/instana-agent-operator/releases/latest/download/instana-agent-operator.yaml
```

Create a `agent.yaml` file with the following content and apply it: `kubectl apply -f agent.yaml`

```yaml
# Deploy Instana Agents with OTel
apiVersion: instana.io/v1
kind: InstanaAgent
metadata:
  name: instana-agent
  namespace: instana-agent
spec:
  zone:
    name: '<your-zone-name>'
  cluster:
    name: '<your-cluster-name>'
  # this setting enables the OpenTelemetry collector endpoint
  opentelemetry:
    enabled: true
  agent:
    key: <your agent key>
    endpointHost: <your host agent endpoint>
    endpointPort: "443"
    env: {}
    configuration_yaml: |
      # Disable Python AutoInstrumentation through Instana
      com.instana.plugin.python:
        autotrace:
          enabled: false
```

#### Install otel-shop in your cluster

```shell
helm install otel-shop otel-shop \
  --create-namespace \
  --namespace otel-shop \
  --repo https://instana.github.io/otel-shop/
```

## License

Apache 2.0
