FROM node:18

EXPOSE 8080

WORKDIR /opt/server

COPY package-lock.json package.json /opt/server/

RUN npm install

COPY server.js tracer.js monitoring.js /opt/server/

CMD ["node", "-r", "./tracer.js", "server.js"]
