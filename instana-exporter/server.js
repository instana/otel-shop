'use strict';

const bodyParser = require('body-parser');
const express = require('express');
const logger = require('./logger');
const port = process.env.APP_PORT || '8080';

const app = express();

app.use(logger.expressLogger);
app.use((req, res, next) => {
  res.set('Timing-Allow-Origin', '*');
  res.set('Access-Control-Allow-Origin', '*');

  next();
});

app.use(bodyParser.urlencoded({ extended: true }));
app.use(bodyParser.json());

app.get('/export', (req, res) => {
  res.json({ success: true });
});

app.listen(port, () => {
  logger.info(`Started on port: ${port}`);
});

setInterval(async () => {
  await fetch(`http://localhost:${port}/export`);
}, 5 * 1000);
