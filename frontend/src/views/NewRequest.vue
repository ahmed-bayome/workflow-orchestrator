<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';
import {
  ChevronLeft,
  Send,
  CheckCircle2,
  Loader2,
  AlertCircle,
  FileText,
  Calendar,
  Layers,
  HelpCircle,
  Hash,
  Mail,
  Type,
  AlignLeft,
  ChevronDown
} from 'lucide-vue-next';
import type { WorkflowDefinition, FormField, FormFieldValue, ApiError } from '@/types';
import type { AxiosError } from 'axios';

const router = useRouter();
const workflows = ref<WorkflowDefinition[]>([]);
const selectedWorkflowId = ref('');
const formData = ref<Record<string, FormFieldValue>>({});
const isLoading = ref(true);
const isSubmitting = ref(false);
const error = ref('');

const selectedWorkflow = computed(() =>
  workflows.value.find((w: WorkflowDefinition) => w.id == Number(selectedWorkflowId.value))
);

const fetchWorkflows = async () => {
  try {
    const response = await api.get<WorkflowDefinition[]>('/admin/workflows');
    workflows.value = response.data.filter((w: WorkflowDefinition) => w.is_active);
  } catch (err) {
    console.error('Error fetching workflows:', err);
  } finally {
    isLoading.value = false;
  }
};

const handleWorkflowChange = () => {
  formData.value = {};
  if (selectedWorkflow.value) {
    selectedWorkflow.value.form_schema.fields.forEach((field: FormField) => {
      formData.value[field.id] = '';
    });
  }
};

const submitRequest = async () => {
  isSubmitting.value = true;
  error.value = '';

  try {
    await api.post('/requests', {
      workflow_definition_id: selectedWorkflowId.value,
      payload: formData.value
    });
    router.push('/requests');
  } catch (err) {
    const axiosError = err as AxiosError<ApiError>;
    error.value = axiosError.response?.data?.message || 'Failed to submit request. Please check all fields.';
  } finally {
    isSubmitting.value = false;
  }
};

onMounted(fetchWorkflows);

const getFieldIcon = (type: string) => {
  switch (type) {
    case 'text': return Type;
    case 'number': return Hash;
    case 'email': return Mail;
    case 'date': return Calendar;
    case 'textarea': return AlignLeft;
    case 'select': return ChevronDown;
    default: return HelpCircle;
  }
};
</script>

