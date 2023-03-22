"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const standalone_1 = require("@adonisjs/core/build/standalone");
const node_schedule_1 = __importDefault(require("node-schedule"));
class Scheduler extends standalone_1.BaseCommand {
    async run() {
        node_schedule_1.default.scheduleJob('0 0 * * *', async () => {
            await this.kernel.exec('cleanup:users', []);
        });
        node_schedule_1.default.scheduleJob('*/30 * * * *', async () => {
            await this.kernel.exec('cleanup:unfinished-games', []);
        });
    }
}
exports.default = Scheduler;
Scheduler.commandName = 'schedule';
Scheduler.settings = {
    loadApp: true,
    stayAlive: true,
};
//# sourceMappingURL=Scheduler.js.map