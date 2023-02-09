"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const luxon_1 = require("luxon");
class UserActiveMiddleware {
    async handle({ auth }, next) {
        await auth.check();
        if (auth.user) {
            auth.user.lastActiveAt = luxon_1.DateTime.now();
            await auth.user.save();
        }
        await next();
    }
}
exports.default = UserActiveMiddleware;
//# sourceMappingURL=UserActive.js.map