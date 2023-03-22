import { DateTime } from 'luxon'
import { BaseModel, column, HasMany, hasMany, hasOne, HasOne } from '@ioc:Adonis/Lucid/Orm'
import Game from './Game'
import GameVote from './GameVote'

export default class GameRound extends BaseModel {
  @column({ isPrimary: true })
  public id: number

  @column()
  public userId: number

  @column()
  public gameId: number

  @column()
  public label: string

  @column.dateTime({ autoCreate: true })
  public createdAt: DateTime

  @column.dateTime({ autoCreate: true, autoUpdate: true })
  public updatedAt: DateTime

  @hasOne(() => Game)
  public user: HasOne<typeof Game>

  @hasMany(() => GameVote)
  public votes: HasMany<typeof GameVote>
}
