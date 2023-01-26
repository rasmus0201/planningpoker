import { DateTime } from 'luxon'

export interface Session {
  sessionId: string
  userId: string
  gamePin: string
  joinType: 'host' | 'play' | 'spectate'
  token: string
  color: string
  user: {
    id: number
    username: string
  }
  connected: boolean
  disconnectedAt?: number
}

class SessionStore {
  public findSession(id: string) {}
  public saveSession(id: string, session: Session) {}
  public findAllSessions() {}
}

export class InMemorySessionStore extends SessionStore {
  #sessions: Map<string, Session>

  constructor() {
    super()
    this.#sessions = new Map<string, Session>()
  }

  public findSession(id: string) {
    return this.#sessions.get(id)
  }

  public saveSession(id: string, session: Session) {
    this.#sessions.set(id, session)
  }

  public deleteSession(id: string) {
    this.#sessions.delete(id)
  }

  public findByDBUserId(id: number): Session | undefined {
    for (const session of this.#sessions.values()) {
      if (session.user.id === id) {
        return session
      }
    }

    return undefined
  }

  public findAllSessions(): Session[] {
    return [...this.#sessions.values()]
      .map((session: Session) => {
        if (!session.disconnectedAt) {
          return session
        }

        // 60 seconds grace period to be offline from the socket.
        if (DateTime.now().minus({ seconds: 60 }).toMillis() <= session.disconnectedAt) {
          return session
        }

        this.deleteSession(session.sessionId)
        return null
      })
      .filter((s): s is Session => s !== null)
  }
}
