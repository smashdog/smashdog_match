import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";

// https://vitejs.dev/config/
export default defineConfig(async () => ({
  plugins: [vue()],

  // Vite options tailored for Tauri development and only applied in `tauri dev` or `tauri build`
  //
  // 1. prevent vite from obscuring rust errors
  clearScreen: false,
  // 2. tauri expects a fixed port, fail if that port is not available
  server: {
    port: 19999,
    strictPort: true,
    watch: {
      // 3. tell vite to ignore watching `src-tauri`
      ignored: [
        "**/src-tauri/**",
        "./vendor/**",
        "./composer.json",
        "./composer.lock",
        "./build.php",
        "./build.bat"
      ],
    },
    proxy: {
      '/api': {
        target: 'http://e.com/',
        changeOrigin: true,
        rewrite: (path) => path
      }
    }
  },
  build: {
    target: 'esnext'
 },
}));
