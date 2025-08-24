<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header + Actions -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
      <div>
        <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
          <i class="fas fa-users text-blue-600"></i>
          <span>Manage Users</span>
        </h1>
        <p class="text-gray-600 mt-1">View, search and manage admin users</p>
      </div>

      <div class="flex items-center gap-3">
        <div class="hidden sm:flex items-center bg-white rounded-lg shadow-sm px-3 py-2">
          <i class="fas fa-search text-gray-400 mr-2"></i>
          <input v-model="filters.search" @input="applyFilters" placeholder="Search users..." class="outline-none text-sm w-48" />
        </div>

        <div class="hidden sm:flex items-center gap-2">
          <select v-model="filters.status" @change="applyFilters" class="form-input text-sm">
            <option value="">All Status</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
          </select>
        </div>

        <button @click="resetFilters" class="btn btn-outline hidden sm:inline-flex">
          Clear
        </button>

        <button @click="showCreateUserModal = true" class="btn btn-primary">
          <i class="fas fa-user-plus mr-2"></i>
          Add User
        </button>
      </div>
    </div>

    <!-- Summary chips -->
    <div class="flex flex-wrap gap-3 mb-6">
      <div class="px-4 py-2 bg-white rounded-lg shadow-sm text-sm">
        <strong class="text-blue-600">{{ totalUsers }}</strong>
        <span class="text-gray-500 ml-2">Total</span>
      </div>
      <div class="px-4 py-2 bg-white rounded-lg shadow-sm text-sm">
        <strong class="text-green-600">{{ activeUsers }}</strong>
        <span class="text-gray-500 ml-2">Active</span>
      </div>
      <div class="px-4 py-2 bg-white rounded-lg shadow-sm text-sm">
        <strong class="text-orange-600">{{ inactiveUsers }}</strong>
        <span class="text-gray-500 ml-2">Inactive</span>
      </div>
  <!-- role counts removed -->
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-12">
      <div class="spinner w-12 h-12"></div>
    </div>

    <!-- Users: cards on small, table on md+ -->
    <div v-else>
      <!-- Cards for small screens -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:hidden">
        <div v-for="user in filteredUsers" :key="user.id" class="bg-white p-4 rounded-lg shadow-sm">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                <i class="fas fa-user"></i>
              </div>
              <div>
                <div class="font-medium text-gray-900">{{ user.username }}</div>
                <div class="text-sm text-gray-500">{{ user.email || '—' }}</div>
              </div>
            </div>
            <div class="text-right">
                <div class="text-sm font-semibold" :class="user.status === 1 ? 'text-green-600' : 'text-gray-500'">
                {{ user.status === 1 ? 'Active' : 'Inactive' }}
              </div>
            </div>
          </div>

            <div class="mt-3 flex items-center justify-between">
            <div class="text-sm text-gray-500">Last: {{ user.last_login ? formatTime(user.last_login) : 'Never' }}</div>
            <div class="flex items-center gap-3">
              <button @click="editUser(user)" class="text-blue-600 hover:text-blue-900" title="Edit"><i class="fas fa-edit"></i></button>
              <button v-if="user.id !== currentUserId" @click="toggleUserStatus(user)" :class="user.status===1 ? 'text-red-600' : 'text-green-600'" :title="user.status===1 ? 'Deactivate' : 'Activate'"><i :class="user.status===1 ? 'fas fa-user-slash' : 'fas fa-user-check'"></i></button>
              <button @click="resetPassword(user)" class="text-orange-600 hover:text-orange-900" title="Reset Password"><i class="fas fa-key"></i></button>
            </div>
          </div>
        </div>
      </div>

      <!-- Table for md+ -->
      <div class="hidden md:block bg-white rounded-lg shadow-md overflow-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50 sticky top-0">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Login</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
              <th class="px-6 py-3"></th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="user in filteredUsers" :key="user.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center mr-3 text-blue-600"><i class="fas fa-user"></i></div>
                  <div>
                    <div class="text-sm font-medium text-gray-900">{{ user.username }}</div>
                    <div class="text-sm text-gray-500">{{ user.email || '—' }}</div>
                  </div>
                </div>
              </td>
              <!-- role column removed -->
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="user.status===1 ? 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800' : 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800'">{{ user.status===1 ? 'Active' : 'Inactive' }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ user.last_login ? formatDate(user.last_login) + ' ' + formatTime(user.last_login) : 'Never' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(user.created_at) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button @click="editUser(user)" class="text-blue-600 hover:text-blue-900 mr-3" title="Edit"><i class="fas fa-edit"></i></button>
                <button v-if="user.id !== currentUserId" @click="toggleUserStatus(user)" :class="user.status===1 ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900'" title="Toggle status"><i :class="user.status===1 ? 'fas fa-user-slash' : 'fas fa-user-check'"></i></button>
                <button @click="resetPassword(user)" class="text-orange-600 hover:text-orange-900 ml-3" title="Reset Password"><i class="fas fa-key"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Empty state -->
      <div v-if="filteredUsers.length === 0" class="text-center py-12">
        <i class="fas fa-users text-4xl text-gray-400 mb-4"></i>
        <h3 class="text-lg font-medium text-gray-600 mb-2">No users found</h3>
        <p class="text-gray-500 mb-4">No users match your current filters.</p>
        <button @click="resetFilters" class="btn btn-primary">
          <i class="fas fa-refresh mr-2"></i>
          Show All Users
        </button>
      </div>
    </div>

    <!-- Create/Edit User Modal -->
    <div v-if="showCreateUserModal || editingUser" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="closeUserModal">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" @click.stop>
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            <i class="fas fa-user-plus mr-2"></i>
            {{ editingUser ? 'Edit User' : 'Create New User' }}
          </h3>
          
          <form @submit.prevent="submitUser">
            <div class="mb-4">
              <label class="form-label">Username *</label>
              <input
                v-model="userForm.username"
                type="text"
                class="form-input"
                :class="{ 'border-red-500': formErrors.username }"
                required
              />
              <p v-if="formErrors.username" class="form-error">{{ formErrors.username }}</p>
            </div>
            
            <!-- Email field commented out - not in current database schema
            <div class="mb-4">
              <label class="form-label">Email</label>
              <input
                v-model="userForm.email"
                type="email"
                class="form-input"
                :class="{ 'border-red-500': formErrors.email }"
              />
              <p v-if="formErrors.email" class="form-error">{{ formErrors.email }}</p>
            </div>
            -->
            
            <div v-if="!editingUser" class="mb-4">
              <label class="form-label">Password *</label>
              <input
                v-model="userForm.password"
                type="password"
                class="form-input"
                :class="{ 'border-red-500': formErrors.password }"
                required
              />
              <p v-if="formErrors.password" class="form-error">{{ formErrors.password }}</p>
            </div>
            
            <!-- role field removed from frontend form -->
            
            <div class="mb-4">
              <label class="form-label flex items-center">
                <input
                  v-model="userForm.status"
                  type="checkbox"
                  class="form-checkbox mr-2"
                />
                Active User
              </label>
            </div>
            
            <div class="flex justify-end space-x-3">
              <button type="button" @click="closeUserModal" class="btn btn-outline">
                Cancel
              </button>
              <button type="submit" class="btn btn-primary" :disabled="loading">
                <i class="fas fa-save mr-2"></i>
                {{ editingUser ? 'Update User' : 'Create User' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Reset Password Modal -->
    <div v-if="resetPasswordUser" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="closeResetPasswordModal">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" @click.stop>
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            <i class="fas fa-key mr-2"></i>
            Reset Password for {{ resetPasswordUser.username }}
          </h3>
          
          <form @submit.prevent="submitPasswordReset">
            <div class="mb-4">
              <label class="form-label">New Password *</label>
              <input
                v-model="passwordForm.new_password"
                type="password"
                class="form-input"
                :class="{ 'border-red-500': formErrors.new_password }"
                required
              />
              <p v-if="formErrors.new_password" class="form-error">{{ formErrors.new_password }}</p>
            </div>
            
            <div class="mb-4">
              <label class="form-label">Confirm Password *</label>
              <input
                v-model="passwordForm.confirm_password"
                type="password"
                class="form-input"
                :class="{ 'border-red-500': formErrors.confirm_password }"
                required
              />
              <p v-if="formErrors.confirm_password" class="form-error">{{ formErrors.confirm_password }}</p>
            </div>
            
            <div class="flex justify-end space-x-3">
              <button type="button" @click="closeResetPasswordModal" class="btn btn-outline">
                Cancel
              </button>
              <button type="submit" class="btn btn-primary" :disabled="loading">
                <i class="fas fa-save mr-2"></i>
                Reset Password
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
import { useAuthStore } from '@/stores/auth'
import { format } from 'date-fns'
import { useToast } from 'vue-toastification'

export default {
  name: 'AdminUsers',
  setup() {
    const authStore = useAuthStore()
    const toast = useToast()
    
    const users = ref([])
    const loading = ref(false)
    const showCreateUserModal = ref(false)
    const editingUser = ref(null)
    const resetPasswordUser = ref(null)
    
    const filters = reactive({
      search: '',
      status: ''
    })
    
    const userForm = reactive({
      username: '',
      email: '',
      password: '',
      status: true
    })
    
    const passwordForm = reactive({
      new_password: '',
      confirm_password: ''
    })
    
    const formErrors = reactive({
      username: '',
      email: '',
      password: '',
      new_password: '',
      confirm_password: ''
    })

    const currentUserId = computed(() => authStore.user?.id)

    const totalUsers = computed(() => users.value.length)
    const activeUsers = computed(() => users.value.filter(u => u.status === 1).length)
    const inactiveUsers = computed(() => users.value.filter(u => u.status === 0).length)
  // role-related counts removed

    const filteredUsers = computed(() => {
      let filtered = users.value

      if (filters.search) {
        const search = filters.search.toLowerCase()
        filtered = filtered.filter(user => 
          user.username.toLowerCase().includes(search) ||
          (user.email && user.email.toLowerCase().includes(search))
        )
      }

  // role filter removed

      if (filters.status !== '') {
        filtered = filtered.filter(user => user.status === parseInt(filters.status))
      }

      return filtered
    })

    const formatDate = (datetime) => {
      return format(new Date(datetime), 'MMM dd, yyyy')
    }

    const formatTime = (datetime) => {
      return format(new Date(datetime), 'HH:mm')
    }

    const applyFilters = () => {
      // Filters are applied reactively through computed property
    }

    const resetFilters = () => {
      filters.search = ''
  // role filter removed
      filters.status = ''
    }

    const resetForm = () => {
      userForm.username = ''
      userForm.email = ''
      userForm.password = ''
      // role removed from frontend form
      userForm.status = true
      
      // Clear errors
      Object.keys(formErrors).forEach(key => {
        formErrors[key] = ''
      })
    }

    const editUser = (user) => {
      editingUser.value = user
      userForm.username = user.username
      userForm.email = user.email || ''
  // role removed
      userForm.status = user.status === 1
    }

    const closeUserModal = () => {
      showCreateUserModal.value = false
      editingUser.value = null
      resetForm()
    }

    const submitUser = async () => {
      // Clear previous errors
      Object.keys(formErrors).forEach(key => {
        formErrors[key] = ''
      })

      loading.value = true
      
      try {
        const userData = {
          username: userForm.username,
          // email: userForm.email || null, // Comment out - not in database schema
          status: userForm.status ? 1 : 0  // Use 1/0 instead of true/false
        }

        if (!editingUser.value) {
          userData.password = userForm.password
        }

        console.log('Sending user data:', userData) // Debug

        const response = await fetch(`${import.meta.env.VITE_API_URL}/users${editingUser.value ? '/' + editingUser.value.id : ''}`, {
          method: editingUser.value ? 'PUT' : 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${authStore.token}`
          },
          body: JSON.stringify(userData)
        })

        const result = await response.json()
        console.log('User creation result:', result) // Debug

        if (response.ok) {
          toast.success(editingUser.value ? 'User updated successfully!' : 'User created successfully!')
          closeUserModal()
          await fetchUsers()
        } else {
          if (result.errors) {
            Object.keys(result.errors).forEach(key => {
              if (formErrors.hasOwnProperty(key)) {
                formErrors[key] = result.errors[key][0]
              }
            })
          } else {
            toast.error(result.message || 'An error occurred')
          }
        }
      } catch (error) {
        console.error('Error submitting user:', error)
        toast.error('Network error occurred')
      } finally {
        loading.value = false
      }
    }

    const toggleUserStatus = async (user) => {
      const action = user.status === 1 ? 'deactivate' : 'activate'
      
      if (confirm(`Are you sure you want to ${action} user "${user.username}"?`)) {
        loading.value = true
        
        try {
          console.log('Toggling user status:', user.id, 'from', user.status) // Debug
          
          const response = await fetch(`${import.meta.env.VITE_API_URL}/users/${user.id}/toggle-status`, {
            method: 'POST', // Backend expects POST, not PUT
            headers: {
              'Content-Type': 'application/json',
              'Authorization': `Bearer ${authStore.token}`
            }
          })

          const result = await response.json()
          console.log('Toggle status result:', result) // Debug

          if (response.ok && result.success) {
            toast.success(`User ${action}d successfully!`)
            await fetchUsers()
          } else {
            toast.error(result.message || 'An error occurred')
          }
        } catch (error) {
          console.error('Error toggling user status:', error)
          toast.error('Network error occurred')
        } finally {
          loading.value = false
        }
      }
    }

    const resetPassword = (user) => {
      resetPasswordUser.value = user
      passwordForm.new_password = ''
      passwordForm.confirm_password = ''
      formErrors.new_password = ''
      formErrors.confirm_password = ''
    }

    const closeResetPasswordModal = () => {
      resetPasswordUser.value = null
      passwordForm.new_password = ''
      passwordForm.confirm_password = ''
      formErrors.new_password = ''
      formErrors.confirm_password = ''
    }

    const submitPasswordReset = async () => {
      formErrors.new_password = ''
      formErrors.confirm_password = ''

      if (passwordForm.new_password !== passwordForm.confirm_password) {
        formErrors.confirm_password = 'Passwords do not match'
        return
      }

      if (passwordForm.new_password.length < 6) {
        formErrors.new_password = 'Password must be at least 6 characters'
        return
      }

      loading.value = true
      
      try {
        const response = await fetch(`${import.meta.env.VITE_API_URL}/users/${resetPasswordUser.value.id}/reset-password`, {
          method: 'POST', // Changed from PUT to POST to match backend
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${authStore.token}`
          },
          body: JSON.stringify({
            new_password: passwordForm.new_password
          })
        })

        const result = await response.json()

        if (response.ok) {
          toast.success('Password reset successfully!')
          closeResetPasswordModal()
        } else {
          if (result.errors && result.errors.new_password) {
            formErrors.new_password = result.errors.new_password[0]
          } else {
            toast.error(result.message || 'An error occurred')
          }
        }
      } catch (error) {
        console.error('Error resetting password:', error)
        toast.error('Network error occurred')
      } finally {
        loading.value = false
      }
    }

    const fetchUsers = async () => {
      loading.value = true
      
      try {
        const response = await fetch(`${import.meta.env.VITE_API_URL}/users`, {
          headers: {
            'Authorization': `Bearer ${authStore.token}`
          }
        })

        const result = await response.json()

        if (response.ok) {
          users.value = result.data || []
        } else {
          toast.error(result.message || 'Failed to fetch users')
        }
      } catch (error) {
        console.error('Error fetching users:', error)
        toast.error('Network error occurred')
      } finally {
        loading.value = false
      }
    }

    onMounted(() => {
      fetchUsers()
    })

    return {
      users,
      loading,
      showCreateUserModal,
      editingUser,
      resetPasswordUser,
      filters,
      userForm,
      passwordForm,
      formErrors,
      currentUserId,
  totalUsers,
  activeUsers,
  inactiveUsers,
      filteredUsers,
      formatDate,
      formatTime,
      applyFilters,
      resetFilters,
      editUser,
      closeUserModal,
      submitUser,
      toggleUserStatus,
      resetPassword,
      closeResetPasswordModal,
      submitPasswordReset
    }
  }
}
</script>
