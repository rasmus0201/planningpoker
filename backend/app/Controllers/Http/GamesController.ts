import type { HttpContextContract } from '@ioc:Adonis/Core/HttpContext'
import { rules, schema } from '@ioc:Adonis/Core/Validator'
import Game from 'App/Models/Game'

export default class GamesController {
  public async index({ auth, response }: HttpContextContract) {
    if (!auth.user) {
      return response.unauthorized()
    }

    await auth.user.load('games')

    return response.json({ message: 'Got games', games: auth.user.games })
  }

  public async create({ auth, response, request }: HttpContextContract) {
    if (!auth.user) {
      return response.unauthorized()
    }

    const validations = schema.create({
      title: schema.string.optional({}, [rules.maxLength(32)]),
    })

    const data = await request.validate({ schema: validations })
    const randomPin = Math.floor(100000 + Math.random() * 900000)

    const game = await Game.create({
      userId: auth.user.id,
      title: data.title ?? '',
      pin: randomPin.toString(),
      state: 'lobby',
    })

    return response.json({ message: 'Created game', game })
  }

  public async view({ auth, response, request }: HttpContextContract) {
    if (!auth.user) {
      return response.unauthorized()
    }

    try {
      const game = await Game.findByOrFail('pin', request.param('pin', ''))
      return response.json({ message: 'Got game', game })
    } catch (error) {
      return response.notFound({ message: 'Resource not found' })
    }
  }

  public async delete({ auth, response, request }: HttpContextContract) {
    if (!auth.user) {
      return response.unauthorized()
    }

    const game = await Game.findByOrFail('pin', request.param('pin', ''))
    if (!game || game.userId !== auth.user.id) {
      return response.unauthorized()
    }

    await game.delete()

    return response.json({ message: 'Delete game' })
  }
}
