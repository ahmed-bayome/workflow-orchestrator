export interface User {
  id: number
  name: string
  email: string
  is_active: boolean
  roles?: Role[]
}

export interface Role {
  id: number
  name: string
  users?: User[]
}

export interface FormField {
  id: string
  label: string
  type: 'text' | 'textarea' | 'number' | 'email' | 'date' | 'select' | 'radio' | 'checkbox' | 'file'
  required: boolean
  placeholder?: string
  options?: { value: string; label: string }[]
  validation?: any
}

export interface FormSchema {
  fields: FormField[]
}

export interface WorkflowStep {
  id: string
  name: string
  role_id: number
  execution_group: string
  approval_mode: 'any' | 'all'
  order: number
}

export interface WorkflowDefinition {
  id: number
  name: string
  description?: string
  form_schema: FormSchema
  steps: WorkflowStep[]
  is_active: boolean
  version: number
  created_by: number
  creator?: User
  created_at: string
  updated_at: string
}

export interface RequestStep {
  id: number
  request_id: number
  step_definition: WorkflowStep
  role_id: number
  role?: Role
  execution_group: string
  order: number
  approval_mode: 'any' | 'all'
  status: 'pending' | 'approved' | 'rejected' | 'skipped'
  required_approvers_snapshot: number[]
  completed_at?: string
  actions: StepAction[]
}

export interface StepAction {
  id: number
  request_step_id: number
  user_id: number
  user?: User
  action: 'approve' | 'reject'
  comment?: string
  created_at: string
}

export interface WorkflowRequest {
  id: number
  workflow_definition_id: number
  workflow_definition?: WorkflowDefinition
  workflow_snapshot: {
    name: string
    form_schema: FormSchema
    steps: WorkflowStep[]
  }
  requester_id: number
  requester?: User
  payload: any
  status: 'pending' | 'in_progress' | 'approved' | 'rejected' | 'failed'
  current_execution_group?: string
  completed_at?: string
  created_at: string
  updated_at: string
  steps: RequestStep[]
}
