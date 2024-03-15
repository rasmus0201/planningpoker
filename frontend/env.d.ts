declare module "*.vue" {
  import type { ComponentOptions } from "vue";
  const component: ComponentOptions;
  export default component;
}

export declare global {
  interface ImportMetaEnv {
    readonly VITE_API_BASE_URL: string;
    readonly VITE_PUSHER_CLUSTER: string;
    readonly VITE_PUSHER_KEY: string;
    readonly VITE_PUSHER_AUTH_ENDPOINT: string;
  }

  interface ImportMeta {
    readonly env: ImportMetaEnv;
  }
}
