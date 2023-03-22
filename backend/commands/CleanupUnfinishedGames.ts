import { BaseCommand } from '@adonisjs/core/build/standalone'
import { DateTime } from 'luxon'

export default class CleanupUnfinishedGames extends BaseCommand {
  public static commandName = 'cleanup:unfinished-games'

  public static settings = {
    loadApp: true,
  }

  public async run() {
    const { default: Game } = await import('App/Models/Game')

    const cutoffDate = DateTime.local().minus({ hours: 9 }).toSQL()

    const games = await Game.query()
      .where('state', '!=', 'finished')
      .andWhere('created_at', '<', cutoffDate)

    let cleanUpCount = 0
    for (const game of games) {
      try {
        game.state = 'finished'
        await game.save()

        cleanUpCount++
      } catch (error) {
        // Gracefully catch
      }
    }

    this.logger.info(`Finished ${cleanUpCount} out of ${games.length} eligible games`)

    this.logger.info('Running unfinished game cleanup')
    this.logger.info('Finished running unfinished game cleanup')
  }
}
