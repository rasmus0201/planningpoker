import Ws from 'App/Services/Ws'
import { type Socket } from 'socket.io'
import crypto from 'crypto'
import { InMemorySessionStore } from 'App/Services/SessionStore'
import { DateTime } from 'luxon'
import { HttpContext } from '@adonisjs/core/build/standalone'
import AuthManager, { OATGuardContract } from '@ioc:Adonis/Addons/Auth'
import { WsAuthGuard } from 'App/Services/WsAuthGuard'
import User from 'App/Models/User'
import Game from 'App/Models/Game'
import GameVote from 'App/Models/GameVote'

Ws.boot()

const io = Ws.io

type JoinType = 'host' | 'play' | 'spectate'

interface AuthenticatableSocket extends Socket {
  sessionId: string
  userId: string
  gamePin: string
  joinType: JoinType
  token: string
  color: string
  user: {
    id: number
    username: string
  }
}

interface WsUser {
  userId: string
  username: string
  joinType: JoinType
  connected: boolean
}

const COLORS = [
  '#f6e58d',
  '#ffbe76',
  '#ff7979',
  '#badc58',
  '#dff9fb',
  '#f9ca24',
  '#f0932b',
  '#eb4d4b',
  '#6ab04c',
  '#c7ecee',
  '#7ed6df',
  '#e056fd',
  '#686de0',
  '#30336b',
  '#95afc0',
  '#22a6b3',
  '#be2edd',
  '#4834d4',
  '#130f40',
  '#535c68',
]

const randomId = () => crypto.randomBytes(8).toString('hex')
const sessionStore = new InMemorySessionStore()

io.use(async (socket: AuthenticatableSocket, next) => {
  const sessionId = socket.handshake.auth.sessionId
  if (sessionId) {
    const session = sessionStore.findSession(sessionId)

    if (session) {
      socket.sessionId = sessionId
      socket.userId = session.userId
      socket.gamePin = session.gamePin
      socket.joinType = session.joinType
      socket.token = session.token
      socket.color = session.color
      socket.user = session.user
      return next()
    }
  }

  // TODO: Check that the game actually exists
  //       should also then check the join type if it is valid.
  const gamePin = socket.handshake.auth.gamePin
  if (!gamePin) {
    return next(new Error('No game pin'))
  }

  const token = socket.handshake.auth.token
  if (!token) {
    return next(new Error('No auth token'))
  }

  const joinType = socket.handshake.auth.joinType
  if (!token) {
    return next(new Error('No join type'))
  }

  let user: User
  try {
    // @ts-ignore
    const api: OATGuardContract<'user', 'api'> = AuthManager.makeMapping(
      // @ts-ignore
      HttpContext.create(socket.request.url || '/', {}, socket.request),
      'api'
    )

    const wsGuard = new WsAuthGuard(socket, api.provider, api.tokenProvider)
    const authenticatable = await wsGuard.authenticate()

    if (!authenticatable) {
      throw new Error('failed')
    }

    user = authenticatable
  } catch (error) {
    return next(new Error('Auth failed'))
  }

  // Create new session
  socket.sessionId = randomId()
  socket.userId = randomId()
  socket.gamePin = gamePin
  socket.joinType = joinType
  socket.token = token
  socket.color = COLORS[Math.floor(Math.random() * COLORS.length)]
  socket.user = {
    id: user.id,
    username: user.username,
  }

  next()
})

