/*
|--------------------------------------------------------------------------
| Routes
|--------------------------------------------------------------------------
|
| This file is dedicated for defining HTTP routes. A single file is enough
| for majority of projects, however you can define routes in different
| files and just make sure to import them inside this file. For example
|
| Define routes in following two files
| ├── start/routes/cart.ts
| ├── start/routes/customer.ts
|
| and then import them inside `start/routes.ts` as follows
|
| import './routes/cart'
| import './routes/customer'
|
*/

import Route from '@ioc:Adonis/Core/Route'
import User from 'App/Models/User'
import { rules, schema } from '@ioc:Adonis/Core/Validator'
import { DateTime } from 'luxon'

Route.get('/', async () => {
  return { hello: 'world' }
})

Route.post('/api/auth/register', async ({ request, response }) => {
  const validations = await schema.create({
    email: schema.string({}, [rules.email(), rules.unique({ table: 'users', column: 'email' })]),
    password: schema.string({}, [rules.minLength(8)]),
    username: schema.string({}, [rules.unique({ table: 'users', column: 'username' })]),
  })

  const data = await request.validate({ schema: validations })
  const user = await User.create(data)

  return response.created(user)
})

Route.post('/api/auth/login', async ({ auth, request, response }) => {
  const email = request.input('email')
  const password = request.input('password')

  try {
    const token = await auth.use('api').attempt(email, password, {
      expiresIn: '30 days',
    })

    const user = await User.findByOrFail('email', email)
    user.lastActiveAt = DateTime.now()
    await user.save()

    return response.ok({ token, user })
  } catch {
    return response.unauthorized({ error: 'Invalid credentials' })
  }
})

Route.post('/api/auth/logout', async ({ auth, response }) => {
  await auth.use('api').revoke()

  return response.ok({
    revoked: true,
  })
})
