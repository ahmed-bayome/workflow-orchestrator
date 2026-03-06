<script setup lang="ts">
import { ref, onMounted } from 'vue';
import api from '../../services/api';
import { 
  AlertCircle, 
  RefreshCcw, 
  Loader2,
  CheckCircle2,
  XCircle
} from 'lucide-vue-next';

interface FailedJob {
  uuid: string;
  job: string;
  exception: string;
  failed_at: string;
}

const failedJobs = ref<FailedJob[]>([]);
const loading = ref(true);
const retrying = ref<string | null>(null);
const error = ref<string | null>(null);
const successMessage = ref<string | null>(null);

const fetchFailedJobs = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await api.get<FailedJob[]>('/admin/failed-jobs');
    failedJobs.value = response.data;
  } catch (err) {
    console.error('Failed to fetch jobs:', err);
    error.value = 'Failed to load failed jobs. The endpoint might not be available yet.';
  } finally {
    loading.value = false;
  }
};

const retryJob = async (uuid: string) => {
  retrying.value = uuid;
  successMessage.value = null;
  try {
    await api.post(`/admin/jobs/${uuid}/retry`);
    failedJobs.value = failedJobs.value.filter(job => job.uuid !== uuid);
    successMessage.value = 'Job queued for retry successfully.';
    setTimeout(() => { successMessage.value = null; }, 3000);
  } catch (err) {
    console.error('Retry failed:', err);
    alert('Failed to retry job. Please try again.');
  } finally {
    retrying.value = null;
  }
};

onMounted(() => {
  fetchFailedJobs();
});
</script>

<template>
  <div class="space-y-8">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-4xl font-black text-slate-900 tracking-tight">Failed Jobs</h1>
        <p class="text-slate-500 font-medium mt-1">Monitor and retry failed background processes.</p>
      </div>
      <button 
        @click="fetchFailedJobs" 
        class="p-2 hover:bg-slate-200 rounded-xl transition-colors"
        :disabled="loading"
      >
        <RefreshCcw class="w-6 h-6 text-slate-600" :class="{ 'animate-spin': loading }" />
      </button>
    </div>

    <transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="transform -translate-y-4 opacity-0"
      enter-to-class="transform translate-y-0 opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="transform translate-y-0 opacity-100"
      leave-to-class="transform -translate-y-4 opacity-0"
    >
      <div v-if="successMessage" class="bg-emerald-50 border-2 border-emerald-100 p-4 rounded-2xl flex items-center gap-3 text-emerald-700 font-bold shadow-sm">
        <CheckCircle2 class="w-5 h-5" />
        {{ successMessage }}
      </div>
    </transition>

    <div v-if="loading" class="flex flex-col items-center justify-center py-24 space-y-4 text-slate-400">
      <Loader2 class="w-12 h-12 animate-spin" />
      <p class="font-bold">Fetching failure logs...</p>
    </div>

    <div v-else-if="error" class="bg-amber-50 border-2 border-amber-100 p-8 rounded-[2rem] text-center space-y-4">
      <div class="w-16 h-16 bg-amber-100 rounded-2xl flex items-center justify-center mx-auto text-amber-600">
        <AlertCircle class="w-8 h-8" />
      </div>
      <h3 class="text-xl font-black text-slate-900">Unable to Fetch Jobs</h3>
      <p class="text-slate-500 max-w-md mx-auto">{{ error }}</p>
    </div>

    <div v-else-if="failedJobs.length === 0" class="bg-white border-2 border-dashed border-slate-200 p-12 rounded-[2rem] text-center space-y-4">
      <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto text-slate-300">
        <CheckCircle2 class="w-8 h-8" />
      </div>
      <h3 class="text-xl font-black text-slate-400 uppercase tracking-widest">No Failed Jobs</h3>
      <p class="text-slate-400 font-medium">System is running smoothly. All background jobs succeeded.</p>
    </div>

    <div v-else class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-200">
              <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Job</th>
              <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Error</th>
              <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Failed At</th>
              <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="job in failedJobs" :key="job.uuid" class="hover:bg-slate-50/50 transition-colors">
              <td class="px-6 py-5">
                <p class="font-bold text-slate-900">{{ job.job.split('\\').pop() }}</p>
                <p class="text-[10px] font-mono text-slate-400 truncate max-w-[200px]">{{ job.uuid }}</p>
              </td>
              <td class="px-6 py-5">
                <div class="max-w-md">
                  <p class="text-sm text-red-600 font-bold line-clamp-1" :title="job.exception">{{ job.exception }}</p>
                </div>
              </td>
              <td class="px-6 py-5">
                <p class="text-sm text-slate-600 font-medium">{{ job.failed_at }}</p>
              </td>
              <td class="px-6 py-5 text-right">
                <button 
                  @click="retryJob(job.uuid)"
                  class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all shadow-md shadow-indigo-200 disabled:opacity-50"
                  :disabled="retrying === job.uuid"
                >
                  <Loader2 v-if="retrying === job.uuid" class="w-3 h-3 animate-spin" />
                  <RefreshCcw v-else class="w-3 h-3" />
                  Retry
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
