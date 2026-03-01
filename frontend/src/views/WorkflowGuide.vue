<script setup lang="ts">
import { 
  Workflow, 
  Layers, 
  GitMerge, 
  Zap, 
  Send, 
  Users,
  ChevronRight,
  Info
} from 'lucide-vue-next';
</script>

<template>
  <div class="max-w-4xl mx-auto space-y-12 animate-in fade-in slide-in-from-bottom-4 duration-500 pb-20">
    <!-- Header -->
    <div class="text-center space-y-4">
      <div class="inline-flex items-center justify-center p-4 bg-indigo-600 rounded-[2rem] text-white shadow-2xl shadow-indigo-200 mb-4">
        <Workflow class="w-10 h-10" />
      </div>
      <h1 class="text-4xl font-black text-slate-900 tracking-tight">How Orchestrator Works</h1>
      <p class="text-slate-500 font-medium text-lg max-w-2xl mx-auto italic">
        A complete A-to-Z guide on architecting, submitting, and processing dynamic workflows.
      </p>
    </div>

    <!-- The Guide Grid -->
    <div class="grid grid-cols-1 gap-12">
      
      <!-- Step 1: Definition -->
      <section class="relative pl-12 md:pl-20 group">
        <div class="absolute left-0 top-0 w-10 h-10 md:w-14 md:h-14 bg-indigo-100 rounded-2xl flex items-center justify-center text-indigo-600 font-black text-xl border-2 border-indigo-200 z-10 group-hover:scale-110 transition-transform">1</div>
        <div class="absolute left-5 md:left-7 top-14 bottom-[-48px] w-0.5 bg-slate-200"></div>
        
        <div class="bg-white rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50 border border-slate-200 space-y-6">
          <div class="flex items-center gap-3 text-slate-900">
            <Layers class="w-6 h-6 text-indigo-500" />
            <h2 class="text-2xl font-black">Dynamic Form Schema</h2>
          </div>
          <p class="text-slate-600 leading-relaxed">
            Every workflow starts with a **Schema**. This defines the data required from the requester. You can add text fields, number inputs, or dropdown menus. These fields are rendered dynamically when a user starts a new request.
          </p>
          <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 flex items-center gap-4 text-xs font-bold text-slate-500">
            <Info class="w-4 h-4 text-indigo-400" />
            Tip: Use "Required" flags to ensure data integrity.
          </div>
        </div>
      </section>

      <!-- Step 2: The Pipeline -->
      <section class="relative pl-12 md:pl-20 group">
        <div class="absolute left-0 top-0 w-10 h-10 md:w-14 md:h-14 bg-indigo-100 rounded-2xl flex items-center justify-center text-indigo-600 font-black text-xl border-2 border-indigo-200 z-10 group-hover:scale-110 transition-transform">2</div>
        <div class="absolute left-5 md:left-7 top-14 bottom-[-48px] w-0.5 bg-slate-200"></div>
        
        <div class="bg-white rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50 border border-slate-200 space-y-8">
          <div class="flex items-center gap-3 text-slate-900">
            <GitMerge class="w-6 h-6 text-indigo-500" />
            <h2 class="text-2xl font-black">Approval Pipeline & Logic</h2>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-3">
              <h3 class="font-black text-indigo-600 uppercase text-[10px] tracking-widest">Execution Groups</h3>
              <p class="text-sm text-slate-600">
                Steps in the **same group** run in parallel. A request only moves to group 2 after **all** required steps in group 1 are complete.
              </p>
            </div>
            <div class="space-y-3">
              <h3 class="font-black text-indigo-600 uppercase text-[10px] tracking-widest">Approval Modes</h3>
              <p class="text-sm text-slate-600">
                <strong>ANY:</strong> Only one person in the role needs to approve.<br>
                <strong>ALL:</strong> Every assigned person must approve.
              </p>
            </div>
          </div>
        </div>
      </section>

      <!-- Step 3: Action -->
      <section class="relative pl-12 md:pl-20 group">
        <div class="absolute left-0 top-0 w-10 h-10 md:w-14 md:h-14 bg-indigo-100 rounded-2xl flex items-center justify-center text-indigo-600 font-black text-xl border-2 border-indigo-200 z-10 group-hover:scale-110 transition-transform">3</div>
        <div class="absolute left-5 md:left-7 top-14 bottom-[-48px] w-0.5 bg-slate-200"></div>
        
        <div class="bg-white rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50 border border-slate-200 space-y-6">
          <div class="flex items-center gap-3 text-slate-900">
            <Users class="w-6 h-6 text-indigo-500" />
            <h2 class="text-2xl font-black">Submission & Review</h2>
          </div>
          <p class="text-slate-600 leading-relaxed">
            Once submitted, the system takes a **Snapshot** of the current workflow. Even if you change the workflow definition later, active requests will follow the rules that existed at the time of submission.
          </p>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="flex items-center gap-3 p-4 bg-indigo-50 rounded-2xl border border-indigo-100">
              <Send class="w-5 h-5 text-indigo-600" />
              <span class="text-xs font-bold text-indigo-900">Instant Notifications</span>
            </div>
            <div class="flex items-center gap-3 p-4 bg-green-50 rounded-2xl border border-green-100">
              <Zap class="w-5 h-5 text-green-600" />
              <span class="text-xs font-bold text-green-900">Real-time Dashboard</span>
            </div>
          </div>
        </div>
      </section>

      <!-- Final Step -->
      <section class="relative pl-12 md:pl-20 group">
        <div class="absolute left-0 top-0 w-10 h-10 md:w-14 md:h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white font-black text-xl border-2 border-indigo-700 z-10 shadow-lg group-hover:scale-110 transition-transform">✓</div>
        
        <div class="bg-indigo-900 rounded-[2.5rem] p-8 shadow-2xl shadow-indigo-300/50 text-white space-y-6">
          <h2 class="text-2xl font-black">Completion</h2>
          <p class="text-indigo-100/80 leading-relaxed">
            When the final group is approved, the request is marked as **Approved**. If any step is rejected, the entire request is rejected immediately, and the pipeline stops.
          </p>
          <router-link to="/admin/workflows/new" class="inline-flex items-center gap-2 bg-white text-indigo-700 px-6 py-3 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-indigo-50 transition-all active:scale-95">
            Start Building
            <ChevronRight class="w-4 h-4" />
          </router-link>
        </div>
      </section>

    </div>
  </div>
</template>
