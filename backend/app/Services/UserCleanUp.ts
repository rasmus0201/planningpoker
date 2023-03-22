import User from 'App/Models/User'
import { DateTime } from 'luxon'

export default class UserCleanUp {
  public async run(user: User) {
    user.username = `DELETED@${user.id}`
    user.email = `DELETED@${user.id}`
    user.password = ''
    user.deletedAt = DateTime.now()

    // Anonymize user votes
    if (user.gameVotes?.length) {
      user.gameVotes.forEach((v) => {
        v.vote = '[DELETED]'
        v.save()
      })
    }

    // Anonymize user games
    if (user.games?.length) {
      user.games.forEach((g) => {
        g.title = '[DELETED]'
        g.save()
      })
    }

    await user.related('apiTokens').query().delete()
    await user.save()
  }
}
