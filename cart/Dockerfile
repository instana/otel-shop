FROM node:17

ENV INSTANA_AUTO_PROFILE true

EXPOSE 8080

WORKDIR /opt/server

COPY package-lock.json /opt/server/
COPY package.json /opt/server/

RUN npm install

COPY server.js /opt/server/

COPY server.js /opt/server/
COPY tracer.js /opt/server/
COPY monitoring.js /opt/server/

CMD ["node", "-r", "./tracer.js", "server.js"]
