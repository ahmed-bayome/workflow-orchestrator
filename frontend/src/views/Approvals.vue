<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import api from '../services/api';
import echo from '../services/echo';
import {
  CheckCircle2,
  XCircle,
  MessageSquare,
  Loader2,
  AlertCircle,
  FileText,
  User,
  ChevronRight,
  ShieldAlert,
  Calendar
} from 'lucide-vue-next';
import { format } from 'date-fns';
import type { PendingApproval, ApiError } from '@/types';
import type { AxiosError } from 'axios';

const pendingApprovals = ref<PendingApproval[]>([]);
const isLoading = ref(true);
const isSubmitting = ref<number | null>(null);
const comments = ref<Record<number, string>>({});
const error = ref('');

const fetchPendingApprovals = async () => {
  try {
    const response = await api.get<PendingApproval[]>('/approvals/pending');
    pendingApprovals.value = response.data;
    // Initialize comments for each new approval
    response.data.forEach((item: PendingApproval) => {
      if (comments.value[item.id] === undefined) {
        comments.value[item.id] = '';
      }
    });
  } catch (err) {
    console.error('Error fetching pending approvals:', err);
    error.value = 'Failed to load pending approvals.';
  } finally {
    isLoading.value = false;
  }
};

const handleAction = async (requestId: number, stepId: number, action: 'approve' | 'reject') => {
  if (action === 'reject' && !comments.value[stepId]) {
    alert('A comment is required for rejection.');
    return;
  }

  isSubmitting.value = stepId;
  error.value = '';

  try {
    await api.post(`/requests/${requestId}/steps/${stepId}/${action}`, {
      comment: comments.value[stepId]
    });
    delete comments.value[stepId];
    await fetchPendingApprovals();
  } catch (err) {
    const axiosError = err as AxiosError<ApiError>;
    error.value = axiosError.response?.data?.message || `Failed to ${action} request.`;
  } finally {
    isSubmitting.value = null;
  }
};

onMounted(() => {
  fetchPendingApprovals();

  // Real-time updates for approvals list
  echo.private('requests')
    .listen('RequestUpdated', () => {
      fetchPendingApprovals();
    });
});

onUnmounted(() => {
  echo.leave('requests');
});


</script>

