<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <!-- Navigation for public pages -->
    <nav v-if="!isAdminRoute" class="bg-white shadow-lg border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <router-link to="/" class="flex items-center space-x-2">
              <i class="fas fa-crown text-2xl text-blue-600"></i>
              <span class="text-xl font-bold text-gray-800">Satta King Portal</span>
            </router-link>
          </div>
          
          <div class="flex items-center space-x-6">
            <router-link
              to="/"
              class="text-gray-600 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
              :class="{ 'text-blue-600 bg-blue-50': $route.name === 'Home' }"
            >
              <i class="fas fa-home mr-1"></i>
              Home
            </router-link>
            
            <router-link
              to="/results"
              class="text-gray-600 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
              :class="{ 'text-blue-600 bg-blue-50': $route.name === 'Results' }"
            >
              <i class="fas fa-list-ol mr-1"></i>
              Results
            </router-link>
            
            <router-link
              to="/archive"
              class="text-gray-600 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
              :class="{ 'text-blue-600 bg-blue-50': $route.name === 'Archive' }"
            >
              <i class="fas fa-archive mr-1"></i>
              Archive
            </router-link>
            
            <router-link
              to="/admin/login"
              class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors"
            >
              <i class="fas fa-user-shield mr-1"></i>
              Admin
            </router-link>
          </div>
        </div>
      </div>
    </nav>

    <!-- Admin Navigation -->
    <nav v-if="isAdminRoute && authStore.isLoggedIn" class="bg-gray-800 shadow-lg">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center space-x-8">
            <router-link to="/admin/dashboard" class="flex items-center space-x-2">
              <i class="fas fa-crown text-2xl text-blue-400"></i>
              <span class="text-xl font-bold text-white">Admin Panel</span>
            </router-link>
            
            <div class="flex items-center space-x-4">
              <router-link
                to="/admin/dashboard"
                class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors"
                :class="{ 'text-white bg-gray-700': $route.name === 'AdminDashboard' }"
              >
                <i class="fas fa-chart-line mr-1"></i>
                Dashboard
              </router-link>
              
              <router-link
                to="/admin/slots"
                class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors"
                :class="{ 'text-white bg-gray-700': $route.name === 'AdminSlots' }"
              >
                <i class="fas fa-clock mr-1"></i>
                Slots
              </router-link>
              
              <router-link
                to="/admin/results"
                class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors"
                :class="{ 'text-white bg-gray-700': $route.name === 'AdminResults' }"
              >
                <i class="fas fa-trophy mr-1"></i>
                Results
              </router-link>
              
              <router-link
                to="/admin/users"
                class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors"
                :class="{ 'text-white bg-gray-700': $route.name === 'AdminUsers' }"
              >
                <i class="fas fa-users mr-1"></i>
                Users
              </router-link>
            </div>
          </div>
          
          <div class="flex items-center space-x-4">
            <span class="text-gray-300 text-sm">
              Welcome, {{ authStore.user?.username }}
            </span>
            <button
              @click="logout"
              class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors"
            >
              <i class="fas fa-sign-out-alt mr-1"></i>
              Logout
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main content -->
    <main class="flex-1">
      <router-view />
    </main>

    <!-- Footer for public pages -->
    <footer v-if="!isAdminRoute" class="bg-gray-800 text-white mt-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div>
            <div class="flex items-center space-x-2 mb-4">
              <i class="fas fa-crown text-2xl text-blue-400"></i>
              <span class="text-xl font-bold">Satta King Portal</span>
            </div>
            <p class="text-gray-400 text-sm">
              Your trusted lottery results platform. Get real-time results and historical data.
            </p>
          </div>
          
          <div>
            <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
            <ul class="space-y-2 text-sm">
              <li><router-link to="/" class="text-gray-400 hover:text-white transition-colors">Home</router-link></li>
              <li><router-link to="/results" class="text-gray-400 hover:text-white transition-colors">Today's Results</router-link></li>
              <li><router-link to="/archive" class="text-gray-400 hover:text-white transition-colors">Results Archive</router-link></li>
            </ul>
          </div>
          
          <div>
            <h3 class="text-lg font-semibold mb-4">Contact</h3>
            <ul class="space-y-2 text-sm text-gray-400">
              <li><i class="fas fa-envelope mr-2"></i>support@sattaking.app</li>
              <li><i class="fas fa-globe mr-2"></i>www.sattaking.app</li>
            </ul>
          </div>
        </div>
        
        <div class="border-t border-gray-700 mt-8 pt-8 text-center text-sm text-gray-400">
          <p>&copy; 2025 Satta King Portal. All rights reserved.</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

export default {
  name: 'App',
  setup() {
    const route = useRoute()
    const router = useRouter()
    const authStore = useAuthStore()

    const isAdminRoute = computed(() => {
      return route.path.startsWith('/admin')
    })

    const logout = async () => {
      await authStore.logout()
      router.push('/')
    }

    // Initialize auth on app load
    authStore.initAuth()

    return {
      authStore,
      isAdminRoute,
      logout
    }
  }
}
</script>
