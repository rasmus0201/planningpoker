"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const Validator_1 = global[Symbol.for('ioc.use')]("Adonis/Core/Validator");
const Game_1 = __importDefault(global[Symbol.for('ioc.use')]("App/Models/Game"));
class GamesController {
    async index({ auth, response }) {
        if (!auth.user) {
            return response.unauthorized();
        }
        await auth.user.load('games');
        return response.json({ message: 'Got games', games: auth.user.games });
    }
    async create({ auth, response, request }) {
        if (!auth.user) {
            return response.unauthorized();
        }
        const validations = Validator_1.schema.create({
            title: Validator_1.schema.string.optional({}, [Validator_1.rules.maxLength(32)]),
        });
        const data = await request.validate({ schema: validations });
        const randomPin = Math.floor(100000 + Math.random() * 900000);
        const game = await Game_1.default.create({
            userId: auth.user.id,
            title: data.title ?? '',
            pin: randomPin.toString(),
            state: 'lobby',
        });
        return response.json({ message: 'Created game', game });
    }
    async view({ auth, response, request }) {
        if (!auth.user) {
            return response.unauthorized();
        }
        try {
            const game = await Game_1.default.findByOrFail('pin', request.param('pin', ''));
            return response.json({ message: 'Got game', game });
        }
        catch (error) {
            return response.notFound({ message: 'Resource not found' });
        }
    }
    async delete({ auth, response, request }) {
        if (!auth.user) {
            return response.unauthorized();
        }
        const game = await Game_1.default.findByOrFail('pin', request.param('pin', ''));
        if (!game || game.userId !== auth.user.id) {
            return response.unauthorized();
        }
        await game.delete();
        return response.json({ message: 'Delete game' });
    }
}
exports.default = GamesController;
//# sourceMappingURL=GamesController.js.map