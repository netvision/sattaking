import { defineStore } from 'pinia'
import { authAPI } from '@/services/api'
import { useToast } from 'vue-toastification'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('auth_token'),
    isAuthenticated: false,
    loading: false,
  }),

  getters: {
    isLoggedIn: (state) => !!state.token && state.isAuthenticated,
  },

  actions: {
    async login(credentials) {
      const toast = useToast()
      this.loading = true
      
      try {
        const response = await authAPI.login(credentials)
        
        if (response.data.success) {
          this.token = response.data.data.access_token
          this.user = response.data.data.user
          this.isAuthenticated = true
          
          localStorage.setItem('auth_token', this.token)
          toast.success('Login successful!')
          
          return { success: true }
        } else {
          toast.error(response.data.message || 'Login failed')
          return { success: false, message: response.data.message }
        }
      } catch (error) {
        const message = error.response?.data?.message || 'Login failed'
        toast.error(message)
        return { success: false, message }
      } finally {
        this.loading = false
      }
    },

    async logout() {
      const toast = useToast()
      this.loading = true
      
      try {
        await authAPI.logout()
        toast.success('Logged out successfully')
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        this.clearAuth()
        this.loading = false
      }
    },

    async checkAuth() {
      if (!this.token) return false
      
      try {
        const response = await authAPI.me()
        if (response.data.success) {
          this.user = response.data.data
          this.isAuthenticated = true
          return true
        }
      } catch (error) {
        console.error('Auth check failed:', error)
        this.clearAuth()
      }
      
      return false
    },

    clearAuth() {
      this.user = null
      this.token = null
      this.isAuthenticated = false
      localStorage.removeItem('auth_token')
    },

    async initAuth() {
      if (this.token) {
        await this.checkAuth()
      }
    },
  },
})
