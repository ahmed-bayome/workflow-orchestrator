<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../services/api';
import { 
  ChevronLeft, 
  Save, 
  Plus, 
  Trash2, 
  Settings,
  GripVertical,
  Layers,
  Activity,
  PlusCircle,
  X,
  ChevronDown,
  Info,
  ExternalLink
} from 'lucide-vue-next';

const router = useRouter();
const roles = ref([]);
const name = ref('');
const description = ref('');
const isSubmitting = ref(false);
const error = ref('');

const fieldTypes = [
  { label: 'Text Input', value: 'text' },
  { label: 'Number Input', value: 'number' },
  { label: 'Dropdown Select', value: 'select' },
  { label: 'Textarea', value: 'textarea' },
];

const fields = ref([
  { id: 'field_' + Date.now(), label: '', type: 'text', required: false, options: [] }
]);

const steps = ref([
  { id: 'step_' + Date.now(), name: '', role_id: null, approval_mode: 'any', execution_group: 1 }
]);

const fetchRoles = async () => {
  try {
    const response = await api.get('/admin/roles');
    roles.value = response.data;
  } catch (err) {
    console.error('Error fetching roles:', err);
  }
};

const addField = () => {
  fields.value.push({
    id: 'field_' + Date.now(),
    label: '',
    type: 'text',
    required: false,
    options: []
  });
};

const removeField = (index: number) => {
  fields.value.splice(index, 1);
};

const addOption = (fieldIndex: number) => {
  if (!fields.value[fieldIndex].options) {
    fields.value[fieldIndex].options = [];
  }
  fields.value[fieldIndex].options.push({ label: '', value: '' });
};

const removeOption = (fieldIndex: number, optionIndex: number) => {
  fields.value[fieldIndex].options.splice(optionIndex, 1);
};

const addStep = () => {
  const lastGroup = steps.value.length > 0 ? steps.value[steps.value.length - 1].execution_group : 1;
  steps.value.push({
    id: 'step_' + Date.now(),
    name: '',
    role_id: null,
    approval_mode: 'any',
    execution_group: lastGroup
  });
};

const removeStep = (index: number) => {
  steps.value.splice(index, 1);
};

const saveWorkflow = async () => {
  isSubmitting.value = true;
  error.value = '';

  try {
    await api.post('/admin/workflows', {
      name: name.value,
      description: description.value,
      form_schema: { fields: fields.value },
      steps: steps.value
    });
    router.push('/admin/workflows');
  } catch (err: any) {
    if (err.response?.data?.errors) {
      const errors = err.response.data.errors;
      error.value = Object.values(errors).flat().join(', ');
    } else {
      error.value = err.response?.data?.message || 'Failed to save workflow. Please check your inputs.';
    }
  } finally {
    isSubmitting.value = false;
  }
};

onMounted(fetchRoles);
</script>

