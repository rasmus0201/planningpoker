import { BaseCommand } from '@adonisjs/core/build/standalone'
import UserCleanUp from 'App/Services/UserCleanUp'
import { DateTime } from 'luxon'

export default class CleanupUsers extends BaseCommand {
  public static commandName = 'cleanup:users'

  public static settings = {
    loadApp: true,
  }

  public async run() {
    const { default: User } = await import('App/Models/User')

    this.logger.info('Running user cleanup')

    const userCleanUpService = new UserCleanUp()

    const cutoffDate = DateTime.local().minus({ days: 90 }).toSQLDate()
    const users = await User.query()
      .preload('apiTokens')
      .preload('games')
      .preload('gameVotes')
      .whereNull('deleted_at')
      .andWhere('created_at', '<', cutoffDate)
      .andWhere('last_active_at', '<', cutoffDate)

    let cleanUpCount = 0
    for (const user of users) {
      try {
        await userCleanUpService.run(user)
        cleanUpCount++
      } catch (error) {
        // Gracefully catch
      }
    }

    this.logger.info(`Anonymized ${cleanUpCount} out of ${users.length} eligible accounts`)
    this.logger.info('Finished running user cleanup')
  }
}
