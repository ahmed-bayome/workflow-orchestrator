import { defineStore } from 'pinia'
import api from '../services/api'
import type { AxiosError } from 'axios'
import type { User, LoginCredentials, RegisterData, ApiError } from '@/types'

interface AuthState {
  user: User | null
  token: string | null
  loading: boolean
  error: string | null
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
    isAdmin: (state) => state.user?.roles?.some((role) => role.name === 'Admin'),
    isEmployee: (state) => state.user?.roles?.some((role) => role.name === 'Employee'),
  },

  actions: {
    async fetchUser() {
      if (!this.token) return
      try {
        const response = await api.get<User>('/auth/me')
        this.user = response.data
      } catch {
        this.logout()
      }
    },

    async login(credentials: LoginCredentials) {
      this.loading = true
      this.error = null
      try {
        const response = await api.post<{ token: string; user: User }>('/auth/login', credentials)
        this.token = response.data.token
        this.user = response.data.user
        localStorage.setItem('token', this.token)
        return true
      } catch (err) {
        const axiosError = err as AxiosError<ApiError>
        this.error = axiosError.response?.data?.message || 'Login failed'
        return false
      } finally {
        this.loading = false
      }
    },

    async register(data: RegisterData) {
      this.loading = true
      this.error = null
      try {
        const response = await api.post<{ token: string; user: User }>('/auth/register', data)
        this.token = response.data.token
        this.user = response.data.user
        localStorage.setItem('token', this.token)
        return true
      } catch (err) {
        const axiosError = err as AxiosError<ApiError>
        this.error = axiosError.response?.data?.message || 'Registration failed'
        return false
      } finally {
        this.loading = false
      }
    },

    logout() {
      this.user = null
      this.token = null
      localStorage.removeItem('token')
      window.location.href = '/login'
    },
  },
})