<template>
  <div class="max-w-5xl mx-auto space-y-10 animate-in fade-in slide-in-from-bottom-4 duration-500 pb-20">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-200">
      <div class="flex items-center gap-4">
        <button 
          @click="router.back()" 
          class="p-2.5 rounded-xl hover:bg-slate-100 transition-colors text-slate-400 hover:text-slate-900 border border-transparent hover:border-slate-200 shadow-sm"
        >
          <ChevronLeft class="w-6 h-6" />
        </button>
        <div>
          <h1 class="text-3xl font-black text-slate-900 tracking-tight leading-none mb-2">Workflow Builder</h1>
          <p class="text-slate-500 font-medium italic">Architecting the future of automation.</p>
        </div>
      </div>
      
      <button 
        @click="saveWorkflow"
        :disabled="isSubmitting"
        class="inline-flex items-center justify-center gap-2 bg-indigo-700 hover:bg-indigo-800 text-white px-8 py-4 rounded-2xl font-black shadow-lg shadow-indigo-100 transition-all active:scale-[0.98] disabled:opacity-70 disabled:cursor-not-allowed text-sm uppercase tracking-widest"
      >
        <Save class="w-5 h-5" />
        {{ isSubmitting ? 'Saving...' : 'Save Workflow' }}
      </button>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="p-6 bg-red-50 border-2 border-red-100 text-red-600 rounded-[2rem] flex items-center gap-4 animate-shake shadow-lg shadow-red-100/50">
      <div class="bg-red-600 p-2 rounded-xl text-white shadow-md">
        <X class="w-5 h-5" />
      </div>
      <span class="font-black text-sm uppercase tracking-tight">{{ error }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
      
      <!-- Main Builder Area -->
      <div class="lg:col-span-8 space-y-10">
        
        <!-- 1. Basic Config (Top) -->
        <section class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-xl shadow-slate-200/50 border border-slate-200">
          <div class="flex items-center gap-4 mb-8">
            <div class="bg-indigo-600 p-3 rounded-2xl text-white shadow-lg shadow-indigo-100">
              <Settings class="w-6 h-6" />
            </div>
            <h2 class="text-2xl font-black text-slate-900">Basic Configuration</h2>
          </div>
          
          <div class="space-y-6">
            <div>
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Workflow Name</label>
              <input 
                v-model="name" 
                type="text" 
                placeholder="e.g. Purchase Request" 
                class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-lg font-bold focus:ring-4 focus:ring-indigo-50 focus:border-indigo-500 outline-none transition-all shadow-inner"
              />
            </div>
            <div>
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Description</label>
              <textarea 
                v-model="description" 
                rows="3" 
                placeholder="What is this workflow for?" 
                class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-base font-medium focus:ring-4 focus:ring-indigo-50 focus:border-indigo-500 outline-none transition-all resize-none shadow-inner"
              ></textarea>
            </div>
          </div>
        </section>

        <!-- 2. Form Schema -->
        <section class="space-y-6">
          <div class="flex items-center justify-between px-2">
            <div class="flex items-center gap-4">
              <div class="bg-indigo-600 p-3 rounded-2xl text-white shadow-lg shadow-indigo-100">
                <Layers class="w-6 h-6" />
              </div>
              <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Dynamic Form Schema</h2>
            </div>
            <button 
              @click="addField"
              class="inline-flex items-center gap-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 px-5 py-2.5 rounded-xl font-black transition-all text-xs uppercase tracking-widest border border-indigo-100 shadow-sm"
            >
              <PlusCircle class="w-4 h-4" />
              Add Field
            </button>
          </div>

          <div class="space-y-4">
            <div 
              v-for="(field, index) in fields" 
              :key="field.id" 
              class="bg-white rounded-[2rem] p-6 md:p-8 border border-slate-200 shadow-xl shadow-slate-100/50 flex flex-col gap-6 animate-in zoom-in-95 duration-300 relative group"
            >
              <div class="flex items-center justify-between border-b border-slate-50 pb-4">
                <div class="flex items-center gap-3">
                  <div class="bg-slate-100 p-2 rounded-lg text-slate-400">
                    <GripVertical class="w-4 h-4" />
                  </div>
                  <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Field #{{ index + 1 }}</span>
                </div>
                <button 
                  @click="removeField(index)"
                  class="p-2 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all"
                  title="Remove Field"
                >
                  <Trash2 class="w-5 h-5" />
                </button>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-6">
                <div class="lg:col-span-4">
                  <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 block ml-1">Field Label</label>
                  <input v-model="field.label" type="text" placeholder="e.g. Total Amount" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-indigo-50 focus:border-indigo-500 outline-none transition-all" />
                </div>
                
                <div class="lg:col-span-4">
                  <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 block ml-1">Input Type</label>
                  <select v-model="field.type" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-indigo-50 focus:border-indigo-500 outline-none transition-all cursor-pointer">
                    <option v-for="t in fieldTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
                  </select>
                </div>

                <div class="lg:col-span-4 flex items-center pt-6">
                  <label class="flex items-center gap-3 cursor-pointer group/toggle">
                    <div class="relative inline-flex items-center">
                      <input type="checkbox" v-model="field.required" class="sr-only peer">
                      <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                    </div>
                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Required Field</span>
                  </label>
                </div>
              </div>

              <!-- Options Editor for Select -->
              <div v-if="field.type === 'select'" class="mt-2 p-6 bg-slate-50 rounded-3xl border-2 border-dashed border-slate-200 animate-in fade-in slide-in-from-top-2 duration-300">
                <div class="flex items-center justify-between mb-6">
                  <div class="flex items-center gap-2">
                    <div class="bg-indigo-100 p-1.5 rounded-lg text-indigo-600">
                      <ChevronDown class="w-4 h-4" />
                    </div>
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Dropdown Configuration</label>
                  </div>
                  <button @click="addOption(index)" class="text-[10px] font-black uppercase tracking-widest text-indigo-600 hover:text-white bg-white hover:bg-indigo-600 px-4 py-2 rounded-xl border border-indigo-100 transition-all shadow-sm">
                    + Add Option
                  </button>
                </div>
                
                <div class="space-y-3">
                  <div v-if="!field.options || field.options.length === 0" class="text-xs text-slate-400 font-medium italic text-center py-4 bg-white/50 rounded-2xl border border-slate-100">
                    No options defined yet. Click "Add Option" to begin.
                  </div>
                  <div v-for="(opt, optIndex) in field.options" :key="optIndex" class="flex gap-3 items-center group/opt animate-in slide-in-from-left-2 duration-200">
                    <div class="w-8 h-8 rounded-xl bg-white border border-slate-100 text-slate-400 flex items-center justify-center text-[10px] font-black shadow-sm">
                      {{ optIndex + 1 }}
                    </div>
                    <input v-model="opt.label" placeholder="Display Label" class="flex-1 px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold focus:ring-4 focus:ring-indigo-50 focus:border-indigo-500 outline-none transition-all shadow-sm" />
                    <input v-model="opt.value" placeholder="Internal Value" class="flex-1 px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold focus:ring-4 focus:ring-indigo-50 focus:border-indigo-500 outline-none transition-all shadow-sm" />
                    <button @click="removeOption(index, optIndex)" class="p-2.5 text-slate-300 hover:text-red-500 hover:bg-white rounded-xl transition-all shadow-sm opacity-0 group-hover/opt:opacity-100">
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- 3. Approval Pipeline -->
        <section class="space-y-6">
          <div class="flex items-center justify-between px-2">
            <div class="flex items-center gap-4">
              <div class="bg-indigo-600 p-3 rounded-2xl text-white shadow-lg shadow-indigo-100">
                <Activity class="w-6 h-6" />
              </div>
              <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Approval Pipeline</h2>
            </div>
            <button 
              @click="addStep"
              class="inline-flex items-center gap-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 px-5 py-2.5 rounded-xl font-black transition-all text-xs uppercase tracking-widest border border-indigo-100 shadow-sm"
            >
              <PlusCircle class="w-4 h-4" />
              Add Step
            </button>
          </div>

          <div class="space-y-4">
            <div v-for="(step, index) in steps" :key="step.id" class="flex items-start gap-6 group">
              <!-- Step Indicator -->
              <div class="flex flex-col items-center gap-2 pt-2">
                <div class="w-10 h-10 rounded-full bg-indigo-700 text-white flex items-center justify-center font-black text-sm shadow-lg shadow-indigo-100 border-2 border-white ring-4 ring-indigo-50">
                  {{ index + 1 }}
                </div>
                <div v-if="index < steps.length - 1" class="w-0.5 h-full min-h-[4rem] bg-indigo-100 rounded-full"></div>
              </div>

              <!-- Step Card -->
              <div class="flex-1 bg-white rounded-[2rem] p-6 border border-slate-200 shadow-xl shadow-slate-100/50 flex flex-wrap lg:flex-nowrap items-end gap-6 relative">
                <div class="flex-1 min-w-[150px]">
                  <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Step Label</label>
                  <input v-model="step.name" placeholder="e.g. Manager Review" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-xs font-bold focus:ring-4 focus:ring-indigo-50 focus:border-indigo-500 outline-none transition-all" />
                </div>

                <div class="flex-1 min-w-[150px]">
                  <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Approver Role</label>
                  <select v-model="step.role_id" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-xs font-bold focus:ring-4 focus:ring-indigo-50 focus:border-indigo-500 outline-none transition-all">
                    <option :value="null">Select Role..</option>
                    <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                  </select>
                </div>

                <div class="flex-1 min-w-[150px]">
                  <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Approval Mode</label>
                  <select v-model="step.approval_mode" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-xs font-bold focus:ring-4 focus:ring-indigo-50 focus:border-indigo-500 outline-none transition-all">
                    <option value="any">ANY (Fastest)</option>
                    <option value="all">ALL (Consensus)</option>
                  </select>
                </div>

                <div class="w-24">
                  <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Group</label>
                  <input v-model.number="step.execution_group" type="number" min="1" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-xs font-bold focus:ring-4 focus:ring-indigo-50 focus:border-indigo-500 outline-none transition-all text-center" />
                </div>

                <button 
                  @click="removeStep(index)"
                  class="p-2.5 text-slate-200 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all"
                >
                  <Trash2 class="w-5 h-5" />
                </button>
              </div>
            </div>
          </div>
        </section>
      </div>

      <!-- Right Sidebar: Quick Tips -->
      <div class="lg:col-span-4 space-y-8">
        <section class="bg-indigo-900 rounded-[2.5rem] p-8 text-white shadow-2xl shadow-indigo-200 border border-white/10 sticky top-8 overflow-hidden group">
          <Workflow class="absolute -right-8 -bottom-8 w-40 h-40 text-white opacity-5 rotate-12 group-hover:scale-110 transition-transform duration-1000" />
          
          <div class="relative z-10 space-y-8">
            <div class="flex items-center gap-3">
              <div class="bg-white/10 p-2 rounded-xl">
                <Info class="w-6 h-6 text-indigo-300" />
              </div>
              <h3 class="text-xl font-black">Quick Guide</h3>
            </div>

            <div class="space-y-6">
              <div class="flex gap-4">
                <div class="w-6 h-6 rounded-full bg-indigo-500 flex items-center justify-center text-[10px] font-black flex-shrink-0 mt-1">1</div>
                <p class="text-sm font-medium text-indigo-100/80 leading-relaxed italic">
                  "Create multiple steps in the same execution group to run them in parallel."
                </p>
              </div>
              <div class="flex gap-4">
                <div class="w-6 h-6 rounded-full bg-indigo-500 flex items-center justify-center text-[10px] font-black flex-shrink-0 mt-1">2</div>
                <p class="text-sm font-medium text-indigo-100/80 leading-relaxed italic">
                  "ANY mode: first approval advances. ALL mode: everyone must agree."
                </p>
              </div>
            </div>

            <router-link 
              to="/admin/workflows/guide" 
              class="w-full flex items-center justify-between px-6 py-4 bg-white/10 hover:bg-white text-white hover:text-indigo-900 rounded-2xl transition-all font-black text-xs uppercase tracking-widest group/btn border border-white/10"
            >
              Full A-Z Guide
              <ExternalLink class="w-4 h-4 group-hover/btn:translate-x-1 group-hover/btn:-translate-y-1 transition-transform" />
            </router-link>
          </div>
        </section>
      </div>

    </div>
  </div>
</template>

<style scoped>
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  10%, 30%, 50%, 70%, 90% { transform: translateX(-2px); }
  20%, 40%, 60%, 80% { transform: translateX(2px); }
}
.animate-shake {
  animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
}
</style>
