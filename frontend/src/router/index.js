import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// Public views
import Home from '@/views/Home.vue'
import Results from '@/views/Results.vue'
import Archive from '@/views/Archive.vue'
import SlotHistory from '@/views/SlotHistory.vue'

// Admin views
import AdminLogin from '@/views/admin/Login.vue'
import AdminDashboard from '@/views/admin/Dashboard.vue'
import AdminSlots from '@/views/admin/Slots.vue'
import AdminResults from '@/views/admin/Results.vue'
import AdminUsers from '@/views/admin/Users.vue'

const routes = [
  // Public routes
  {
    path: '/',
    name: 'Home',
    component: Home,
    meta: { title: 'Home - Satta King Portal' }
  },
  {
    path: '/results',
    name: 'Results',
    component: Results,
    meta: { title: 'Today\'s Results - Satta King Portal' }
  },
  {
    path: '/results/slot/:id',
    name: 'SlotHistory',
    component: SlotHistory,
    meta: { title: 'Slot History - Satta King Portal' }
  },
  {
    path: '/archive',
    name: 'Archive',
    component: Archive,
    meta: { title: 'Results Archive - Satta King Portal' }
  },
  
  // Admin routes
  {
    path: '/admin/login',
    name: 'AdminLogin',
    component: AdminLogin,
    meta: { title: 'Admin Login - Satta King Portal', guest: true }
  },
  {
    path: '/admin',
    redirect: '/admin/dashboard'
  },
  {
    path: '/admin/dashboard',
    name: 'AdminDashboard',
    component: AdminDashboard,
    meta: { title: 'Admin Dashboard - Satta King Portal', requiresAuth: true }
  },
  {
    path: '/admin/slots',
    name: 'AdminSlots',
    component: AdminSlots,
    meta: { title: 'Manage Slots - Admin Panel', requiresAuth: true }
  },
  {
    path: '/admin/results',
    name: 'AdminResults',
    component: AdminResults,
    meta: { title: 'Manage Results - Admin Panel', requiresAuth: true }
  },
  {
    path: '/admin/users',
    name: 'AdminUsers',
    component: AdminUsers,
    meta: { title: 'Manage Users - Admin Panel', requiresAuth: true }
  },
  
  // 404 route
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('@/views/NotFound.vue'),
    meta: { title: 'Page Not Found - Satta King Portal' }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else {
      return { top: 0 }
    }
  }
})

// Navigation guards
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  
  // Update page title
  document.title = to.meta.title || 'Satta King Portal'
  
  // Check if route requires authentication
  if (to.matched.some(record => record.meta.requiresAuth)) {
    if (!authStore.isLoggedIn) {
      // Try to check auth if token exists
      if (authStore.token) {
        await authStore.checkAuth()
      }
      
      if (!authStore.isLoggedIn) {
        next('/admin/login')
        return
      }
    }
  }
  
  // Redirect authenticated users away from login page
  if (to.matched.some(record => record.meta.guest) && authStore.isLoggedIn) {
    next('/admin/dashboard')
    return
  }
  
  next()
})

export default router
