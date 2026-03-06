<script setup lang="ts">
import { ref, onMounted } from 'vue';
import api from '../../services/api';
import { 
  BarChart3, 
  CheckCircle2, 
  XCircle, 
  Clock, 
  Calendar,
  Loader2
} from 'lucide-vue-next';

interface ReportData {
  total: number;
  by_status: {
    pending: number;
    in_progress: number;
    approved: number;
    rejected: number;
  };
  last_7_days: number;
}

const reports = ref<ReportData | null>(null);
const loading = ref(true);
const error = ref<string | null>(null);

const fetchReports = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await api.get<ReportData>('/reports/requests');
    reports.value = response.data;
  } catch (err) {
    console.error('Failed to fetch reports:', err);
    error.value = 'Failed to load report data. Please try again later.';
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchReports();
});
</script>

<template>
  <div class="space-y-8">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-4xl font-black text-slate-900 tracking-tight">System Reports</h1>
        <p class="text-slate-500 font-medium mt-1">Overview of all workflow requests and system activity.</p>
      </div>
      <button 
        @click="fetchReports" 
        class="p-2 hover:bg-slate-200 rounded-xl transition-colors"
        :disabled="loading"
      >
        <Loader2 v-if="loading" class="w-6 h-6 animate-spin text-indigo-600" />
        <BarChart3 v-else class="w-6 h-6 text-slate-600" />
      </button>
    </div>

    <div v-if="loading" class="flex flex-col items-center justify-center py-24 space-y-4">
      <Loader2 class="w-12 h-12 animate-spin text-indigo-600" />
      <p class="text-slate-500 font-bold animate-pulse">Analyzing system data...</p>
    </div>

    <div v-else-if="error" class="bg-red-50 border-2 border-red-100 p-6 rounded-3xl flex items-center gap-4 text-red-700">
      <XCircle class="w-8 h-8 flex-shrink-0" />
      <p class="font-bold">{{ error }}</p>
    </div>

    <div v-else-if="reports" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <!-- Total Requests -->
      <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-200/60 hover:shadow-xl transition-all group overflow-hidden relative">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-indigo-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
        <div class="relative">
          <div class="w-14 h-14 bg-indigo-100 rounded-2xl flex items-center justify-center mb-6 text-indigo-600 group-hover:rotate-12 transition-transform">
            <BarChart3 class="w-7 h-7" />
          </div>
          <p class="text-slate-500 font-black uppercase tracking-widest text-[10px] mb-1">Total Requests</p>
          <p class="text-5xl font-black text-slate-900 leading-none">{{ reports.total }}</p>
        </div>
      </div>

      <!-- Approved -->
      <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-200/60 hover:shadow-xl transition-all group overflow-hidden relative">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
        <div class="relative">
          <div class="w-14 h-14 bg-emerald-100 rounded-2xl flex items-center justify-center mb-6 text-emerald-600 group-hover:rotate-12 transition-transform">
            <CheckCircle2 class="w-7 h-7" />
          </div>
          <p class="text-slate-500 font-black uppercase tracking-widest text-[10px] mb-1">Approved</p>
          <p class="text-5xl font-black text-slate-900 leading-none">{{ reports.by_status.approved }}</p>
        </div>
      </div>

      <!-- Rejected -->
      <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-200/60 hover:shadow-xl transition-all group overflow-hidden relative">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-red-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
        <div class="relative">
          <div class="w-14 h-14 bg-red-100 rounded-2xl flex items-center justify-center mb-6 text-red-600 group-hover:rotate-12 transition-transform">
            <XCircle class="w-7 h-7" />
          </div>
          <p class="text-slate-500 font-black uppercase tracking-widest text-[10px] mb-1">Rejected</p>
          <p class="text-5xl font-black text-slate-900 leading-none">{{ reports.by_status.rejected }}</p>
        </div>
      </div>

      <!-- Pending / In Progress -->
      <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-200/60 hover:shadow-xl transition-all group overflow-hidden relative">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-amber-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
        <div class="relative">
          <div class="w-14 h-14 bg-amber-100 rounded-2xl flex items-center justify-center mb-6 text-amber-600 group-hover:rotate-12 transition-transform">
            <Clock class="w-7 h-7" />
          </div>
          <p class="text-slate-500 font-black uppercase tracking-widest text-[10px] mb-1">Pending & In Progress</p>
          <p class="text-5xl font-black text-slate-900 leading-none">{{ reports.by_status.pending + reports.by_status.in_progress }}</p>
        </div>
      </div>

      <!-- Last 7 Days -->
      <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-200/60 hover:shadow-xl transition-all group overflow-hidden relative md:col-span-2 lg:col-span-1">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-blue-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
        <div class="relative">
          <div class="w-14 h-14 bg-blue-100 rounded-2xl flex items-center justify-center mb-6 text-blue-600 group-hover:rotate-12 transition-transform">
            <Calendar class="w-7 h-7" />
          </div>
          <p class="text-slate-500 font-black uppercase tracking-widest text-[10px] mb-1">New Last 7 Days</p>
          <p class="text-5xl font-black text-slate-900 leading-none">{{ reports.last_7_days }}</p>
        </div>
      </div>
    </div>
  </div>
</template>
