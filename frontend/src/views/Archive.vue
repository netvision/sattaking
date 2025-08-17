<template>
  <!-- Hero Banner -->
  <div class="bg-gradient-to-br from-blue-600 to-purple-700 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <div class="text-center">
        <h1 class="text-4xl md:text-6xl font-extrabold mb-4">
          <i class="fas fa-archive text-yellow-400 mr-2"></i>
          Results Archive
        </h1>
        <p class="text-xl md:text-2xl text-blue-100 mb-2">Explore past lottery results using our comprehensive archive.</p>
        <p class="text-lg text-blue-200 max-w-2xl mx-auto">Select a date below to view detailed historical draws.</p>
      </div>
    </div>
  </div>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Date Selector -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
      <div class="flex flex-col md:flex-row items-center justify-between space-y-4 md:space-y-0">
        <div class="flex items-center space-x-4">
          <label for="date-picker" class="text-sm font-medium text-gray-700">
            Select Date:
          </label>
          <input
            id="date-picker"
            v-model="selectedDate"
            type="date"
            :max="today"
            class="form-input w-auto"
            @change="fetchResultsForDate"
          />
        </div>
        
        <div class="flex items-center space-x-2">
          <button
            @click="goToPreviousDay"
            class="btn btn-outline"
            :disabled="resultsStore.loading"
          >
            <i class="fas fa-chevron-left mr-1"></i>
            Previous Day
          </button>
          
          <button
            @click="goToNextDay"
            class="btn btn-outline"
            :disabled="resultsStore.loading || selectedDate === today"
          >
            Next Day
            <i class="fas fa-chevron-right ml-1"></i>
          </button>
          
          <button
            @click="goToToday"
            class="btn btn-primary"
            :disabled="resultsStore.loading"
          >
            <i class="fas fa-calendar-day mr-1"></i>
            Today
          </button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="resultsStore.loading" class="flex justify-center py-12">
      <div class="spinner w-12 h-12"></div>
    </div>

    <!-- Results for Selected Date -->
    <div v-else-if="resultsStore.archiveResults.length > 0">
      <!-- Date Header -->
      <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">
          Results for {{ formatSelectedDate }}
        </h2>
        <p class="text-gray-600">{{ resultsStore.archiveResults.length }} results found</p>
      </div>

      <!-- Results Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div
          v-for="result in resultsStore.archiveResults"
          :key="result.id"
          class="bg-white rounded-lg shadow-lg p-6 hover:shadow-2xl transition-all duration-200"
        >
          <div class="text-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">{{ result.slot.title }}</h3>
            <p class="text-sm text-gray-500">
              <i class="fas fa-clock mr-1"></i>
              {{ formatTime(result.slot.scheduled_time) }}
            </p>
          </div>

          <div class="text-center">
            <div class="text-6xl md:text-7xl font-extrabold text-yellow-500 mb-4">{{ result.result }}</div>
            <div class="mt-4 text-xs text-gray-500">
              <p v-if="result.slot.description">{{ result.slot.description }}</p>
              <p>ID: {{ result.id }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Download/Export Options -->
      <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
          <i class="fas fa-download mr-2"></i>
          Export Results
        </h3>
        
        <div class="flex flex-wrap gap-4">
          <button
            @click="exportAsJSON"
            class="btn btn-outline"
          >
            <i class="fas fa-file-code mr-2"></i>
            Download JSON
          </button>
          
          <button
            @click="exportAsCSV"
            class="btn btn-outline"
          >
            <i class="fas fa-file-csv mr-2"></i>
            Download CSV
          </button>
          
          <button
            @click="printResults"
            class="btn btn-outline"
          >
            <i class="fas fa-print mr-2"></i>
            Print Results
          </button>
        </div>
      </div>
    </div>

    <!-- No Results State -->
    <div v-else class="text-center py-16">
      <i class="fas fa-calendar-times text-6xl text-gray-400 mb-6"></i>
      <h2 class="text-2xl font-semibold text-gray-600 mb-4">No Results Found</h2>
      <p class="text-gray-500 mb-8 max-w-md mx-auto">
        No lottery results were found for {{ formatSelectedDate }}. 
        Try selecting a different date or check if results were announced on this day.
      </p>
      
      <div class="space-y-4">
        <button @click="goToToday" class="btn btn-primary">
          <i class="fas fa-calendar-day mr-2"></i>
          View Today's Results
        </button>
        
        <div>
          <router-link to="/results" class="btn btn-outline">
            <i class="fas fa-list-ol mr-2"></i>
            Current Results
          </router-link>
        </div>
      </div>
    </div>

    <!-- Quick Date Navigation -->
    <div class="bg-white rounded-lg shadow-md p-6 mt-8">
      <h3 class="text-lg font-semibold text-gray-800 mb-4">
        <i class="fas fa-calendar-alt mr-2"></i>
        Quick Navigation
      </h3>
      
      <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-2">
        <button
          v-for="(date, index) in recentDates"
          :key="index"
          @click="selectDate(date.value)"
          class="btn btn-outline text-sm py-2"
          :class="{ 'btn-primary': selectedDate === date.value }"
        >
          {{ date.label }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { computed, onMounted, ref, watch } from 'vue'
import { useResultsStore } from '@/stores/results'
import { formatTime } from '@/utils/dateTime'
import { format, subDays, addDays } from 'date-fns'

export default {
  name: 'Archive',
  setup() {
    const resultsStore = useResultsStore()
    const selectedDate = ref(format(new Date(), 'yyyy-MM-dd'))
    const today = format(new Date(), 'yyyy-MM-dd')

    const formatSelectedDate = computed(() => {
      return format(new Date(selectedDate.value), 'EEEE, MMMM dd, yyyy')
    })

    const recentDates = computed(() => {
      const dates = []
      for (let i = 6; i >= 0; i--) {
        const date = subDays(new Date(), i)
        dates.push({
          label: i === 0 ? 'Today' : i === 1 ? 'Yesterday' : format(date, 'MMM dd'),
          value: format(date, 'yyyy-MM-dd')
        })
      }
      return dates
    })

    const fetchResultsForDate = async () => {
      if (selectedDate.value) {
        await resultsStore.fetchArchiveResults(selectedDate.value)
      }
    }

    const goToPreviousDay = () => {
      const prevDate = subDays(new Date(selectedDate.value), 1)
      selectedDate.value = format(prevDate, 'yyyy-MM-dd')
      fetchResultsForDate()
    }

    const goToNextDay = () => {
      if (selectedDate.value < today) {
        const nextDate = addDays(new Date(selectedDate.value), 1)
        selectedDate.value = format(nextDate, 'yyyy-MM-dd')
        fetchResultsForDate()
      }
    }

    const goToToday = () => {
      selectedDate.value = today
      fetchResultsForDate()
    }

    const selectDate = (date) => {
      selectedDate.value = date
      fetchResultsForDate()
    }

    const exportAsJSON = () => {
      const data = {
        date: selectedDate.value,
        results: resultsStore.archiveResults
      }
      const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' })
      const url = URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.download = `results-${selectedDate.value}.json`
      link.click()
      URL.revokeObjectURL(url)
    }

    const exportAsCSV = () => {
      const headers = ['Slot Title', 'Result', 'Declared At', 'Auto Generated', 'Locked']
      const rows = resultsStore.archiveResults.map(result => [
        result.slot.title,
        result.result,
        result.declared_at,
        result.is_auto ? 'Yes' : 'No',
        result.locked ? 'Yes' : 'No'
      ])
      
      const csvContent = [headers, ...rows]
        .map(row => row.map(field => `"${field}"`).join(','))
        .join('\n')
      
      const blob = new Blob([csvContent], { type: 'text/csv' })
      const url = URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.download = `results-${selectedDate.value}.csv`
      link.click()
      URL.revokeObjectURL(url)
    }

    const printResults = () => {
      window.print()
    }

    onMounted(() => {
      fetchResultsForDate()
    })

    return {
      resultsStore,
      selectedDate,
      today,
      formatSelectedDate,
      recentDates,
      formatTime,
      fetchResultsForDate,
      goToPreviousDay,
      goToNextDay,
      goToToday,
      selectDate,
      exportAsJSON,
      exportAsCSV,
      printResults
    }
  }
}
</script>

<style scoped>
@media print {
  .btn, nav, footer {
    display: none !important;
  }
  
  .result-card {
    break-inside: avoid;
    margin-bottom: 20px;
  }
}
</style>
