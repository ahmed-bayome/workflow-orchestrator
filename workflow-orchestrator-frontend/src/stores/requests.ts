import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'
import echo from '@/services/echo'
import type { WorkflowRequest } from '@/types'

export const useRequestsStore = defineStore('requests', () => {
  const requests = ref<WorkflowRequest[]>([])
  const currentRequest = ref<WorkflowRequest | null>(null)
  const loading = ref(false)

  async function fetchRequests() {
    loading.value = true
    try {
      const response = await api.get('/requests')
      requests.value = response.data
    } finally {
      loading.value = false
    }
  }

  async function fetchRequestDetails(id: number) {
    loading.value = true
    try {
      const response = await api.get(`/requests/${id}`)
      currentRequest.value = response.data
      
      // Listen to updates for this specific request
      echo.channel(`request.${id}`)
        .listen('RequestUpdated', (e: any) => {
          currentRequest.value = e.request
        })
        .listen('StepUpdated', (e: any) => {
          if (currentRequest.value) {
            const stepIndex = currentRequest.value.steps.findIndex(
              (s: any) => s.id === e.step.id
            )
            if (stepIndex !== -1) {
              currentRequest.value.steps[stepIndex] = e.step
            }
          }
        })
    } finally {
      loading.value = false
    }
  }

  async function createRequest(workflowDefinitionId: number, payload: any) {
    const response = await api.post('/requests', {
      workflow_definition_id: workflowDefinitionId,
      payload,
    })
    requests.value.unshift(response.data)
    return response.data
  }

  function updateRequestRealtime(updatedRequest: WorkflowRequest) {
    const index = requests.value.findIndex(r => r.id === updatedRequest.id)
    if (index !== -1) {
      requests.value[index] = updatedRequest
    }
    if (currentRequest.value?.id === updatedRequest.id) {
      currentRequest.value = updatedRequest
    }
  }

  function cleanupListeners(requestId: number) {
    echo.leave(`request.${requestId}`)
  }

  return {
    requests,
    currentRequest,
    loading,
    fetchRequests,
    fetchRequestDetails,
    createRequest,
    updateRequestRealtime,
    cleanupListeners,
  }
})
