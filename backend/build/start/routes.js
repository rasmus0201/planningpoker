"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const Route_1 = __importDefault(global[Symbol.for('ioc.use')]("Adonis/Core/Route"));
const MeController_1 = __importDefault(global[Symbol.for('ioc.use')]("App/Controllers/Http/MeController"));
const AuthController_1 = __importDefault(global[Symbol.for('ioc.use')]("App/Controllers/Http/AuthController"));
const GamesController_1 = __importDefault(global[Symbol.for('ioc.use')]("App/Controllers/Http/GamesController"));
Route_1.default.get('/', async () => {
    return { hello: 'world' };
});
Route_1.default.group(() => {
    Route_1.default.group(() => {
        Route_1.default.post('/register', async (ctx) => new AuthController_1.default().register(ctx)).middleware('guest');
        Route_1.default.post('/login', async (ctx) => new AuthController_1.default().login(ctx)).middleware('guest');
        Route_1.default.post('/logout', async (ctx) => new AuthController_1.default().logout(ctx)).middleware('auth');
        Route_1.default.post('/forgot-password', async (ctx) => new AuthController_1.default().forgotPassword(ctx))
            .middleware('guest')
            .as('auth.forgotPassword');
        Route_1.default.post('/reset-password', async (ctx) => new AuthController_1.default().resetPassword(ctx))
            .middleware('guest')
            .as('auth.resetPassword');
    }).prefix('/auth');
    Route_1.default.group(() => {
        Route_1.default.get('/', async (ctx) => new MeController_1.default().view(ctx));
        Route_1.default.patch('/', async (ctx) => new MeController_1.default().update(ctx));
        Route_1.default.delete('/', async (ctx) => new MeController_1.default().delete(ctx));
        Route_1.default.post('/export', async (ctx) => new MeController_1.default().export(ctx));
    })
        .prefix('/me')
        .middleware('auth');
    Route_1.default.group(() => {
        Route_1.default.get('/', async (ctx) => new GamesController_1.default().index(ctx));
        Route_1.default.post('/', async (ctx) => new GamesController_1.default().create(ctx));
        Route_1.default.get('/:pin', async (ctx) => new GamesController_1.default().view(ctx));
        Route_1.default.delete('/:pin', async (ctx) => new GamesController_1.default().delete(ctx));
    })
        .prefix('/games')
        .middleware('auth');
}).prefix('/api');
//# sourceMappingURL=routes.js.map