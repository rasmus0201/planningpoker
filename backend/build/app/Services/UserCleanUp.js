"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const luxon_1 = require("luxon");
class UserCleanUp {
    async run(user) {
        user.username = `DELETED@${user.id}`;
        user.email = `DELETED@${user.id}`;
        user.password = '';
        user.deletedAt = luxon_1.DateTime.now();
        if (user.gameVotes?.length) {
            user.gameVotes.forEach((v) => {
                v.vote = '[DELETED]';
                v.save();
            });
        }
        if (user.games?.length) {
            user.games.forEach((g) => {
                g.title = '[DELETED]';
                g.save();
            });
        }
        await user.related('apiTokens').query().delete();
        await user.save();
    }
}
exports.default = UserCleanUp;
//# sourceMappingURL=UserCleanUp.js.map