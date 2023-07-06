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
  "question",
  "coffee",
  "infinite",
  "yak-shaving"
] as const;

export type PokerCardsType = (typeof PokerCards)[number];

export type JoinType = "host" | "play" | "spectate";

export type GameStateType = "lobby" | "voting" | "revealing" | "finished";

export interface Game {
  id: number;
  userId: number;
  title: string;
  pin: string;
  state: GameStateType;
}

export interface WsUser {
  socketId: string;
  broadcastingId: string;
  userId: number;
  participantId: number | null;
  username: string;
  hasVoted: boolean;
  joinType?: JoinType;
  connected?: boolean;
  self?: boolean;
}

export interface Participant {
  id: number;
  username: string;
}

export interface UserPokerCard {
  vote: PokerCardsType;
  username: string;
}
