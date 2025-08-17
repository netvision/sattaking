import axios from 'axios'

// API configuration
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'https://api.sattaking.app'

// Create axios instance
const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
})

// Request interceptor to add auth token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor to handle errors
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Clear token and redirect to login
      localStorage.removeItem('auth_token')
      window.location.href = '/admin/login'
    }
    return Promise.reject(error)
  }
)

// Auth API
export const authAPI = {
  login: (credentials) => api.post('/auth/login', credentials),
  logout: () => api.post('/auth/logout'),
  me: () => api.get('/auth/me'),
}

// Results API
export const resultsAPI = {
  getToday: () => api.get('/results/today'),
  getArchive: (date) => api.get('/results/archive', { params: { date } }),
  getLatest: (limit = 10) => api.get('/results/latest', { params: { limit } }),
  getBySlot: (slotId, params = {}) => api.get('/results', { params: { slot_id: slotId, ...params } }),
  create: (data) => api.post('/results', data),
  update: (id, data) => api.put(`/results/${id}`, data),
  lock: (id) => api.post(`/results/${id}/lock`),
}

// Slots API
export const slotsAPI = {
  getAll: (params = {}) => api.get('/slots', { params }),
  getToday: () => api.get('/slots/today'),
  getById: (id) => api.get(`/slots/${id}`),
  create: (data) => api.post('/slots', data),
  update: (id, data) => api.put(`/slots/${id}`, data),
  delete: (id) => api.delete(`/slots/${id}`),
}

// Info API
export const infoAPI = {
  getPlatformInfo: () => api.get('/info'),
  getStatus: () => api.get('/info/status'),
}

export default api
