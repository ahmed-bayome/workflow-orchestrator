<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import api from '../../services/api';
import {
  Users,
  PlusCircle,
  Search,
  Settings,
  Trash2,
  Mail,
  Shield,
  Loader2,
  AlertCircle,
  X,
  Save,
  Key,
  Eye,
  EyeOff,
  RotateCcw,
  UserCheck,
  UserMinus
} from 'lucide-vue-next';
import type { User, Role, UserFormData, ApiError } from '@/types';
import type { AxiosError } from 'axios';

const users = ref<(User & { deleted_at?: string | null })[]>([]);
const roles = ref<Role[]>([]);
const isLoading = ref(true);
const isSubmitting = ref(false);
const searchQuery = ref('');

// Modal State
const showModal = ref(false);
const isEditing = ref(false);
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);
const userForm = ref<UserFormData & { id?: number | null }>({
  id: null,
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  roles: []
});
const formError = ref('');

const fetchUsers = async () => {
  isLoading.value = true;
  try {
    const [usersRes, rolesRes] = await Promise.all([
      api.get<User[]>('/admin/users'),
      api.get<Role[]>('/admin/roles')
    ]);
    users.value = usersRes.data;
    roles.value = rolesRes.data;
  } catch (err) {
    console.error('Error fetching users:', err);
  } finally {
    isLoading.value = false;
  }
};

