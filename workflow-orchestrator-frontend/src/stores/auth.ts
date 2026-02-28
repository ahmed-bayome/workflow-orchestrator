import { defineStore } from 'pinia';
import api from '../services/api';

interface User {
  id: number;
  name: string;
  email: string;
  roles: { id: number; name: string }[];
}

interface AuthState {
  user: User | null;
  token: string | null;
  loading: boolean;
  error: string | null;
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    user: null,
    token: localStorage.getItem('token'),
    loading: false,
    error: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    isAdmin: (state) => state.user?.roles.some((role) => role.name === 'Admin'),
    isEmployee: (state) => state.user?.roles.some((role) => role.name === 'Employee'),
  },

  actions: {
    async fetchUser() {
      if (!this.token) return;
      try {
        const response = await api.get('/auth/me');
        this.user = response.data;
      } catch (err: any) {
        this.logout();
      }
    },

    async login(credentials: any) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.post('/auth/login', credentials);
        this.token = response.data.token;
        this.user = response.data.user;
        localStorage.setItem('token', this.token as string);
        return true;
      } catch (err: any) {
        this.error = err.response?.data?.error || 'Login failed';
        return false;
      } finally {
        this.loading = false;
      }
    },

    async register(data: any) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.post('/auth/register', data);
        this.token = response.data.token;
        this.user = response.data.user;
        localStorage.setItem('token', this.token as string);
        return true;
      } catch (err: any) {
        this.error = err.response?.data?.errors || 'Registration failed';
        return false;
      } finally {
        this.loading = false;
      }
    },

    logout() {
      this.user = null;
      this.token = null;
      localStorage.removeItem('token');
      window.location.href = '/login';
    },
  },
});
