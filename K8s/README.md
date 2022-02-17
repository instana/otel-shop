# Instana Agent Install

The easiest way to install the Instana agent is with the helm [chart](https://hub.helm.sh/charts/stable/instana-agent). If you really want to do it by hand, template descriptors are available in the official [documentation](https://docs.instana.io/ecosystem/kubernetes/).

Please make sure to enable the OpenTelemetry collector endpoint on the Instana Agent.

Example:

```bash
helm install instana-agent \
   --repo https://agents.instana.io/helm \
   --namespace instana-agent \
   --create-namespace \
   --set agent.key=xxx-your-key-xxx \
   --set agent.endpointHost=ingress-red-saas.instana.io \
   --set agent.endpointPort=443 \
   --set cluster.name='my-otel-shop-cluster' \
   --set zone.name='my-otel-shop-zone' \
   --set opentelemetry.enable=true \
   instana-agent
```

# Stan's OTel Shop Install

Install Stan's OTel Shop on to your K8s cluster using the helm chart, see the [README](helm/README.md) for details of the various options.

```shell
$ cd helm
$ helm install --name otel-shop --namespace otel-shop .
```

## Quotas and Scaling

You can apply resource quotas to the namespace where you installed Stan's OTel Shop.

```shell
$ kubectl -n otel-shop apply -f resource-quota.yaml
```

The quotas and usage are shown in the Instana Kubernetes dashboards.

Optinally you can also run the `autoscale.sh` script to configure automatic scaling of the deployments. You will need to edit the script if you did not deploy to the `otel-shop` namespace. Varying the load on the application will cause Kubernetes to scale up/down the various deployments.

## Istio

Stan's Robot Shop will run on Kubernetes with Istio service mesh. Configure Istio ingress.

```shell
$ kubectl -n otel-shop apply -f Istio/gateway.yaml
```

Now use the exposed Istio gateway to access OTel Shop.

```shell
$ kubectl -n istio-system get svc istio-ingressgateway
```

The above will display the IP address of the Istio gateway.

**NOTE** The Instana agent only works with later versions of Istio.
