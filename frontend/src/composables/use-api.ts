import axios from "axios";
import Echo from "laravel-echo";

import { useUserStore } from "@/pinia/user";

const createApi = (headers: Record<string, string> | undefined = undefined) =>
  axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL,
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
      ...headers
    }
  });

export function useUnauthenticatedApi() {
  return createApi();
}

const waitForConnection = (ws: Echo) =>
  new Promise<void>((resolve) =>
    setTimeout(async () => {
      if (!ws) {
        throw new Error("No websocket instance");
      }

      if (!ws.socketId()) {
        await waitForConnection(ws);
      }

      resolve();
    }, 25)
  );

export function useApi(ws: Echo | undefined = undefined) {
  const userStore = useUserStore();
  const api = createApi();

  api.interceptors.request.use(async (config) => {
    if (!ws) {
      return config;
    }

    if (!ws.socketId()) {
      await waitForConnection(ws);
    }

    config.headers["X-Socket-Id"] = ws.socketId();

    return config;
  });

  api.interceptors.response.use(
    (response) => response,
    (error) => {
      if (error.response?.status === 401 && error.response?.data?.message === "Unauthenticated.") {
        return userStore.logout();
      }

      return Promise.reject(error);
    }
  );

  return api;
}
