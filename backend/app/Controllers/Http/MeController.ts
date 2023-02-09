import type { HttpContextContract } from '@ioc:Adonis/Core/HttpContext'
import { rules, schema } from '@ioc:Adonis/Core/Validator'
import UserCleanUp from 'App/Services/UserCleanUp'
import { DateTime } from 'luxon'

export default class MeController {
  public async view({ response, auth }: HttpContextContract) {
    if (!auth.user) {
      return response.unauthorized()
    }

    return response.json({ message: 'Got user', user: auth.user })
  }

  public async update({ request, response, auth }: HttpContextContract) {
    if (!auth.user) {
      return response.unauthorized()
    }

    const validations = schema.create({
      email: schema.string.optional({}, [
        rules.email(),
        rules.unique({ table: 'users', column: 'email', whereNot: { email: auth.user.email } }),
      ]),
      password: schema.string.optional({}, [rules.minLength(8)]),
      username: schema.string.optional({}, [
        rules.unique({
          table: 'users',
          column: 'username',
          whereNot: { username: auth.user.username },
        }),
      ]),
    })

    const data = await request.validate({ schema: validations })

    if (data.username) {
      if (data.username.startsWith('DELETED@')) {
        throw new Error('Username not allowed')
      }

      auth.user.username = data.username
    }

    if (data.email) {
      auth.user.email = data.email
    }

    if (data.password) {
      auth.user.password = data.password
    }

    if (auth.user.$isDirty) {
      await auth.user.save()
    }

    return response.json({ message: 'User updated', user: auth.user })
  }

  public async delete({ response, auth }: HttpContextContract) {
    if (!auth.user) {
      return response.unauthorized()
    }

    const userCleanUpService = new UserCleanUp()

    await userCleanUpService.run(auth.user)
    await auth.use('api').revoke()

    return response.json({ message: 'User deleted' })
  }

  public async export({ response, auth }: HttpContextContract) {
    if (!auth.user) {
      return response.unauthorized()
    }

    // Load relations
    await auth.user.load('games')
    await auth.user.load('gameVotes')

    return response.json({ message: 'Exported data', export: auth.user.toJSON() })
  }
}
