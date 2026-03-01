import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'
import type { PendingApproval } from '@/types'

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
    approveStep,
    rejectStep,
  }
})
