'use strict';

const logger = require('pino')();
const pinoHttp = require('pino-http')();

module.exports = logger;
module.exports.expressLogger = pinoHttp;
