import vue from "@vitejs/plugin-vue";
import { readFileSync } from "fs";
import os from "os";
import { resolve } from "path";
import { fileURLToPath, URL } from "url";
import { defineConfig } from "vite";
import eslint from "vite-plugin-eslint";
import mkcert from "vite-plugin-mkcert";

// https://vitejs.dev/config/
export default defineConfig(({ command }) => {
  if (command === "serve") {
    /**
     * Use local development SSL certificate if one exists in ~/.ssl.
     *
     * Can be generated using [mkcert](https://github.com/FiloSottile/mkcert#installation)
     *
     * ```
     * $ mkdir -p ~/.ssl && cd $_ && mkcert -install localhost
     * ```
     */

    const devServerConfig = {
      host: "localhost",
      https: {
        key: "",
        cert: ""
      },
      proxy: {
        "/api": {
          target: "http://127.0.0.1:8000",
          changeOrigin: true,
          secure: false,
          ws: true
        }
      }
    };

    const sslDir = resolve(os.homedir(), ".ssl");
    const sslKeyPath = resolve(sslDir, `${devServerConfig.host}-key.pem`);
    const sslCertPath = resolve(sslDir, `${devServerConfig.host}.pem`);

    try {
      devServerConfig.https = {
        key: readFileSync(sslKeyPath),
        cert: readFileSync(sslCertPath)
      };
    } catch (e) {
      process.stdout.write(`SSL certificate for "${devServerConfig.host}" not found in ${sslDir}\n\n\n`);
      process.stdout.write("Please generate one using mkcert:\n\n");
      process.stdout.write(`$ mkdir -p '${sslDir}' && cd $_ && mkcert -install ${devServerConfig.host}\n\n\n`);
    }

    return {
      plugins: [mkcert(), vue(), eslint()],
      server: devServerConfig,
      resolve: {
        alias: {
          "@": fileURLToPath(new URL("./src", import.meta.url))
        }
      }
    };
  }

  return {
    plugins: [vue(), eslint()],
    resolve: {
      alias: {
        "@": fileURLToPath(new URL("./src", import.meta.url))
      }
    }
  };
});
