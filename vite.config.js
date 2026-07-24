import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import tailwindcss from '@tailwindcss/vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.ts'],
      refresh: true,
    }),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
    tailwindcss(),
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources/js'),
      '@purdia/auth': path.resolve(__dirname, 'packages/auth/src/index.ts'),
      '@purdia/crypto': path.resolve(__dirname, 'packages/crypto/src/index.ts'),
      '@purdia/http': path.resolve(__dirname, 'packages/http/src/index.ts'),
      '@purdia/theme': path.resolve(__dirname, 'packages/theme/src/index.ts'),
      '@purdia/toast': path.resolve(__dirname, 'packages/toast/src/index.ts'),
      '@purdia/composables': path.resolve(__dirname, 'packages/composables/src/index.ts'),
      '@purdia/ui': path.resolve(__dirname, 'packages/ui'),
      // Force axios to use its pre-built browser ESM bundle
      'axios': path.resolve(__dirname, 'node_modules/axios/dist/esm/axios.js'),
    },
  },
  build: {
    chunkSizeWarningLimit: 300,
    rollupOptions: {
      output: {
        manualChunks(id) {
          if (id.includes('node_modules/vue') || id.includes('node_modules/pinia') || id.includes('node_modules/@vue')) {
            return 'vendor'
          }
        },
      },
    },
  },
  server: {
    watch: {
      ignored: ['**/storage/framework/views/**'],
    },
  },
})
