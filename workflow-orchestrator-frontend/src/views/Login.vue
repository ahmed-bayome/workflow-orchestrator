<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { Workflow, Mail, Lock, Loader2, AlertCircle } from 'lucide-vue-next';

const authStore = useAuthStore();
const router = useRouter();

const email = ref('admin@test.com');
const password = ref('password');
const isLoading = ref(false);
const error = ref('');

const login = async () => {
  isLoading.value = true;
  error.value = '';
  
  const success = await authStore.login({
    email: email.value,
    password: password.value,
  });

  if (success) {
    router.push('/');
  } else {
    error.value = authStore.error || 'Login failed. Please check your credentials.';
  }
  
  isLoading.value = false;
};
</script>

<template>
  <div class="min-h-screen bg-indigo-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8 border border-indigo-100">
      <div class="flex flex-col items-center mb-8">
        <div class="bg-indigo-700 p-3 rounded-2xl shadow-lg mb-4">
          <Workflow class="w-10 h-10 text-white" />
        </div>
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Welcome back</h1>
        <p class="text-gray-500 mt-2">Sign in to your account</p>
      </div>

      <div v-if="error" class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-3 animate-shake">
        <AlertCircle class="w-5 h-5 flex-shrink-0" />
        <span class="text-sm font-medium">{{ error }}</span>
      </div>

      <form @submit.prevent="login" class="space-y-5">
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Email address</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
              <Mail class="w-5 h-5 text-gray-400" />
            </div>
            <input
              v-model="email"
              type="email"
              required
              class="block w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-all outline-none"
              placeholder="name@example.com"
            />
          </div>
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
              <Lock class="w-5 h-5 text-gray-400" />
            </div>
            <input
              v-model="password"
              type="password"
              required
              class="block w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-all outline-none"
              placeholder="••••••••"
            />
          </div>
        </div>

        <button
          type="submit"
          :disabled="isLoading"
          class="w-full py-3.5 px-4 bg-indigo-700 hover:bg-indigo-800 text-white font-bold rounded-xl shadow-lg shadow-indigo-200 transition-all active:scale-[0.98] disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2"
        >
          <Loader2 v-if="isLoading" class="w-5 h-5 animate-spin" />
          <span v-else>Sign In</span>
        </button>
      </form>

      <div class="mt-8 pt-6 border-t border-gray-100 text-center">
        <p class="text-sm text-gray-500">
          Don't have an account?
          <router-link to="/register" class="text-indigo-700 font-bold hover:text-indigo-800 transition-colors ml-1">
            Register here
          </router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  10%, 30%, 50%, 70%, 90% { transform: translateX(-2px); }
  20%, 40%, 60%, 80% { transform: translateX(2px); }
}
.animate-shake {
  animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
}
</style>
