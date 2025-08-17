<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">
          <i class="fas fa-clock text-blue-600 mr-2"></i>
          Manage Slots
        </h1>
        <p class="text-gray-600 mt-2">Create and manage lottery slots</p>
      </div>
      
      <button
        @click="showCreateModal = true"
        class="btn btn-primary"
      >
        <i class="fas fa-plus mr-2"></i>
        Create New Slot
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
      <div class="flex flex-wrap items-center gap-4">
        <div>
          <label class="text-sm font-medium text-gray-700 mr-2">Filter by date:</label>
          <input
            v-model="filters.date"
            type="date"
            class="form-input w-auto"
            @change="applyFilters"
          />
        </div>
        
        <div>
          <label class="text-sm font-medium text-gray-700 mr-2">Status:</label>
          <select v-model="filters.active" class="form-input w-auto" @change="applyFilters">
            <option value="">All Slots</option>
            <option value="1">Active Only</option>
            <option value="0">Inactive Only</option>
          </select>
        </div>
        
        <button @click="resetFilters" class="btn btn-outline">
          <i class="fas fa-times mr-2"></i>
          Clear Filters
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="slotsStore.loading" class="flex justify-center py-12">
      <div class="spinner w-12 h-12"></div>
    </div>

    <!-- Slots Table -->
    <div v-else class="bg-white rounded-lg shadow-md overflow-hidden">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Scheduled Time</th>
            <th>Auto Generate</th>
            <th>Status</th>
            <th>Has Result</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="slot in slotsStore.allSlots" :key="slot.id">
            <td class="font-medium">{{ slot.id }}</td>
            <td>
              <div>
                <div class="font-medium text-gray-900">{{ slot.title }}</div>
                <div v-if="slot.description" class="text-sm text-gray-500">{{ slot.description }}</div>
              </div>
            </td>
            <td>
              <div class="text-sm">
                <div class="text-gray-900">Daily</div>
                <div class="text-gray-500">{{ formatTime(slot.scheduled_time) }}</div>
              </div>
            </td>
            <td>
              <span v-if="slot.is_auto" class="badge badge-info">
                <i class="fas fa-robot mr-1"></i>
                Auto
              </span>
              <span v-else class="badge badge-secondary">
                <i class="fas fa-hand-paper mr-1"></i>
                Manual
              </span>
            </td>
            <td>
              <span v-if="slot.is_active" class="badge badge-success">
                <i class="fas fa-check-circle mr-1"></i>
                Active
              </span>
              <span v-else class="badge badge-danger">
                <i class="fas fa-times-circle mr-1"></i>
                Inactive
              </span>
            </td>
            <td>
              <span v-if="slot.has_result_today" class="badge badge-success">
                <i class="fas fa-check mr-1"></i>
                Yes
              </span>
              <span v-else class="badge badge-warning">
                <i class="fas fa-clock mr-1"></i>
                Pending
              </span>
            </td>
            <td>
              <div class="flex items-center space-x-2">
                <button
                  @click="editSlot(slot)"
                  class="text-blue-600 hover:text-blue-900"
                  title="Edit Slot"
                >
                  <i class="fas fa-edit"></i>
                </button>
                
                <button
                  @click="toggleSlotStatus(slot)"
                  :class="slot.is_active ? 'text-orange-600 hover:text-orange-900' : 'text-green-600 hover:text-green-900'"
                  :title="slot.is_active ? 'Deactivate Slot' : 'Activate Slot'"
                >
                  <i :class="slot.is_active ? 'fas fa-pause' : 'fas fa-play'"></i>
                </button>
                
                <button
                  @click="deleteSlot(slot)"
                  class="text-red-600 hover:text-red-900"
                  title="Delete Slot"
                  :disabled="slot.has_result_today"
                >
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      
      <!-- Empty State -->
      <div v-if="slotsStore.allSlots.length === 0" class="text-center py-12">
        <i class="fas fa-clock text-4xl text-gray-400 mb-4"></i>
        <h3 class="text-lg font-medium text-gray-600 mb-2">No slots found</h3>
        <p class="text-gray-500 mb-4">Create your first slot to get started.</p>
        <button @click="showCreateModal = true" class="btn btn-primary">
          <i class="fas fa-plus mr-2"></i>
          Create First Slot
        </button>
      </div>
    </div>

    <!-- Create/Edit Slot Modal -->
    <div v-if="showCreateModal || showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="closeModals">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" @click.stop>
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            <i class="fas fa-clock mr-2"></i>
            {{ showCreateModal ? 'Create New Slot' : 'Edit Slot' }}
          </h3>
          
          <form @submit.prevent="submitSlot">
            <div class="mb-4">
              <label class="form-label">Title *</label>
              <input
                v-model="slotForm.title"
                type="text"
                class="form-input"
                :class="{ 'border-red-500': formErrors.title }"
                placeholder="e.g., Morning King"
                required
              />
              <p v-if="formErrors.title" class="form-error">{{ formErrors.title }}</p>
            </div>
            
            <div class="mb-4">
              <label class="form-label">Scheduled Time *</label>
              <input
                v-model="slotForm.scheduled_time"
                type="time"
                class="form-input"
                :class="{ 'border-red-500': formErrors.scheduled_time }"
                required
              />
              <p v-if="formErrors.scheduled_time" class="form-error">{{ formErrors.scheduled_time }}</p>
              <p class="text-xs text-gray-500 mt-1">This slot will run daily at this time</p>
            </div>
            
            <div class="mb-4">
              <label class="form-label">Description</label>
              <textarea
                v-model="slotForm.description"
                class="form-input"
                rows="3"
                placeholder="Optional description for this slot"
              ></textarea>
            </div>
            
            <div class="mb-4">
              <div class="flex items-center space-x-4">
                <label class="flex items-center">
                  <input
                    v-model="slotForm.is_auto"
                    type="checkbox"
                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                  />
                  <span class="ml-2 text-sm font-medium text-gray-700">Auto Generate Result</span>
                </label>
                
                <label class="flex items-center">
                  <input
                    v-model="slotForm.is_active"
                    type="checkbox"
                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                  />
                  <span class="ml-2 text-sm font-medium text-gray-700">Active</span>
                </label>
              </div>
            </div>
            
            <div class="flex justify-end space-x-3">
              <button type="button" @click="closeModals" class="btn btn-outline">
                Cancel
              </button>
              <button type="submit" class="btn btn-primary" :disabled="slotsStore.loading">
                <i class="fas fa-save mr-2"></i>
                {{ showCreateModal ? 'Create Slot' : 'Update Slot' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { onMounted, reactive, ref } from 'vue'
import { useSlotsStore } from '@/stores/slots'
import { formatTime } from '@/utils/dateTime'
import { useToast } from 'vue-toastification'

export default {
  name: 'AdminSlots',
  setup() {
    const slotsStore = useSlotsStore()
    const toast = useToast()
    
    const showCreateModal = ref(false)
    const showEditModal = ref(false)
    const editingSlot = ref(null)
    
    const filters = reactive({
      date: '',
      active: ''
    })
    
    const slotForm = reactive({
      title: '',
      scheduled_time: '',
      description: '',
      is_auto: false,
      is_active: true
    })
    
    const formErrors = reactive({
      title: '',
      scheduled_time: ''
    })

    const applyFilters = async () => {
      const params = {}
      if (filters.date) params.date = filters.date
      if (filters.active !== '') params.active = filters.active
      
      await slotsStore.fetchAllSlots(params)
    }

    const resetFilters = async () => {
      filters.date = ''
      filters.active = ''
      await slotsStore.fetchAllSlots()
    }

    const resetForm = () => {
      slotForm.title = ''
      slotForm.scheduled_time = ''
      slotForm.description = ''
      slotForm.is_auto = false
      slotForm.is_active = true
      
      formErrors.title = ''
      formErrors.scheduled_time = ''
    }

    const closeModals = () => {
      showCreateModal.value = false
      showEditModal.value = false
      editingSlot.value = null
      resetForm()
    }

    const editSlot = (slot) => {
      editingSlot.value = slot
      slotForm.title = slot.title
      // Convert time-only format (HH:MM:SS) to time input format (HH:MM)
      slotForm.scheduled_time = slot.scheduled_time ? slot.scheduled_time.substring(0, 5) : ''
      slotForm.description = slot.description || ''
      slotForm.is_auto = !!slot.is_auto
      slotForm.is_active = !!slot.is_active
      showEditModal.value = true
    }

    const submitSlot = async () => {
      // Reset errors
      formErrors.title = ''
      formErrors.scheduled_time = ''

      // Prepare the data to send
      const slotData = { ...slotForm }
      
      // Convert HH:MM to HH:MM:SS format for backend
      if (slotData.scheduled_time && slotData.scheduled_time.length === 5) {
        slotData.scheduled_time = slotData.scheduled_time + ':00'
      }

      // Convert booleans to integers for backend
      slotData.is_auto = slotData.is_auto ? 1 : 0
      slotData.is_active = slotData.is_active ? 1 : 0

      let result
      try {
        if (showCreateModal.value) {
          result = await slotsStore.createSlot(slotData)
        } else {
          result = await slotsStore.updateSlot(editingSlot.value.id, slotData)
        }
      } catch (error) {
        console.error('Exception in slot operation:', error)
        result = { success: false, message: 'Exception occurred', error: error.message }
      }

      if (result.success) {
        closeModals()
      } else {
        // Handle validation errors
        if (result.errors) {
          Object.keys(result.errors).forEach(key => {
            if (formErrors.hasOwnProperty(key)) {
              formErrors[key] = result.errors[key][0]
            }
          })
        }
      }
    }

    const toggleSlotStatus = async (slot) => {
      const newStatus = !slot.is_active
      
      const result = await slotsStore.updateSlot(slot.id, {
        is_active: newStatus ? 1 : 0  // Convert to integer like we do for create
      })

      if (result.success) {
        toast.success(`Slot ${newStatus ? 'activated' : 'deactivated'} successfully`)
      }
    }

    const deleteSlot = async (slot) => {
      if (slot.has_result_today) {
        toast.error('Cannot delete slot with existing results')
        return
      }

      if (confirm(`Are you sure you want to delete "${slot.title}"? This action cannot be undone.`)) {
        const result = await slotsStore.deleteSlot(slot.id)
        if (result.success) {
          toast.success('Slot deleted successfully')
        }
      }
    }

    onMounted(() => {
      slotsStore.fetchAllSlots()
    })

    return {
      slotsStore,
      showCreateModal,
      showEditModal,
      filters,
      slotForm,
      formErrors,
      formatTime,
      applyFilters,
      resetFilters,
      closeModals,
      editSlot,
      submitSlot,
      toggleSlotStatus,
      deleteSlot
    }
  }
}
</script>
