import { DateTime } from 'luxon'
import { BaseModel, column, hasMany, HasMany, HasOne, hasOne } from '@ioc:Adonis/Lucid/Orm'
import GameRound from './GameRound'

export default class Game extends BaseModel {
  @column({ isPrimary: true })
  public id: number

  @column()
  public userId: number

  @column()
  public title: string

  @column()
  public pin: string

  @column()
  public state: 'lobby' | 'voting' | 'reveal' | 'finished'

  @column.dateTime({ autoCreate: true })
  public createdAt: DateTime

  @column.dateTime({ autoCreate: true, autoUpdate: true })
  public updatedAt: DateTime

  @hasMany(() => GameRound)
  public rounds: HasMany<typeof GameRound>

  @hasOne(() => GameRound, {
    onQuery: (query) => {
      query.orderBy('id', 'desc')
      query.limit(1)
    },
  })
  public latestRound: HasOne<typeof GameRound>
}
