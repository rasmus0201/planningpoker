import { DateTime } from 'luxon'
import Hash from '@ioc:Adonis/Core/Hash'
import { column, beforeSave, BaseModel, hasMany, HasMany } from '@ioc:Adonis/Lucid/Orm'
import Game from './Game'

export default class User extends BaseModel {
  @column({ isPrimary: true })
  public id: number

  @column()
  public email: string

  @column()
  public username: string

  @column({ serializeAs: null })
  public password: string

  @column()
  public rememberMeToken: string | null

  @column.dateTime({ autoCreate: true })
  public lastActiveAt: DateTime

  @column.dateTime({ autoCreate: true })
  public createdAt: DateTime

  @column.dateTime({ autoCreate: true, autoUpdate: true })
  public updatedAt: DateTime

  @column.dateTime()
  public deletedAt: DateTime | null

  @beforeSave()
  public static async beforeSave(user: User) {
    if (user.$dirty.password && user.password !== '') {
      user.password = await Hash.make(user.password)
    }

    if (!user.deletedAt && user.$dirty.password === '' && user.password === '') {
      user.deletedAt = DateTime.now()
    }
  }

  @hasMany(() => Game)
  public games: HasMany<typeof Game>
}
