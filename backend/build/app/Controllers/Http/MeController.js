"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const Validator_1 = global[Symbol.for('ioc.use')]("Adonis/Core/Validator");
const UserCleanUp_1 = __importDefault(global[Symbol.for('ioc.use')]("App/Services/UserCleanUp"));
class MeController {
    async view({ response, auth }) {
        if (!auth.user) {
            return response.unauthorized();
        }
        return response.json({ message: 'Got user', user: auth.user });
    }
    async update({ request, response, auth }) {
        if (!auth.user) {
            return response.unauthorized();
        }
        const validations = Validator_1.schema.create({
            email: Validator_1.schema.string.optional({}, [
                Validator_1.rules.email(),
                Validator_1.rules.unique({ table: 'users', column: 'email', whereNot: { email: auth.user.email } }),
            ]),
            password: Validator_1.schema.string.optional({}, [Validator_1.rules.minLength(8)]),
            username: Validator_1.schema.string.optional({}, [
                Validator_1.rules.unique({
                    table: 'users',
                    column: 'username',
                    whereNot: { username: auth.user.username },
                }),
            ]),
        });
        const data = await request.validate({ schema: validations });
        if (data.username) {
            if (data.username.startsWith('DELETED@')) {
                throw new Error('Username not allowed');
            }
            auth.user.username = data.username;
        }
        if (data.email) {
            auth.user.email = data.email;
        }
        if (data.password) {
            auth.user.password = data.password;
        }
        if (auth.user.$isDirty) {
            await auth.user.save();
        }
        return response.json({ message: 'User updated', user: auth.user });
    }
    async delete({ response, auth }) {
        if (!auth.user) {
            return response.unauthorized();
        }
        const userCleanUpService = new UserCleanUp_1.default();
        await userCleanUpService.run(auth.user);
        await auth.use('api').revoke();
        return response.json({ message: 'User deleted' });
    }
    async export({ response, auth }) {
        if (!auth.user) {
            return response.unauthorized();
        }
        await auth.user.load('games');
        await auth.user.load('gameVotes');
        return response.json({ message: 'Exported data', export: auth.user.toJSON() });
    }
}
exports.default = MeController;
//# sourceMappingURL=MeController.js.map