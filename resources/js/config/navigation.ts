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
        children: [
          { label: 'List', icon: 'List', to: '/tasks' },
          { label: 'Kanban', icon: 'Columns3', to: '/tasks/kanban' },
        ],
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
        id: 'budget',
        label: 'Budget',
        icon: 'PiggyBank',
        to: '/budget',
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
      {
        id: 'converter',
        label: 'Unit Converter',
        icon: 'ArrowLeftRight',
        children: [
          { label: 'Semua', icon: 'LayoutGrid', to: '/converter' },
          { label: 'Panjang', icon: 'Ruler', to: '/converter/length' },
          { label: 'Berat', icon: 'Weight', to: '/converter/weight' },
          { label: 'Suhu', icon: 'Thermometer', to: '/converter/temperature' },
          { label: 'Luas', icon: 'Square', to: '/converter/area' },
          { label: 'Volume', icon: 'Cuboid', to: '/converter/volume' },
          { label: 'Kecepatan', icon: 'Gauge', to: '/converter/speed' },
          { label: 'Waktu', icon: 'Timer', to: '/converter/time' },
          { label: 'Data Digital', icon: 'HardDrive', to: '/converter/data' },
          { label: 'Custom', icon: 'Wrench', to: '/converter/custom' },
        ],
      },
      {
        id: 'tools',
        label: 'Tools',
        icon: 'Wrench',
        children: [
          { label: 'SQL Generator', icon: 'Database', to: '/tools/sql-generator' },
        ],
      },
      {
        id: 'vault',
        label: 'Password Vault',
        icon: 'Lock',
        to: '/vault',
      },
      {
        id: 'drive',
        label: 'Google Drive',
        icon: 'HardDrive',
        to: '/drive',
      },
      {
        id: 'accounting',
        label: 'Akuntansi',
        icon: 'Calculator',
        children: [
          { label: 'COA', icon: 'List', to: '/accounting' },
          { label: 'Jurnal', icon: 'FileText', to: '/accounting/journal' },
          { label: 'Buku Besar', icon: 'BookOpen', to: '/accounting/ledger' },
          { label: 'Trial Balance', icon: 'BarChart3', to: '/accounting/reports/trial-balance' },
          { label: 'Laba Rugi', icon: 'TrendingUp', to: '/accounting/reports/income-statement' },
          { label: 'Neraca', icon: 'Layers', to: '/accounting/reports/balance-sheet' },
        ],
      },
    ],
  },
  {
    title: 'Bisnis',
    items: [
      {
        id: 'pos',
        label: 'Point of Sale',
        icon: 'ShoppingCart',
        children: [
          { label: 'Outlet', icon: 'Store', to: '/pos' },
          { label: 'Kasir', icon: 'Monitor', to: '/pos/cashier' },
          { label: 'Katalog', icon: 'Package', to: '/pos/catalog' },
          { label: 'Diskon', icon: 'Percent', to: '/pos/discount' },
          { label: 'Voucher', icon: 'Ticket', to: '/pos/voucher' },
          { label: 'Meja', icon: 'LayoutGrid', to: '/pos/tables' },
          { label: 'Transaksi', icon: 'Receipt', to: '/pos/transactions' },
          { label: 'Member', icon: 'Users', to: '/pos/members' },
          { label: 'Open Bills', icon: 'Clock', to: '/pos/open-bills' },
          { label: 'Laporan', icon: 'BarChart3', to: '/pos/reports' },
        ],
      },
      {
        id: 'supplier',
        label: 'Supplier',
        icon: 'Truck',
        children: [
          { label: 'Dashboard', icon: 'LayoutDashboard', to: '/supplier/1' },
          { label: 'Supplier', icon: 'Users', to: '/supplier/suppliers' },
          { label: 'Purchase Order', icon: 'ClipboardList', to: '/supplier/purchase-orders' },
          { label: 'Laporan', icon: 'BarChart3', to: '/supplier/reports' },
        ],
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
        id: 'database',
        label: 'Database',
        icon: 'Database',
        to: '/database',
      },
      {
        id: 'logs',
        label: 'Log Viewer',
        icon: 'ScrollText',
        to: '/logs',
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
