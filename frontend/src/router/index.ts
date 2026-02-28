import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/login',
      name: 'Login',
      component: () => import('../views/Login.vue'),
      meta: { guest: true },
    },
    {
      path: '/register',
      name: 'Register',
      component: () => import('../views/Register.vue'),
      meta: { guest: true },
    },
    {
      path: '/',
      component: () => import('../layouts/AppLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'Dashboard',
          component: () => import('../views/Dashboard.vue'),
        },
        {
          path: 'requests',
          name: 'Requests',
          component: () => import('../views/Requests.vue'),
        },
        {
          path: 'requests/new',
          name: 'NewRequest',
          component: () => import('../views/NewRequest.vue'),
        },
        {
          path: 'requests/:id',
          name: 'RequestDetails',
          component: () => import('../views/RequestDetails.vue'),
        },
        {
          path: 'approvals',
          name: 'Approvals',
          component: () => import('../views/Approvals.vue'),
        },
        {
          path: 'admin/workflows',
          name: 'AdminWorkflows',
          component: () => import('../views/admin/Workflows.vue'),
          meta: { requiresAdmin: true },
        },
        {
          path: 'admin/workflows/guide',
          name: 'WorkflowGuide',
          component: () => import('../views/WorkflowGuide.vue'),
          meta: { requiresAdmin: true },
        },
        {
          path: 'admin/workflows/new',
          name: 'NewWorkflow',
          component: () => import('../views/admin/NewWorkflow.vue'),
          meta: { requiresAdmin: true },
        },
        {
          path: 'admin/workflows/:id/edit',
          name: 'EditWorkflow',
          component: () => import('../views/admin/NewWorkflow.vue'),
          meta: { requiresAdmin: true },
        },
        {
          path: 'admin/users',
          name: 'AdminUsers',
          component: () => import('../views/admin/Users.vue'),
          meta: { requiresAdmin: true },
        },
        {
          path: 'admin/roles',
          name: 'AdminRoles',
          component: () => import('../views/admin/Roles.vue'),
          meta: { requiresAdmin: true },
        },
      ],
    },
  ],
});

router.beforeEach(async (to, _from, next) => {
  const authStore = useAuthStore();
  
  if (authStore.isAuthenticated && !authStore.user) {
    await authStore.fetchUser();
  }

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login');
  } else if (to.meta.guest && authStore.isAuthenticated) {
    next('/');
  } else if (to.meta.requiresAdmin && !authStore.isAdmin) {
    next('/');
  } else {
    next();
  }
});

export default router;
