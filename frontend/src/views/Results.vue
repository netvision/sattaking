<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="text-center mb-8">
      <h1 class="text-3xl font-bold text-gray-800 mb-4">
        <i class="fas fa-list-ol text-blue-600 mr-2"></i>
        Today's Results
      </h1>
      <p class="text-gray-600">{{ currentDate }}</p>
    </div>

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
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div
          v-for="result in resultsStore.todayResults"
          :key="result.id"
          class="result-card transform hover:scale-105 transition-transform duration-200"
        >
          <div class="flex justify-between items-start mb-4">
            <div>
              <h3 class="text-lg font-semibold text-gray-800">{{ result.slot.title }}</h3>
              <p class="text-sm text-gray-500">
                <i class="fas fa-clock mr-1"></i>
                Scheduled: {{ formatTime(result.slot.scheduled_time) }}
              </p>
              <p class="text-sm text-gray-500">
                <i class="fas fa-check-circle mr-1"></i>
                Declared: {{ formatTime(result.declared_at) }}
              </p>
            </div>
            
            <div class="flex flex-col items-end space-y-2">
              <span v-if="result.is_auto" class="badge badge-info">
                <i class="fas fa-robot mr-1"></i>
                Auto
              </span>
              <span v-if="result.locked" class="badge badge-warning">
                <i class="fas fa-lock mr-1"></i>
                Locked
              </span>
            </div>
          </div>
          
          <div class="text-center">
            <div class="result-number">{{ result.result }}</div>
            
            <!-- Additional Info -->
            <div class="mt-4 text-xs text-gray-500">
              <p v-if="result.slot.description">{{ result.slot.description }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Summary Stats -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
          <i class="fas fa-chart-bar mr-2"></i>
          Today's Summary
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-center">
          <div class="p-4 bg-blue-50 rounded-lg">
            <div class="text-2xl font-bold text-blue-600">{{ totalSlots }}</div>
            <div class="text-sm text-gray-600">Total Slots</div>
          </div>
          
          <div class="p-4 bg-green-50 rounded-lg">
            <div class="text-2xl font-bold text-green-600">{{ resultsStore.todayResults.length }}</div>
            <div class="text-sm text-gray-600">Results Declared</div>
          </div>
          
          <div class="p-4 bg-purple-50 rounded-lg">
            <div class="text-2xl font-bold text-purple-600">{{ autoResults }}</div>
            <div class="text-sm text-gray-600">Auto Generated</div>
          </div>
          
          <div class="p-4 bg-orange-50 rounded-lg">
            <div class="text-2xl font-bold text-orange-600">{{ pendingResults }}</div>
            <div class="text-sm text-gray-600">Pending</div>
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
          class="slot-card"
        >
          <div class="flex justify-between items-center">
            <div>
              <h4 class="font-semibold text-gray-800">{{ slot.title }}</h4>
              <p class="text-sm text-gray-500">
                <i class="fas fa-clock mr-1"></i>
                {{ formatTime(slot.scheduled_time) }}
              </p>
            </div>
            
            <div class="flex flex-col items-end space-y-1">
              <span v-if="slot.is_auto" class="badge badge-secondary">
                <i class="fas fa-robot mr-1"></i>
                Auto
              </span>
              <span class="badge badge-warning">
                <i class="fas fa-hourglass-half mr-1"></i>
                Pending
              </span>
            </div>
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

    const upcomingSlots = computed(() => {
      return slotsStore.todaySlots.filter(slot => {
        return !resultsStore.todayResults.some(result => result.slot_id === slot.id)
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
      upcomingSlots,
      formatTime,
      refreshResults
    }
  }
}
</script>
