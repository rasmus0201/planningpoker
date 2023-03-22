import { AuthenticationException } from '@adonisjs/auth/build/standalone'
import { type Socket } from 'socket.io'
import crypto from 'crypto'
import { base64 } from '@poppinss/utils/build/helpers'
import { TokenProviderContract, UserProviderContract } from '@ioc:Adonis/Addons/Auth'
import User from 'App/Models/User'

export class WsAuthGuard {
  private ctx: any
  private provider: UserProviderContract<User>
  private tokenProvider: TokenProviderContract

  /**
   * Length of the raw token. The hash length will vary
   */
  private tokenLength = 60

  constructor(
    ctx: Socket,
    provider: UserProviderContract<User>,
    tokenProvider: TokenProviderContract
  ) {
    this.ctx = ctx
    this.provider = provider
    this.tokenProvider = tokenProvider
  }

  /**
   * Authenticates the current HTTP request by checking for the bearer token
   */
  public async authenticate() {
    const token = this.getBearerToken()
    const { tokenId, value } = this.parsePublicToken(token)

    const providerToken = await this.getProviderToken(tokenId, value)
    const providerUser = await this.getUserById(providerToken.userId)

    return providerUser.user
  }

  private getBearerToken(): string {
    /**
     * Ensure the "Authorization"/token value exists
     */
    const token = this.ctx.handshake.auth.token as string
    if (!token) {
      throw AuthenticationException.invalidToken('ws')
    }

    /**
     * Ensure that token has minimum of two parts and the first
     * part is a constant string named `bearer`
     */
    const [type, value] = token.split(' ')
    if (!type || type.toLowerCase() !== 'bearer' || !value) {
      throw AuthenticationException.invalidToken('ws')
    }

    return value
  }

  /**
   * Returns the token by reading it from the token provider
   */
  private async getProviderToken(tokenId: string, value: string) {
    const providerToken = await this.tokenProvider.read(tokenId, this.generateHash(value), 'api')
    if (!providerToken) {
      throw AuthenticationException.invalidToken('ws')
    }

    return providerToken
  }

  /**
   * Returns user from the user session id
   */
  private async getUserById(id) {
    const authenticatable = await this.provider.findById(id)
    if (!authenticatable.user) {
      throw AuthenticationException.invalidToken('ws')
    }

    return authenticatable
  }

  /**
   * Parses the token received in the request. The method also performs
   * some initial level of sanity checks.
   */
  private parsePublicToken(token: string) {
    const parts = token.split('.')
    /**
     * Ensure the token has two parts
     */
    if (parts.length !== 2) {
      throw AuthenticationException.invalidToken('ws')
    }

    /**
     * Ensure the first part is a base64 encode id
     */
    const tokenId = base64.urlDecode(parts[0], undefined, true)
    if (!tokenId) {
      throw AuthenticationException.invalidToken('ws')
    }

    /**
     * Ensure 2nd part of the token has the expected length
     */
    if (parts[1].length !== this.tokenLength) {
      throw AuthenticationException.invalidToken('ws')
    }

    return {
      tokenId,
      value: parts[1],
    }
  }

  /**
   * Converts value to a sha256 hash
   */
  private generateHash(token: string) {
    return crypto.createHash('sha256').update(token).digest('hex')
  }
}
