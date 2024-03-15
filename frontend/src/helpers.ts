import Color from "color";

import type { JoinType } from "@/types";

export const darkenColor = (color: string) => Color(color).darken(0.5).hex();
export const dynamicForeground = (color: string) => (Color(color).isDark() ? "#fff" : "#000");

export const mapJoinType = (joinType: JoinType | undefined) => {
  switch (joinType) {
    case "host":
      return "host";
    case "spectate":
      return "spectator";
    case "play":
      return "player";
    default:
      return "0.o?";
  }
};
