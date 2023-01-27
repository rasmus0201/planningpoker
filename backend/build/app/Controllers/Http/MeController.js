"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const Validator_1 = global[Symbol.for('ioc.use')]("Adonis/Core/Validator");
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
        auth.user.username = `DELETED@${auth.user.id}`;
        auth.user.email = `DELETED@${auth.user.id}`;
        auth.user.password = '';
        await auth.user.save();
        await auth.use('api').revoke();
        return response.json({ message: 'User deleted' });
    }
    async export({ response, auth }) {
        if (!auth.user) {
            return response.unauthorized();
        }
        return response.json({ message: 'Exported data' });
    }
}
exports.default = MeController;
//# sourceMappingURL=MeController.js.map