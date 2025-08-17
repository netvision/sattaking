<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">
          <i class="fas fa-trophy text-green-600 mr-2"></i>
          Manage Results
        </h1>
        <p class="text-gray-600 mt-2">Set and manage lottery results</p>
      </div>
      
      <button
        @click="showSetResultModal = true"
        class="btn btn-primary"
      >
        <i class="fas fa-plus mr-2"></i>
        Set New Result
      </button>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow-md p-4 text-center">
        <h3 class="text-2xl font-bold text-blue-600">{{ todayResults.length }}</h3>
        <p class="text-gray-600">Today's Results</p>
      </div>
      
      <div class="bg-white rounded-lg shadow-md p-4 text-center">
        <h3 class="text-2xl font-bold text-green-600">{{ autoResults }}</h3>
        <p class="text-gray-600">Auto Generated</p>
      </div>
      
      <div class="bg-white rounded-lg shadow-md p-4 text-center">
        <h3 class="text-2xl font-bold text-orange-600">{{ lockedResults }}</h3>
        <p class="text-gray-600">Locked Results</p>
      </div>
      
      <div class="bg-white rounded-lg shadow-md p-4 text-center">
        <h3 class="text-2xl font-bold text-purple-600">{{ pendingSlots.length }}</h3>
        <p class="text-gray-600">Pending Slots</p>
      </div>
    </div>

    <!-- Pending Slots Section -->
    <div v-if="pendingSlots.length > 0" class="bg-white rounded-lg shadow-md p-6 mb-8">
      <h2 class="text-xl font-semibold text-gray-800 mb-4">
        <i class="fas fa-clock text-orange-500 mr-2"></i>
        Pending Results ({{ pendingSlots.length }})
      </h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="slot in pendingSlots"
          :key="slot.id"
          class="border border-orange-200 rounded-lg p-4 bg-orange-50"
        >
          <div class="flex justify-between items-start mb-3">
            <div>
              <h3 class="font-semibold text-gray-800">{{ slot.title }}</h3>
              <p class="text-sm text-gray-600">
                <i class="fas fa-clock mr-1"></i>
                {{ formatTime(slot.scheduled_time) }}
              </p>
            </div>
            <span v-if="slot.is_auto" class="badge badge-info">Auto</span>
          </div>
          
          <button
            @click="openSetResult(slot)"
            class="w-full btn btn-primary btn-sm"
          >
            <i class="fas fa-trophy mr-2"></i>
            Set Result
          </button>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
      <div class="flex flex-wrap items-center gap-4">
        <div>
          <label class="text-sm font-medium text-gray-700 mr-2">Date:</label>
          <input
            v-model="filters.date"
            type="date"
            class="form-input w-auto"
            @change="applyFilters"
          />
        </div>
        
        <div>
          <label class="text-sm font-medium text-gray-700 mr-2">Type:</label>
          <select v-model="filters.type" class="form-input w-auto" @change="applyFilters">
            <option value="">All Results</option>
            <option value="auto">Auto Generated</option>
            <option value="manual">Manual</option>
          </select>
        </div>
        
        <div>
          <label class="text-sm font-medium text-gray-700 mr-2">Status:</label>
          <select v-model="filters.locked" class="form-input w-auto" @change="applyFilters">
            <option value="">All</option>
            <option value="1">Locked</option>
            <option value="0">Unlocked</option>
          </select>
        </div>
        
        <button @click="resetFilters" class="btn btn-outline">
          <i class="fas fa-times mr-2"></i>
          Clear
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="resultsStore.loading" class="flex justify-center py-12">
      <div class="spinner w-12 h-12"></div>
    </div>

    <!-- Results Table -->
    <div v-else class="bg-white rounded-lg shadow-md overflow-hidden">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Slot</th>
            <th>Result</th>
            <th>Declared At</th>
            <th>Type</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="result in filteredResults" :key="result.id">
            <td class="font-medium">{{ result.id }}</td>
            <td>
              <div>
                <div class="font-medium text-gray-900">{{ result.slot.title }}</div>
                <div class="text-sm text-gray-500">{{ formatTime(result.slot.scheduled_time) }}</div>
              </div>
            </td>
            <td>
              <div class="inline-block bg-gradient-to-br from-blue-500 to-purple-600 text-white text-lg font-bold px-3 py-1 rounded-full min-w-12 text-center">
                {{ result.result }}
              </div>
            </td>
            <td>
              <div class="text-sm">
                <div>{{ formatDate(result.declared_at) }}</div>
                <div class="text-gray-500">{{ formatTime(result.declared_at) }}</div>
              </div>
            </td>
            <td>
              <span v-if="result.is_auto" class="badge badge-info">
                <i class="fas fa-robot mr-1"></i>
                Auto
              </span>
              <span v-else class="badge badge-secondary">
                <i class="fas fa-hand-paper mr-1"></i>
                Manual
              </span>
            </td>
            <td>
              <span v-if="result.locked" class="badge badge-warning">
                <i class="fas fa-lock mr-1"></i>
                Locked
              </span>
              <span v-else class="badge badge-success">
                <i class="fas fa-unlock mr-1"></i>
                Unlocked
              </span>
            </td>
            <td>
              <div class="flex items-center space-x-2">
                <button
                  v-if="!result.locked"
                  @click="editResult(result)"
                  class="text-blue-600 hover:text-blue-900"
                  title="Edit Result"
                >
                  <i class="fas fa-edit"></i>
                </button>
                
                <button
                  v-if="!result.locked"
                  @click="lockResult(result)"
                  class="text-orange-600 hover:text-orange-900"
                  title="Lock Result"
                >
                  <i class="fas fa-lock"></i>
                </button>
                
                <span v-if="result.locked" class="text-gray-400" title="Result is locked">
                  <i class="fas fa-lock"></i>
                </span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      
      <!-- Empty State -->
      <div v-if="filteredResults.length === 0" class="text-center py-12">
        <i class="fas fa-trophy text-4xl text-gray-400 mb-4"></i>
        <h3 class="text-lg font-medium text-gray-600 mb-2">No results found</h3>
        <p class="text-gray-500 mb-4">No results match your current filters.</p>
        <button @click="resetFilters" class="btn btn-primary">
          <i class="fas fa-refresh mr-2"></i>
          Show All Results
        </button>
      </div>
    </div>

    <!-- Set Result Modal -->
    <div v-if="showSetResultModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="closeSetResultModal">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" @click.stop>
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            <i class="fas fa-trophy mr-2"></i>
            Set Result
          </h3>
          
          <form @submit.prevent="submitResult">
            <div class="mb-4">
              <label class="form-label">Select Slot *</label>
              <select v-model="resultForm.slot_id" class="form-input" required>
                <option value="">Choose a slot...</option>
                <option v-for="slot in availableSlots" :key="slot.id" :value="slot.id">
                  {{ slot.title }} - {{ formatTime(slot.scheduled_time) }}
                </option>
              </select>
            </div>
            
            <div class="mb-4">
              <label class="form-label">Result (0-99) *</label>
              <input
                v-model.number="resultForm.result"
                type="number"
                min="0"
                max="99"
                class="form-input"
                :class="{ 'border-red-500': formErrors.result }"
                required
              />
              <p v-if="formErrors.result" class="form-error">{{ formErrors.result }}</p>
            </div>
            
            <div class="mb-4">
              <label class="form-label">Declared Time</label>
              <input
                v-model="resultForm.declared_at"
                type="datetime-local"
                class="form-input"
              />
              <p class="text-xs text-gray-500 mt-1">Leave empty to use current time</p>
            </div>
            
            <div class="flex justify-end space-x-3">
              <button type="button" @click="closeSetResultModal" class="btn btn-outline">
                Cancel
              </button>
              <button type="submit" class="btn btn-primary" :disabled="resultsStore.loading">
                <i class="fas fa-save mr-2"></i>
                {{ editingResult ? 'Update Result' : 'Set Result' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { computed, onMounted, reactive, ref } from 'vue'
import { useResultsStore } from '@/stores/results'
import { useSlotsStore } from '@/stores/slots'
import { format } from 'date-fns'
import { useToast } from 'vue-toastification'

export default {
  name: 'AdminResults',
  setup() {
    const resultsStore = useResultsStore()
    const slotsStore = useSlotsStore()
    const toast = useToast()
    
    const showSetResultModal = ref(false)
    const editingResult = ref(null)
    
    const filters = reactive({
      date: format(new Date(), 'yyyy-MM-dd'),
      type: '',
      locked: ''
    })
    
    const resultForm = reactive({
      slot_id: '',
      result: null,
      declared_at: ''
    })
    
    const formErrors = reactive({
      result: ''
    })

    const todayResults = computed(() => resultsStore.todayResults)
    const autoResults = computed(() => todayResults.value.filter(r => r.is_auto).length)
    const lockedResults = computed(() => todayResults.value.filter(r => r.locked).length)

    const pendingSlots = computed(() => {
      return slotsStore.todaySlots.filter(slot => {
        return !todayResults.value.some(result => result.slot_id === slot.id)
      })
    })

    const availableSlots = computed(() => {
      if (editingResult.value) {
        // When editing, only show the current slot
        return slotsStore.allSlots.filter(slot => slot.id === editingResult.value.slot_id)
      }
      // When creating, show slots without results for today
      return pendingSlots.value
    })

    const filteredResults = computed(() => {
      let results = filters.date === format(new Date(), 'yyyy-MM-dd') 
        ? todayResults.value 
        : resultsStore.archiveResults

      if (filters.type === 'auto') {
        results = results.filter(r => r.is_auto)
      } else if (filters.type === 'manual') {
        results = results.filter(r => !r.is_auto)
      }

      if (filters.locked === '1') {
        results = results.filter(r => r.locked)
      } else if (filters.locked === '0') {
        results = results.filter(r => !r.locked)
      }

      return results
    })

    const formatDate = (datetime) => {
      return format(new Date(datetime), 'MMM dd, yyyy')
    }

    const formatTime = (datetime) => {
      return format(new Date(datetime), 'HH:mm')
    }

    const applyFilters = async () => {
      if (filters.date === format(new Date(), 'yyyy-MM-dd')) {
        await resultsStore.fetchTodayResults()
      } else {
        await resultsStore.fetchArchiveResults(filters.date)
      }
    }

    const resetFilters = async () => {
      filters.date = format(new Date(), 'yyyy-MM-dd')
      filters.type = ''
      filters.locked = ''
      await resultsStore.fetchTodayResults()
    }

    const resetForm = () => {
      resultForm.slot_id = ''
      resultForm.result = null
      resultForm.declared_at = ''
      formErrors.result = ''
    }

    const openSetResult = (slot) => {
      resetForm()
      resultForm.slot_id = slot.id
      showSetResultModal.value = true
    }

    const editResult = (result) => {
      editingResult.value = result
      resultForm.slot_id = result.slot_id
      resultForm.result = result.result
      resultForm.declared_at = format(new Date(result.declared_at), "yyyy-MM-dd'T'HH:mm")
      showSetResultModal.value = true
    }

    const closeSetResultModal = () => {
      showSetResultModal.value = false
      editingResult.value = null
      resetForm()
    }

    const submitResult = async () => {
      formErrors.result = ''

      let result
      if (editingResult.value) {
        result = await resultsStore.updateResult(editingResult.value.id, resultForm)
      } else {
        result = await resultsStore.createResult(resultForm)
      }

      if (result.success) {
        closeSetResultModal()
        await loadData()
      } else {
        if (result.errors && result.errors.result) {
          formErrors.result = result.errors.result[0]
        }
      }
    }

    const lockResult = async (result) => {
      if (confirm(`Are you sure you want to lock the result for "${result.slot.title}"? This action cannot be undone.`)) {
        await resultsStore.lockResult(result.id)
        await loadData()
      }
    }

    const loadData = async () => {
      await Promise.all([
        resultsStore.fetchTodayResults(),
        slotsStore.fetchTodaySlots(),
        slotsStore.fetchAllSlots()
      ])
    }

    onMounted(() => {
      loadData()
    })

    return {
      resultsStore,
      slotsStore,
      showSetResultModal,
      editingResult,
      filters,
      resultForm,
      formErrors,
      todayResults,
      autoResults,
      lockedResults,
      pendingSlots,
      availableSlots,
      filteredResults,
      formatDate,
      formatTime,
      applyFilters,
      resetFilters,
      openSetResult,
      editResult,
      closeSetResultModal,
      submitResult,
      lockResult
    }
  }
}
</script>
