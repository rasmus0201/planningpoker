"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const Ws_1 = __importDefault(global[Symbol.for('ioc.use')]("App/Services/Ws"));
const crypto_1 = __importDefault(require("crypto"));
const SessionStore_1 = global[Symbol.for('ioc.use')]("App/Services/SessionStore");
const luxon_1 = require("luxon");
const standalone_1 = require("@adonisjs/core/build/standalone");
const Auth_1 = __importDefault(global[Symbol.for('ioc.use')]("Adonis/Addons/Auth"));
const WsAuthGuard_1 = global[Symbol.for('ioc.use')]("App/Services/WsAuthGuard");
const Game_1 = __importDefault(global[Symbol.for('ioc.use')]("App/Models/Game"));
const GameVote_1 = __importDefault(global[Symbol.for('ioc.use')]("App/Models/GameVote"));
Ws_1.default.boot();
const io = Ws_1.default.io;
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
];
const randomId = () => crypto_1.default.randomBytes(8).toString('hex');
const sessionStore = new SessionStore_1.InMemorySessionStore();
io.use(async (socket, next) => {
    const sessionId = socket.handshake.auth.sessionId;
    if (sessionId) {
        const session = sessionStore.findSession(sessionId);
        if (session) {
            socket.sessionId = sessionId;
            socket.userId = session.userId;
            socket.gamePin = session.gamePin;
            socket.joinType = session.joinType;
            socket.token = session.token;
            socket.color = session.color;
            socket.user = session.user;
            return next();
        }
    }
    const gamePin = socket.handshake.auth.gamePin;
    if (!gamePin) {
        return next(new Error('No game pin'));
    }
    const token = socket.handshake.auth.token;
    if (!token) {
        return next(new Error('No auth token'));
    }
    const joinType = socket.handshake.auth.joinType;
    if (!token) {
        return next(new Error('No join type'));
    }
    let user;
    try {
        const api = Auth_1.default.makeMapping(standalone_1.HttpContext.create(socket.request.url || '/', {}, socket.request), 'api');
        const wsGuard = new WsAuthGuard_1.WsAuthGuard(socket, api.provider, api.tokenProvider);
        const authenticatable = await wsGuard.authenticate();
        if (!authenticatable) {
            throw new Error('failed');
        }
        user = authenticatable;
    }
    catch (error) {
        return next(new Error('Auth failed'));
    }
    socket.sessionId = randomId();
    socket.userId = randomId();
    socket.gamePin = gamePin;
    socket.joinType = joinType;
    socket.token = token;
    socket.color = COLORS[Math.floor(Math.random() * COLORS.length)];
    socket.user = {
        id: user.id,
        username: user.username,
    };
    next();
});
io.on('connection', async (socket) => {
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
    });
    socket.on('ready', async () => {
        socket.emit('session', {
            sessionId: socket.sessionId,
            userId: socket.userId,
            gamePin: socket.gamePin,
            color: socket.color,
            user: socket.user,
        });
        socket.join(socket.userId);
        socket.join(socket.gamePin);
        if (socket.joinType === 'host') {
            socket.join(`host-${socket.gamePin}`);
        }
        const users = [];
        sessionStore.findAllSessions().forEach((session) => {
            if (session.gamePin !== socket.gamePin) {
                return;
            }
            users.push({
                userId: session.userId,
                joinType: session.joinType,
                username: session.user.username,
                connected: session.connected,
            });
        });
        socket.emit('users', users);
        const game = (await Game_1.default.findBy('pin', socket.gamePin));
        await game.load('latestRound');
        socket.emit(`game ${game.state}`);
        if (game.state === 'voting' && game.latestRound) {
            if (socket.joinType === 'play') {
                const existingVote = await GameVote_1.default.query()
                    .where('round_id', game.latestRound.id)
                    .where('user_id', socket.user.id)
                    .first();
                if (existingVote) {
                    socket.emit('game session vote', {
                        vote: existingVote.vote,
                    });
                }
            }
            const playerSessions = sessionStore
                .findAllSessions()
                .filter((s) => s.gamePin === game.pin && s.joinType === 'play');
            const votes = await GameVote_1.default.query().preload('user').where('round_id', game.latestRound.id);
            const votingUserIds = votes
                .flatMap((v) => v.userId)
                .reduce((acc, curr) => ((acc[curr] = true), acc), {});
            for (const playerSession of playerSessions) {
                if (!votingUserIds[playerSession.user.id]) {
                    continue;
                }
                socket.emit('game vote', {
                    userId: playerSession.userId,
                    username: playerSession.user.username,
                });
            }
        }
        else if (game.state === 'reveal' && game.latestRound) {
            const votes = await GameVote_1.default.query().preload('user').where('round_id', game.latestRound.id);
            sendGameRevealEvent(votes, socket, 'socket');
        }
        socket.broadcast.to(socket.gamePin).emit('user connected', {
            userId: socket.userId,
            joinType: socket.joinType,
            username: socket.user.username,
            connected: true,
        });
    });
    socket.on('users list', () => {
        const users = [];
        sessionStore.findAllSessions().forEach((session) => {
            if (session.gamePin !== socket.gamePin) {
                return;
            }
            users.push({
                userId: session.userId,
                joinType: session.joinType,
                username: session.user.username,
                connected: session.connected,
            });
        });
        socket.emit('users', users);
    });
    socket.on('token moved', (position) => {
        socket.broadcast.to(`host-${socket.gamePin}`).emit('token moved', {
            position,
            user: {
                userId: socket.userId,
                username: socket.user.username,
                color: socket.color,
            },
        });
    });
    socket.on('game start', async () => {
        if (socket.joinType !== 'host') {
            return;
        }
        const game = (await Game_1.default.findBy('pin', socket.gamePin));
        game.state = 'voting';
        await game.related('rounds').create({
            userId: socket.user.id,
            gameId: game.id,
            label: 'Round #1',
        });
        game.save();
        io.in(socket.gamePin).emit('game voting');
    });
    socket.on('game forceReveal', async () => {
        if (socket.joinType !== 'host') {
            return;
        }
        const game = (await Game_1.default.findBy('pin', socket.gamePin));
        await game.load('latestRound');
        if (!game.latestRound) {
            return;
        }
        const votes = await GameVote_1.default.query().preload('user').where('round_id', game.latestRound.id);
        game.state = 'reveal';
        await game.save();
        sendGameRevealEvent(votes, socket, 'broadcast');
    });
    socket.on('game continue', async () => {
        if (socket.joinType !== 'host') {
            return;
        }
        const game = (await Game_1.default.findBy('pin', socket.gamePin));
        game.state = 'voting';
        await game.loadCount('rounds');
        await game.related('rounds').create({
            userId: socket.user.id,
            gameId: game.id,
            label: `Round #${game.$extras.rounds_count + 1}`,
        });
        game.save();
        io.in(socket.gamePin).emit('game voting');
    });
    socket.on('game finish', async () => {
        if (socket.joinType !== 'host') {
            return;
        }
        const game = (await Game_1.default.findBy('pin', socket.gamePin));
        game.state = 'finished';
        await game.save();
        io.in(socket.gamePin).emit('game finished');
    });
    socket.on('game vote', async (value) => {
        if (socket.joinType !== 'play') {
            return;
        }
        const game = (await Game_1.default.findBy('pin', socket.gamePin));
        await game.load('latestRound');
        if (!game.latestRound) {
            return;
        }
        const existingVote = await GameVote_1.default.query()
            .where('round_id', game.latestRound.id)
            .where('user_id', socket.user.id)
            .first();
        if (existingVote) {
            return;
        }
        await GameVote_1.default.create({
            roundId: game.latestRound.id,
            userId: socket.user.id,
            vote: value,
        });
        io.in(socket.gamePin).emit('game vote', {
            userId: socket.userId,
            username: socket.user.username,
        });
        const playerIds = sessionStore
            .findAllSessions()
            .filter((s) => s.gamePin === game.pin && s.joinType === 'play')
            .map((s) => s.user.id);
        const votes = await GameVote_1.default.query().preload('user').where('round_id', game.latestRound.id);
        const votingUserIds = votes.flatMap((v) => v.userId);
        const missingVotesUserIds = playerIds.filter((x) => !votingUserIds.includes(x));
        if (missingVotesUserIds.length === 0) {
            game.state = 'reveal';
            await game.save();
            setTimeout(() => {
                sendGameRevealEvent(votes, socket, 'broadcast');
            }, 1000);
        }
    });
    socket.on('disconnect', async () => {
        const matchingSockets = await io.in(socket.userId).fetchSockets();
        if (matchingSockets.length === 0) {
            socket.broadcast.to(socket.gamePin).emit('user disconnected', socket.userId);
            sessionStore.saveSession(socket.sessionId, {
                sessionId: socket.sessionId,
                userId: socket.userId,
                gamePin: socket.gamePin,
                joinType: socket.joinType,
                token: socket.token,
                color: socket.color,
                user: socket.user,
                disconnectedAt: luxon_1.DateTime.now().toMillis(),
                connected: false,
            });
        }
    });
});
const sendGameRevealEvent = (votes, socket, type) => {
    const data = votes.map((v) => {
        const session = sessionStore.findByDBUserId(v.userId);
        return {
            userId: session?.userId,
            username: v.user.username,
            color: socket.color,
            user: session?.user,
            vote: v.vote,
        };
    });
    data.sort((a, b) => {
        return a.vote.localeCompare(b.vote);
    });
    if (type === 'broadcast') {
        io.in(socket.gamePin).emit('game reveal', data);
    }
    else {
        socket.emit('game reveal', data);
    }
};
//# sourceMappingURL=socket.js.map