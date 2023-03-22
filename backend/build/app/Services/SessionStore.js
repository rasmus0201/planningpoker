"use strict";
var __classPrivateFieldSet = (this && this.__classPrivateFieldSet) || function (receiver, state, value, kind, f) {
    if (kind === "m") throw new TypeError("Private method is not writable");
    if (kind === "a" && !f) throw new TypeError("Private accessor was defined without a setter");
    if (typeof state === "function" ? receiver !== state || !f : !state.has(receiver)) throw new TypeError("Cannot write private member to an object whose class did not declare it");
    return (kind === "a" ? f.call(receiver, value) : f ? f.value = value : state.set(receiver, value)), value;
};
var __classPrivateFieldGet = (this && this.__classPrivateFieldGet) || function (receiver, state, kind, f) {
    if (kind === "a" && !f) throw new TypeError("Private accessor was defined without a getter");
    if (typeof state === "function" ? receiver !== state || !f : !state.has(receiver)) throw new TypeError("Cannot read private member from an object whose class did not declare it");
    return kind === "m" ? f : kind === "a" ? f.call(receiver) : f ? f.value : state.get(receiver);
};
var _InMemorySessionStore_sessions;
Object.defineProperty(exports, "__esModule", { value: true });
exports.InMemorySessionStore = void 0;
const luxon_1 = require("luxon");
class SessionStore {
    findSession(_id) { }
    saveSession(_id, _session) { }
    findAllSessions() { }
}
class InMemorySessionStore extends SessionStore {
    constructor() {
        super();
        _InMemorySessionStore_sessions.set(this, void 0);
        __classPrivateFieldSet(this, _InMemorySessionStore_sessions, new Map(), "f");
    }
    findSession(id) {
        return __classPrivateFieldGet(this, _InMemorySessionStore_sessions, "f").get(id);
    }
    saveSession(id, session) {
        __classPrivateFieldGet(this, _InMemorySessionStore_sessions, "f").set(id, session);
    }
    deleteSession(id) {
        __classPrivateFieldGet(this, _InMemorySessionStore_sessions, "f").delete(id);
    }
    findByDBUserId(id) {
        for (const session of __classPrivateFieldGet(this, _InMemorySessionStore_sessions, "f").values()) {
            if (session.user.id === id) {
                return session;
            }
        }
        return undefined;
    }
    findAllSessions() {
        return [...__classPrivateFieldGet(this, _InMemorySessionStore_sessions, "f").values()]
            .map((session) => {
            if (!session.disconnectedAt) {
                return session;
            }
            if (luxon_1.DateTime.now().minus({ minutes: 120 }).toMillis() <= session.disconnectedAt) {
                return session;
            }
            this.deleteSession(session.sessionId);
            return null;
        })
            .filter((s) => s !== null);
    }
}
exports.InMemorySessionStore = InMemorySessionStore;
_InMemorySessionStore_sessions = new WeakMap();
//# sourceMappingURL=SessionStore.js.map