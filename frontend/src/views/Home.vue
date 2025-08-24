<template>
  <div>
    <!-- Hero Section -->
    <div class="bg-gradient-to-br from-blue-600 to-purple-700 text-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center">
          <h1 class="text-4xl md:text-6xl font-bold mb-6">
            <i class="fas fa-crown text-yellow-400 mr-4"></i>
            Satta King Portal
          </h1>
          <p class="text-xl md:text-2xl mb-8 text-blue-100">
            Your Trusted Lottery Results Platform
          </p>
          <p class="text-lg mb-8 text-blue-200 max-w-2xl mx-auto">
            Get real-time lottery results, browse historical data, and stay updated with the latest announcements.
          </p>
          
          <div class="flex justify-center">
            <router-link
              to="/archive"
              class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition-colors inline-flex items-center justify-center"
            >
              <i class="fas fa-archive mr-2"></i>
              Browse Archive
            </router-link>
          </div>
        </div>
      </div>
    </div>

  <!-- Quick Results Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="text-center mb-12 relative">
        <div class="inline-block relative">
          <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-blue-600 via-fuchsia-600 to-purple-600 drop-shadow-sm">
            Today's Live Draws
          </h2>
          <div class="h-1 w-24 mx-auto mt-4 rounded-full bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500"></div>
        </div>
       
      </div>

      <!-- Loading State -->
      <div v-if="resultsStore.loading" class="flex justify-center py-8">
        <div class="spinner w-8 h-8"></div>
      </div>

      <!-- Highlighted Auto Slots (first two auto slots) -->
      <div v-else-if="slotsStore.todaySlots.length > 0" class="mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
          <SlotCard
            v-for="(slot, index) in primaryAutoSlots"
            :key="slot.id"
            :slot="slot"
            :primary="true"
            :todayResult="resultsBySlot[slot.id] || null"
            :prevResult="prevResultsBySlot[slot.id]"
          />
        </div>

        <!-- Other slots grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <SlotCard
            v-for="slot in secondarySlots"
            :key="slot.id"
            :slot="slot"
            :todayResult="resultsBySlot[slot.id] || null"
            :prevResult="prevResultsBySlot[slot.id]"
          />
        </div>
      </div>

      <!-- No Results State -->
      <div v-else class="text-center py-12">
        <i class="fas fa-clock text-4xl text-gray-400 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-600 mb-2">No Results Yet</h3>
        <p class="text-gray-500 mb-6">Results will be announced as per the scheduled time.</p>
        <router-link to="/results" class="btn btn-primary">
          <i class="fas fa-refresh mr-2"></i>
          Check for Updates
        </router-link>
      </div>

      <!-- View All Results Button -->
      <div v-if="resultsStore.todayResults.length > 0" class="text-center">
        <router-link to="/results" class="btn btn-primary">
          <i class="fas fa-eye mr-2"></i>
          View All Results
        </router-link>
      </div>
    </div>

    <!-- Features Section -->
    <div class="bg-white py-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="text-3xl font-bold text-gray-800 mb-4">Why Choose Our Platform?</h2>
          <p class="text-gray-600 max-w-2xl mx-auto">
            We provide reliable, fast, and accurate lottery results with advanced features for the best user experience.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="text-center p-6">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <i class="fas fa-bolt text-2xl text-blue-600"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Real-time Updates</h3>
            <p class="text-gray-600">
              Get instant notifications when results are announced. No more waiting or refreshing.
            </p>
          </div>

          <div class="text-center p-6">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <i class="fas fa-shield-alt text-2xl text-green-600"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">100% Accurate</h3>
            <p class="text-gray-600">
              All results are verified and authenticated. Trust our platform for reliable information.
            </p>
          </div>

          <div class="text-center p-6">
            <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <i class="fas fa-history text-2xl text-purple-600"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Complete Archive</h3>
            <p class="text-gray-600">
              Access historical results with our comprehensive archive system and calendar view.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats Section -->
    <div class="bg-gray-50 py-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-center">
          <div>
            <div class="text-3xl font-bold text-blue-600 mb-2">{{ todaySlotsCount }}</div>
            <div class="text-gray-600">Today's Slots</div>
          </div>
          
          <div>
            <div class="text-3xl font-bold text-green-600 mb-2">{{ resultsStore.todayResults.length }}</div>
            <div class="text-gray-600">Results Declared</div>
          </div>
          
          <div>
            <div class="text-3xl font-bold text-purple-600 mb-2">24/7</div>
            <div class="text-gray-600">Service Available</div>
          </div>
          
          <div>
            <div class="text-3xl font-bold text-orange-600 mb-2">100%</div>
            <div class="text-gray-600">Accuracy Rate</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { onMounted, ref, computed } from 'vue'
import { useResultsStore } from '@/stores/results'
import { useSlotsStore } from '@/stores/slots'
import { formatTime, formatDateTime } from '@/utils/dateTime'
import { format } from 'date-fns'
import SlotCard from '@/components/SlotCard.vue'

export default {
  name: 'Home',
  components: { SlotCard },
  setup() {
    const resultsStore = useResultsStore()
    const slotsStore = useSlotsStore()
    const todaySlotsCount = ref(0)

    onMounted(async () => {
      // Fetch today's results, slots, and a broader latest results list for historical lookup
      await Promise.all([
        resultsStore.fetchTodayResults(),
        slotsStore.fetchTodaySlots(),
        resultsStore.fetchLatestResults(50)
      ])
      todaySlotsCount.value = slotsStore.todaySlots.length
    })

    const resultsBySlot = computed(() => {
      const map = {}
      resultsStore.todayResults.forEach(r => { map[r.slot_id] = r })
      return map
    })

    const prevResultsBySlot = computed(() => {
      const map = {}
      if (!resultsStore.latestResults || !resultsStore.latestResults.length) return map

      const today = new Date()
      const yesterday = new Date(today)
      yesterday.setDate(today.getDate() - 1)
      const yesterdayKey = format(yesterday, 'yyyy-MM-dd')

      // Only map explicit yesterday's declared results. Do NOT fallback to older entries.
      for (const r of resultsStore.latestResults) {
        const declared = r.declared_at || r.created_at || ''
        const datePart = declared.slice(0, 10)
        if (datePart === yesterdayKey) {
          map[r.slot_id] = r.result
        }
      }

      return map
    })

    const primaryAutoSlots = computed(() => {
      // pick first two auto slots from today's slots
      const autos = slotsStore.todaySlots.filter(s => s.is_auto).slice(0, 2)
      // If less than 2 autos, fill with active manual slots
      if (autos.length < 2) {
        const needed = 2 - autos.length
        const others = slotsStore.todaySlots.filter(s => !s.is_auto).slice(0, needed)
        return [...autos, ...others]
      }
      return autos
    })

    const secondarySlots = computed(() => {
      const primaryIds = primaryAutoSlots.value.map(s => s.id)
      return slotsStore.todaySlots.filter(s => !primaryIds.includes(s.id))
    })

    return {
      resultsStore,
      slotsStore,
      todaySlotsCount,
      formatTime,
      formatDateTime,
      resultsBySlot,
      prevResultsBySlot,
      primaryAutoSlots,
      secondarySlots
    }
  }
}
</script>
