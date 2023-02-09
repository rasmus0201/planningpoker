import type { HttpContextContract } from '@ioc:Adonis/Core/HttpContext'
import { DateTime } from 'luxon'

export default class UserActiveMiddleware {
  public async handle({ auth }: HttpContextContract, next: () => Promise<void>) {
    await auth.check()

    if (auth.user) {
      auth.user.lastActiveAt = DateTime.now()
      await auth.user.save()
    }

    await next()
  }
}
