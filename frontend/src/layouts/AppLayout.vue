<script setup lang="ts">
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';
import {
  LayoutDashboard,
  FileText,
  CheckSquare,
  Users,
  Shield,
  LogOut,
  Workflow,
  Menu
} from 'lucide-vue-next';
import { ref, onMounted, onUnmounted } from 'vue';

const authStore = useAuthStore();
const router = useRouter();
const isCollapsed = ref(false);
const isMobileMenuOpen = ref(false);

const logout = () => {
  authStore.logout();
  router.push('/login');
};

const navItems = [
  { name: 'Dashboard', path: '/', icon: LayoutDashboard },
  { name: 'My Requests', path: '/requests', icon: FileText },
  { name: 'Pending Approvals', path: '/approvals', icon: CheckSquare },
];

const adminItems = [
  { name: 'Workflows', path: '/admin/workflows', icon: Workflow },
  { name: 'Users', path: '/admin/users', icon: Users },
  { name: 'Roles', path: '/admin/roles', icon: Shield },
];

// Close mobile menu on route change
router.afterEach(() => {
  isMobileMenuOpen.value = false;
});

// Auto-collapse sidebar on smaller desktop screens
const checkScreenSize = () => {
  isCollapsed.value = window.innerWidth < 1024;
};

onMounted(() => {
  checkScreenSize();
  window.addEventListener('resize', checkScreenSize);
});

onUnmounted(() => {
  window.removeEventListener('resize', checkScreenSize);
});
</script>

