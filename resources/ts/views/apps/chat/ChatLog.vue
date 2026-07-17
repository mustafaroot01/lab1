<script lang="ts" setup>
import { useChatStore } from '@/views/apps/chat/useChatStore'

const store = useChatStore()

interface MessageGroup {
  senderId: number
  isAdmin: boolean
  isSystem?: boolean
  dateDivider?: string
  messages: any[]
}

const formatDateDivider = (iso: string) => {
  if (!iso) return ''
  const d = new Date(iso)
  const now = new Date()
  if (d.toDateString() === now.toDateString()) return 'اليوم'
  const yesterday = new Date(now)
  yesterday.setDate(now.getDate() - 1)
  if (d.toDateString() === yesterday.toDateString()) return 'أمس'
  return d.toLocaleDateString('ar', { day: 'numeric', month: 'long', year: 'numeric' })
}

const formatTime = (iso: string) => {
  if (!iso) return ''
  return new Date(iso).toLocaleTimeString('ar', { hour: 'numeric', minute: 'numeric' })
}

const msgGroups = computed(() => {
  const messages = store.activeChat?.messages || []
  if (!messages.length) return []

  const groups: MessageGroup[] = []
  let currentSenderId = messages[0].sender_id
  let lastDateStr = new Date(messages[0].created_at || '').toDateString()

  let currentGroup: MessageGroup = {
    senderId: currentSenderId,
    isAdmin: messages[0].is_admin,
    isSystem: messages[0].is_system,
    dateDivider: formatDateDivider(messages[0].created_at || ''),
    messages: [messages[0]],
  }

  if (messages.length === 1) return [currentGroup]

  messages.slice(1).forEach((msg, index) => {
    const msgDateStr = new Date(msg.created_at || '').toDateString()
    const isNewDate = msgDateStr !== lastDateStr

    if (!isNewDate && msg.sender_id === currentSenderId && msg.is_system === currentGroup.isSystem) {
      currentGroup.messages.push(msg)
    }
    else {
      groups.push(currentGroup)
      currentSenderId = msg.sender_id
      currentGroup = {
        senderId: currentSenderId,
        isAdmin: msg.is_admin,
        isSystem: msg.is_system,
        dateDivider: isNewDate ? formatDateDivider(msg.created_at || '') : undefined,
        messages: [msg],
      }
      if (isNewDate) lastDateStr = msgDateStr
    }
    if (index === messages.length - 2)
      groups.push(currentGroup)
  })

  return groups
})
</script>

