import { $api } from '@/utils/api'
import { getEcho } from '@/plugins/echo'

type Conversation = {
  id: number
  status: 'open' | 'closed'
  patient: {
    id: number
    name: string
    phone: string
    district?: string
    area?: string
    orders_count?: number
  }
  assigned_to?: {
    id: number
    name: string
  } | null
  assigned_at?: string | null
  is_assigned?: boolean
  closed_at: string | null
  closed_by: string | null
  created_at?: string
  unread_count?: number
  last_message_preview?: string
  last_message_at?: string
  admin_last_read_message_id?: number
  patient_last_read_message_id?: number
}

type ChatMessage = {
  id: number
  conversation_id: number
  sender_id: number
  is_admin: boolean
  is_system?: boolean
  sender_name: string
  body: string | null
  attachment: {
    url: string
    type: 'image' | 'pdf'
    name: string
    size: number
    mime: string
  } | null
  edited_at: string | null
  created_at: string
}

type ActiveChat = {
  conversation: Conversation
  messages: ChatMessage[]
  patient_history?: Conversation[]
  patient_history_total_count?: number
} | null

interface State {
  conversations: Conversation[]
  activeChat: ActiveChat
  loading: boolean
  sending: boolean
  loadingMore: boolean
  hasMoreConversations: boolean
  conversationsCursor: string | null
  messagesCursor: string | null
  hasMoreMessages: boolean
  listening: boolean
  cannedResponses: { id: number; title: string; body: string }[]
  archiveTickets: Conversation[]
  archiveTotalCount: number
  archiveCursor: string | null
  archiveHasMore: boolean
  archiveLoading: boolean
}

