<script setup lang="ts">
import { ref, onMounted } from 'vue';
import api from '../services/api';
import { 
  FileText, 
  Search, 
  PlusCircle, 
  Filter,
  Calendar,
  ChevronRight,
  AlertCircle
} from 'lucide-vue-next';
import { format } from 'date-fns';

const requests = ref([]);
const filteredRequests = ref([]);
const searchQuery = ref('');
const statusFilter = ref('all');
const isLoading = ref(true);

const fetchRequests = async () => {
  try {
    const response = await api.get('/requests');
    requests.value = response.data;
    filterRequests();
  } catch (error) {
    console.error('Error fetching requests:', error);
  } finally {
    isLoading.value = false;
  }
};

const filterRequests = () => {
  filteredRequests.value = requests.value.filter((request: any) => {
    const matchesSearch = request.workflow_definition?.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                          request.status.toLowerCase().includes(searchQuery.value.toLowerCase());
    const matchesStatus = statusFilter.value === 'all' || request.status === statusFilter.value;
    return matchesSearch && matchesStatus;
  });
};

onMounted(fetchRequests);

const getStatusStyles = (status: string) => {
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
  <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">My Requests</h1>
        <p class="text-gray-500 mt-1">Manage and track your workflow submissions.</p>
      </div>
      <router-link
        to="/requests/new"
        class="inline-flex items-center gap-2 bg-indigo-700 hover:bg-indigo-800 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-indigo-100 transition-all active:scale-[0.98]"
      >
        <PlusCircle class="w-5 h-5" />
        New Request
      </router-link>
    </div>

    <!-- Filters and Search -->
    <div class="flex flex-col md:flex-row gap-4 bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
      <div class="flex-1 relative">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
          <Search class="w-5 h-5" />
        </div>
        <input
          v-model="searchQuery"
          @input="filterRequests"
          type="text"
          placeholder="Search requests..."
          class="block w-full pl-11 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition-all outline-none text-sm"
        />
      </div>
      <div class="flex items-center gap-2">
        <Filter class="w-5 h-5 text-gray-400" />
        <select
          v-model="statusFilter"
          @change="filterRequests"
          class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-indigo-600 focus:border-indigo-600 block w-full md:w-44 p-2.5 transition-all outline-none"
        >
          <option value="all">All Statuses</option>
          <option value="pending">Pending</option>
          <option value="in_progress">In Progress</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
        </select>
      </div>
    </div>

    <!-- Requests List -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <div v-if="isLoading" class="p-12 flex flex-col items-center justify-center gap-4">
        <div class="w-10 h-10 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
        <p class="text-gray-500 font-medium">Loading requests...</p>
      </div>

      <div v-else-if="filteredRequests.length === 0" class="p-20 text-center text-gray-500">
        <div class="bg-indigo-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 text-indigo-200">
          <FileText class="w-10 h-10" />
        </div>
        <h3 class="text-xl font-bold text-gray-900">No requests found</h3>
        <p class="mt-2 text-gray-500 max-w-xs mx-auto">Try changing your filters or create a new request to get started.</p>
        <router-link
          to="/requests/new"
          class="mt-6 inline-flex items-center text-indigo-700 font-bold hover:text-indigo-800 transition-colors"
        >
          Create your first request <ChevronRight class="w-4 h-4 ml-1" />
        </router-link>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-gray-50 border-b border-gray-100">
              <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Workflow</th>
              <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Submitted By</th>
              <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Date</th>
              <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr
              v-for="request in filteredRequests"
              :key="request.id"
              @click="$router.push(`/requests/${request.id}`)"
              class="group hover:bg-indigo-50/30 transition-all cursor-pointer"
            >
              <td class="px-6 py-5">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 font-black text-xs shadow-sm group-hover:scale-110 transition-transform">
                    {{ request.workflow_definition?.name.charAt(0) }}
                  </div>
                  <span class="text-sm font-black text-gray-900">{{ request.workflow_definition?.name }}</span>
                </div>
              </td>
              <td class="px-6 py-5">
                <div class="flex items-center gap-2">
                  <div class="w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-600 border border-slate-200">
                    {{ request.requester?.name.charAt(0) }}
                  </div>
                  <span class="text-sm font-bold text-slate-600">{{ request.requester?.name }}</span>
                </div>
              </td>
              <td class="px-6 py-5">
                <div class="flex items-center gap-2 text-slate-500 text-xs font-bold uppercase tracking-tighter">
                  <Calendar class="w-3.5 h-3.5" />
                  {{ format(new Date(request.created_at), 'MMM dd, yyyy') }}
                </div>
              </td>
              <td class="px-6 py-5">
                <span :class="['px-3 py-1 text-[10px] font-black uppercase rounded-lg border', getStatusStyles(request.status)]">
                  {{ request.status.replace('_', ' ') }}
                </span>
              </td>
              <td class="px-6 py-5 text-right">
                <div class="inline-flex items-center gap-1.5 text-indigo-700 font-black text-xs uppercase tracking-widest group-hover:translate-x-1 transition-transform">
                  View Details
                  <ChevronRight class="w-4 h-4" />
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
