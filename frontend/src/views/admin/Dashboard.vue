<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-800">
            <i class="fas fa-chart-line text-blue-600 mr-2"></i>
            Admin Dashboard
          </h1>
          <p class="text-gray-600 mt-2">Welcome back, {{ authStore.user?.username }}!</p>
        </div>
        <div class="text-right">
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
            <div class="text-xs text-blue-600 font-medium">SERVER TIME (IST)</div>
            <div class="text-lg font-bold text-blue-700">{{ currentTime }}</div>
            <div class="text-xs text-blue-600">{{ currentDate }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i class="fas fa-clock text-2xl text-blue-600"></i>
          </div>
          <div class="ml-4">
            <h3 class="text-lg font-semibold text-gray-800">Today's Slots</h3>
            <p class="text-3xl font-bold text-blue-600">{{ stats.todaySlots }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i class="fas fa-trophy text-2xl text-green-600"></i>
          </div>
          <div class="ml-4">
            <h3 class="text-lg font-semibold text-gray-800">Results Declared</h3>
            <p class="text-3xl font-bold text-green-600">{{ stats.resultsToday }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i class="fas fa-robot text-2xl text-purple-600"></i>
          </div>
          <div class="ml-4">
            <h3 class="text-lg font-semibold text-gray-800">Auto Generated</h3>
            <p class="text-3xl font-bold text-purple-600">{{ stats.autoResults }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-orange-500">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i class="fas fa-hourglass-half text-2xl text-orange-600"></i>
          </div>
          <div class="ml-4">
            <h3 class="text-lg font-semibold text-gray-800">Pending</h3>
            <p class="text-3xl font-bold text-orange-600">{{ stats.pendingResults }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
      <h2 class="text-xl font-semibold text-gray-800 mb-4">
        <i class="fas fa-bolt mr-2"></i>
        Quick Actions
      </h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <router-link
          to="/admin/slots"
          class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors"
        >
          <i class="fas fa-plus-circle text-blue-600 text-xl mr-3"></i>
          <span class="text-blue-800 font-medium">Create New Slot</span>
        </router-link>

  <!-- Set Result action removed (manage results on /admin/results) -->

        <router-link
          to="/admin/results"
          class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors"
        >
          <i class="fas fa-list text-purple-600 text-xl mr-3"></i>
          <span class="text-purple-800 font-medium">Manage Results</span>
        </router-link>

        <router-link
          to="/admin/users"
          class="flex items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors"
        >
          <i class="fas fa-users text-orange-600 text-xl mr-3"></i>
          <span class="text-orange-800 font-medium">Manage Users</span>
        </router-link>
      </div>
    </div>

    <!-- Today's Schedule -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Pending Slots -->
      <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">
          <i class="fas fa-clock text-orange-500 mr-2"></i>
          Pending Results
        </h2>
        
        <div v-if="pendingSlots.length > 0" class="space-y-3">
          <div
            v-for="slot in pendingSlots"
            :key="slot.id"
            class="flex items-center justify-between p-3 bg-orange-50 rounded-lg"
          >
            <div>
              <h4 class="font-medium text-gray-800">{{ slot.title }}</h4>
              <p class="text-sm text-gray-600">
                <i class="fas fa-clock mr-1"></i>
                {{ formatTime(slot.scheduled_time) }}
              </p>
            </div>
            
            <div class="flex items-center space-x-2">
              <span v-if="slot.is_auto" class="badge badge-info">Auto</span>
              <!-- Set Result removed from dashboard; use Manage Results page -->
            </div>
          </div>
        </div>
        
        <div v-else class="text-center py-8 text-gray-500">
          <i class="fas fa-check-circle text-4xl mb-2"></i>
          <p>All results have been declared!</p>
        </div>
      </div>

      <!-- Recent Results -->
      <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">
          <i class="fas fa-trophy text-green-500 mr-2"></i>
          Recent Results
        </h2>
        
        <div v-if="recentResults.length > 0" class="space-y-3">
          <div
            v-for="result in recentResults"
            :key="result.id"
            class="flex items-center justify-between p-3 bg-green-50 rounded-lg"
          >
            <div>
              <h4 class="font-medium text-gray-800">{{ result.slot.title }}</h4>
              <p class="text-sm text-gray-600">
                <i class="fas fa-check-circle mr-1"></i>
                {{ formatTime(result.declared_at) }}
              </p>
            </div>
            
            <div class="flex items-center space-x-3">
              <div class="text-2xl font-bold text-green-600 bg-white px-3 py-1 rounded-full">
                {{ result.result }}
              </div>
              <span v-if="result.is_auto" class="badge badge-info">Auto</span>
              <span v-if="result.locked" class="badge badge-warning">
                <i class="fas fa-lock mr-1"></i>
                Locked
              </span>
            </div>
          </div>
        </div>
        
        <div v-else class="text-center py-8 text-gray-500">
          <i class="fas fa-clock text-4xl mb-2"></i>
          <p>No results declared yet today.</p>
        </div>
      </div>
    </div>

  <!-- Set Result modal removed from dashboard; manage results on Manage Results page -->
  </div>
</template>

<script>
import { computed, onMounted, onBeforeUnmount, reactive, ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useResultsStore } from '@/stores/results'
import { useSlotsStore } from '@/stores/slots'
import { formatTime } from '@/utils/dateTime'

export default {
  name: 'AdminDashboard',
  setup() {
    const authStore = useAuthStore()
    const resultsStore = useResultsStore()
    const slotsStore = useSlotsStore()
    
  // Set result modal removed from dashboard; state/functions cleaned up

    // Current time display
    const currentTime = ref('')
    const currentDate = ref('')

    const updateTime = () => {
      const now = new Date()
      // Format time as HH:MM:SS
      currentTime.value = now.toLocaleTimeString('en-IN', {
        timeZone: 'Asia/Kolkata',
        hour12: false,
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
      })
      // Format date
      currentDate.value = now.toLocaleDateString('en-IN', {
        timeZone: 'Asia/Kolkata',
        weekday: 'short',
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    }

    // Update time immediately and then every second
    updateTime()
    const timeInterval = setInterval(updateTime, 1000)

    // Cleanup interval on unmount
    onBeforeUnmount(() => {
      clearInterval(timeInterval)
    })

    const stats = computed(() => ({
      todaySlots: slotsStore.todaySlots.length,
      resultsToday: resultsStore.todayResults.length,
      autoResults: resultsStore.todayResults.filter(r => r.is_auto).length,
      pendingResults: slotsStore.todaySlots.length - resultsStore.todayResults.length
    }))

    const pendingSlots = computed(() => {
      return slotsStore.todaySlots.filter(slot => {
        return !resultsStore.todayResults.some(result => result.slot_id === slot.id)
      })
    })

    const recentResults = computed(() => {
      return resultsStore.todayResults.slice(-5).reverse()
    })

  // set-result helpers removed

    const loadDashboardData = async () => {
      await Promise.all([
        resultsStore.fetchTodayResults(),
        slotsStore.fetchTodaySlots()
      ])
    }

    onMounted(() => {
      loadDashboardData()
    })

    return {
      authStore,
      resultsStore,
      slotsStore,
      stats,
      pendingSlots,
      recentResults,
      currentTime,
      currentDate,
      formatTime
    }
  }
}
</script>
