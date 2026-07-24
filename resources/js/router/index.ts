import type { RouteRecordRaw } from 'vue-router'

export const routes: RouteRecordRaw[] = [
  // ---------- Auth (guest only) ----------
  {
    path: '/login',
    name: 'login',
    component: () => import('@/pages/auth/Login.vue'),
    meta: { guest: true },
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('@/pages/auth/Register.vue'),
    meta: { guest: true },
  },

  // ---------- Dashboard (protected) ----------
  {
    path: '/',
    component: () => import('@/layouts/DashboardLayout.vue'),
    children: [
      {
        path: '',
        name: 'dashboard',
        component: () => import('@/pages/Dashboard.vue'),
      },
      // Notes
      {
        path: 'notes',
        name: 'notes',
        component: () => import('@/pages/notes/Index.vue'),
      },
      // Tasks
      {
        path: 'tasks',
        name: 'tasks',
        component: () => import('@/pages/tasks/Index.vue'),
      },
      // Bookmarks
      {
        path: 'bookmarks',
        name: 'bookmarks',
        component: () => import('@/pages/bookmarks/Index.vue'),
      },
      // Calendar
      {
        path: 'calendar',
        name: 'calendar',
        component: () => import('@/pages/calendar/Index.vue'),
      },
      // Pomodoro
      {
        path: 'pomodoro',
        name: 'pomodoro',
        component: () => import('@/pages/pomodoro/Index.vue'),
      },
      // Scratchpads
      {
        path: 'scratchpads',
        name: 'scratchpads',
        component: () => import('@/pages/scratchpads/Index.vue'),
      },
      // Habits
      {
        path: 'habits',
        name: 'habits',
        component: () => import('@/pages/habits/Index.vue'),
      },
      // Finance
      {
        path: 'finance',
        name: 'finance',
        component: () => import('@/pages/finance/Index.vue'),
      },
      // Reading List
      {
        path: 'reading-list',
        name: 'reading-list',
        component: () => import('@/pages/reading-list/Index.vue'),
      },
      // Journal
      {
        path: 'journal',
        name: 'journal',
        component: () => import('@/pages/journal/Index.vue'),
      },
      // Goals
      {
        path: 'goals',
        name: 'goals',
        component: () => import('@/pages/goals/Index.vue'),
      },
      // Quotes
      {
        path: 'quotes',
        name: 'quotes',
        component: () => import('@/pages/quotes/Index.vue'),
      },
      // Wishlist
      {
        path: 'wishlist',
        name: 'wishlist',
        component: () => import('@/pages/wishlist/Index.vue'),
      },
      // Streaks
      {
        path: 'streaks',
        name: 'streaks',
        component: () => import('@/pages/streaks/Index.vue'),
      },
      // Trash
      {
        path: 'trash',
        name: 'trash',
        component: () => import('@/pages/trash/Index.vue'),
      },
      // Activity
      {
        path: 'activity',
        name: 'activity',
        component: () => import('@/pages/activity/Index.vue'),
      },
      // Settings
      {
        path: 'settings/general',
        name: 'settings.general',
        component: () => import('@/pages/settings/General.vue'),
      },
      {
        path: 'settings/appearance',
        name: 'settings.appearance',
        component: () => import('@/pages/settings/Appearance.vue'),
      },
      {
        path: 'settings/account',
        name: 'settings.account',
        component: () => import('@/pages/settings/Account.vue'),
      },
      {
        path: 'settings/export',
        name: 'settings.export',
        component: () => import('@/pages/settings/Export.vue'),
      },
    ],
  },
]
