import { BaseCommand } from '@adonisjs/core/build/standalone'
import schedule from 'node-schedule'

export default class Scheduler extends BaseCommand {
  public static commandName = 'schedule'

  public static settings = {
    loadApp: true,
    stayAlive: true,
  }

  public async run() {
    schedule.scheduleJob('0 0 * * *', async () => {
      await this.kernel.exec('cleanup:users', [])
    })

    schedule.scheduleJob('*/30 * * * *', async () => {
      await this.kernel.exec('cleanup:unfinished-games', [])
    })
  }
}
