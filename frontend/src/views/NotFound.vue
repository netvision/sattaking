<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-purple-50 flex items-center justify-center px-4">
    <div class="max-w-md w-full text-center">
      <!-- 404 Animation -->
      <div class="mb-8">
        <div class="text-8xl font-bold text-gray-300 mb-4 animate-bounce">404</div>
        <div class="w-32 h-32 mx-auto mb-6 relative">
          <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-purple-500 rounded-full animate-pulse opacity-20"></div>
          <div class="absolute inset-2 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
            <i class="fas fa-search text-white text-4xl"></i>
          </div>
        </div>
      </div>

      <!-- Error Message -->
      <h1 class="text-3xl font-bold text-gray-800 mb-4">Page Not Found</h1>
      <p class="text-gray-600 mb-8 leading-relaxed">
        Oops! The page you're looking for doesn't exist. 
        It might have been moved, deleted, or you entered the wrong URL.
      </p>

      <!-- Action Buttons -->
      <div class="space-y-4">
        <router-link
          to="/"
          class="inline-flex items-center justify-center w-full px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white font-medium rounded-lg hover:from-blue-600 hover:to-purple-700 transition-all duration-200 transform hover:scale-105 shadow-lg"
        >
          <i class="fas fa-home mr-2"></i>
          Go to Homepage
        </router-link>

        <button
          @click="goBack"
          class="inline-flex items-center justify-center w-full px-6 py-3 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-all duration-200"
        >
          <i class="fas fa-arrow-left mr-2"></i>
          Go Back
        </button>
      </div>

      <!-- Helpful Links -->
      <div class="mt-12 pt-8 border-t border-gray-200">
        <p class="text-sm text-gray-500 mb-4">You might be looking for:</p>
        <div class="grid grid-cols-2 gap-4">
          <router-link
            to="/"
            class="text-blue-600 hover:text-blue-800 text-sm font-medium transition-colors duration-200"
          >
            <i class="fas fa-trophy mr-1"></i>
            Latest Results
          </router-link>
          
          <router-link
            to="/results"
            class="text-blue-600 hover:text-blue-800 text-sm font-medium transition-colors duration-200"
          >
            <i class="fas fa-chart-line mr-1"></i>
            All Results
          </router-link>
          
          <router-link
            to="/archive"
            class="text-blue-600 hover:text-blue-800 text-sm font-medium transition-colors duration-200"
          >
            <i class="fas fa-archive mr-1"></i>
            Archive
          </router-link>
          
          <router-link
            to="/admin"
            class="text-blue-600 hover:text-blue-800 text-sm font-medium transition-colors duration-200"
          >
            <i class="fas fa-cog mr-1"></i>
            Admin Panel
          </router-link>
        </div>
      </div>

      <!-- Contact Info -->
      <div class="mt-8 text-xs text-gray-400">
        <p>If you believe this is an error, please contact support.</p>
      </div>
    </div>
  </div>
</template>

<script>
import { useRouter } from 'vue-router'

export default {
  name: 'NotFound',
  setup() {
    const router = useRouter()

    const goBack = () => {
      if (window.history.length > 1) {
        router.go(-1)
      } else {
        router.push('/')
      }
    }

    return {
      goBack
    }
  }
}
</script>

<style scoped>
@keyframes bounce {
  0%, 20%, 53%, 80%, 100% {
    transform: translate3d(0,0,0);
  }
  40%, 43% {
    transform: translate3d(0, -10px, 0);
  }
  70% {
    transform: translate3d(0, -5px, 0);
  }
  90% {
    transform: translate3d(0, -2px, 0);
  }
}

.animate-bounce {
  animation: bounce 2s infinite;
}
</style>
