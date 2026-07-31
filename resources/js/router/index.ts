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
  // ---------- OAuth Callback (standalone) ----------
  {
    path: '/drive/callback',
    name: 'drive.callback',
    component: () => import('@/pages/drive/Callback.vue'),
    meta: { public: true },
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
      {
        path: 'tasks/kanban',
        name: 'tasks.kanban',
        component: () => import('@/pages/tasks/Kanban.vue'),
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
      // Budget
      {
        path: 'budget',
        name: 'budget',
        component: () => import('@/pages/budget/Index.vue'),
      },
      // Vault
      {
        path: 'vault',
        name: 'vault',
        component: () => import('@/pages/vault/Index.vue'),
      },
      // Google Drive
      {
        path: 'drive',
        name: 'drive',
        component: () => import('@/pages/drive/Index.vue'),
      },
      // Accounting
      {
        path: 'accounting',
        name: 'accounting',
        component: () => import('@/pages/accounting/Index.vue'),
      },
      {
        path: 'accounting/journal',
        name: 'accounting.journal',
        component: () => import('@/pages/accounting/Journal.vue'),
      },
      {
        path: 'accounting/ledger',
        name: 'accounting.ledger',
        component: () => import('@/pages/accounting/Ledger.vue'),
      },
      {
        path: 'accounting/reports/trial-balance',
        name: 'accounting.reports.trial-balance',
        component: () => import('@/pages/accounting/reports/TrialBalance.vue'),
      },
      {
        path: 'accounting/reports/income-statement',
        name: 'accounting.reports.income-statement',
        component: () => import('@/pages/accounting/reports/IncomeStatement.vue'),
      },
      {
        path: 'accounting/reports/balance-sheet',
        name: 'accounting.reports.balance-sheet',
        component: () => import('@/pages/accounting/reports/BalanceSheet.vue'),
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
      // Market
      {
        path: 'market',
        name: 'market',
        component: () => import('@/pages/market/Index.vue'),
      },
      // Gold
      {
        path: 'gold',
        name: 'gold',
        component: () => import('@/pages/gold/Index.vue'),
      },
      // Converter
      {
        path: 'converter',
        name: 'converter',
        component: () => import('@/pages/converter/Index.vue'),
      },
      {
        path: 'converter/length',
        name: 'converter.length',
        component: () => import('@/pages/converter/Length.vue'),
      },
      {
        path: 'converter/weight',
        name: 'converter.weight',
        component: () => import('@/pages/converter/Weight.vue'),
      },
      {
        path: 'converter/temperature',
        name: 'converter.temperature',
        component: () => import('@/pages/converter/Temperature.vue'),
      },
      {
        path: 'converter/area',
        name: 'converter.area',
        component: () => import('@/pages/converter/Area.vue'),
      },
      {
        path: 'converter/volume',
        name: 'converter.volume',
        component: () => import('@/pages/converter/Volume.vue'),
      },
      {
        path: 'converter/speed',
        name: 'converter.speed',
        component: () => import('@/pages/converter/Speed.vue'),
      },
      {
        path: 'converter/time',
        name: 'converter.time',
        component: () => import('@/pages/converter/Time.vue'),
      },
      {
        path: 'converter/data',
        name: 'converter.data',
        component: () => import('@/pages/converter/Data.vue'),
      },
      {
        path: 'converter/custom',
        name: 'converter.custom',
        component: () => import('@/pages/converter/Custom.vue'),
      },
      // Tools
      {
        path: 'tools/sql-generator',
        name: 'tools.sql-generator',
        component: () => import('@/pages/tools/SqlGenerator.vue'),
      },
      // Database Manager
      {
        path: 'database',
        name: 'database',
        component: () => import('@/pages/database/Index.vue'),
      },
      {
        path: 'database/:table',
        name: 'database.table',
        component: () => import('@/pages/database/TableView.vue'),
      },
      // Log Viewer
      {
        path: 'logs',
        name: 'logs',
        component: () => import('@/pages/logs/Index.vue'),
      },
      // Foundry
      {
        path: 'foundry',
        name: 'foundry',
        component: () => import('@/pages/foundry/Overview.vue'),
      },
      {
        path: 'foundry/graph',
        name: 'foundry.graph',
        component: () => import('@/pages/foundry/Graph.vue'),
      },
      {
        path: 'foundry/modules',
        name: 'foundry.modules',
        component: () => import('@/pages/foundry/Modules.vue'),
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
      {
        path: 'settings/market',
        name: 'settings.market',
        component: () => import('@/pages/settings/Market.vue'),
      },
      // POS
      {
        path: 'pos',
        name: 'pos',
        component: () => import('@/pages/pos/Index.vue'),
      },
      {
        path: 'pos/outlet/setup',
        name: 'pos.outlet.setup',
        component: () => import('@/pages/pos/outlet/Setup.vue'),
      },
      {
        path: 'pos/outlet/settings',
        name: 'pos.outlet.settings',
        component: () => import('@/pages/pos/outlet/Settings.vue'),
      },
      {
        path: 'pos/catalog',
        name: 'pos.catalog',
        component: () => import('@/pages/pos/catalog/Index.vue'),
      },
      {
        path: 'pos/discount',
        name: 'pos.discount',
        component: () => import('@/pages/pos/discount/Index.vue'),
      },
      {
        path: 'pos/voucher',
        name: 'pos.voucher',
        component: () => import('@/pages/pos/voucher/Index.vue'),
      },
      {
        path: 'pos/voucher/detail',
        name: 'pos.voucher.detail',
        component: () => import('@/pages/pos/voucher/VoucherDetail.vue'),
      },
      {
        path: 'pos/tables',
        name: 'pos.tables',
        component: () => import('@/pages/pos/tables/Index.vue'),
      },
      {
        path: 'pos/tables/order-queue',
        name: 'pos.tables.order-queue',
        component: () => import('@/pages/pos/tables/OrderQueue.vue'),
      },
      {
        path: 'pos/tables/qr',
        name: 'pos.tables.qr',
        component: () => import('@/pages/pos/tables/QrCodeDisplay.vue'),
      },
      {
        path: 'pos/cashier',
        name: 'pos.cashier',
        component: () => import('@/pages/pos/cashier/Index.vue'),
      },
      {
        path: 'pos/transactions',
        name: 'pos.transactions',
        component: () => import('@/pages/pos/transactions/Index.vue'),
      },
      {
        path: 'pos/transactions/:id',
        name: 'pos.transactions.detail',
        component: () => import('@/pages/pos/transactions/TransactionDetail.vue'),
      },
      {
        path: 'pos/members',
        name: 'pos.members',
        component: () => import('@/pages/pos/members/Index.vue'),
      },
      {
        path: 'pos/open-bills',
        name: 'pos.open-bills',
        component: () => import('@/pages/pos/open-bills/Index.vue'),
      },
      {
        path: 'pos/reports',
        name: 'pos.reports',
        component: () => import('@/pages/pos/reports/Index.vue'),
      },
      {
        path: 'pos/reports/daily',
        name: 'pos.reports.daily',
        component: () => import('@/pages/pos/reports/DailyReport.vue'),
      },
      {
        path: 'pos/reports/products',
        name: 'pos.reports.products',
        component: () => import('@/pages/pos/reports/ProductRanking.vue'),
      },
      {
        path: 'pos/reports/revenue',
        name: 'pos.reports.revenue',
        component: () => import('@/pages/pos/reports/RevenueTrend.vue'),
      },
      // Supplier
      {
        path: 'supplier',
        name: 'supplier',
        component: () => import('@/pages/supplier/Index.vue'),
      },
      {
        path: 'supplier/suppliers',
        name: 'supplier.suppliers',
        component: () => import('@/pages/supplier/suppliers/Index.vue'),
      },
      {
        path: 'supplier/suppliers/detail',
        name: 'supplier.suppliers.detail',
        component: () => import('@/pages/supplier/suppliers/Detail.vue'),
      },
      {
        path: 'supplier/purchase-orders',
        name: 'supplier.purchase-orders',
        component: () => import('@/pages/supplier/purchase-orders/Index.vue'),
      },
      {
        path: 'supplier/purchase-orders/detail',
        name: 'supplier.purchase-orders.detail',
        component: () => import('@/pages/supplier/purchase-orders/Detail.vue'),
      },
      {
        path: 'supplier/purchase-orders/create',
        name: 'supplier.purchase-orders.create',
        component: () => import('@/pages/supplier/purchase-orders/Create.vue'),
      },
      {
        path: 'supplier/reports',
        name: 'supplier.reports',
        component: () => import('@/pages/supplier/reports/Index.vue'),
      },
    ],
  },

  // ---------- Public QR Order (no auth, no DashboardLayout) ----------
  {
    path: '/pos/qr/:token/menu',
    name: 'pos.qr-order.menu',
    component: () => import('@/pages/pos/qr-order/Menu.vue'),
    meta: { public: true },
  },
  {
    path: '/pos/qr/:token/cart',
    name: 'pos.qr-order.cart',
    component: () => import('@/pages/pos/qr-order/Cart.vue'),
    meta: { public: true },
  },
  {
    path: '/pos/qr/:token/order/:orderId',
    name: 'pos.qr-order.status',
    component: () => import('@/pages/pos/qr-order/OrderStatus.vue'),
    meta: { public: true },
  },
]
