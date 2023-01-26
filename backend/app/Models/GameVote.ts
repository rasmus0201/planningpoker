import { DateTime } from 'luxon'
import { BaseModel, BelongsTo, belongsTo, column, hasOne, HasOne } from '@ioc:Adonis/Lucid/Orm'
import GameRound from './GameRound'
import User from './User'

export default class GameVote extends BaseModel {
  @column({ isPrimary: true })
  public id: number

  @column()
  public userId: number

  @column()
  public roundId: number

  @column()
  public vote: string

  @column.dateTime({ autoCreate: true })
  public createdAt: DateTime

  @column.dateTime({ autoCreate: true, autoUpdate: true })
  public updatedAt: DateTime

  @hasOne(() => GameRound)
  public round: HasOne<typeof GameRound>

  @belongsTo(() => User)
  public user: BelongsTo<typeof User>
}
