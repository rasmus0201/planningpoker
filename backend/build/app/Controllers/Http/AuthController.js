"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const User_1 = __importDefault(global[Symbol.for('ioc.use')]("App/Models/User"));
const luxon_1 = require("luxon");
const Validator_1 = global[Symbol.for('ioc.use')]("Adonis/Core/Validator");
const PasswordReset_1 = __importDefault(global[Symbol.for('ioc.use')]("App/Models/PasswordReset"));
const Helpers_1 = global[Symbol.for('ioc.use')]("Adonis/Core/Helpers");
const Mail_1 = __importDefault(global[Symbol.for('ioc.use')]("Adonis/Addons/Mail"));
const Env_1 = __importDefault(global[Symbol.for('ioc.use')]("Adonis/Core/Env"));
const Database_1 = __importDefault(global[Symbol.for('ioc.use')]("Adonis/Lucid/Database"));
class AuthController {
    async register({ auth, request, response }) {
        const validations = Validator_1.schema.create({
            email: Validator_1.schema.string({}, [Validator_1.rules.email(), Validator_1.rules.unique({ table: 'users', column: 'email' })]),
            password: Validator_1.schema.string({}, [Validator_1.rules.minLength(8)]),
            username: Validator_1.schema.string({}, [Validator_1.rules.unique({ table: User_1.default.table, column: 'username' })]),
        });
        const data = await request.validate({ schema: validations });
        if (data.username.startsWith('DELETED@')) {
            throw new Error('Username not allowed');
        }
        const user = await User_1.default.create(data);
        const token = await auth.use('api').generate(user);
        return response.created({ user, token });
    }
    async login({ auth, request, response }) {
        const email = request.input('email');
        const password = request.input('password');
        try {
            const token = await auth.use('api').attempt(email, password, {
                expiresIn: '60 days',
            });
            const user = await User_1.default.findByOrFail('email', email);
            user.lastActiveAt = luxon_1.DateTime.now();
            await user.save();
            return response.ok({ token, user });
        }
        catch {
            return response.unauthorized({ error: 'Invalid credentials' });
        }
    }
    async logout({ response, auth }) {
        if (!auth.user) {
            return response.unauthorized();
        }
        await auth.use('api').revoke();
        return response.json({ message: 'User logged out' });
    }
    async forgotPassword({ request, response }) {
        const validations = Validator_1.schema.create({
            email: Validator_1.schema.string({}, [Validator_1.rules.email()]),
            returnPath: Validator_1.schema.string(),
        });
        const data = await request.validate({ schema: validations });
        const user = await User_1.default.findBy('email', data.email);
        if (user) {
            const passwordReset = await PasswordReset_1.default.create({
                email: user.email,
                token: Helpers_1.string.generateRandom(64),
                expiredAt: luxon_1.DateTime.now().plus({ hours: 1 }),
            });
            const url = `${Env_1.default.get('APP_FRONTEND_URL')}${data.returnPath.trim()}?token=${passwordReset.token}`;
            Mail_1.default.send((message) => {
                message
                    .from(Env_1.default.get('MAIL_FROM'))
                    .to(user.email)
                    .subject(`Password reset on ${Env_1.default.get('APP_NAME')}`)
                    .htmlView('emails/reset-password', { url, expiresIn: 'Expires in 1 hour.' });
            });
        }
        return response.ok({ message: 'Password reset sent' });
    }
    async resetPassword({ request, response }) {
        const validations = Validator_1.schema.create({
            email: Validator_1.schema.string({}, [
                Validator_1.rules.email(),
                Validator_1.rules.exists({ table: User_1.default.table, column: 'email' }),
            ]),
            password: Validator_1.schema.string({}, [Validator_1.rules.minLength(8)]),
            token: Validator_1.schema.string({}, [
                Validator_1.rules.exists({
                    table: PasswordReset_1.default.table,
                    column: 'token',
                    where: {
                        email: request.input('email'),
                    },
                }),
            ]),
        });
        const data = await request.validate({ schema: validations });
        const passwordReset = await PasswordReset_1.default.findByOrFail('token', data.token);
        if (luxon_1.DateTime.now() >= passwordReset.expiredAt) {
            return response.badRequest({
                expiredAt: passwordReset.expiredAt,
                now: luxon_1.DateTime.now(),
                errors: [{ rule: 'expired', field: 'token', message: 'Token has expired' }],
            });
        }
        const user = await User_1.default.findByOrFail('email', data.email);
        user.password = data.password;
        await user.save();
        passwordReset.expiredAt = luxon_1.DateTime.now();
        passwordReset.save();
        await Database_1.default.from('api_tokens').where('user_id', user.id).delete();
        return response.ok({ message: 'Password was reset' });
    }
}
exports.default = AuthController;
//# sourceMappingURL=AuthController.js.map