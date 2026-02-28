<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { Workflow, Mail, Lock, User, Loader2, AlertCircle } from 'lucide-vue-next';

const authStore = useAuthStore();
const router = useRouter();

const name = ref('');
const email = ref('');
const password = ref('');
const password_confirmation = ref('');
const isLoading = ref(false);
const error = ref('');

const register = async () => {
  isLoading.value = true;
  error.value = '';
  
  if (password.value !== password_confirmation.value) {
    error.value = 'Passwords do not match.';
    isLoading.value = false;
    return;
  }

  const success = await authStore.register({
    name: name.value,
    email: email.value,
    password: password.value,
    password_confirmation: password_confirmation.value,
  });

  if (success) {
    router.push('/');
  } else {
    error.value = typeof authStore.error === 'string' ? authStore.error : 'Registration failed.';
  }
  
  isLoading.value = false;
};
</script>

<template>
  <div class="min-h-screen bg-indigo-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8 border border-indigo-100 my-8">
      <div class="flex flex-col items-center mb-8">
        <div class="bg-indigo-700 p-3 rounded-2xl shadow-lg mb-4">
          <Workflow class="w-10 h-10 text-white" />
        </div>
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight text-center leading-none">Create your account</h1>
        <p class="text-gray-500 mt-2">Join the Orchestrator community</p>
      </div>

      <div v-if="error" class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-3">
        <AlertCircle class="w-5 h-5 flex-shrink-0" />
        <span class="text-sm font-medium">{{ error }}</span>
      </div>

      <form @submit.prevent="register" class="space-y-4">
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Full Name</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
              <User class="w-5 h-5 text-gray-400" />
            </div>
            <input
              v-model="name"
              type="text"
              required
              class="block w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-all outline-none"
              placeholder="John Doe"
            />
          </div>
        </div>

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

        <div class="grid grid-cols-1 gap-4">
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

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Confirm Password</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <Lock class="w-5 h-5 text-gray-400" />
              </div>
              <input
                v-model="password_confirmation"
                type="password"
                required
                class="block w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-all outline-none"
                placeholder="••••••••"
              />
            </div>
          </div>
        </div>

        <button
          type="submit"
          :disabled="isLoading"
          class="w-full mt-4 py-3.5 px-4 bg-indigo-700 hover:bg-indigo-800 text-white font-bold rounded-xl shadow-lg shadow-indigo-200 transition-all active:scale-[0.98] disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2"
        >
          <Loader2 v-if="isLoading" class="w-5 h-5 animate-spin" />
          <span v-else>Register Now</span>
        </button>
      </form>

      <div class="mt-8 pt-6 border-t border-gray-100 text-center">
        <p class="text-sm text-gray-500">
          Already have an account?
          <router-link to="/login" class="text-indigo-700 font-bold hover:text-indigo-800 transition-colors ml-1">
            Sign in
          </router-link>
        </p>
      </div>
    </div>
  </div>
</template>
