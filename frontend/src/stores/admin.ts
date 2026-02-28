import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'
import type { User, Role, WorkflowDefinition } from '@/types'

export const useAdminStore = defineStore('admin', () => {
  const users = ref<User[]>([])
  const roles = ref<Role[]>([])
  const workflows = ref<WorkflowDefinition[]>([])

  async function fetchUsers() {
    const response = await api.get('/admin/users')
    users.value = response.data
  }

  async function createUser(userData: any) {
    const response = await api.post('/admin/users', userData)
    users.value.push(response.data)
  }

  async function updateUser(id: number, userData: any) {
    const response = await api.put(`/admin/users/${id}`, userData)
    const index = users.value.findIndex(u => u.id === id)
    if (index !== -1) {
      users.value[index] = response.data
    }
  }

  async function deleteUser(id: number) {
    await api.delete(`/admin/users/${id}`)
    users.value = users.value.filter(u => u.id !== id)
  }

  async function fetchRoles() {
    const response = await api.get('/admin/roles')
    roles.value = response.data
  }

  async function createRole(name: string) {
    const response = await api.post('/admin/roles', { name })
    roles.value.push(response.data)
  }

  async function deleteRole(id: number) {
    await api.delete(`/admin/roles/${id}`)
    roles.value = roles.value.filter(r => r.id !== id)
  }

  async function fetchWorkflows() {
    const response = await api.get('/admin/workflows')
    workflows.value = response.data
  }

  async function createWorkflow(workflowData: any) {
    const response = await api.post('/admin/workflows', workflowData)
    workflows.value.push(response.data)
  }

  async function updateWorkflow(id: number, workflowData: any) {
    const response = await api.put(`/admin/workflows/${id}`, workflowData)
    const index = workflows.value.findIndex(w => w.id === id)
    if (index !== -1) {
      workflows.value[index] = response.data
    }
  }

  return {
    users,
    roles,
    workflows,
    fetchUsers,
    createUser,
    updateUser,
    deleteUser,
    fetchRoles,
    createRole,
    deleteRole,
    fetchWorkflows,
    createWorkflow,
    updateWorkflow,
  }
})
