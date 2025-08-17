import { defineStore } from 'pinia'
import { slotsAPI } from '@/services/api'
import { useToast } from 'vue-toastification'

export const useSlotsStore = defineStore('slots', {
  state: () => ({
    allSlots: [],
    todaySlots: [],
    loading: false,
    selectedSlot: null,
  }),

  getters: {
    activeSlots: (state) => state.allSlots.filter(slot => slot.is_active),
    autoSlots: (state) => state.allSlots.filter(slot => slot.is_auto),
    getSlotById: (state) => (id) => state.allSlots.find(slot => slot.id === id),
  },

  actions: {
    async fetchAllSlots(params = {}) {
      this.loading = true
      try {
        const response = await slotsAPI.getAll(params)
        if (response.data.success) {
          this.allSlots = response.data.data
        }
      } catch (error) {
        console.error('Error fetching slots:', error)
        useToast().error('Failed to fetch slots')
      } finally {
        this.loading = false
      }
    },

    async fetchTodaySlots() {
      this.loading = true
      try {
        const response = await slotsAPI.getToday()
        if (response.data.success) {
          this.todaySlots = response.data.data
        }
      } catch (error) {
        console.error('Error fetching today slots:', error)
        useToast().error('Failed to fetch today\'s slots')
      } finally {
        this.loading = false
      }
    },

    async fetchSlotById(id) {
      this.loading = true
      try {
        const response = await slotsAPI.getById(id)
        if (response.data.success) {
          this.selectedSlot = response.data.data
          return response.data.data
        }
      } catch (error) {
        console.error('Error fetching slot:', error)
        useToast().error('Failed to fetch slot details')
      } finally {
        this.loading = false
      }
      return null
    },

    async createSlot(slotData) {
      const toast = useToast()
      this.loading = true
      
      try {
        const response = await slotsAPI.create(slotData)
        if (response.data.success) {
          toast.success('Slot created successfully!')
          await this.fetchAllSlots() // Refresh slots
          return { success: true, data: response.data.data }
        } else {
          toast.error(response.data.message || 'Failed to create slot')
          return { success: false, message: response.data.message, errors: response.data.errors }
        }
      } catch (error) {
        const message = error.response?.data?.message || 'Failed to create slot'
        const errors = error.response?.data?.errors || {}
        const debugData = error.response?.data?.debug_received_data
        const debugAttributes = error.response?.data?.debug_model_attributes
        
        toast.error(message)
        return { 
          success: false, 
          message, 
          errors,
          debug_received_data: debugData,
          debug_model_attributes: debugAttributes
        }
      } finally {
        this.loading = false
      }
    },

    async updateSlot(id, slotData) {
      const toast = useToast()
      this.loading = true
      
      try {
        const response = await slotsAPI.update(id, slotData)
        if (response.data.success) {
          toast.success('Slot updated successfully!')
          await this.fetchAllSlots() // Refresh slots
          return { success: true, data: response.data.data }
        } else {
          toast.error(response.data.message || 'Failed to update slot')
          return { success: false, message: response.data.message, errors: response.data.errors }
        }
      } catch (error) {
        const message = error.response?.data?.message || 'Failed to update slot'
        const errors = error.response?.data?.errors || {}
        const debugData = error.response?.data?.debug_received_data
        const debugAttributes = error.response?.data?.debug_model_attributes
        
        toast.error(message)
        return { 
          success: false, 
          message, 
          errors,
          debug_received_data: debugData,
          debug_model_attributes: debugAttributes
        }
      } finally {
        this.loading = false
      }
    },

    async deleteSlot(id) {
      const toast = useToast()
      this.loading = true
      
      try {
        const response = await slotsAPI.delete(id)
        if (response.data.success) {
          toast.success('Slot deleted successfully!')
          await this.fetchAllSlots() // Refresh slots
          return { success: true }
        } else {
          toast.error(response.data.message || 'Failed to delete slot')
          return { success: false, message: response.data.message }
        }
      } catch (error) {
        const message = error.response?.data?.message || 'Failed to delete slot'
        toast.error(message)
        return { success: false, message }
      } finally {
        this.loading = false
      }
    },
  },
})
