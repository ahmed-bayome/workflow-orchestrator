<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../services/api';
import echo from '../services/echo';
import { 
  ChevronLeft, 
  Clock, 
  CheckCircle2, 
  XCircle, 
  User, 
  Calendar,
  AlertCircle,
  FileText,
  Activity,
  MessageSquare,
  ArrowRight,
  Shield,
  Loader2
} from 'lucide-vue-next';
import { format } from 'date-fns';

const route = useRoute();
const router = useRouter();
const request = ref<any>(null);
const isLoading = ref(true);
const error = ref('');

const fetchRequestDetails = async () => {
  try {
    const response = await api.get(`/requests/${route.params.id}`);
    request.value = response.data;
  } catch (err) {
    console.error('Error fetching request details:', err);
    error.value = 'Failed to load request details.';
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchRequestDetails();
  
  // Real-time updates
  echo.private(`request.${route.params.id}`)
    .listen('RequestUpdated', (data: any) => {
      request.value = data.request;
    })
    .listen('StepUpdated', (data: any) => {
      const stepIndex = request.value.steps.findIndex((s: any) => s.id === data.step.id);
      if (stepIndex !== -1) {
        request.value.steps[stepIndex] = data.step;
      }
    });
});

onUnmounted(() => {
  echo.leave(`request.${route.params.id}`);
});

const getStatusColor = (status: string) => {
  switch (status) {
    case 'approved': return 'text-green-600 bg-green-50 border-green-100';
    case 'rejected': return 'text-red-600 bg-red-50 border-red-100';
    case 'in_progress': return 'text-blue-600 bg-blue-50 border-blue-100';
    case 'pending': return 'text-yellow-600 bg-yellow-50 border-yellow-100';
    case 'skipped': return 'text-gray-400 bg-gray-50 border-gray-100';
    default: return 'text-gray-600 bg-gray-50 border-gray-100';
  }
};

const getStepIcon = (status: string) => {
  switch (status) {
    case 'approved': return CheckCircle2;
    case 'rejected': return XCircle;
    case 'in_progress': return Loader2;
    case 'pending': return Clock;
    default: return Activity;
  }
};
</script>

