"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const standalone_1 = require("@adonisjs/auth/build/standalone");
class AuthMiddleware {
    async handle({ auth }, next, customGuards) {
        const guards = customGuards.length ? customGuards : [auth.name];
        await this.authenticate(auth, guards);
        await next();
    }
    async authenticate(auth, guards) {
        for (let guard of guards) {
            if (await auth.use(guard).check()) {
                throw new standalone_1.AuthenticationException('Only unauthenticated users', 'E_UNAUTHORIZED_ACCESS', guard);
            }
        }
        return true;
    }
}
exports.default = AuthMiddleware;
//# sourceMappingURL=Guest.js.map