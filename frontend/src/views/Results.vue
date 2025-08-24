<template>
  <!-- Hero Banner -->
  <div class="bg-gradient-to-br from-blue-600 to-purple-700 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <div class="text-center">
        <h1 class="text-4xl md:text-6xl font-extrabold mb-4">
          <i class="fas fa-list-ol text-yellow-400 mr-2"></i>
          Today's Live Results
        </h1>
        <p class="text-xl md:text-2xl text-blue-100 mb-2">Real-time lottery results updated instantly as draws occur.</p>
        <p class="text-lg text-blue-200 max-w-2xl mx-auto">Stay tuned and follow along for the latest announcements.</p>
      </div>
    </div>
  </div>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Refresh Button -->
    <div class="text-center mb-8">
      <button
        @click="refreshResults"
        :disabled="resultsStore.loading"
        class="btn btn-primary"
      >
        <i class="fas fa-sync-alt mr-2" :class="{ 'animate-spin': resultsStore.loading }"></i>
        Refresh Results
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="resultsStore.loading && !resultsStore.todayResults.length" class="flex justify-center py-12">
      <div class="spinner w-12 h-12"></div>
    </div>

    <!-- Results Grid -->
    <div v-else-if="resultsStore.todayResults.length > 0">
      <!-- Summary Stats (keep at top) -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
          <i class="fas fa-chart-bar mr-2"></i>
          Today's Summary
        </h3>
        
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
          <div class="p-4 bg-blue-50 rounded-lg">
            <div class="text-2xl font-bold text-blue-600">{{ totalSlots }}</div>
            <div class="text-sm text-gray-600">Total Slots</div>
          </div>
          
          <div class="p-4 bg-green-50 rounded-lg">
            <div class="text-2xl font-bold text-green-600">{{ resultsStore.todayResults.length }}</div>
            <div class="text-sm text-gray-600">Results Declared</div>
          </div>
          
          
          <div class="p-4 bg-orange-50 rounded-lg">
            <div class="text-2xl font-bold text-orange-600">{{ pendingResults }}</div>
            <div class="text-sm text-gray-600">Pending</div>
          </div>
        </div>
      </div>

      <!-- Declared Results -->
      <div v-if="declaredResults.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div
          v-for="result in declaredResults"
          :key="result.id"
          class="result-card bg-white rounded-lg shadow-lg p-6 hover:shadow-2xl transition-all duration-200"
        >
          <div class="text-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">{{ result.slot.title }}</h3>
            <p class="text-sm text-gray-500">
              <i class="fas fa-clock mr-1"></i>
              {{ formatTime(result.slot.scheduled_time) }}
            </p>
          </div>
          
          <div class="text-center">
            <div class="result-number text-6xl md:text-7xl font-extrabold text-yellow-500 mb-4">{{ result.result }}</div>
            
            <!-- Additional Info -->
            <div class="mt-4 text-xs text-gray-500">
              <p v-if="result.slot.description">{{ result.slot.description }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Passed but not declared (pending) -->
      <div v-if="passedButNotDeclared.length > 0" class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
          <i class="fas fa-hourglass-half mr-2"></i>
          Pending Results (scheduled time passed)
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div v-for="slot in passedButNotDeclared" :key="slot.id" class="bg-white rounded-lg shadow-lg p-6">
            <div class="text-center">
              <h4 class="text-lg font-semibold text-gray-800 mb-2">{{ slot.title }}</h4>
              <p class="text-xl text-gray-600"><i class="fas fa-clock mr-1"></i>{{ formatTime(slot.scheduled_time) }}</p>
              <div class="text-sm text-gray-500 mt-2">Awaiting result</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- No Results State -->
    <div v-else class="text-center py-16">
      <i class="fas fa-clock text-6xl text-gray-400 mb-6"></i>
      <h2 class="text-2xl font-semibold text-gray-600 mb-4">No Results Available</h2>
      <p class="text-gray-500 mb-8 max-w-md mx-auto">
        Results will be announced as per the scheduled time. Please check back later or refresh the page.
      </p>
      
      <div class="space-y-4">
        <button @click="refreshResults" class="btn btn-primary">
          <i class="fas fa-refresh mr-2"></i>
          Refresh Now
        </button>
        
        <div>
          <router-link to="/archive" class="btn btn-outline">
            <i class="fas fa-archive mr-2"></i>
            View Previous Results
          </router-link>
        </div>
      </div>
    </div>

    <!-- Upcoming Slots -->
    <div v-if="upcomingSlots.length > 0" class="bg-white rounded-lg shadow-md p-6">
      <h3 class="text-lg font-semibold text-gray-800 mb-4">
        <i class="fas fa-clock mr-2"></i>
        Upcoming Today
      </h3>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="slot in upcomingSlots"
          :key="slot.id"
          class="bg-white rounded-lg shadow-lg p-6 hover:shadow-2xl transition-all duration-200"
        >
          <div class="text-center">
            <h4 class="text-lg font-semibold text-gray-800 mb-2">{{ slot.title }}</h4>
            <p class="text-xl text-blue-600">
              <i class="fas fa-clock mr-1"></i>
              {{ formatTime(slot.scheduled_time) }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { computed, onMounted, ref } from 'vue'
import { useResultsStore } from '@/stores/results'
import { useSlotsStore } from '@/stores/slots'
import { formatTime } from '@/utils/dateTime'
import { format } from 'date-fns'

export default {
  name: 'Results',
  setup() {
    const resultsStore = useResultsStore()
    const slotsStore = useSlotsStore()
    const totalSlots = ref(0)

    const currentDate = computed(() => {
      return format(new Date(), 'EEEE, MMMM dd, yyyy')
    })

    const autoResults = computed(() => {
      return resultsStore.todayResults.filter(result => result.is_auto).length
    })

    const pendingResults = computed(() => {
      return totalSlots.value - resultsStore.todayResults.length
    })

    // Declared results (results reported for today)
    const declaredResults = computed(() => resultsStore.todayResults)

    // Slots which had scheduled_time <= now and do not have declared results (pending / passed)
    const passedButNotDeclared = computed(() => {
      const now = new Date()
      return slotsStore.todaySlots.filter(slot => {
        const hasResult = resultsStore.todayResults.some(r => r.slot_id === slot.id)
        if (hasResult) return false
        const scheduled = new Date()
        const [h, m] = slot.scheduled_time.split(':')
        scheduled.setHours(parseInt(h), parseInt(m), 0, 0)
        return scheduled <= now
      })
    })

    // Upcoming slots (scheduled in the future)
    const upcomingSlots = computed(() => {
      const now = new Date()
      return slotsStore.todaySlots.filter(slot => {
        const scheduled = new Date()
        const [h, m] = slot.scheduled_time.split(':')
        scheduled.setHours(parseInt(h), parseInt(m), 0, 0)
        return scheduled > now && !resultsStore.todayResults.some(r => r.slot_id === slot.id)
      })
    })

    const refreshResults = async () => {
      await Promise.all([
        resultsStore.fetchTodayResults(),
        slotsStore.fetchTodaySlots()
      ])
    }

    onMounted(async () => {
      await refreshResults()
      totalSlots.value = slotsStore.todaySlots.length
    })

    return {
      resultsStore,
      slotsStore,
      currentDate,
      totalSlots,
      autoResults,
      pendingResults,
  declaredResults,
  passedButNotDeclared,
  upcomingSlots,
      formatTime,
      refreshResults
    }
  }
}
</script>
