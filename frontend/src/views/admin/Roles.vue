<script setup lang="ts">
import { ref, onMounted } from 'vue';
import api from '../../services/api';
import {
  PlusCircle,
  Trash2,
  User,
  ShieldCheck,
  ShieldAlert,
  Loader2,
  AlertCircle,
  ArrowRight
} from 'lucide-vue-next';
import type { Role, ApiError } from '@/types';
import type { AxiosError } from 'axios';

const roles = ref<Role[]>([]);
const isLoading = ref(true);
const newRoleName = ref('');
const isSubmitting = ref(false);
const error = ref('');

const fetchRoles = async () => {
  try {
    const response = await api.get<Role[]>('/admin/roles');
    roles.value = response.data;
  } catch (err) {
    console.error('Error fetching roles:', err);
  } finally {
    isLoading.value = false;
  }
};

const createRole = async () => {
  if (!newRoleName.value) return;
  isSubmitting.value = true;
  error.value = '';

  try {
    const response = await api.post<Role>('/admin/roles', { name: newRoleName.value });
    roles.value.push(response.data);
    newRoleName.value = '';
  } catch (err) {
    const axiosError = err as AxiosError<ApiError>;
    error.value = axiosError.response?.data?.message || 'Failed to create role.';
  } finally {
    isSubmitting.value = false;
  }
};

const deleteRole = async (id: number) => {
  if (!confirm('Are you sure? This action cannot be undone.')) return;
  try {
    await api.delete(`/admin/roles/${id}`);
    roles.value = roles.value.filter((r: Role) => r.id !== id);
  } catch (err) {
    const axiosError = err as AxiosError<ApiError>;
    alert(axiosError.response?.data?.message || 'Failed to delete role.');
  }
};

onMounted(fetchRoles);
</script>

<template>
  <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500 pb-20">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-black text-gray-900 tracking-tight leading-none mb-2">Role Management</h1>
        <p class="text-gray-500 font-medium italic">Define and structure your organization's hierarchy.</p>
      </div>
      <div class="flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-700 rounded-xl font-bold border border-indigo-100 shadow-sm">
        <ShieldCheck class="w-5 h-5" />
        <span class="text-lg leading-none">{{ roles.length }} Active Roles</span>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
      <!-- Left: Create Role Form -->
      <div class="lg:col-span-4 sticky top-8">
        <section class="bg-white rounded-3xl shadow-xl shadow-gray-100/50 border border-gray-100 p-8 space-y-6">
          <div class="flex items-center gap-3 mb-2">
            <div class="bg-indigo-700 p-2.5 rounded-xl text-white shadow-lg shadow-indigo-100">
              <PlusCircle class="w-5 h-5" />
            </div>
            <h2 class="text-xl font-black text-gray-900 uppercase tracking-widest text-sm">Create New Role</h2>
          </div>

          <div v-if="error" class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl flex items-center gap-4 text-xs font-bold uppercase tracking-tight">
            <AlertCircle class="w-5 h-5 flex-shrink-0" />
            {{ error }}
          </div>

          <form @submit.prevent="createRole" class="space-y-4">
            <div>
              <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Role Identifier</label>
              <input
                v-model="newRoleName"
                type="text"
                required
                class="block w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl text-gray-900 font-bold focus:ring-4 focus:ring-indigo-50 focus:border-indigo-600 transition-all outline-none shadow-inner"
                placeholder="e.g. Legal Compliance"
              />
            </div>
            <button
              type="submit"
              :disabled="isSubmitting"
              class="w-full inline-flex items-center justify-center gap-2 bg-indigo-700 hover:bg-indigo-800 text-white px-6 py-4 rounded-2xl font-black shadow-lg shadow-indigo-100 transition-all active:scale-[0.98] disabled:opacity-70 disabled:cursor-not-allowed group"
            >
              <Loader2 v-if="isSubmitting" class="w-5 h-5 animate-spin" />
              <ArrowRight v-else class="w-5 h-5 group-hover:translate-x-1 transition-transform" />
              {{ isSubmitting ? 'CREATING...' : 'ADD NEW ROLE' }}
            </button>
          </form>

          <div class="mt-8 pt-6 border-t border-gray-100 bg-gray-50/50 p-6 rounded-2xl">
            <h4 class="text-sm font-black text-gray-900 mb-3 flex items-center gap-2">
              <ShieldAlert class="w-4 h-4 text-indigo-400" />
              ROLE GUIDELINES
            </h4>
            <p class="text-xs text-gray-500 font-medium italic leading-relaxed">
              Roles are used across all workflows to determine who has approval authority. Changes here will propagate globally.
            </p>
          </div>
        </section>
      </div>

      <!-- Right: Roles List -->
      <div class="lg:col-span-8 space-y-6">
        <div v-if="isLoading" class="p-20 flex flex-col items-center justify-center gap-6">
          <div class="w-16 h-16 border-4 border-indigo-100 border-t-indigo-600 rounded-full animate-spin"></div>
          <p class="text-gray-400 font-black uppercase tracking-widest text-xs">Fetching authority levels...</p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div
            v-for="role in roles"
            :key="role.id"
            class="bg-white rounded-3xl p-6 border border-gray-100 shadow-xl shadow-gray-100/30 flex items-center gap-6 group hover:border-indigo-200 transition-all"
          >
            <div class="bg-indigo-50 w-16 h-16 rounded-2xl flex items-center justify-center text-indigo-600 font-black text-2xl shadow-inner group-hover:rotate-6 transition-transform">
              {{ role.name.charAt(0) }}
            </div>

            <div class="flex-1 overflow-hidden">
              <h3 class="text-lg font-black text-gray-900 leading-none truncate">{{ role.name }}</h3>
              <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mt-2 flex items-center gap-1.5 leading-none">
                <User class="w-3.5 h-3.5" />
                {{ role.users?.length || 0 }} Members Assigned
              </p>
            </div>

            <button
              @click="deleteRole(role.id)"
              class="p-3 text-red-200 hover:text-red-500 hover:bg-red-50 rounded-2xl transition-all opacity-0 group-hover:opacity-100"
            >
              <Trash2 class="w-5 h-5" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
