<script setup lang="ts">
import { ref, onMounted } from 'vue';
import api from '../../services/api';
import { 
  Workflow, 
  PlusCircle, 
  Settings, 
  Trash2, 
  CheckCircle2, 
  XCircle,
  Clock,
  User,
  Activity,
  ChevronRight,
  Shield,
  Layers
} from 'lucide-vue-next';

const workflows = ref([]);
const isLoading = ref(true);
const isDeleting = ref<number | null>(null);

const fetchWorkflows = async () => {
  try {
    const response = await api.get('/admin/workflows');
    workflows.value = response.data;
  } catch (err) {
    console.error('Error fetching workflows:', err);
  } finally {
    isLoading.value = false;
  }
};

const deleteWorkflow = async (id: number) => {
  if (!confirm('Are you sure you want to delete this workflow? This action cannot be undone.')) return;
  
  isDeleting.value = id;
  try {
    await api.delete(`/admin/workflows/${id}`);
    workflows.value = workflows.value.filter((w: any) => w.id !== id);
  } catch (err: any) {
    alert(err.response?.data?.error || 'Failed to delete workflow.');
  } finally {
    isDeleting.value = null;
  }
};

const toggleStatus = async (workflow: any) => {
  try {
    const action = workflow.is_active ? 'deactivate' : 'activate';
    await api.post(`/admin/workflows/${workflow.id}/${action}`);
    workflow.is_active = !workflow.is_active;
  } catch (err) {
    console.error('Error toggling workflow status:', err);
  }
};

onMounted(fetchWorkflows);
</script>

<template>
  <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500 pb-20">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-black text-gray-900 tracking-tight leading-none mb-2">Workflow Definitions</h1>
        <p class="text-gray-500 font-medium">Design and manage your custom approval pipelines.</p>
      </div>
      <router-link
        to="/admin/workflows/new"
        class="inline-flex items-center gap-2 bg-indigo-700 hover:bg-indigo-800 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-indigo-100 transition-all active:scale-[0.98]"
      >
        <PlusCircle class="w-5 h-5" />
        New Workflow
      </router-link>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="p-20 flex flex-col items-center justify-center gap-6">
      <div class="w-16 h-16 border-4 border-indigo-100 border-t-indigo-600 rounded-full animate-spin"></div>
      <p class="text-gray-400 font-black uppercase tracking-widest text-xs">Architecting views...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="workflows.length === 0" class="p-20 text-center bg-white rounded-3xl border-2 border-dashed border-gray-100 shadow-sm">
      <div class="bg-indigo-50 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6 text-indigo-200">
        <Workflow class="w-12 h-12" />
      </div>
      <h3 class="text-2xl font-black text-gray-900 leading-tight">No Workflows Yet</h3>
      <p class="mt-2 text-gray-500 max-w-sm mx-auto font-medium">Create your first automated approval pipeline to streamline your operations.</p>
      <router-link
        to="/admin/workflows/new"
        class="mt-6 inline-flex items-center gap-2 bg-indigo-700 hover:bg-indigo-800 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-indigo-100 transition-all active:scale-[0.98]"
      >
        <PlusCircle class="w-5 h-5" />
        Start Designing
      </router-link>
    </div>

    <!-- Workflows Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      <div 
        v-for="workflow in workflows" 
        :key="workflow.id" 
        class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-200 overflow-hidden flex flex-col group transition-all"
      >
        <div class="p-8 space-y-6 flex-1">
          <div class="flex items-start justify-between">
            <div class="bg-indigo-700 p-3 rounded-2xl shadow-lg shadow-indigo-100 text-white">
              <Workflow class="w-8 h-8" />
            </div>
            <button 
              @click="toggleStatus(workflow)"
              :class="['px-3 py-1 text-[10px] font-black uppercase tracking-widest rounded-full border transition-colors relative z-10', 
                workflow.is_active ? 'bg-green-50 text-green-700 border-green-100 hover:bg-green-100' : 'bg-red-50 text-red-700 border-red-100 hover:bg-red-100']"
            >
              {{ workflow.is_active ? 'Active' : 'Inactive' }}
            </button>
          </div>

          <div>
            <h3 class="text-xl font-black text-slate-900 leading-tight">{{ workflow.name }}</h3>
            <p class="text-sm text-slate-500 font-medium mt-2 line-clamp-2 leading-relaxed">
              {{ workflow.description || 'No description provided.' }}
            </p>
          </div>

          <div class="flex items-center gap-6 pt-4">
            <div class="flex flex-col">
              <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1.5">Fields</span>
              <div class="flex items-center gap-1.5 text-slate-700 font-bold">
                <Layers class="w-4 h-4 text-indigo-400" />
                {{ workflow.form_schema.fields.length }}
              </div>
            </div>
            <div class="flex flex-col">
              <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1.5">Steps</span>
              <div class="flex items-center gap-1.5 text-slate-700 font-bold">
                <Activity class="w-4 h-4 text-indigo-400" />
                {{ workflow.steps.length }}
              </div>
            </div>
            <div class="flex flex-col">
              <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1.5">Version</span>
              <div class="flex items-center gap-1.5 text-slate-700 font-bold">
                v{{ workflow.version }}
              </div>
            </div>
          </div>
        </div>

        <div class="px-8 py-5 bg-slate-50 border-t border-slate-100 flex items-center justify-between relative z-10">
          <div class="flex items-center gap-2 text-xs font-bold text-slate-500">
            <User class="w-3.5 h-3.5" />
            {{ workflow.creator?.name }}
          </div>
          <div class="flex items-center gap-2">
            <button 
              @click="deleteWorkflow(workflow.id)"
              :disabled="isDeleting === workflow.id"
              class="p-2.5 text-slate-400 hover:text-red-600 hover:bg-white rounded-xl transition-all shadow-sm hover:shadow-md disabled:opacity-50"
              title="Delete Workflow"
            >
              <Trash2 v-if="isDeleting !== workflow.id" class="w-5 h-5" />
              <div v-else class="w-5 h-5 border-2 border-red-500 border-t-transparent rounded-full animate-spin"></div>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