io.on('connection', async (socket: AuthenticatableSocket) => {
  // Persist session
  sessionStore.saveSession(socket.sessionId, {
    sessionId: socket.sessionId,
    userId: socket.userId,
    gamePin: socket.gamePin,
    joinType: socket.joinType,
    token: socket.token,
    color: socket.color,
    user: socket.user,
    disconnectedAt: undefined,
    connected: true,
  })

  // Run when connection establishes.
  socket.on('ready', async () => {
    // Emit session details
    socket.emit('session', {
      sessionId: socket.sessionId,
      userId: socket.userId,
      gamePin: socket.gamePin,
      color: socket.color,
      user: socket.user,
    })

    // Join the "userId" room
    socket.join(socket.userId)

    // Join the game room
    socket.join(socket.gamePin)

    // Join the host room
    if (socket.joinType === 'host') {
      socket.join(`host-${socket.gamePin}`)
    }

    // Fetch existing users
    const users: WsUser[] = []
    sessionStore.findAllSessions().forEach((session) => {
      if (session.gamePin !== socket.gamePin) {
        return
      }

      users.push({
        userId: session.userId,
        joinType: session.joinType,
        username: session.user.username,
        connected: session.connected,
      })
    })
    socket.emit('users', users)

    // Get game from DB and send appropriate event
    // according to game state.
    const game = (await Game.findBy('pin', socket.gamePin))!
    await game.load('latestRound')
    socket.emit(`game ${game.state}`)

    if (game.state === 'voting' && game.latestRound) {
      if (socket.joinType === 'play') {
        // Check if user already voted
        const existingVote = await GameVote.query()
          .where('round_id', game.latestRound.id)
          .where('user_id', socket.user.id)
          .first()

        if (existingVote) {
          socket.emit('game session vote', {
            vote: existingVote.vote,
          })
        }
      }

      // Emit all the players who already gave a vote
      const playerSessions = sessionStore
        .findAllSessions()
        .filter((s) => s.gamePin === game.pin && s.joinType === 'play')

      const votes = await GameVote.query().preload('user').where('round_id', game.latestRound.id)
      const votingUserIds = votes
        .flatMap((v) => v.userId)
        .reduce((acc, curr) => ((acc[curr] = true), acc), {})

      for (const playerSession of playerSessions) {
        if (!votingUserIds[playerSession.user.id]) {
          continue
        }

        socket.emit('game vote', {
          userId: playerSession.userId,
          username: playerSession.user.username,
        })
      }
    } else if (game.state === 'reveal' && game.latestRound) {
      const votes = await GameVote.query().preload('user').where('round_id', game.latestRound.id)

      sendGameRevealEvent(votes, socket, 'socket')
    }

    // Notify existing users
    socket.broadcast.to(socket.gamePin).emit('user connected', {
      userId: socket.userId,
      joinType: socket.joinType,
      username: socket.user.username,
      connected: true,
    })
  })

  socket.on('users list', () => {
    const users: WsUser[] = []
    sessionStore.findAllSessions().forEach((session) => {
      if (session.gamePin !== socket.gamePin) {
        return
      }

      users.push({
        userId: session.userId,
        joinType: session.joinType,
        username: session.user.username,
        connected: session.connected,
      })
    })

    socket.emit('users', users)
  })

  // Game play
  socket.on('token moved', (position) => {
    socket.broadcast.to(`host-${socket.gamePin}`).emit('token moved', {
      position,
      user: {
        userId: socket.userId,
        username: socket.user.username,
        color: socket.color,
      },
    })
  })

  socket.on('game start', async () => {
    if (socket.joinType !== 'host') {
      return
    }

    const game = (await Game.findBy('pin', socket.gamePin))!
    game.state = 'voting'

    await game.related('rounds').create({
      userId: socket.user.id,
      gameId: game.id,
      label: 'Round #1',
    })

    game.save()

    io.in(socket.gamePin).emit('game voting')
  })

  socket.on('game forceReveal', async () => {
    if (socket.joinType !== 'host') {
      return
    }

    // Check if game has any round
    const game = (await Game.findBy('pin', socket.gamePin))!
    await game.load('latestRound')

    if (!game.latestRound) {
      return
    }

    const votes = await GameVote.query().preload('user').where('round_id', game.latestRound.id)

    game.state = 'reveal'
    await game.save()

    sendGameRevealEvent(votes, socket, 'broadcast')
  })

  socket.on('game continue', async () => {
    if (socket.joinType !== 'host') {
      return
    }

    const game = (await Game.findBy('pin', socket.gamePin))!
    game.state = 'voting'

    await game.loadCount('rounds')
    await game.related('rounds').create({
      userId: socket.user.id,
      gameId: game.id,
      label: `Round #${game.$extras.rounds_count + 1}`,
    })

    game.save()

    io.in(socket.gamePin).emit('game voting')
  })

  socket.on('game finish', async () => {
    if (socket.joinType !== 'host') {
      return
    }

    const game = (await Game.findBy('pin', socket.gamePin))!
    game.state = 'finished'
    await game.save()

    io.in(socket.gamePin).emit('game finished')
  })

  socket.on('game vote', async (value: string) => {
    if (socket.joinType !== 'play') {
      return
    }

    // Check if game has any round
    const game = (await Game.findBy('pin', socket.gamePin))!
    await game.load('latestRound')

    if (!game.latestRound) {
      return
    }

    // Check if user already voted
    const existingVote = await GameVote.query()
      .where('round_id', game.latestRound.id)
      .where('user_id', socket.user.id)
      .first()

    if (existingVote) {
      return
    }

    // Store value in DB
    await GameVote.create({
      roundId: game.latestRound.id,
      userId: socket.user.id,
      vote: value,
    })

    io.in(socket.gamePin).emit('game vote', {
      userId: socket.userId,
      username: socket.user.username,
    })

    const playerIds = sessionStore
      .findAllSessions()
      .filter((s) => s.gamePin === game.pin && s.joinType === 'play')
      .map((s) => s.user.id)

    const votes = await GameVote.query().preload('user').where('round_id', game.latestRound.id)
    const votingUserIds = votes.flatMap((v) => v.userId)
    const missingVotesUserIds = playerIds.filter((x) => !votingUserIds.includes(x))

    // Send reveal if all voted
    if (missingVotesUserIds.length === 0) {
      game.state = 'reveal'
      await game.save()

      // Delay reveal event, as the host should display all users voted, before it reveals
      setTimeout(() => {
        sendGameRevealEvent(votes, socket, 'broadcast')
      }, 1000)
    }
  })

  // Notify users upon disconnection
  socket.on('disconnect', async () => {
    const matchingSockets = await io.in(socket.userId).fetchSockets()
    if (matchingSockets.length === 0) {
      // Notify other users
      socket.broadcast.to(socket.gamePin).emit('user disconnected', socket.userId)

      // Update the connection status of the session
      sessionStore.saveSession(socket.sessionId, {
        sessionId: socket.sessionId,
        userId: socket.userId,
        gamePin: socket.gamePin,
        joinType: socket.joinType,
        token: socket.token,
        color: socket.color,
        user: socket.user,
        disconnectedAt: DateTime.now().toMillis(),
        connected: false,
      })
    }
  })
})

const sendGameRevealEvent = (
  votes: GameVote[],
  socket: AuthenticatableSocket,
  type: 'broadcast' | 'socket'
) => {
  const data = votes.map((v) => {
    const session = sessionStore.findByDBUserId(v.userId)

    return {
      userId: session?.userId,
      username: v.user.username,
      color: socket.color,
      user: session?.user,
      vote: v.vote,
    }
  })

  data.sort((a, b) => {
    return a.vote.localeCompare(b.vote)
  })

  if (type === 'broadcast') {
    io.in(socket.gamePin).emit('game reveal', data)
  } else {
    socket.emit('game reveal', data)
  }
}
