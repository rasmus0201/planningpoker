"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
require("reflect-metadata");
const source_map_support_1 = __importDefault(require("source-map-support"));
const https_1 = require("https");
const Ignitor_1 = require("@adonisjs/core/build/src/Ignitor");
const path_1 = require("path");
const fs_1 = require("fs");
const os_1 = __importDefault(require("os"));
source_map_support_1.default.install({ handleUncaughtExceptions: false });
const sslDir = (0, path_1.resolve)(os_1.default.homedir(), '.ssl');
const privateKey = (0, fs_1.readFileSync)((0, path_1.resolve)(sslDir, 'localhost-key.pem'), 'utf8');
const certificate = (0, fs_1.readFileSync)((0, path_1.resolve)(sslDir, 'localhost.pem'), 'utf8');
const options = { key: privateKey, cert: certificate };
source_map_support_1.default.install({ handleUncaughtExceptions: false });
new Ignitor_1.Ignitor(__dirname).httpServer().start((handle) => {
    return (0, https_1.createServer)(options, handle);
});
//# sourceMappingURL=server.js.map