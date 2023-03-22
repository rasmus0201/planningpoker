"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const corsConfig = {
    enabled: true,
    origin: [
        'https://localhost:5173',
        'https://127.0.0.1:5173',
        'https://localhost:5174',
        'https://127.0.0.1:5174',
    ],
    methods: ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE'],
    headers: true,
    exposeHeaders: [
        'cache-control',
        'content-language',
        'content-type',
        'expires',
        'last-modified',
        'pragma',
    ],
    credentials: true,
    maxAge: 90,
};
exports.default = corsConfig;
//# sourceMappingURL=cors.js.map