export const useChatStore = defineStore('chat', {
  state: (): State => ({
    conversations: [],
    activeChat: null,
    loading: false,
    sending: false,
    loadingMore: false,
    hasMoreConversations: false,
    conversationsCursor: null,
    messagesCursor: null,
    hasMoreMessages: false,
    listening: false,
    cannedResponses: [],
    archiveTickets: [],
    archiveTotalCount: 0,
    archiveCursor: null,
    archiveHasMore: false,
    archiveLoading: false,
  }),
  actions: {
    async fetchConversations(params?: { q?: string; status?: string; assigned_status?: string }) {
      this.initRealtimeListeners()
      this.fetchCannedResponses()
      this.loading = true
      this.conversationsCursor = null
      try {
        const res = await $api<{ status: boolean; conversations: Conversation[]; meta: { next_cursor?: string; has_more: boolean } }>('/admin/chat', {
          params: params || {},
        })
        if (res?.status) {
          this.conversations = res.conversations || []
          this.conversationsCursor = res.meta?.next_cursor || null
          this.hasMoreConversations = res.meta?.has_more || false
        }
      } catch (e) {
        console.error('fetchConversations error:', e)
      } finally {
        this.loading = false
      }
    },

    async fetchCannedResponses() {
      if (this.cannedResponses.length > 0) return
      try {
        const res = await $api<{ status: boolean; responses: { id: number; title: string; body: string }[] }>('/admin/chat/canned-responses')
        if (res?.status && res.responses) {
          this.cannedResponses = res.responses
        }
      } catch (e) {
        console.error('fetchCannedResponses error:', e)
      }
    },

    async loadMoreConversations(params?: { q?: string; status?: string; assigned_status?: string }) {
      if (!this.hasMoreConversations || this.loadingMore || !this.conversationsCursor) return
      this.loadingMore = true
      try {
        const res = await $api<{ status: boolean; conversations: Conversation[]; meta: { next_cursor?: string; has_more: boolean } }>('/admin/chat', {
          params: { ...params, cursor: this.conversationsCursor },
        })
        if (res?.status) {
          this.conversations.push(...(res.conversations || []))
          this.conversationsCursor = res.meta?.next_cursor || null
          this.hasMoreConversations = res.meta?.has_more || false
        }
      } catch (e) {
        console.error('loadMoreConversations error:', e)
      } finally {
        this.loadingMore = false
      }
    },

    async openConversation(conversationId: number) {
      this.loading = true
      this.messagesCursor = null
      try {
        const res = await $api<{ status: boolean; conversation: Conversation; messages: ChatMessage[]; patient_history?: Conversation[]; patient_history_total_count?: number; meta: { next_cursor?: string; has_more: boolean } }>(`/admin/chat/${conversationId}`)
        if (res?.status) {
          this.activeChat = {
            conversation: res.conversation,
            messages: res.messages || [],
            patient_history: res.patient_history || [],
            patient_history_total_count: res.patient_history_total_count || 0,
          }
          this.messagesCursor = res.meta?.next_cursor || null
          this.hasMoreMessages = res.meta?.has_more || false
          // تعليم كمقروء
          await $api(`/admin/chat/${conversationId}/read`, { method: 'POST' })
          // تحديث unread_count في القائمة أو إدراجها في حال لم تكن معروضة في الفلتر الحالي
          const conv = this.conversations.find(c => c.id === conversationId)
          if (conv) {
            conv.unread_count = 0
          } else {
            this.conversations.unshift(res.conversation)
          }
        }
      } catch (e) {
        console.error('[Chat] openConversation error:', e)
      } finally {
        this.loading = false
      }
    },

    async loadOlderMessages() {
      if (!this.activeChat || !this.hasMoreMessages || !this.messagesCursor || this.loadingMore) return
      this.loadingMore = true
      try {
        const res = await $api<{ status: boolean; messages: ChatMessage[]; meta: { next_cursor?: string; has_more: boolean } }>(`/admin/chat/${this.activeChat.conversation.id}/messages`, {
          params: { cursor: this.messagesCursor },
        })
        if (res?.status) {
          // prepend الرسائل الأقدم
          this.activeChat.messages = [...(res.messages || []), ...this.activeChat.messages]
          this.messagesCursor = res.meta?.next_cursor || null
          this.hasMoreMessages = res.meta?.has_more || false
        }
      } catch (e) {
        console.error('loadOlderMessages error:', e)
      } finally {
        this.loadingMore = false
      }
    },

    async sendMsg(body: string, attachment?: File) {
      if (!this.activeChat || (!body && !attachment)) return
      this.sending = true
      try {
        const formData = new FormData()
        if (body) formData.append('body', body)
        if (attachment) formData.append('attachment', attachment)

        const res = await $api<{ status: boolean; data: ChatMessage }>(`/admin/chat/${this.activeChat.conversation.id}/send`, {
          method: 'POST',
          body: formData,
        })
        if (res?.status && res.data) {
          this.activeChat.messages.push(res.data)
          // تحديث preview في القائمة
          const conv = this.conversations.find(c => c.id === this.activeChat!.conversation.id)
          if (conv) {
            conv.last_message_preview = body || 'مرفق'
            conv.last_message_at = res.data.created_at
          }
        }
      } catch (e) {
        console.error('sendMsg error:', e)
      } finally {
        this.sending = false
      }
    },

    async closeConversation() {
      if (!this.activeChat) return
      try {
        const res = await $api<{ status: boolean; data: Conversation }>(`/admin/chat/${this.activeChat.conversation.id}/close`, {
          method: 'POST',
        })
        if (res?.status && res.data) {
          this.activeChat.conversation.status = 'closed'
          this.activeChat.conversation.closed_at = res.data.closed_at
          const conv = this.conversations.find(c => c.id === this.activeChat!.conversation.id)
          if (conv) {
            conv.status = 'closed'
            conv.closed_at = res.data.closed_at
          }
          // مزامنة سجل التذاكر والبيانات
          await this.openConversation(this.activeChat.conversation.id)
        }
      } catch (e) {
        console.error('closeConversation error:', e)
      }
    },

    async reopenConversation() {
      if (!this.activeChat) return
      try {
        const res = await $api<{ status: boolean; data: Conversation }>(`/admin/chat/${this.activeChat.conversation.id}/reopen`, {
          method: 'POST',
        })
        if (res?.status && res.data) {
          this.activeChat.conversation.status = 'open'
          this.activeChat.conversation.closed_at = null
          const conv = this.conversations.find(c => c.id === this.activeChat!.conversation.id)
          if (conv) {
            conv.status = 'open'
            conv.closed_at = null
          }
          // مزامنة سجل التذاكر والبيانات
          await this.openConversation(this.activeChat.conversation.id)
        }
      } catch (e) {
        console.error('reopenConversation error:', e)
      }
    },

    async claimConversation() {
      if (!this.activeChat) return
      try {
        const res = await $api<{ status: boolean; data: Conversation }>(`/admin/chat/${this.activeChat.conversation.id}/claim`, {
          method: 'POST',
        })
        if (res?.status && res.data) {
          this.activeChat.conversation.assigned_to = res.data.assigned_to
          this.activeChat.conversation.assigned_at = res.data.assigned_at
          this.activeChat.conversation.is_assigned = res.data.is_assigned
          const conv = this.conversations.find(c => c.id === this.activeChat!.conversation.id)
          if (conv) {
            conv.assigned_to = res.data.assigned_to
            conv.is_assigned = res.data.is_assigned
          }
          // إعادة جلب المحادثة لتحديث رسائل النظام
          await this.openConversation(this.activeChat.conversation.id)
        }
      } catch (e) {
        console.error('claimConversation error:', e)
      }
    },

    async claimConversationById(conversationId: number) {
      try {
        const res = await $api<{ status: boolean; data: Conversation }>(`/admin/chat/${conversationId}/claim`, {
          method: 'POST',
        })
        if (res?.status && res.data) {
          const conv = this.conversations.find(c => c.id === conversationId)
          if (conv) {
            conv.assigned_to = res.data.assigned_to
            conv.is_assigned = res.data.is_assigned
          }
          if (this.activeChat && this.activeChat.conversation.id === conversationId) {
            this.activeChat.conversation.assigned_to = res.data.assigned_to
            this.activeChat.conversation.is_assigned = res.data.is_assigned
          }
        }
      } catch (e) {
        console.error('claimConversationById error:', e)
      }
    },

    async fetchPatientArchive(patientId: number, params?: { q?: string; status?: string }) {
      this.archiveLoading = true
      this.archiveCursor = null
      try {
        const res = await $api<{ status: boolean; history: Conversation[]; meta: { next_cursor?: string; has_more: boolean; total_count: number } }>(`/admin/chat/patient/${patientId}/history`, {
          params: params || {},
        })
        if (res?.status) {
          this.archiveTickets = res.history || []
          this.archiveCursor = res.meta?.next_cursor || null
          this.archiveHasMore = res.meta?.has_more || false
          this.archiveTotalCount = res.meta?.total_count || 0
        }
      } catch (e) {
        console.error('fetchPatientArchive error:', e)
      } finally {
        this.archiveLoading = false
      }
    },

    async loadMorePatientArchive(patientId: number, params?: { q?: string; status?: string }) {
      if (!this.archiveHasMore || this.archiveLoading || !this.archiveCursor) return
      this.archiveLoading = true
      try {
        const res = await $api<{ status: boolean; history: Conversation[]; meta: { next_cursor?: string; has_more: boolean; total_count: number } }>(`/admin/chat/patient/${patientId}/history`, {
          params: { ...params, cursor: this.archiveCursor },
        })
        if (res?.status) {
          this.archiveTickets.push(...(res.history || []))
          this.archiveCursor = res.meta?.next_cursor || null
          this.archiveHasMore = res.meta?.has_more || false
          this.archiveTotalCount = res.meta?.total_count || 0
        }
      } catch (e) {
        console.error('loadMorePatientArchive error:', e)
      } finally {
        this.archiveLoading = false
      }
    },

    initRealtimeListeners() {
      if (this.listening) return
      const echo = getEcho()
      if (!echo) return

      this.listening = true

      echo.private('private-admin-chat')
        .listen('.MessageCreated', (data: { message: ChatMessage; conversation: Conversation }) => {
          if (!data || !data.message || !data.conversation) return

          // إذا كانت المحادثة مفتوحة أمام المشرف الآن
          if (this.activeChat && this.activeChat.conversation.id === data.message.conversation_id) {
            const exists = this.activeChat.messages.some(m => m.id === data.message.id)
            if (!exists) {
              this.activeChat.messages.push(data.message)
            }
            this.activeChat.conversation.last_message_at = data.conversation.last_message_at
            this.activeChat.conversation.last_message_preview = data.conversation.last_message_preview
          }

          // تحديث قائمة المحادثات الجانبية
          const convIndex = this.conversations.findIndex(c => c.id === data.conversation.id)
          if (convIndex !== -1) {
            const conv = this.conversations[convIndex]
            conv.last_message_at = data.conversation.last_message_at
            conv.last_message_preview = data.conversation.last_message_preview

            // زيادة العداد فقط إذا لم يرسلها المشرف ولم يكن فاتحاً لهذه المحادثة
            if (!data.message.is_admin && (!this.activeChat || this.activeChat.conversation.id !== conv.id)) {
              conv.unread_count = (conv.unread_count || 0) + 1
            }

            // نقل المحادثة إلى أعلى القائمة
            this.conversations.splice(convIndex, 1)
            this.conversations.unshift(conv)
          } else {
            // محادثة جديدة كلياً، تضاف لأعلى القائمة
            this.conversations.unshift(data.conversation)
          }
        })
        .listen('.ConversationAssigned', (data: { conversation: Conversation }) => {
          if (!data || !data.conversation) return

          if (this.activeChat && this.activeChat.conversation.id === data.conversation.id) {
            this.activeChat.conversation.assigned_to = data.conversation.assigned_to
            this.activeChat.conversation.assigned_at = data.conversation.assigned_at
            this.activeChat.conversation.is_assigned = data.conversation.is_assigned
          }

          const conv = this.conversations.find(c => c.id === data.conversation.id)
          if (conv) {
            conv.assigned_to = data.conversation.assigned_to
            conv.is_assigned = data.conversation.is_assigned
          }
        })
        .listen('.ConversationRead', (data: { conversation_id: number; admin_last_read_message_id?: number; patient_last_read_message_id?: number }) => {
          if (!data || !data.conversation_id) return

          if (this.activeChat && this.activeChat.conversation.id === data.conversation_id) {
            if (data.admin_last_read_message_id !== undefined)
              this.activeChat.conversation.admin_last_read_message_id = data.admin_last_read_message_id
            if (data.patient_last_read_message_id !== undefined)
              this.activeChat.conversation.patient_last_read_message_id = data.patient_last_read_message_id
          }

          const conv = this.conversations.find(c => c.id === data.conversation_id)
          if (conv) {
            if (data.admin_last_read_message_id !== undefined)
              conv.admin_last_read_message_id = data.admin_last_read_message_id
            if (data.patient_last_read_message_id !== undefined)
              conv.patient_last_read_message_id = data.patient_last_read_message_id
          }
        })
    },

    stopRealtimeListeners() {
      if (!this.listening) return
      const echo = getEcho()
      if (echo) {
        echo.leave('private-admin-chat')
      }
      this.listening = false
    },
  },
})