<template>
  <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500 pb-20">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-black text-gray-900 tracking-tight leading-none mb-2">Pending Approvals</h1>
        <p class="text-gray-500 font-medium">Review and take action on requests waiting for your decision.</p>
      </div>
      <div class="flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-700 rounded-xl font-bold border border-indigo-100 shadow-sm">
        <ShieldAlert class="w-5 h-5" />
        <span class="text-lg leading-none">{{ pendingApprovals.length }} Actions</span>
      </div>
    </div>

    <!-- Error State -->
    <div v-if="error" class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl flex items-center gap-4 animate-shake">
      <AlertCircle class="w-6 h-6 flex-shrink-0" />
      <span class="text-sm font-bold">{{ error }}</span>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="p-20 flex flex-col items-center justify-center gap-6">
      <div class="w-16 h-16 border-4 border-indigo-100 border-t-indigo-600 rounded-full animate-spin"></div>
      <p class="text-gray-400 font-black uppercase tracking-widest text-xs">Awaiting decisions...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="pendingApprovals.length === 0" class="p-20 text-center bg-white rounded-3xl border-2 border-dashed border-gray-100 shadow-sm">
      <div class="bg-gray-50 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6 text-gray-200">
        <CheckCircle2 class="w-12 h-12" />
      </div>
      <h3 class="text-2xl font-black text-gray-900 leading-tight">All caught up!</h3>
      <p class="mt-2 text-gray-500 max-w-sm mx-auto font-medium">There are no requests pending your approval right now. Take a break!</p>
    </div>

    <div v-else class="space-y-8">
      <div
        v-for="item in pendingApprovals"
        :key="item.id"
        class="bg-white rounded-3xl shadow-xl shadow-gray-100/50 border border-gray-100 overflow-hidden transition-all"
      >
        <div class="flex flex-col md:flex-row">
          <!-- Left Side: Request Summary -->
          <div class="md:w-1/3 bg-gray-50/50 p-8 border-r border-gray-50 space-y-6">
            <div class="space-y-4">
              <div class="flex items-center gap-3">
                <div class="bg-indigo-700 p-2.5 rounded-xl shadow-lg shadow-indigo-100 text-white">
                  <FileText class="w-6 h-6" />
                </div>
                <div>
                  <h3 class="text-xl font-black text-gray-900 leading-none">{{ item.request.workflow_definition?.name }}</h3>
                  <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mt-1.5">Request #{{ item.request.id }}</p>
                </div>
              </div>

              <div class="space-y-4 pt-4">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center border border-gray-100 shadow-sm text-gray-500">
                    <User class="w-4 h-4" />
                  </div>
                  <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-0.5">Requester</p>
                    <p class="text-sm font-bold text-gray-900">{{ item.request.requester?.name }}</p>
                  </div>
                </div>

                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center border border-gray-100 shadow-sm text-gray-500">
                    <Calendar class="w-4 h-4" />
                  </div>
                  <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-0.5">Submitted On</p>
                    <p class="text-sm font-bold text-gray-900">{{ format(new Date(item.request.created_at), 'MMM dd, yyyy') }}</p>
                  </div>
                </div>
              </div>

              <div class="pt-6 border-t border-gray-100">
                <router-link
                  :to="`/requests/${item.request.id}`"
                  class="flex items-center justify-between w-full px-4 py-3 bg-white hover:bg-indigo-50 rounded-2xl border border-gray-200 text-sm font-bold text-indigo-700 transition-all active:scale-[0.98] group"
                >
                  View Full Request
                  <ChevronRight class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                </router-link>
              </div>
            </div>
          </div>

          <!-- Right Side: Action Panel -->
          <div class="flex-1 p-8 md:p-10 flex flex-col space-y-8">
            <div class="space-y-4">
              <h4 class="text-lg font-black text-gray-900 flex items-center gap-2">
                <MessageSquare class="w-5 h-5 text-indigo-400" />
                Decision &amp; Feedback
              </h4>
              <p class="text-sm text-gray-500 font-medium leading-relaxed">
                Provide your decision and comments for the current step: <span class="text-indigo-700 font-black">{{ item.step_definition?.name }}</span>.
              </p>
              <textarea
                v-model="comments[item.id]"
                placeholder="Share your thoughts (required for rejections)..."
                rows="4"
                class="block w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl text-gray-900 focus:ring-4 focus:ring-indigo-50 focus:border-indigo-600 transition-all outline-none resize-none shadow-sm text-sm"
              ></textarea>
            </div>

            <div class="flex flex-col sm:flex-row items-center gap-4 mt-auto pt-6 border-t border-gray-50">
              <button
                @click="handleAction(item.request.id, item.id, 'reject')"
                :disabled="isSubmitting !== null"
                class="flex-1 w-full flex items-center justify-center gap-2.5 px-6 py-4 rounded-2xl border-2 border-red-600 text-red-600 font-black hover:bg-red-50 transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed group"
              >
                <XCircle class="w-5 h-5 group-hover:rotate-12 transition-transform" />
                REJECT
              </button>

              <button
                @click="handleAction(item.request.id, item.id, 'approve')"
                :disabled="isSubmitting !== null"
                class="flex-1 w-full flex items-center justify-center gap-2.5 px-6 py-4 bg-indigo-700 text-white rounded-2xl font-black shadow-lg shadow-indigo-100 hover:bg-indigo-800 transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed group"
              >
                <Loader2 v-if="isSubmitting === item.id" class="w-5 h-5 animate-spin" />
                <CheckCircle2 v-else class="w-5 h-5 group-hover:scale-110 transition-transform" />
                APPROVE
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
