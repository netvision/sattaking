<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Slot History</h1>
        <p class="text-sm text-gray-500">Past results for <span class="font-semibold">{{ slotTitle }}</span></p>
      </div>
      <div>
        <router-link to="/results" class="btn btn-outline">Back to Results</router-link>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-lg font-semibold">{{ slotTitle }}</h3>
          <p class="text-sm text-gray-500">Scheduled: {{ formatTime(slotScheduled) }}</p>
        </div>
        <div class="text-sm text-gray-600">Showing last {{ results.length }} results</div>
      </div>
    </div>

    <div v-if="loading" class="flex justify-center py-12">
      <div class="spinner w-12 h-12"></div>
    </div>

    <div v-else>
      <div v-if="results.length === 0" class="text-center py-12">
        <i class="fas fa-history text-4xl text-gray-400 mb-4"></i>
        <div class="text-gray-600">No historical results found for this slot.</div>
      </div>

      <div v-else class="grid gap-4">
        <div v-for="r in results" :key="r.id" class="result-card flex items-center justify-between p-4">
          <div>
            <div class="text-sm text-gray-500">{{ formatDate(r.declared_at) }}</div>
            <div class="text-xl font-bold">{{ r.result }}</div>
          </div>
          <div class="text-right text-sm text-gray-500">
            <div v-if="r.locked">Final</div>
          </div>
        </div>

        <div class="flex justify-center mt-4">
          <button v-if="hasMore" @click="loadMore" class="btn btn-outline">Load more</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { resultsAPI, slotsAPI } from '@/services/api'
import { formatTime } from '@/utils/dateTime'

export default {
  name: 'SlotHistory',
  setup() {
    const route = useRoute()
    const slotId = route.params.id || null
    const slotTitle = ref('')
    const slotScheduled = ref('')
    const results = ref([])
    const loading = ref(false)
    const page = ref(1)
    const perPage = 20
    const hasMore = ref(false)

    const fetchSlot = async () => {
      try {
        const resp = await slotsAPI.getById(slotId)
        if (resp.data && resp.data.success) {
          slotTitle.value = resp.data.data.title
          slotScheduled.value = resp.data.data.scheduled_time
        }
      } catch (e) {
        console.error('Failed to fetch slot details', e)
      }
    }

    const fetchResults = async () => {
      loading.value = true
      try {
        const resp = await resultsAPI.getBySlot(slotId, { page: page.value, per_page: perPage })
        if (resp.data && resp.data.success) {
          const data = resp.data.data
          if (Array.isArray(data)) {
            results.value = results.value.concat(data)
            hasMore.value = false
          } else if (data && Array.isArray(data.results)) {
            results.value = results.value.concat(data.results)
            hasMore.value = !!data.next_page
          } else {
            // Fallback: if data is object but not paginated, try to push it
            results.value = results.value.concat(data)
            hasMore.value = false
          }
        }
      } catch (e) {
        console.error('Failed to fetch results', e)
      } finally {
        loading.value = false
      }
    }

    const loadMore = async () => {
      page.value += 1
      await fetchResults()
    }

    const formatDate = (dt) => {
      try {
        // declared_at may be ISO string; show date + time succinctly
        const d = new Date(dt)
        return d.toLocaleString()
      } catch (e) { return dt }
    }

    onMounted(async () => {
      if (!slotId) return
      await fetchSlot()
      await fetchResults()
    })

    return { slotTitle, slotScheduled, results, loading, hasMore, loadMore, formatTime: formatTime, formatDate }
  }
}
</script>