<template>
  <div class="max-w-4xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <div class="flex items-center gap-4">
      <button
        @click="router.back()"
        class="p-2.5 rounded-xl hover:bg-gray-100 transition-colors text-gray-500 hover:text-gray-900 border border-transparent hover:border-gray-200 shadow-sm"
      >
        <ChevronLeft class="w-6 h-6" />
      </button>
      <div>
        <h1 class="text-3xl font-bold text-gray-900 tracking-tight leading-none">New Request</h1>
        <p class="text-gray-500 mt-1.5 font-medium">Submit a new workflow request for approval.</p>
      </div>
    </div>

    <div v-if="error" class="p-5 bg-red-50 border border-red-200 text-red-700 rounded-2xl flex items-center gap-4 animate-shake">
      <div class="bg-red-100 p-2 rounded-lg">
        <AlertCircle class="w-6 h-6 flex-shrink-0" />
      </div>
      <span class="text-sm font-bold">{{ error }}</span>
    </div>

    <div class="bg-white rounded-3xl shadow-xl shadow-gray-100/50 border border-gray-100 overflow-hidden">
      <!-- Loading Overlay -->
      <div v-if="isLoading" class="p-20 flex flex-col items-center justify-center gap-6">
        <div class="relative flex items-center justify-center">
          <div class="w-16 h-16 border-4 border-indigo-100 rounded-full animate-ping opacity-75"></div>
          <div class="absolute w-12 h-12 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
        </div>
        <p class="text-gray-500 font-bold animate-pulse uppercase tracking-widest text-sm">Initializing form...</p>
      </div>

      <div v-else class="flex flex-col md:flex-row">
        <!-- Sidebar Info -->
        <div class="md:w-1/3 bg-indigo-50/50 p-8 border-r border-gray-50 hidden md:block">
          <div class="sticky top-8 space-y-8">
            <div class="space-y-4">
              <div class="bg-indigo-700 p-3 rounded-2xl w-fit shadow-lg shadow-indigo-200 text-white mb-6">
                <FileText class="w-8 h-8" />
              </div>
              <h3 class="text-xl font-bold text-gray-900">Request Details</h3>
              <p class="text-sm text-gray-600 leading-relaxed">
                Choose a workflow and fill in the required details to start the approval process.
              </p>
            </div>

            <div class="space-y-6">
              <div class="flex items-start gap-3">
                <div class="bg-white p-2 rounded-lg shadow-sm text-indigo-600 mt-0.5">
                  <CheckCircle2 class="w-4 h-4" />
                </div>
                <div>
                  <h4 class="text-sm font-bold text-gray-900">Validations</h4>
                  <p class="text-xs text-gray-500 mt-1">Real-time validation ensures accuracy.</p>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <div class="bg-white p-2 rounded-lg shadow-sm text-indigo-600 mt-0.5">
                  <Layers class="w-4 h-4" />
                </div>
                <div>
                  <h4 class="text-sm font-bold text-gray-900">Process Flow</h4>
                  <p class="text-xs text-gray-500 mt-1">Multi-stage approval pipeline.</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Form -->
        <div class="flex-1 p-8 md:p-10">
          <form @submit.prevent="submitRequest" class="space-y-8">
            <!-- Workflow Selection -->
            <div class="space-y-4 animate-in fade-in duration-700">
              <label class="text-sm font-bold text-gray-900 flex items-center gap-2">
                <Layers class="w-4 h-4 text-indigo-600" />
                SELECT WORKFLOW TYPE
              </label>
              <div class="relative">
                <select
                  v-model="selectedWorkflowId"
                  @change="handleWorkflowChange"
                  required
                  class="block w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl text-gray-900 font-medium focus:ring-4 focus:ring-indigo-50 focus:border-indigo-600 transition-all outline-none appearance-none cursor-pointer"
                >
                  <option value="" disabled>Choose a workflow type...</option>
                  <option v-for="w in workflows" :key="w.id" :value="w.id">{{ w.name }}</option>
                </select>
                <div class="absolute inset-y-0 right-5 flex items-center pointer-events-none text-gray-400">
                  <ChevronDown class="w-5 h-5" />
                </div>
              </div>
              <p v-if="selectedWorkflow" class="text-sm text-gray-500 bg-gray-50 p-4 rounded-xl border-l-4 border-indigo-400 font-medium italic">
                {{ selectedWorkflow.description }}
              </p>
            </div>

            <!-- Dynamic Form Fields -->
            <div v-if="selectedWorkflow" class="space-y-8 border-t border-gray-100 pt-8 animate-in slide-in-from-top-4 duration-500">
              <h3 class="text-xl font-extrabold text-gray-900 flex items-center gap-2">
                <FileText class="w-6 h-6 text-indigo-600" />
                Fill Information
              </h3>

              <div class="grid grid-cols-1 gap-8">
                <div v-for="field in selectedWorkflow.form_schema.fields" :key="field.id" class="space-y-2">
                  <label :for="field.id" class="text-sm font-bold text-gray-700 uppercase tracking-tight flex items-center gap-2">
                    <component :is="getFieldIcon(field.type)" class="w-4 h-4 text-indigo-400" />
                    {{ field.label }}
                    <span v-if="field.required" class="text-red-500 font-bold">*</span>
                  </label>

                  <!-- Inputs -->
                  <div v-if="field.type === 'textarea'">
                    <textarea
                      :id="field.id"
                      v-model="formData[field.id] as string"
                      :required="field.required"
                      :placeholder="field.placeholder"
                      rows="4"
                      class="block w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl text-gray-900 focus:ring-4 focus:ring-indigo-50 focus:border-indigo-600 transition-all outline-none resize-none shadow-sm"
                    ></textarea>
                  </div>

                  <div v-else-if="field.type === 'select'">
                    <div class="relative">
                      <select
                        :id="field.id"
                        v-model="formData[field.id] as string"
                        :required="field.required"
                        class="block w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl text-gray-900 focus:ring-4 focus:ring-indigo-50 focus:border-indigo-600 transition-all outline-none appearance-none cursor-pointer shadow-sm"
                      >
                        <option value="" disabled>{{ field.placeholder || 'Select option...' }}</option>
                        <option v-for="opt in field.options" :key="opt.value" :value="opt.value">
                          {{ opt.label }}
                        </option>
                      </select>
                      <div class="absolute inset-y-0 right-5 flex items-center pointer-events-none text-gray-400">
                        <ChevronDown class="w-5 h-5" />
                      </div>
                    </div>
                  </div>

                  <div v-else>
                    <input
                      :id="field.id"
                      v-model="formData[field.id] as string"
                      :type="field.type"
                      :required="field.required"
                      :placeholder="field.placeholder"
                      class="block w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl text-gray-900 focus:ring-4 focus:ring-indigo-50 focus:border-indigo-600 transition-all outline-none shadow-sm"
                    />
                  </div>
                </div>
              </div>

              <!-- Submit Button -->
              <div class="pt-6">
                <button
                  type="submit"
                  :disabled="isSubmitting"
                  class="w-full flex items-center justify-center gap-3 bg-indigo-700 hover:bg-indigo-800 text-white px-8 py-5 rounded-2xl font-black text-lg shadow-xl shadow-indigo-200 transition-all active:scale-[0.98] disabled:opacity-70 disabled:cursor-not-allowed group"
                >
                  <Loader2 v-if="isSubmitting" class="w-6 h-6 animate-spin" />
                  <Send v-else class="w-6 h-6 group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform" />
                  {{ isSubmitting ? 'SUBMITTING...' : 'SUBMIT REQUEST' }}
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>
