import { type Socket } from "socket.io-client";

export const PokerCards = [
  "1",
  "2",
  "3",
  "5",
  "8",
  "13",
  "20",
  "40",
  "100",
  "brownie",
  "coffee",
  "infinite",
  "yak-shaving"
] as const;

export type PokerCardsType = (typeof PokerCards)[number];

export type JoinType = "host" | "play" | "spectate";

export type GameStateType = "lobby" | "voting" | "reveal" | "finished";

export interface Game {
  id: number;
  user_id: number;
  title: string;
  pin: string;
  state: GameStateType;
}

export interface TokenMovedEvent {
  x: number;
  y: number;
  containerWidth: number;
  containerHeight: number;
}

export interface WsUser {
  userId: string;
  username: string;
  hasVoted: boolean;
  joinType?: JoinType;
  connected?: boolean;
  color?: string;
  self?: boolean;
}

export interface WsSession {
  sessionId: string;
  userId: string;
  gamePin: string;
  color: string;
  user: WsUser;
}

export interface AuthenticatableSocket extends Socket {
  userId?: string;
}

export interface UserPokerCard {
  vote: PokerCardsType;
  username: string;
}
