import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'
import { initHttp, post as httpPost } from '@purdia/http'
import { configureSecureStorage } from '@purdia/crypto'
import { configureAuth, createAuthGuard } from '@purdia/auth'
import { configureTheme } from '@purdia/theme'
import { useAuthStore } from '@purdia/auth'
import { useToastStore } from '@purdia/toast'
import { routes } from '@/router'
import App from '@/App.vue'

// ---------------------------------------------------------------------------
// Pinia
// ---------------------------------------------------------------------------

const pinia = createPinia()

// ---------------------------------------------------------------------------
// Router
// ---------------------------------------------------------------------------

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// ---------------------------------------------------------------------------
// App
// ---------------------------------------------------------------------------

const app = createApp(App)

app.use(pinia)
app.use(router)

// ---------------------------------------------------------------------------
// Configure @purdia packages
// ---------------------------------------------------------------------------

configureSecureStorage({
  secret: import.meta.env.VITE_APP_KEY ?? 'purdia-client-secret-v1',
})

initHttp({
  services: {
    main: { baseURL: '/api', timeout: 30_000 },
    auth: { baseURL: '/api/auth', timeout: 15_000 },
  },
  onUnauthorized: () => router.push({ name: 'login' }),
  onError: (err) => {
    const toast = useToastStore()
    toast.error(err.message)
  },
  locale: () => localStorage.getItem('app_locale') ?? 'id',
})

configureAuth({
  login: async (email: string, password: string) => {
    const response = await httpPost<{
      user: { id: number; name: string; email: string; preferences: Record<string, unknown> }
      token: string
      refresh_token: string
    }>('/auth/login', { email, password })
    return {
      user: response.data.user,
      tokens: { token: response.data.token, refresh_token: response.data.refresh_token },
    }
  },
  register: async (name: string, email: string, password: string) => {
    const response = await httpPost<{
      user: { id: number; name: string; email: string; preferences: Record<string, unknown> }
      token: string
      refresh_token: string
    }>('/auth/register', { name, email, password, password_confirmation: password })
    return {
      user: response.data.user,
      tokens: { token: response.data.token, refresh_token: response.data.refresh_token },
    }
  },
  onLogout: async () => {
    try {
      await httpPost('/auth/logout')
    } catch {
      // Token might already be invalid
    }
  },
  keys: {
    token: 'auth_token',
    refreshToken: 'refresh_token',
    user: 'auth_user',
  },
})

configureTheme({
  defaultColor: 'indigo',
  getUserKey: () => {
    const auth = useAuthStore()
    return auth.user?.id?.toString() ?? null
  },
})

// Install auth guard
createAuthGuard(router, {
  loginRoute: 'login',
  homeRoute: 'dashboard',
})

// ---------------------------------------------------------------------------
// Mount
// ---------------------------------------------------------------------------

app.mount('#app')
