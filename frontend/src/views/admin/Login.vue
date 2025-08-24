<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-100 via-purple-100 to-pink-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
      <div class="bg-white/90 rounded-2xl shadow-2xl px-8 py-10">
        <!-- Header -->
        <div class="text-center mb-8">
          <div class="mx-auto h-20 w-20 bg-gradient-to-br from-blue-600 to-purple-600 rounded-full flex items-center justify-center shadow-lg">
            <i class="fas fa-crown text-4xl text-yellow-300 drop-shadow"></i>
          </div>
          <h2 class="mt-6 text-3xl font-extrabold text-gray-900 tracking-tight">
            Admin Portal
          </h2>
          <p class="mt-2 text-base text-gray-500 font-medium">
            Sign in to your admin account
          </p>
        </div>

        <!-- Login Form -->
        <form @submit.prevent="handleLogin" class="space-y-6">
        <!-- Username Field -->
        <div>
          <label for="username" class="block text-sm font-semibold text-gray-700 mb-1 tracking-wide">
            <i class="fas fa-user mr-2 text-blue-500"></i>
            Username
          </label>
          <input
            id="username"
            v-model="form.username"
            type="text"
            required
            class="w-full rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 bg-gray-50 px-4 py-3 text-base text-gray-800 placeholder-gray-400 shadow-sm transition-all duration-150"
            :class="{ 'border-red-500 ring-red-100': errors.username }"
            placeholder="Enter your username"
            autocomplete="username"
          />
          <p v-if="errors.username" class="form-error">{{ errors.username }}</p>
        </div>

        <!-- Password Field -->
        <div>
          <label for="password" class="block text-sm font-semibold text-gray-700 mb-1 tracking-wide">
            <i class="fas fa-lock mr-2 text-blue-500"></i>
            Password
          </label>
          <div class="relative">
            <input
              id="password"
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              required
              class="w-full rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 bg-gray-50 px-4 py-3 text-base text-gray-800 placeholder-gray-400 shadow-sm pr-10 transition-all duration-150"
              :class="{ 'border-red-500 ring-red-100': errors.password }"
              placeholder="Enter your password"
              autocomplete="current-password"
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute inset-y-0 right-0 pr-3 flex items-center"
              tabindex="-1"
            >
              <i 
                :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"
                class="text-gray-400 hover:text-blue-500 transition-colors"
              ></i>
            </button>
          </div>
          <p v-if="errors.password" class="form-error">{{ errors.password }}</p>
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <input
              id="remember-me"
              v-model="form.rememberMe"
              type="checkbox"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            />
            <label for="remember-me" class="ml-2 block text-sm text-gray-900">
              Remember me
            </label>
          </div>
        </div>

        <!-- Submit Button -->
        <div>
          <button
            type="submit"
            :disabled="authStore.loading"
            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
              <i 
                v-if="authStore.loading"
                class="fas fa-spinner fa-spin text-blue-500 group-hover:text-blue-400"
              ></i>
              <i 
                v-else
                class="fas fa-sign-in-alt text-blue-500 group-hover:text-blue-400"
              ></i>
            </span>
            {{ authStore.loading ? 'Signing in...' : 'Sign in' }}
          </button>
        </div>

        <!-- Error Message -->
        <div v-if="loginError" class="bg-red-50 border border-red-200 rounded-md p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <i class="fas fa-exclamation-circle text-red-400"></i>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">
                Login Failed
              </h3>
              <div class="mt-2 text-sm text-red-700">
                {{ loginError }}
              </div>
            </div>
          </div>
        </div>
      </form>

      <!-- Additional Info -->
      <div class="text-center">
        <p class="text-xs text-gray-500">
          Access is restricted to authorized administrators only.
        </p>
        <router-link 
          to="/" 
          class="mt-4 inline-flex items-center text-sm text-blue-600 hover:text-blue-800"
        >
          <i class="fas fa-arrow-left mr-2"></i>
          Back to Public Site
        </router-link>
      </div>
    </div>
  </div>
</div>
</template>

<script>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

export default {
  name: 'AdminLogin',
  setup() {
    const router = useRouter()
    const authStore = useAuthStore()
    
    const form = reactive({
      username: '',
      password: '',
      rememberMe: false
    })
    
    const errors = reactive({
      username: '',
      password: ''
    })
    
    const showPassword = ref(false)
    const loginError = ref('')

    const validateForm = () => {
      let isValid = true
      
      // Reset errors
      errors.username = ''
      errors.password = ''
      loginError.value = ''
      
      // Validate username
      if (!form.username.trim()) {
        errors.username = 'Username is required'
        isValid = false
      }
      
      // Validate password
      if (!form.password.trim()) {
        errors.password = 'Password is required'
        isValid = false
      } else if (form.password.length < 3) {
        errors.password = 'Password must be at least 3 characters'
        isValid = false
      }
      
      return isValid
    }

    const handleLogin = async () => {
      if (!validateForm()) {
        return
      }

      try {
        const result = await authStore.login({
          username: form.username,
          password: form.password
        })

        if (result.success) {
          // Redirect to admin dashboard
          router.push('/admin/dashboard')
        } else {
          loginError.value = result.message || 'Invalid username or password'
        }
      } catch (error) {
        loginError.value = 'An error occurred during login. Please try again.'
        console.error('Login error:', error)
      }
    }

    return {
      authStore,
      form,
      errors,
      showPassword,
      loginError,
      handleLogin
    }
  }
}
</script>
