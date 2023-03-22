"use strict";
var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    var desc = Object.getOwnPropertyDescriptor(m, k);
    if (!desc || ("get" in desc ? !m.__esModule : desc.writable || desc.configurable)) {
      desc = { enumerable: true, get: function() { return m[k]; } };
    }
    Object.defineProperty(o, k2, desc);
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
}) : function(o, v) {
    o["default"] = v;
});
var __importStar = (this && this.__importStar) || function (mod) {
    if (mod && mod.__esModule) return mod;
    var result = {};
    if (mod != null) for (var k in mod) if (k !== "default" && Object.prototype.hasOwnProperty.call(mod, k)) __createBinding(result, mod, k);
    __setModuleDefault(result, mod);
    return result;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const standalone_1 = require("@adonisjs/core/build/standalone");
const UserCleanUp_1 = __importDefault(global[Symbol.for('ioc.use')]("App/Services/UserCleanUp"));
const luxon_1 = require("luxon");
class CleanupUsers extends standalone_1.BaseCommand {
    async run() {
        const { default: User } = await Promise.resolve().then(() => __importStar(global[Symbol.for('ioc.use')]('App/Models/User')));
        this.logger.info('Running user cleanup');
        const userCleanUpService = new UserCleanUp_1.default();
        const cutoffDate = luxon_1.DateTime.local().minus({ days: 90 }).toSQLDate();
        const users = await User.query()
            .preload('apiTokens')
            .preload('games')
            .preload('gameVotes')
            .whereNull('deleted_at')
            .andWhere('created_at', '<', cutoffDate)
            .andWhere('last_active_at', '<', cutoffDate);
        let cleanUpCount = 0;
        for (const user of users) {
            try {
                await userCleanUpService.run(user);
                cleanUpCount++;
            }
            catch (error) {
            }
        }
        this.logger.info(`Anonymized ${cleanUpCount} out of ${users.length} eligible accounts`);
        this.logger.info('Finished running user cleanup');
    }
}
exports.default = CleanupUsers;
CleanupUsers.commandName = 'cleanup:users';
CleanupUsers.settings = {
    loadApp: true,
};
//# sourceMappingURL=CleanupUsers.js.map