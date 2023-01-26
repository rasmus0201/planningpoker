import type { HttpContextContract } from '@ioc:Adonis/Core/HttpContext'
import User from 'App/Models/User'
import { DateTime } from 'luxon'
import { rules, schema } from '@ioc:Adonis/Core/Validator'
import PasswordReset from 'App/Models/PasswordReset'
import { string } from '@ioc:Adonis/Core/Helpers'
import Mail from '@ioc:Adonis/Addons/Mail'
import Env from '@ioc:Adonis/Core/Env'
import Database from '@ioc:Adonis/Lucid/Database'

export default class AuthController {
  public async register({ auth, request, response }: HttpContextContract) {
    const validations = schema.create({
      email: schema.string({}, [rules.email(), rules.unique({ table: 'users', column: 'email' })]),
      password: schema.string({}, [rules.minLength(8)]),
      username: schema.string({}, [rules.unique({ table: User.table, column: 'username' })]),
    })

    const data = await request.validate({ schema: validations })
    const user = await User.create(data)

    const token = await auth.use('api').generate(user)

    return response.created({ user, token })
  }

  public async login({ auth, request, response }: HttpContextContract) {
    const email = request.input('email')
    const password = request.input('password')

    try {
      const token = await auth.use('api').attempt(email, password, {
        expiresIn: '60 days',
      })

      const user = await User.findByOrFail('email', email)
      user.lastActiveAt = DateTime.now()
      await user.save()

      return response.ok({ token, user })
    } catch {
      return response.unauthorized({ error: 'Invalid credentials' })
    }
  }

  public async logout({ response, auth }: HttpContextContract) {
    if (!auth.user) {
      return response.unauthorized()
    }

    await auth.use('api').revoke()

    return response.json({ message: 'User logged out' })
  }

  public async forgotPassword({ request, response }: HttpContextContract) {
    const validations = schema.create({
      email: schema.string({}, [rules.email()]),
      returnPath: schema.string(),
    })

    const data = await request.validate({ schema: validations })
    const user = await User.findBy('email', data.email)
    if (user) {
      const passwordReset = await PasswordReset.create({
        email: user.email,
        token: string.generateRandom(64),
        expiredAt: DateTime.now().plus({ hours: 1 }),
      })

      const url = `${Env.get('APP_FRONTEND_URL')}${data.returnPath.trim()}?token=${
        passwordReset.token
      }`

      Mail.send((message) => {
        message
          .from(Env.get('MAIL_FROM'))
          .to(user.email)
          .subject(`Password reset on ${Env.get('APP_NAME')}`)
          .htmlView('emails/reset-password', { url, expiresIn: 'Expires in 1 hour.' })
      })
    }

    return response.ok({ message: 'Password reset sent' })
  }

  public async resetPassword({ request, response }: HttpContextContract) {
    const validations = schema.create({
      email: schema.string({}, [
        rules.email(),
        rules.exists({ table: User.table, column: 'email' }),
      ]),
      password: schema.string({}, [rules.minLength(8)]),
      token: schema.string({}, [
        rules.exists({
          table: PasswordReset.table,
          column: 'token',
          where: {
            email: request.input('email'),
          },
        }),
      ]),
    })

    const data = await request.validate({ schema: validations })
    const passwordReset = await PasswordReset.findByOrFail('token', data.token)

    if (DateTime.now() >= passwordReset.expiredAt) {
      return response.badRequest({
        expiredAt: passwordReset.expiredAt,
        now: DateTime.now(),
        errors: [{ rule: 'expired', field: 'token', message: 'Token has expired' }],
      })
    }

    // Save new password
    const user = await User.findByOrFail('email', data.email)
    user.password = data.password
    await user.save()

    // Expire reset token
    passwordReset.expiredAt = DateTime.now()
    passwordReset.save()

    // Make sure users API tokens get removed
    await Database.from('api_tokens').where('user_id', user.id).delete()

    return response.ok({ message: 'Password was reset' })
  }
}