<template>
  <div class="chat-log">
    <!-- Load older messages -->
    <div v-if="store.hasMoreMessages" class="d-flex justify-center py-4">
      <VBtn
        v-if="!store.loadingMore"
        variant="tonal"
        size="small"
        color="secondary"
        prepend-icon="tabler-chevron-up"
        rounded="pill"
        @click="store.loadOlderMessages()"
      >
        رسائل أقدم
      </VBtn>
      <VProgressCircular v-else indeterminate size="22" width="2" color="primary" />
    </div>

    <!-- Message groups -->
    <div
      v-for="(grp, index) in msgGroups"
      :key="grp.senderId + String(index)"
    >
      <!-- Date Divider -->
      <div v-if="grp.dateDivider" class="date-divider">
        <div class="date-divider__line" />
        <span class="date-divider__label">{{ grp.dateDivider }}</span>
        <div class="date-divider__line" />
      </div>

      <!-- System Event -->
      <div v-if="grp.isSystem" class="system-event">
        <span class="system-event__pill">
          <VIcon icon="tabler-info-circle" size="13" />
          {{ grp.messages[0].body }}
          <span class="system-event__time">{{ formatTime(grp.messages[0].created_at) }}</span>
        </span>
      </div>

      <!-- Chat Bubble Group -->
      <div
        v-else
        class="msg-row"
        :class="grp.isAdmin ? 'msg-row--admin' : 'msg-row--patient'"
      >
        <!-- Avatar (pinned to bottom of group) -->
        <VAvatar
          size="32"
          variant="tonal"
          :color="grp.isAdmin ? 'primary' : 'secondary'"
          class="msg-avatar"
        >
          <span class="text-xs font-weight-bold">
            {{ avatarText(grp.isAdmin
              ? (grp.messages[0].sender_admin?.name || 'أدمن')
              : (store.activeChat?.conversation.patient.name || '؟')) }}
          </span>
        </VAvatar>

        <!-- Bubble stack -->
        <div class="msg-stack">
          <!-- Sender name (admin only) -->
          <span v-if="grp.isAdmin && grp.messages[0].sender_admin?.name" class="msg-sender-name">
            {{ grp.messages[0].sender_admin.name }}
          </span>

          <!-- Individual bubbles -->
          <div
            v-for="(msg, mIdx) in grp.messages"
            :key="msg.id"
            class="bubble"
            :class="[
              grp.isAdmin ? 'bubble--admin' : 'bubble--patient',
              mIdx === 0 && grp.isAdmin ? 'bubble--r-first' : '',
              mIdx === 0 && !grp.isAdmin ? 'bubble--l-first' : '',
              mIdx === grp.messages.length - 1 && grp.isAdmin ? 'bubble--r-last' : '',
              mIdx === grp.messages.length - 1 && !grp.isAdmin ? 'bubble--l-last' : '',
            ]"
          >
            <!-- Text -->
            <p v-if="msg.body" class="bubble__text">{{ msg.body }}</p>

            <!-- Image -->
            <a
              v-if="msg.attachment?.type === 'image'"
              :href="msg.attachment.url"
              target="_blank"
              class="d-block mt-1"
            >
              <VImg :src="msg.attachment.url" max-height="180" max-width="260" cover class="rounded-lg" />
            </a>

            <!-- File -->
            <a
              v-else-if="msg.attachment"
              :href="msg.attachment.url"
              target="_blank"
              class="bubble__file"
              :class="grp.isAdmin ? 'bubble__file--admin' : 'bubble__file--patient'"
            >
              <VIcon icon="tabler-file-description" size="20" class="flex-shrink-0" />
              <span class="bubble__file-name">{{ msg.attachment.name || 'ملف مرفق' }}</span>
              <VIcon icon="tabler-download" size="16" class="ms-auto flex-shrink-0 opacity-60" />
            </a>
          </div>

          <!-- Timestamp + read tick -->
          <div class="msg-meta" :class="grp.isAdmin ? 'msg-meta--admin' : ''">
            <span class="msg-time">{{ formatTime(grp.messages.at(-1)?.created_at) }}</span>
            <VIcon
              v-if="grp.isAdmin"
              :icon="(store.activeChat?.conversation.patient_last_read_message_id && grp.messages.at(-1)?.id <= store.activeChat.conversation.patient_last_read_message_id) ? 'tabler-checks' : 'tabler-check'"
              size="13"
              :class="(store.activeChat?.conversation.patient_last_read_message_id && grp.messages.at(-1)?.id <= store.activeChat.conversation.patient_last_read_message_id) ? 'text-info' : 'text-disabled'"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.chat-log {
  padding: 16px 20px;
  display: flex;
  flex-direction: column;
  gap: 2px;

  // ─── Date Divider ───────────────────────────────────
  .date-divider {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-block: 20px 16px;

    &__line {
      flex: 1;
      height: 1px;
      background: rgba(var(--v-border-color), 0.15);
    }

    &__label {
      font-size: 0.7rem;
      font-weight: 600;
      color: rgba(var(--v-theme-on-surface), 0.4);
      white-space: nowrap;
      background: rgba(var(--v-theme-on-surface), 0.04);
      border: 1px solid rgba(var(--v-border-color), 0.15);
      border-radius: 20px;
      padding: 3px 12px;
    }
  }

  // ─── System Event Pill ───────────────────────────────
  .system-event {
    display: flex;
    justify-content: center;
    margin-block: 10px;

    &__pill {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-size: 0.72rem;
      color: rgba(var(--v-theme-on-surface), 0.5);
      background: rgba(var(--v-theme-on-surface), 0.05);
      border: 1px solid rgba(var(--v-border-color), 0.15);
      border-radius: 20px;
      padding: 4px 12px;
    }

    &__time {
      opacity: 0.6;
      margin-inline-start: 2px;
    }
  }

  // ─── Message Row ─────────────────────────────────────
  .msg-row {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    margin-block-end: 8px;

    // Patient: avatar left, stack right
    &--patient {
      flex-direction: row;
    }

    // Admin: avatar right, stack left (visually right via flex-row-reverse)
    &--admin {
      flex-direction: row-reverse;
    }
  }

  .msg-avatar {
    flex-shrink: 0;
    align-self: flex-end;
    margin-block-end: 18px; // align with bottom of bubble, above timestamp
  }

  // ─── Bubble Stack ────────────────────────────────────
  .msg-stack {
    display: flex;
    flex-direction: column;
    gap: 2px;
    // Critical: prevent overflow
    min-width: 0;
    max-width: min(68%, 440px);
  }

  .msg-sender-name {
    font-size: 0.68rem;
    font-weight: 600;
    color: rgba(var(--v-theme-on-surface), 0.45);
    text-align: end;
    padding-inline: 4px;
    margin-block-end: 2px;
  }

  // ─── Bubble ──────────────────────────────────────────
  .bubble {
    padding: 8px 12px;
    border-radius: 14px;
    // Force text to wrap - critical fix
    word-break: break-word;
    overflow-wrap: anywhere;

    &__text {
      margin: 0;
      font-size: 0.875rem;
      line-height: 1.5;
    }

    // Patient bubble (incoming)
    &--patient {
      background: rgb(var(--v-theme-surface));
      color: rgba(var(--v-theme-on-surface), 0.88);
      border: 1px solid rgba(var(--v-border-color), 0.2);
      box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    // Admin bubble (outgoing)
    &--admin {
      background: rgb(var(--v-theme-primary));
      color: #fff;
      box-shadow: 0 2px 5px rgba(var(--v-theme-primary), 0.35);
    }

    // Corner shaping - first bubble in group (near avatar)
    &--l-first { border-start-start-radius: 4px; }
    &--r-first { border-start-end-radius: 4px; }
    // Last bubble in group
    &--l-last  { border-end-start-radius: 4px; }
    &--r-last  { border-end-end-radius: 4px; }

    // File attachment inside bubble
    &__file {
      display: flex;
      align-items: center;
      gap: 8px;
      border-radius: 8px;
      padding: 7px 10px;
      margin-block-start: 6px;
      text-decoration: none;
      transition: opacity 0.15s;

      &:hover { opacity: 0.8; }

      &--patient {
        background: rgba(var(--v-theme-on-surface), 0.05);
        border: 1px solid rgba(var(--v-border-color), 0.25);
        color: rgba(var(--v-theme-on-surface), 0.8);
      }

      &--admin {
        background: rgba(255, 255, 255, 0.15);
        color: #fff;
      }
    }

    &__file-name {
      font-size: 0.8rem;
      font-weight: 500;
      flex: 1;
      min-width: 0;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
  }

  // ─── Timestamp + Read Tick ───────────────────────────
  .msg-meta {
    display: flex;
    align-items: center;
    gap: 3px;
    padding-inline: 3px;
    margin-block-start: 2px;

    &--admin {
      justify-content: flex-end;
    }
  }

  .msg-time {
    font-size: 0.68rem;
    color: rgba(var(--v-theme-on-surface), 0.38);
  }
}
</style>
