import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'
import echo from '@/services/echo'
import type { PendingApproval, WorkflowRequest } from '@/types'

export const useApprovalsStore = defineStore('approvals', () => {
  const pendingApprovals = ref<PendingApproval[]>([])
  const loading = ref(false)

  async function fetchPendingApprovals() {
    loading.value = true
    try {
      const response = await api.get<PendingApproval[]>('/approvals/pending')
      pendingApprovals.value = response.data
    } finally {
      loading.value = false
    }
  }

  function subscribeToUpdates() {
    echo.private('requests')
      .listen('RequestUpdated', (_e: { request: WorkflowRequest }) => {
        fetchPendingApprovals()
      })
  }

  function unsubscribeFromUpdates() {
    echo.leave('requests')
  }

  async function approveStep(requestId: number, stepId: number, comment?: string) {
    await api.post(`/requests/${requestId}/steps/${stepId}/approve`, { comment })
    await fetchPendingApprovals()
  }

  async function rejectStep(requestId: number, stepId: number, comment: string) {
    await api.post(`/requests/${requestId}/steps/${stepId}/reject`, { comment })
    await fetchPendingApprovals()
  }

  return {
    pendingApprovals,
    loading,
    fetchPendingApprovals,
    subscribeToUpdates,
    unsubscribeFromUpdates,
    approveStep,
    rejectStep,
  }
})
