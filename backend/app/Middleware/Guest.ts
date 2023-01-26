import { AuthenticationException } from '@adonisjs/auth/build/standalone'
import type { GuardsList } from '@ioc:Adonis/Addons/Auth'
import type { HttpContextContract } from '@ioc:Adonis/Core/HttpContext'

export default class AuthMiddleware {
  public async handle(
    { auth }: HttpContextContract,
    next: () => Promise<void>,
    customGuards: (keyof GuardsList)[]
  ) {
    const guards = customGuards.length ? customGuards : [auth.name]
    await this.authenticate(auth, guards)
    await next()
  }

  protected async authenticate(auth: HttpContextContract['auth'], guards: (keyof GuardsList)[]) {
    for (let guard of guards) {
      if (await auth.use(guard).check()) {
        throw new AuthenticationException(
          'Only unauthenticated users',
          'E_UNAUTHORIZED_ACCESS',
          guard
        )
      }
    }

    return true
  }
}
