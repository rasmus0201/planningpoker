"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.WsAuthGuard = void 0;
const standalone_1 = require("@adonisjs/auth/build/standalone");
const crypto_1 = __importDefault(require("crypto"));
const helpers_1 = require("@poppinss/utils/build/helpers");
class WsAuthGuard {
    constructor(ctx, provider, tokenProvider) {
        this.tokenLength = 60;
        this.ctx = ctx;
        this.provider = provider;
        this.tokenProvider = tokenProvider;
    }
    async authenticate() {
        const token = this.getBearerToken();
        const { tokenId, value } = this.parsePublicToken(token);
        const providerToken = await this.getProviderToken(tokenId, value);
        const providerUser = await this.getUserById(providerToken.userId);
        return providerUser.user;
    }
    getBearerToken() {
        const token = this.ctx.handshake.auth.token;
        if (!token) {
            throw standalone_1.AuthenticationException.invalidToken('ws');
        }
        const [type, value] = token.split(' ');
        if (!type || type.toLowerCase() !== 'bearer' || !value) {
            throw standalone_1.AuthenticationException.invalidToken('ws');
        }
        return value;
    }
    async getProviderToken(tokenId, value) {
        const providerToken = await this.tokenProvider.read(tokenId, this.generateHash(value), 'api');
        if (!providerToken) {
            throw standalone_1.AuthenticationException.invalidToken('ws');
        }
        return providerToken;
    }
    async getUserById(id) {
        const authenticatable = await this.provider.findById(id);
        if (!authenticatable.user) {
            throw standalone_1.AuthenticationException.invalidToken('ws');
        }
        return authenticatable;
    }
    parsePublicToken(token) {
        const parts = token.split('.');
        if (parts.length !== 2) {
            throw standalone_1.AuthenticationException.invalidToken('ws');
        }
        const tokenId = helpers_1.base64.urlDecode(parts[0], undefined, true);
        if (!tokenId) {
            throw standalone_1.AuthenticationException.invalidToken('ws');
        }
        if (parts[1].length !== this.tokenLength) {
            throw standalone_1.AuthenticationException.invalidToken('ws');
        }
        return {
            tokenId,
            value: parts[1],
        };
    }
    generateHash(token) {
        return crypto_1.default.createHash('sha256').update(token).digest('hex');
    }
}
exports.WsAuthGuard = WsAuthGuard;
//# sourceMappingURL=WsAuthGuard.js.map