<template>
  <div class="flex h-screen w-full bg-slate-100 overflow-hidden relative">

    <!-- Mobile Header -->
    <header class="lg:hidden fixed top-0 left-0 right-0 h-16 bg-indigo-800 text-white flex items-center justify-between px-6 z-[60] shadow-md">
      <div class="flex items-center gap-3">
        <Workflow class="w-6 h-6" />
        <span class="text-xl font-black tracking-tighter uppercase leading-none">Orchestrator</span>
      </div>
      <button
        @click="isMobileMenuOpen = !isMobileMenuOpen"
        class="p-2 hover:bg-white/10 rounded-xl transition-colors"
      >
        <Menu v-if="!isMobileMenuOpen" class="w-6 h-6" />
      </button>
    </header>

    <!-- Sidebar Overlay (Mobile Only) -->
    <div
      v-if="isMobileMenuOpen"
      class="lg:hidden fixed inset-0 bg-indigo-950/60 backdrop-blur-sm z-[70] animate-in fade-in duration-300"
      @click="isMobileMenuOpen = false"
    ></div>

    <!-- Sidebar -->
    <aside
      class="fixed lg:static inset-y-0 left-0 flex flex-col bg-indigo-700 text-white shadow-2xl z-[80] flex-shrink-0 transition-all duration-300 ease-in-out"
      :class="[
        isCollapsed ? 'lg:w-24' : 'lg:w-72',
        isMobileMenuOpen ? 'translate-x-0 w-72' : '-translate-x-full lg:translate-x-0'
      ]"
    >
      <!-- Desktop Header Area -->
      <div class="p-6 hidden lg:flex items-center justify-between">
        <div v-if="!isCollapsed" class="flex items-center gap-3 animate-in fade-in slide-in-from-left-2">
          <Workflow class="w-8 h-8 text-white" />
          <span class="text-2xl font-black tracking-tighter uppercase leading-none">Orchestrator</span>
        </div>
        <div v-else class="mx-auto">
          <Workflow class="w-8 h-8 text-white" />
        </div>
      </div>

      <!-- Mobile Close Button -->
      <div class="lg:hidden p-6 flex items-center justify-between border-b border-white/10">
        <div class="flex items-center gap-3">
          <Workflow class="w-7 h-7 text-white" />
          <span class="text-xl font-black tracking-tighter uppercase">Orchestrator</span>
        </div>
        <button @click="isMobileMenuOpen = false" class="p-2 hover:bg-white/10 rounded-xl">
          <X class="w-6 h-6" />
        </button>
      </div>

      <!-- Nav Menu -->
      <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto scrollbar-hide">
        <router-link
          v-for="item in navItems"
          :key="item.path"
          :to="item.path"
          class="flex items-center gap-4 px-4 py-3.5 rounded-2xl hover:bg-white/10 transition-all font-bold text-sm group"
          :class="[
            isCollapsed ? 'lg:justify-center' : '',
            $route.path === item.path || ($route.path.startsWith(item.path) && item.path !== '/')
              ? 'bg-white/20 text-white shadow-lg shadow-indigo-800/20'
              : ''
          ]"
        >
          <component :is="item.icon" class="w-5 h-5 flex-shrink-0 group-hover:scale-110 transition-transform" />
          <span v-if="!isCollapsed || isMobileMenuOpen" class="whitespace-nowrap">{{ item.name }}</span>
        </router-link>

        <div v-if="authStore.isAdmin" class="pt-8">
          <p v-if="!isCollapsed || isMobileMenuOpen" class="px-4 text-[10px] font-black text-indigo-300 uppercase tracking-widest mb-4 opacity-60">Admin Engine</p>
          <div class="space-y-2">
            <router-link
              v-for="item in adminItems"
              :key="item.path"
              :to="item.path"
              class="flex items-center gap-4 px-4 py-3.5 rounded-2xl hover:bg-white/10 transition-all font-bold text-sm group"
              :class="[
                isCollapsed ? 'lg:justify-center' : '',
                $route.path.startsWith(item.path)
                  ? 'bg-white/20 text-white shadow-lg shadow-indigo-800/20'
                  : ''
              ]"
              :title="(isCollapsed && !isMobileMenuOpen) ? item.name : ''"
            >
              <component :is="item.icon" class="w-5 h-5 flex-shrink-0 group-hover:scale-110 transition-transform" />
              <span v-if="!isCollapsed || isMobileMenuOpen" class="whitespace-nowrap">{{ item.name }}</span>
            </router-link>
          </div>
        </div>
      </nav>

      <!-- Profile Bottom Section -->
      <div class="mt-auto p-4 bg-indigo-800/40 border-t border-white/5">
        <div class="flex items-center gap-4 p-3 rounded-2xl transition-all" :class="[(isCollapsed && !isMobileMenuOpen) ? 'lg:justify-center' : 'bg-white/5 shadow-inner']">
          <div class="w-10 h-10 rounded-xl bg-indigo-500 flex items-center justify-center text-lg font-black shadow-lg flex-shrink-0">
            {{ authStore.user?.name.charAt(0) }}
          </div>
          <div v-if="!isCollapsed || isMobileMenuOpen" class="overflow-hidden animate-in fade-in slide-in-from-bottom-2">
            <p class="text-sm font-black truncate leading-none mb-1">{{ authStore.user?.name }}</p>
            <p class="text-[9px] font-bold text-indigo-300 uppercase tracking-widest truncate opacity-70">{{ authStore.user?.roles?.[0]?.name }}</p>
          </div>
        </div>

        <button
          @click="logout"
          class="mt-4 flex items-center gap-4 w-full px-4 py-3.5 rounded-2xl hover:bg-red-500 text-white lg:text-indigo-200 lg:hover:text-white transition-all font-black text-[10px] uppercase tracking-widest group"
          :class="[(isCollapsed && !isMobileMenuOpen) ? 'lg:justify-center' : '']"
        >
          <LogOut class="w-4 h-4 group-hover:-translate-x-1 transition-transform" />
          <span v-if="!isCollapsed || isMobileMenuOpen">Sign Out</span>
        </button>
      </div>
    </aside>

    <!-- Content Area -->
    <main class="flex-1 h-screen overflow-y-auto bg-slate-100 flex flex-col scroll-smooth pt-16 lg:pt-0">
      <div class="p-6 md:p-8 lg:p-12 w-full max-w-7xl mx-auto">
        <router-view />
      </div>
    </main>
  </div>
</template>

