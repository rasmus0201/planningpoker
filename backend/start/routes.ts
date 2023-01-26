import Route from '@ioc:Adonis/Core/Route'
import MeController from 'App/Controllers/Http/MeController'
import AuthController from 'App/Controllers/Http/AuthController'
import GamesController from 'App/Controllers/Http/GamesController'

Route.get('/', async () => {
  return { hello: 'world' }
})

Route.group(() => {
  Route.group(() => {
    Route.post('/register', async (ctx) => new AuthController().register(ctx)).middleware('guest')
    Route.post('/login', async (ctx) => new AuthController().login(ctx)).middleware('guest')
    Route.post('/logout', async (ctx) => new AuthController().logout(ctx)).middleware('auth')

    Route.post('/forgot-password', async (ctx) => new AuthController().forgotPassword(ctx))
      .middleware('guest')
      .as('auth.forgotPassword')
    Route.post('/reset-password', async (ctx) => new AuthController().resetPassword(ctx))
      .middleware('guest')
      .as('auth.resetPassword')
  }).prefix('/auth')

  Route.group(() => {
    Route.get('/', async (ctx) => new MeController().view(ctx))
    Route.patch('/', async (ctx) => new MeController().update(ctx))
    Route.delete('/', async (ctx) => new MeController().delete(ctx))
    Route.post('/export', async (ctx) => new MeController().export(ctx))
  })
    .prefix('/me')
    .middleware('auth')

  Route.group(() => {
    Route.get('/', async (ctx) => new GamesController().index(ctx))
    Route.post('/', async (ctx) => new GamesController().create(ctx))
    Route.get('/:pin', async (ctx) => new GamesController().view(ctx))
    Route.delete('/:pin', async (ctx) => new GamesController().delete(ctx))
  })
    .prefix('/games')
    .middleware('auth')
}).prefix('/api')
