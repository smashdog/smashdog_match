import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import VueDevTools from 'vite-plugin-vue-devtools'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    VueDevTools()
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },
  server: {
    hmr: true,
  },
  proxy: {
    '/api': {
      target: 'http://e.com/',
      changeOrigin: true,
      rewrite: (path) => path
    }
  },
  build: {
    target: 'esnext'
  },
})