<template>
  <div class="max-w-6xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500 pb-20">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
      <div class="flex items-center gap-4">
        <button 
          @click="router.back()" 
          class="p-2.5 rounded-xl hover:bg-white transition-all text-gray-500 hover:text-gray-900 border border-transparent hover:border-gray-200 shadow-sm"
        >
          <ChevronLeft class="w-6 h-6" />
        </button>
        <div>
          <div class="flex items-center gap-3 mb-1">
            <h1 class="text-3xl font-black text-gray-900 tracking-tight leading-none">Request #{{ route.params.id }}</h1>
            <span v-if="request" :class="['px-3 py-1 text-[11px] font-bold uppercase rounded-full border', getStatusColor(request.status)]">
              {{ request.status.replace('_', ' ') }}
            </span>
          </div>
          <p v-if="request" class="text-gray-500 font-medium">Submitted for {{ request.workflow_definition?.name }}</p>
        </div>
      </div>
      
      <div v-if="request" class="flex items-center gap-4 bg-white px-6 py-4 rounded-2xl border border-gray-100 shadow-sm">
        <div class="text-right">
          <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-none mb-1">Created on</p>
          <p class="text-sm font-bold text-gray-900">{{ format(new Date(request.created_at), 'MMM dd, yyyy HH:mm') }}</p>
        </div>
        <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600">
          <Calendar class="w-5 h-5" />
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-if="error" class="p-8 bg-white rounded-3xl border-2 border-dashed border-red-200 text-center space-y-4">
      <div class="bg-red-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto text-red-500">
        <AlertCircle class="w-8 h-8" />
      </div>
      <div>
        <h3 class="text-lg font-bold text-gray-900">Something went wrong</h3>
        <p class="text-gray-500 max-w-sm mx-auto mt-1">{{ error }}</p>
      </div>
      <button @click="fetchRequestDetails" class="text-indigo-600 font-bold hover:underline">Try again</button>
    </div>

    <!-- Loading State -->
    <div v-else-if="isLoading" class="p-20 flex flex-col items-center justify-center gap-6">
      <div class="w-16 h-16 border-4 border-indigo-100 border-t-indigo-600 rounded-full animate-spin"></div>
      <p class="text-gray-400 font-bold uppercase tracking-widest text-xs">Fetching records...</p>
    </div>

    <!-- Content -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Left: Request Payload -->
      <div class="lg:col-span-2 space-y-8">
        <section class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/60 border border-slate-200 overflow-hidden">
          <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <h2 class="text-xl font-black text-slate-900 flex items-center gap-3">
              <div class="bg-indigo-600 p-2 rounded-xl text-white shadow-lg shadow-indigo-200">
                <FileText class="w-5 h-5" />
              </div>
              Submission Data
            </h2>
            <div class="flex items-center gap-3 px-4 py-2 bg-white rounded-2xl border border-slate-200 shadow-sm">
              <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-[10px] font-bold">
                {{ request.requester?.name.charAt(0) }}
              </div>
              <span class="text-xs font-bold text-slate-600">{{ request.requester?.name }}</span>
            </div>
          </div>
          
          <div class="p-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
              <div 
                v-for="field in request.workflow_snapshot.form_schema.fields" 
                :key="field.id" 
                :class="[
                  'p-5 rounded-3xl border transition-all group',
                  field.type === 'textarea' ? 'sm:col-span-2 lg:col-span-3 bg-slate-50/50 border-slate-100' : 'bg-white border-slate-100 shadow-sm'
                ]"
              >
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2 transition-colors">
                  {{ field.label }}
                </label>
                
                <div v-if="field.type === 'textarea'" class="text-slate-700 font-medium whitespace-pre-wrap text-sm leading-relaxed">
                  {{ request.payload[field.id] || 'N/A' }}
                </div>
                
                <div v-else class="flex items-center gap-3">
                  <div class="w-1.5 h-1.5 rounded-full bg-indigo-500 shadow-[0_0_8px_rgba(79,70,229,0.4)]"></div>
                  <span class="text-base font-bold text-slate-900 tracking-tight">
                    {{ request.payload[field.id] || 'N/A' }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Activity Feed (Optional) -->
        <section class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
          <h2 class="text-xl font-black text-gray-900 mb-8 flex items-center gap-3">
            <Activity class="w-6 h-6 text-indigo-600" />
            Full History
          </h2>
          
          <div class="space-y-12">
            <div v-for="(step, index) in request.steps" :key="step.id" class="relative pl-12">
              <!-- Connector Line -->
              <div 
                v-if="index < request.steps.length - 1" 
                class="absolute left-[15px] top-10 bottom-[-48px] w-0.5 bg-slate-100"
              ></div>

              <!-- Icon -->
              <div :class="['absolute left-0 top-0.5 w-8 h-8 rounded-lg flex items-center justify-center border-2 z-10 shadow-sm', getStatusColor(step.status)]">
                <component :is="getStepIcon(step.status)" class="w-4 h-4" :class="step.status === 'in_progress' ? 'animate-spin' : ''" />
              </div>

              <div class="space-y-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-2">
                  <h4 class="font-bold text-gray-900 text-lg leading-none">{{ step.step_definition.name }}</h4>
                  <span v-if="step.completed_at" class="text-[10px] font-bold text-gray-400 uppercase">
                    Completed {{ format(new Date(step.completed_at), 'MMM dd, HH:mm') }}
                  </span>
                </div>

                <div v-if="step.actions && step.actions.length > 0" class="space-y-4">
                  <div v-for="action in step.actions" :key="action.id" class="bg-gray-50 rounded-2xl p-5 border border-gray-100 space-y-3 relative group transition-all hover:bg-white hover:shadow-md">
                    <div class="flex items-center justify-between">
                      <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xs shadow-sm">
                          {{ action.user?.name.charAt(0) }}
                        </div>
                        <div>
                          <p class="text-sm font-bold text-gray-900 leading-none">{{ action.user?.name }}</p>
                          <p class="text-[10px] text-gray-500 uppercase font-bold mt-1 tracking-wider">{{ action.action }}ed</p>
                        </div>
                      </div>
                      <div :class="['p-1.5 rounded-lg border', action.action === 'approve' ? 'bg-green-100 text-green-600 border-green-200' : 'bg-red-100 text-red-600 border-red-200']">
                        <CheckCircle2 v-if="action.action === 'approve'" class="w-4 h-4" />
                        <XCircle v-else class="w-4 h-4" />
                      </div>
                    </div>
                    <div v-if="action.comment" class="flex items-start gap-3 bg-white/50 p-3 rounded-xl border border-gray-50 italic text-sm text-gray-600">
                      <MessageSquare class="w-4 h-4 mt-1 text-gray-300 flex-shrink-0" />
                      "{{ action.comment }}"
                    </div>
                  </div>
                </div>
                
                <div v-else-if="step.status === 'pending' || step.status === 'in_progress'" class="text-sm text-gray-400 font-medium italic pl-1 flex items-center gap-2">
                  <Clock class="w-4 h-4" />
                  Waiting for approvers in {{ step.role?.name || 'Assigned Role' }}
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>

      <!-- Right: Workflow Visualization -->
      <div class="space-y-8">
        <section class="bg-indigo-900 rounded-[2.5rem] p-8 md:p-10 text-white shadow-2xl shadow-indigo-200/50 sticky top-8 border border-white/10">
          <h3 class="text-xl font-black mb-10 flex items-center gap-3">
            <Shield class="w-6 h-6 text-indigo-400" />
            Step Pipeline
          </h3>

          <div class="space-y-0 relative">
            <div v-for="(step, index) in request.steps" :key="step.id" class="relative group">
              <!-- Connector Line -->
              <div 
                v-if="index < request.steps.length - 1" 
                class="absolute left-[19px] top-10 bottom-0 w-0.5 bg-white/10"
              ></div>

              <div class="flex items-start gap-6 pb-12 last:pb-0">
                <!-- Step Order Circle -->
                <div :class="[
                  'relative z-10 w-10 h-10 rounded-full flex items-center justify-center text-sm font-black border-2 transition-all duration-500 flex-shrink-0',
                  step.status === 'approved' ? 'bg-green-500 border-green-500 text-white shadow-lg shadow-green-500/20' : 
                  step.status === 'rejected' ? 'bg-red-500 border-red-500 text-white shadow-lg shadow-red-500/20' :
                  step.status === 'in_progress' ? 'bg-white border-white text-indigo-900 scale-110 shadow-xl' : 
                  'bg-indigo-800/50 border-indigo-700 text-indigo-400'
                ]">
                  {{ index + 1 }}
                </div>
                
                <!-- Step Label -->
                <div class="flex-1 pt-1">
                  <p :class="['font-bold text-base leading-tight', step.status === 'pending' ? 'text-indigo-400' : 'text-white']">
                    {{ step.step_definition.name }}
                  </p>
                  <p class="text-[10px] font-black uppercase tracking-widest text-indigo-300/60 mt-1">
                    {{ step.approval_mode === 'all' ? 'All must approve' : 'Any can approve' }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-10 pt-10 border-t border-white/5 space-y-5">
            <div class="flex items-center justify-between">
              <span class="text-indigo-300/60 font-black uppercase tracking-widest text-[10px]">Overall Progress</span>
              <span :class="['font-black uppercase tracking-widest text-xs', 
                request.status === 'approved' ? 'text-green-400' : 
                request.status === 'rejected' ? 'text-red-400' : 'text-indigo-300'
              ]">
                {{ request.status.replace('_', ' ') }}
              </span>
            </div>
            <div class="h-2.5 bg-indigo-950/50 rounded-full overflow-hidden border border-white/5 shadow-inner">
              <div 
                class="h-full transition-all duration-1000 ease-out rounded-full shadow-lg"
                :class="[
                  request.status === 'approved' ? 'bg-green-500' : 
                  request.status === 'rejected' ? 'bg-red-500' : 'bg-indigo-400'
                ]"
                :style="{ width: `${(request.steps.filter((s: any) => s.status === 'approved').length / request.steps.length) * 100}%` }"
              ></div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>