const filteredUsers = computed(() => {
  return users.value.filter((user: User) =>
    user.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    user.email.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

const openCreateModal = () => {
  isEditing.value = false;
  showPassword.value = false;
  showPasswordConfirmation.value = false;
  userForm.value = {
    id: null,
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    roles: []
  };
  formError.value = '';
  showModal.value = true;
};

const openEditModal = (user: User) => {
  isEditing.value = true;
  showPassword.value = false;
  showPasswordConfirmation.value = false;
  userForm.value = {
    id: user.id,
    name: user.name,
    email: user.email,
    password: '',
    password_confirmation: '',
    roles: user.roles?.map((r: Role) => r.id) ?? []
  };
  formError.value = '';
  showModal.value = true;
};

const saveUser = async () => {
  if (userForm.value.password !== userForm.value.password_confirmation) {
    formError.value = 'Passwords do not match.';
    return;
  }

  isSubmitting.value = true;
  formError.value = '';

  try {
    if (isEditing.value) {
      const response = await api.put<User>(`/admin/users/${userForm.value.id}`, userForm.value);
      const index = users.value.findIndex((u: User) => u.id === userForm.value.id);
      if (index !== -1) users.value[index] = response.data;
    } else {
      const response = await api.post<User>('/admin/users', userForm.value);
      users.value.push(response.data);
    }
    showModal.value = false;
  } catch (err) {
    const axiosError = err as AxiosError<ApiError>;
    if (axiosError.response?.data?.errors) {
      formError.value = Object.values(axiosError.response.data.errors).flat().join(' ');
    } else {
      formError.value = axiosError.response?.data?.message || 'Failed to save user.';
    }
  } finally {
    isSubmitting.value = false;
  }
};

const deleteUser = async (id: number) => {
  if (!confirm('Are you sure you want to delete this user? This will soft-delete their account.')) return;

  try {
    await api.delete(`/admin/users/${id}`);
    await fetchUsers();
  } catch (err) {
    const axiosError = err as AxiosError<ApiError>;
    alert(axiosError.response?.data?.message || 'Failed to delete user.');
  }
};

const restoreUser = async (id: number) => {
  try {
    await api.post(`/admin/users/${id}/restore`);
    await fetchUsers();
  } catch (err) {
    const axiosError = err as AxiosError<ApiError>;
    alert(axiosError.response?.data?.message || 'Failed to restore user.');
  }
};

const toggleStatus = async (user: User & { deleted_at?: string | null }) => {
  try {
    const action = user.is_active ? 'deactivate' : 'activate';
    await api.post(`/admin/users/${user.id}/${action}`);
    user.is_active = !user.is_active;
  } catch (err) {
    console.error('Error toggling user status:', err);
  }
};

onMounted(fetchUsers);
</script>

<template>
  <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500 pb-20">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-black text-gray-900 tracking-tight leading-none mb-2">User Management</h1>
        <p class="text-gray-500 font-medium italic">Empower your organization's members.</p>
      </div>
      <button
        @click="openCreateModal"
        class="inline-flex items-center gap-2 bg-indigo-700 hover:bg-indigo-800 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-indigo-100 transition-all active:scale-[0.98]"
      >
        <PlusCircle class="w-5 h-5" />
        New Member
      </button>
    </div>

    <!-- Toolbar -->
    <div class="flex flex-col md:flex-row gap-4 bg-white p-4 rounded-3xl shadow-xl shadow-gray-100/50 border border-gray-100">
      <div class="flex-1 relative">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
          <Search class="w-5 h-5" />
        </div>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search by name or email..."
          class="block w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-100 rounded-2xl text-gray-900 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-all outline-none text-sm font-medium"
        />
      </div>
    </div>

    <!-- Users Grid -->
    <div v-if="isLoading" class="p-20 flex flex-col items-center justify-center gap-6">
      <div class="w-16 h-16 border-4 border-indigo-100 border-t-indigo-600 rounded-full animate-spin"></div>
      <p class="text-gray-400 font-black uppercase tracking-widest text-xs">Accessing personnel records...</p>
    </div>

    <div v-else-if="filteredUsers.length === 0" class="p-20 text-center bg-white rounded-3xl border-2 border-dashed border-gray-100">
      <Users class="w-12 h-12 mx-auto text-gray-200 mb-4" />
      <p class="text-gray-500 font-medium">No members found matching your search.</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      <div
        v-for="user in filteredUsers"
        :key="user.id"
        class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border-2 overflow-hidden group relative transition-all"
        :class="[
          user.deleted_at ? 'border-slate-200 opacity-60 grayscale-[0.5] bg-slate-50' :
          (user.is_active ? 'border-green-500/30' : 'border-red-400/30')
        ]"
      >
        <!-- Status Indicator Dot -->
        <div v-if="!user.deleted_at" :class="['absolute top-6 right-6 w-3 h-3 rounded-full border-2 border-white ring-2', user.is_active ? 'bg-green-500 ring-green-100' : 'bg-red-400 ring-red-100']"></div>
        <div v-else class="absolute top-6 right-6 text-[10px] font-black text-red-500 uppercase tracking-widest bg-red-50 px-2 py-1 rounded-lg border border-red-100 shadow-sm">Deleted</div>

        <div class="p-8">
          <div class="flex flex-col items-center text-center space-y-4">
            <div class="relative">
              <div class="w-20 h-20 rounded-3xl bg-indigo-50 flex items-center justify-center text-indigo-600 font-black text-3xl shadow-inner group-hover:scale-110 transition-transform duration-500">
                {{ user.name.charAt(0) }}
              </div>
              <div class="absolute -bottom-1 -right-1 bg-white p-1 rounded-lg shadow-sm">
                <Shield v-if="user.roles?.some(r => r.name === 'Admin')" class="w-4 h-4 text-indigo-600" />
                <Users v-else class="w-4 h-4 text-gray-400" />
              </div>
            </div>

            <div>
              <h3 class="text-xl font-black text-gray-900 leading-tight truncate px-4">{{ user.name }}</h3>
              <p class="text-sm text-gray-500 font-medium mt-1.5 flex items-center justify-center gap-1.5">
                <Mail class="w-3.5 h-3.5" />
                {{ user.email }}
              </p>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-2 pt-2">
              <span
                v-for="role in user.roles"
                :key="role.id"
                class="px-3 py-1 bg-gray-50 text-gray-600 text-[10px] font-black uppercase tracking-widest rounded-lg border border-gray-100"
              >
                {{ role.name }}
              </span>
              <span v-if="!user.roles?.length" class="text-[10px] text-gray-400 italic">No roles assigned</span>
            </div>
          </div>
        </div>

        <div class="px-8 py-5 bg-gray-50/50 border-t border-gray-50 flex items-center justify-between">
          <template v-if="!user.deleted_at">
            <button
              @click="toggleStatus(user)"
              :class="[
                'flex items-center gap-2 px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all active:scale-95 shadow-sm border',
                user.is_active
                  ? 'bg-red-50 text-red-600 border-red-100 hover:bg-red-600 hover:text-white hover:border-red-600 hover:shadow-lg hover:shadow-red-100'
                  : 'bg-green-50 text-green-600 border-green-100 hover:bg-green-600 hover:text-white hover:border-green-600 hover:shadow-lg hover:shadow-green-100'
              ]"
            >
              <component :is="user.is_active ? UserMinus : UserCheck" class="w-3.5 h-3.5" />
              {{ user.is_active ? 'Deactivate' : 'Activate' }}
            </button>

            <div class="flex items-center gap-2">
              <button
                @click="openEditModal(user)"
                class="p-2 text-gray-400 hover:text-indigo-600 rounded-lg hover:bg-white transition-all"
              >
                <Settings class="w-5 h-5" />
              </button>
              <button
                @click="deleteUser(user.id)"
                class="p-2 text-gray-400 hover:text-red-600 rounded-lg hover:bg-white transition-all"
              >
                <Trash2 class="w-5 h-5" />
              </button>
            </div>
          </template>
          <template v-else>
            <button
              @click="restoreUser(user.id)"
              class="w-full flex items-center justify-center gap-2 text-indigo-600 hover:text-white hover:bg-indigo-600 px-4 py-2 rounded-xl border border-indigo-100 bg-white transition-all font-black text-[10px] uppercase tracking-widest shadow-sm"
            >
              <RotateCcw class="w-4 h-4" />
              Restore Member
            </button>
          </template>
        </div>
      </div>
    </div>

    <!-- User Modal -->
    <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 overflow-y-auto">
      <div class="fixed inset-0 bg-indigo-900/60 backdrop-blur-sm animate-in fade-in duration-300" @click="showModal = false"></div>

      <div class="bg-white w-full max-w-lg rounded-[2rem] md:rounded-[2.5rem] shadow-2xl relative z-10 my-auto overflow-hidden animate-in zoom-in-95 duration-300 border border-white">
        <!-- Scrollable Content Area -->
        <div class="max-h-[calc(100vh-5rem)] overflow-y-auto scrollbar-hide p-6 sm:p-8 md:p-10">
          <div class="flex items-center justify-between mb-6 md:mb-8">
            <h2 class="text-xl md:text-2xl font-black text-gray-900 tracking-tight leading-none">
              {{ isEditing ? 'Edit Member' : 'New Member' }}
            </h2>
            <button @click="showModal = false" class="p-2 hover:bg-gray-100 rounded-full transition-colors text-gray-400">
              <X class="w-5 h-5 md:w-6 md:h-6" />
            </button>
          </div>

          <div v-if="formError" class="mb-6 p-4 bg-red-50 border border-red-100 text-red-600 rounded-2xl flex items-center gap-3 text-sm font-bold">
            <AlertCircle class="w-5 h-5 flex-shrink-0" />
            <span class="flex-1">{{ formError }}</span>
          </div>

          <form @submit.prevent="saveUser" class="space-y-5 md:space-y-6">
            <div class="space-y-4">
              <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Full Name</label>
                <input
                  v-model="userForm.name"
                  type="text"
                  required
                  class="block w-full px-5 py-3.5 md:py-4 bg-gray-50 border border-gray-100 rounded-2xl text-gray-900 font-bold focus:ring-4 focus:ring-indigo-50 focus:border-indigo-600 transition-all outline-none shadow-inner text-sm md:text-base"
                  placeholder="John Doe"
                />
              </div>

              <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Email Address</label>
                <input
                  v-model="userForm.email"
                  type="email"
                  required
                  class="block w-full px-5 py-3.5 md:py-4 bg-gray-50 border border-gray-100 rounded-2xl text-gray-900 font-bold focus:ring-4 focus:ring-indigo-50 focus:border-indigo-600 transition-all outline-none shadow-inner text-sm md:text-base"
                  placeholder="john@example.com"
                />
              </div>

              <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">
                  {{ isEditing ? 'Change Password (optional)' : 'Account Password' }}
                </label>
                <div class="relative">
                  <Key class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                  <input
                    v-model="userForm.password"
                    :type="showPassword ? 'text' : 'password'"
                    :required="!isEditing"
                    class="block w-full pl-11 pr-12 py-3.5 md:py-4 bg-gray-50 border border-gray-100 rounded-2xl text-gray-900 font-bold focus:ring-4 focus:ring-indigo-50 focus:border-indigo-600 transition-all outline-none shadow-inner text-sm md:text-base"
                    placeholder="••••••••"
                  />
                  <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-indigo-600 transition-colors"
                  >
                    <Eye v-if="!showPassword" class="w-5 h-5" />
                    <EyeOff v-else class="w-5 h-5" />
                  </button>
                </div>
              </div>

              <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Confirm Password</label>
                <div class="relative">
                  <Key class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                  <input
                    v-model="userForm.password_confirmation"
                    :type="showPasswordConfirmation ? 'text' : 'password'"
                    :required="!isEditing && !!userForm.password"
                    class="block w-full pl-11 pr-12 py-3.5 md:py-4 bg-gray-50 border border-gray-100 rounded-2xl text-gray-900 font-bold focus:ring-4 focus:ring-indigo-50 focus:border-indigo-600 transition-all outline-none shadow-inner text-sm md:text-base"
                    placeholder="••••••••"
                  />
                  <button
                    type="button"
                    @click="showPasswordConfirmation = !showPasswordConfirmation"
                    class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-indigo-600 transition-colors"
                  >
                    <Eye v-if="!showPasswordConfirmation" class="w-5 h-5" />
                    <EyeOff v-else class="w-5 h-5" />
                  </button>
                </div>
              </div>

              <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2.5 ml-1">Assign Roles</label>
                <div class="flex flex-wrap gap-2">
                  <button
                    v-for="role in roles"
                    :key="role.id"
                    type="button"
                    @click="userForm.roles.includes(role.id) ? userForm.roles = userForm.roles.filter(id => id !== role.id) : userForm.roles.push(role.id)"
                    :class="['px-3 md:px-4 py-2 rounded-xl text-[10px] md:text-xs font-black transition-all border',
                      userForm.roles.includes(role.id) ? 'bg-indigo-700 border-indigo-700 text-white shadow-lg shadow-indigo-100' : 'bg-white border-gray-200 text-gray-500 hover:border-indigo-300']"
                  >
                    {{ role.name }}
                  </button>
                </div>
              </div>
            </div>

            <div class="pt-6 border-t border-gray-50 flex flex-col sm:flex-row gap-3 md:gap-4">
              <button
                type="button"
                @click="showModal = false"
                class="flex-1 px-6 py-3.5 md:py-4 rounded-2xl font-black text-gray-500 hover:bg-gray-100 transition-all text-sm uppercase tracking-widest"
              >
                CANCEL
              </button>
              <button
                type="submit"
                :disabled="isSubmitting"
                class="flex-[2] inline-flex items-center justify-center gap-2 bg-indigo-700 hover:bg-indigo-800 text-white px-6 py-3.5 md:py-4 rounded-2xl font-black shadow-lg shadow-indigo-100 transition-all active:scale-[0.98] disabled:opacity-70 disabled:cursor-not-allowed group text-sm uppercase tracking-widest"
              >
                <Loader2 v-if="isSubmitting" class="w-5 h-5 animate-spin" />
                <Save v-else class="w-5 h-5" />
                {{ isEditing ? 'UPDATE MEMBER' : 'CREATE MEMBER' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

