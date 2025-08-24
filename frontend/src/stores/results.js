import { defineStore } from 'pinia'
import { resultsAPI } from '@/services/api'
import { useToast } from 'vue-toastification'

export const useResultsStore = defineStore('results', {
  state: () => ({
    todayResults: [],
    archiveResults: [],
    latestResults: [],
    loading: false,
    selectedDate: null,
  }),

  getters: {
    getTodayResultsCount: (state) => state.todayResults.length,
    getResultBySlotId: (state) => (slotId) => {
      return state.todayResults.find(result => result.slot_id === slotId)
    },
  },

  actions: {
    async fetchTodayResults() {
      this.loading = true
      try {
        const response = await resultsAPI.getToday()
        if (response.data.success) {
          this.todayResults = response.data.data
        }
      } catch (error) {
        console.error('Error fetching today results:', error)
        useToast().error('Failed to fetch today\'s results')
      } finally {
        this.loading = false
      }
    },

    async fetchArchiveResults(date) {
      this.loading = true
      this.selectedDate = date
      try {
        const response = await resultsAPI.getArchive(date)
        if (response.data.success) {
          this.archiveResults = response.data.data
          return { success: true, data: response.data.data }
        } else {
          return { success: false, data: [] }
        }
      } catch (error) {
        console.error('Error fetching archive results:', error)
        useToast().error('Failed to fetch archive results')
        return { success: false, data: [] }
      } finally {
        this.loading = false
      }
    },

    async fetchLatestResults(limit = 10) {
      this.loading = true
      try {
        const response = await resultsAPI.getLatest(limit)
        if (response.data.success) {
          this.latestResults = response.data.data
        }
      } catch (error) {
        console.error('Error fetching latest results:', error)
        useToast().error('Failed to fetch latest results')
      } finally {
        this.loading = false
      }
    },

    async createResult(resultData) {
      const toast = useToast()
      this.loading = true
      
      try {
        const response = await resultsAPI.create(resultData)
        if (response.data.success) {
          toast.success('Result saved successfully!')
          await this.fetchTodayResults() // Refresh today's results
          return { success: true, data: response.data.data }
        } else {
          toast.error(response.data.message || 'Failed to save result')
          return { success: false, message: response.data.message }
        }
      } catch (error) {
        const message = error.response?.data?.message || 'Failed to save result'
        toast.error(message)
        return { success: false, message }
      } finally {
        this.loading = false
      }
    },

    async updateResult(id, resultData) {
      const toast = useToast()
      this.loading = true
      
      try {
        const response = await resultsAPI.update(id, resultData)
        if (response.data.success) {
          toast.success('Result updated successfully!')
          await this.fetchTodayResults() // Refresh today's results
          return { success: true, data: response.data.data }
        } else {
          toast.error(response.data.message || 'Failed to update result')
          return { success: false, message: response.data.message }
        }
      } catch (error) {
        const message = error.response?.data?.message || 'Failed to update result'
        toast.error(message)
        return { success: false, message }
      } finally {
        this.loading = false
      }
    },

    async lockResult(id) {
      const toast = useToast()
      this.loading = true
      
      try {
        const response = await resultsAPI.lock(id)
        if (response.data.success) {
          toast.success('Result locked successfully!')
          await this.fetchTodayResults() // Refresh today's results
          return { success: true }
        } else {
          toast.error(response.data.message || 'Failed to lock result')
          return { success: false, message: response.data.message }
        }
      } catch (error) {
        const message = error.response?.data?.message || 'Failed to lock result'
        toast.error(message)
        return { success: false, message }
      } finally {
        this.loading = false
      }
    },

    async fetchMonthResults(month) {
      this.loading = true
      try {
        const response = await resultsAPI.getMonth(month)
        if (response.data.success) {
          return { success: true, data: response.data.data }
        } else {
          return { success: false, data: [] }
        }
      } catch (error) {
        console.error('Error fetching month results:', error)
        useToast().error('Failed to fetch month results')
        return { success: false, data: [] }
      } finally {
        this.loading = false
      }
    },
  },
})
