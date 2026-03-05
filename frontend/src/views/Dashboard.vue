<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import api from '../services/api';
import {
  FileText,
  CheckSquare,
  Clock,
  ChevronRight,
  PlusCircle,
  TrendingUp,
  AlertCircle,
  Workflow,
  Shield
} from 'lucide-vue-next';
import { formatDistanceToNow } from 'date-fns';
import type { WorkflowRequest } from '@/types';

const authStore = useAuthStore();
const stats = ref({
  pending_requests: 0,
  pending_approvals: 0,
  total_requests: 0,
});
const recentRequests = ref<WorkflowRequest[]>([]);
const isLoading = ref(true);

import echo from '@/services/echo';
import { onUnmounted } from 'vue';

const fetchDashboardData = async () => {
  try {
    const [requestsRes, approvalsRes] = await Promise.all([
      api.get<WorkflowRequest[]>('/requests'),
      api.get('/approvals/pending')
    ]);

    const allRequests = requestsRes.data;
    recentRequests.value = allRequests.slice(0, 5);

    stats.value = {
      pending_requests: allRequests.filter((r: WorkflowRequest) => r.status === 'pending' || r.status === 'in_progress').length,
      pending_approvals: approvalsRes.data.length,
      total_requests: allRequests.length,
    };
  } catch (error) {
    console.error('Error fetching dashboard data:', error);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchDashboardData();

  echo.private('requests')
    .listen('RequestCreated', () => {
      // Re-fetch dashboard stats so pending counts update live
      fetchDashboardData();
    })
    .listen('RequestUpdated', () => {
      fetchDashboardData();
    });
});

onUnmounted(() => {
  echo.leave('requests');
});

const getStatusColor = (status: string) => {
  switch (status) {
    case 'approved': return 'bg-green-100 text-green-700 border-green-200';
    case 'rejected': return 'bg-red-100 text-red-700 border-red-200';
    case 'in_progress': return 'bg-blue-100 text-blue-700 border-blue-200';
    case 'pending': return 'bg-yellow-100 text-yellow-700 border-yellow-200';
    default: return 'bg-gray-100 text-gray-700 border-gray-200';
  }
};
</script>

<template>
  <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <!-- Welcome Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Dashboard</h1>
        <p class="text-gray-500 mt-1">Welcome back, {{ authStore.user?.name }}! Here's what's happening today.</p>
      </div>
      <router-link
        to="/requests/new"
        class="inline-flex items-center gap-2 bg-indigo-700 hover:bg-indigo-800 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-indigo-100 transition-all active:scale-[0.98]"
      >
        <PlusCircle class="w-5 h-5" />
        New Request
      </router-link>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <router-link to="/requests" class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-start gap-4 transition-all hover:shadow-md active:scale-[0.98] group">
        <div class="p-3 bg-blue-50 rounded-xl text-blue-600 group-hover:scale-110 transition-transform">
          <Clock class="w-6 h-6" />
        </div>
        <div>
          <p class="text-sm font-medium text-gray-500">Pending Requests</p>
          <p class="text-2xl font-bold text-gray-900 mt-1">{{ stats.pending_requests }}</p>
        </div>
      </router-link>

      <router-link to="/approvals" class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-start gap-4 transition-all hover:shadow-md active:scale-[0.98] group">
        <div class="p-3 bg-amber-50 rounded-xl text-amber-600 group-hover:scale-110 transition-transform">
          <CheckSquare class="w-6 h-6" />
        </div>
        <div>
          <p class="text-sm font-medium text-gray-500">Pending Approvals</p>
          <p class="text-2xl font-bold text-gray-900 mt-1">{{ stats.pending_approvals }}</p>
        </div>
      </router-link>

      <router-link to="/requests" class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-start gap-4 transition-all hover:shadow-md active:scale-[0.98] group">
        <div class="p-3 bg-indigo-50 rounded-xl text-indigo-600 group-hover:scale-110 transition-transform">
          <FileText class="w-6 h-6" />
        </div>
        <div>
          <p class="text-sm font-medium text-gray-500">Total Requests</p>
          <p class="text-2xl font-bold text-gray-900 mt-1">{{ stats.total_requests }}</p>
        </div>
      </router-link>
    </div>

    <!-- Dashboard Content -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Recent Requests -->
      <section class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-50 flex items-center justify-between">
          <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
            <TrendingUp class="w-5 h-5 text-indigo-500" />
            Recent Requests
          </h2>
          <router-link to="/requests" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">
            View all
          </router-link>
        </div>

        <div class="divide-y divide-gray-50">
          <div v-if="isLoading" class="p-8 flex justify-center">
            <div class="w-8 h-8 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
          </div>

          <div v-else-if="recentRequests.length === 0" class="p-12 text-center text-gray-500">
            <AlertCircle class="w-12 h-12 mx-auto text-gray-200 mb-4" />
            <p>No requests found yet.</p>
          </div>

          <router-link
            v-for="request in recentRequests"
            :key="request.id"
            :to="`/requests/${request.id}`"
            class="group flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition-colors"
          >
            <div class="flex items-center gap-4">
              <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold group-hover:scale-110 transition-transform">
                {{ request.workflow_definition?.name.charAt(0) }}
              </div>
              <div>
                <p class="text-sm font-bold text-gray-900">{{ request.workflow_definition?.name }}</p>
                <p class="text-xs text-gray-500 mt-1">
                  Submitted {{ formatDistanceToNow(new Date(request.created_at)) }} ago
                </p>
              </div>
            </div>
            <div class="flex items-center gap-3">
              <span :class="['px-2.5 py-1 text-[10px] font-bold uppercase rounded-full border', getStatusColor(request.status)]">
                {{ request.status.replace('_', ' ') }}
              </span>
              <ChevronRight class="w-5 h-5 text-gray-300 group-hover:text-indigo-400 transition-colors" />
            </div>
          </router-link>
        </div>
      </section>

      <!-- Action Items / Tips / Activity -->
      <section class="space-y-6">
        <div class="bg-indigo-700 rounded-2xl p-6 text-white shadow-lg shadow-indigo-100 relative overflow-hidden group">
          <div class="relative z-10">
            <h3 class="text-xl font-bold mb-2">Build a new Workflow</h3>
            <p class="text-indigo-100 mb-6 text-sm opacity-90">Design a custom form and approval pipeline in minutes. No code required.</p>
            <router-link
              to="/admin/workflows/new"
              class="inline-flex items-center gap-2 bg-white text-indigo-700 px-5 py-2.5 rounded-xl font-bold shadow-md hover:bg-indigo-50 transition-all active:scale-[0.98]"
            >
              Get Started
              <ChevronRight class="w-4 h-4" />
            </router-link>
          </div>
          <Workflow class="absolute -right-4 -bottom-4 w-32 h-32 text-white opacity-10 rotate-12 group-hover:scale-110 transition-transform duration-700" />
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
          <h3 class="text-lg font-bold text-gray-900 mb-4">Quick Stats</h3>
          <div class="space-y-4">
            <div v-for="role in authStore.user?.roles" :key="role.id" class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <Shield class="w-4 h-4 text-indigo-400" />
                <span class="text-sm text-gray-600">{{ role.name }}</span>
              </div>
              <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded-md">Active</span>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>
