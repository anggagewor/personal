export interface NavChild {
  label: string
  icon: string
  to: string
}

export interface NavItem {
  id: string
  label: string
  icon: string
  to?: string
  children?: NavChild[]
}

export interface NavGroup {
  title?: string
  items: NavItem[]
}

/**
 * Main sidebar navigation structure.
 * Icons use Lucide icon names (rendered via @lucide/vue).
 */
export const navigation: NavGroup[] = [
  {
    items: [
      {
        id: 'dashboard',
        label: 'Dashboard',
        icon: 'LayoutDashboard',
        to: '/',
      },
    ],
  },
  {
    title: 'Produktivitas',
    items: [
      {
        id: 'notes',
        label: 'Catatan',
        icon: 'FileText',
        to: '/notes',
      },
      {
        id: 'tasks',
        label: 'Tugas',
        icon: 'ListTodo',
        to: '/tasks',
      },
      {
        id: 'pomodoro',
        label: 'Pomodoro',
        icon: 'Clock',
        to: '/pomodoro',
      },
      {
        id: 'habits',
        label: 'Habits',
        icon: 'CheckCircle',
        to: '/habits',
      },
      {
        id: 'goals',
        label: 'Goals',
        icon: 'Target',
        to: '/goals',
      },
      {
        id: 'calendar',
        label: 'Kalender',
        icon: 'Calendar',
        to: '/calendar',
      },
      {
        id: 'journal',
        label: 'Jurnal',
        icon: 'BookOpen',
        to: '/journal',
      },
    ],
  },
  {
    title: 'Referensi',
    items: [
      {
        id: 'bookmarks',
        label: 'Bookmark',
        icon: 'Bookmark',
        to: '/bookmarks',
      },
      {
        id: 'reading-list',
        label: 'Reading List',
        icon: 'Library',
        to: '/reading-list',
      },
      {
        id: 'scratchpads',
        label: 'Scratchpad',
        icon: 'StickyNote',
        to: '/scratchpads',
      },
      {
        id: 'finance',
        label: 'Keuangan',
        icon: 'Wallet',
        to: '/finance',
      },
      {
        id: 'market',
        label: 'Market',
        icon: 'TrendingUp',
        to: '/market',
      },
      {
        id: 'gold',
        label: 'Emas',
        icon: 'Coins',
        to: '/gold',
      },
      {
        id: 'quotes',
        label: 'Quotes',
        icon: 'Quote',
        to: '/quotes',
      },
      {
        id: 'wishlist',
        label: 'Wishlist',
        icon: 'Heart',
        to: '/wishlist',
      },
    ],
  },
  {
    title: 'Kelola',
    items: [
      {
        id: 'users',
        label: 'Pengguna',
        icon: 'Users',
        children: [
          { label: 'Semua Pengguna', icon: 'List', to: '/users' },
          { label: 'Role & Permission', icon: 'Shield', to: '/users/roles' },
        ],
      },
      {
        id: 'activity',
        label: 'Aktivitas',
        icon: 'Activity',
        to: '/activity',
      },
      {
        id: 'streaks',
        label: 'Streaks',
        icon: 'Flame',
        to: '/streaks',
      },
      {
        id: 'trash',
        label: 'Sampah',
        icon: 'Trash2',
        to: '/trash',
      },
      {
        id: 'settings',
        label: 'Pengaturan',
        icon: 'Settings',
        children: [
          { label: 'Umum', icon: 'SlidersHorizontal', to: '/settings/general' },
          { label: 'Tampilan', icon: 'Palette', to: '/settings/appearance' },
          { label: 'Akun', icon: 'UserCircle', to: '/settings/account' },
          { label: 'Market', icon: 'TrendingUp', to: '/settings/market' },
          { label: 'Export', icon: 'Download', to: '/settings/export' },
        ],
      },
    ],
  },
]
