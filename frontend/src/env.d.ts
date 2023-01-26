declare module "*.vue" {
  import type { ComponentOptions } from "vue";
  const component: ComponentOptions;
  export default component;
}

export declare global {
  interface ImportMetaEnv {
    readonly VITE_API_URL: string;
    readonly VITE_WS_URL: string;
  }

  interface ImportMeta {
    readonly env: ImportMetaEnv;
  }
}
