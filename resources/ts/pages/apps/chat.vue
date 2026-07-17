<script lang="ts" setup>
import { onUnmounted } from 'vue'
import { themes } from '@/plugins/vuetify/theme'
import ChatActiveChatUserProfileSidebarContent from '@/views/apps/chat/ChatActiveChatUserProfileSidebarContent.vue'
import ChatLeftSidebarContent from '@/views/apps/chat/ChatLeftSidebarContent.vue'
import ChatLog from '@/views/apps/chat/ChatLog.vue'
import { useChat } from '@/views/apps/chat/useChat'
import { useChatStore } from '@/views/apps/chat/useChatStore'
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { useDisplay, useTheme } from 'vuetify'

definePage({
  meta: {
    layoutWrapperClasses: 'layout-content-height-fixed',
  },
})

// composables
const vuetifyDisplays = useDisplay()
const store = useChatStore()
const { isLeftSidebarOpen } = useResponsiveLeftSidebar(vuetifyDisplays.smAndDown)
const { resolveStatusColor } = useChat()

// Perfect scrollbar
const chatLogPS = ref()

const scrollToBottomInChatLog = () => {
  const scrollEl = chatLogPS.value?.$el || chatLogPS.value

  if (scrollEl)
    scrollEl.scrollTop = scrollEl.scrollHeight
}

// Auto-load older messages when scrolling near top
const onChatScroll = () => {
  const scrollEl = chatLogPS.value?.$el || chatLogPS.value
  if (!scrollEl) return

  // عند الوصول لأعلى 100px — حمّل رسائل أقدم
  if (scrollEl.scrollTop < 100 && store.hasMoreMessages && !store.loadingMore) {
    const prevHeight = scrollEl.scrollHeight

    store.loadOlderMessages().then(() => {
      // حافظ على موضع الـ scroll بعد إضافة الرسائل
      nextTick(() => {
        scrollEl.scrollTop = scrollEl.scrollHeight - prevHeight
      })
    })
  }
}

// Search query
const q = ref('')
const filterStatus = ref('')
const filterAssigned = ref('')

watch(
  [q, filterStatus, filterAssigned],
  ([searchVal, statusVal, assignedVal]) => {
    store.fetchConversations({ q: searchVal, status: statusVal, assigned_status: assignedVal })
  },
  { immediate: true },
)

// Open Sidebar in smAndDown when "start conversation" is clicked
const startConversation = () => {
  if (vuetifyDisplays.mdAndUp.value)
    return
  isLeftSidebarOpen.value = true
}

// Chat message
const msg = ref('')
const selectedFile = ref<File | null>(null)

const sendMessage = async () => {
  if (!msg.value && !selectedFile.value)
    return

  await store.sendMsg(msg.value, selectedFile.value || undefined)

  // Reset
  msg.value = ''
  selectedFile.value = null

  // Scroll to bottom
  nextTick(() => {
    scrollToBottomInChatLog()
  })
}

const openConversation = async (conversationId: number) => {
  await store.openConversation(conversationId)

  // مزامنة تبويب الفلترة إذا كانت المحادثة المنتقل إليها في حالة مختلفة (مثلاً عند فتح محادثة مغلقة من السجل)
  if (store.activeChat) {
    const status = store.activeChat.conversation.status
    if (filterStatus.value && filterStatus.value !== status) {
      filterStatus.value = status
    }
  }

  // Reset message input
  msg.value = ''
  selectedFile.value = null

  // if smAndDown =>  Close Chat & Contacts left sidebar
  if (vuetifyDisplays.smAndDown.value)
    isLeftSidebarOpen.value = false

  // Scroll to bottom
  nextTick(() => {
    scrollToBottomInChatLog()
  })
}

// Active chat user profile sidebar
const isActiveChatUserProfileSidebarOpen = ref(false)

// file input
const refInputEl = ref<HTMLElement>()

const onFileChange = (e: Event) => {
  const target = e.target as HTMLInputElement
  if (target.files && target.files[0])
    selectedFile.value = target.files[0]
}

const isClosed = computed(() => store.activeChat?.conversation.status === 'closed')

const toggleCloseReopen = async () => {
  if (isClosed.value)
    await store.reopenConversation()
  else
    await store.closeConversation()
}

const { name } = useTheme()

const chatContentContainerBg = computed(() => {
  let color = 'transparent'

  if (themes)
    color = themes?.[name.value].colors?.background as string

  return color
})

onUnmounted(() => {
  store.stopRealtimeListeners()
})
</script>

<template>
  <VLayout
    class="chat-app-layout"
    style="z-index: 0;"
  >
    <!-- 👉 Active Chat sidebar -->
    <VNavigationDrawer
      v-model="isActiveChatUserProfileSidebarOpen"
      data-allow-mismatch
      width="374"
      absolute
      temporary
      location="end"
      touchless
      class="active-chat-user-profile-sidebar"
    >
      <ChatActiveChatUserProfileSidebarContent
        @close="isActiveChatUserProfileSidebarOpen = false"
        @open-conversation="(id) => { isActiveChatUserProfileSidebarOpen = false; openConversation(id); }"
      />
    </VNavigationDrawer>

    <!-- 👉 Left sidebar   -->
    <VNavigationDrawer
      v-model="isLeftSidebarOpen"
      data-allow-mismatch
      absolute
      touchless
      location="start"
      width="370"
      :temporary="$vuetify.display.smAndDown"
      class="chat-list-sidebar"
      :permanent="$vuetify.display.mdAndUp"
    >
      <ChatLeftSidebarContent
        v-model:is-drawer-open="isLeftSidebarOpen"
        v-model:search="q"
        v-model:filter-status="filterStatus"
        v-model:filter-assigned="filterAssigned"
        @open-conversation="openConversation"
        @close="isLeftSidebarOpen = false"
      />
    </VNavigationDrawer>

    <!-- 👉 Chat content -->
    <VMain class="chat-content-container">
      <!-- 👉 Right content: Active Chat -->
      <div
        v-if="store.activeChat"
        class="d-flex flex-column h-100"
      >
        <!-- 👉 Active chat header -->
        <div class="active-chat-header d-flex align-center justify-space-between bg-surface px-5 py-3 border-b">
          <div class="d-flex align-center gap-3">
            <IconBtn
              class="d-md-none me-1"
              @click="isLeftSidebarOpen = true"
            >
              <VIcon icon="tabler-menu-2" />
            </IconBtn>

            <div
              class="d-flex align-center cursor-pointer gap-3"
              @click="isActiveChatUserProfileSidebarOpen = true"
            >
              <div class="position-relative flex-shrink-0">
                <VAvatar
                  size="40"
                  variant="tonal"
                  color="primary"
                >
                  <span class="text-body-1 font-weight-bold">{{ avatarText(store.activeChat.conversation.patient.name) }}</span>
                </VAvatar>
                <span
                  class="position-absolute bottom-0 end-0 rounded-circle border border-surface d-block"
                  :class="store.activeChat.conversation.status === 'open' ? 'bg-success' : 'bg-secondary'"
                  style="width: 10px; height: 10px;"
                />
              </div>

              <div class="overflow-hidden">
                <div class="d-flex align-center gap-2 mb-0.5">
                  <h6 class="text-body-1 font-weight-bold mb-0 text-high-emphasis">
                    {{ store.activeChat.conversation.patient.name }}
                  </h6>
                  <span class="text-xs font-weight-medium text-disabled">#{{ store.activeChat.conversation.id }}</span>
                  <span
                    class="badge-status text-xs font-weight-bold px-2 py-0.5 rounded-pill"
                    :class="store.activeChat.conversation.status === 'open' ? 'bg-success text-white' : 'bg-secondary text-white'"
                  >
                    {{ store.activeChat.conversation.status === 'open' ? '🟢 مفتوحة' : '🔒 مغلقة' }}
                  </span>
                </div>
                <div class="d-flex align-center gap-3 text-caption text-medium-emphasis">
                  <span dir="ltr" class="d-flex align-center gap-1">
                    <VIcon icon="tabler-phone" size="14" class="text-primary" />
                    {{ store.activeChat.conversation.patient.phone }}
                  </span>
                  <span v-if="store.activeChat.conversation.patient.district" class="d-flex align-center gap-1">
                    <VIcon icon="tabler-map-pin" size="14" class="text-primary" />
                    {{ store.activeChat.conversation.patient.district }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Right actions bar -->
          <div class="d-flex align-center gap-2 flex-wrap justify-end">
            <!-- Ticket History Shortcut -->
            <VBtn
              v-if="(store.activeChat.patient_history_total_count || 0) > 0 || (store.activeChat.patient_history && store.activeChat.patient_history.length > 0)"
              variant="tonal"
              color="info"
              size="small"
              prepend-icon="tabler-history"
              class="font-weight-bold px-3"
              @click="isActiveChatUserProfileSidebarOpen = true"
            >
              سجل التذاكر ({{ store.activeChat.patient_history_total_count ?? store.activeChat.patient_history?.length }})
            </VBtn>

            <VDivider vertical class="mx-1" style="height: 24px;" />

            <!-- Assigned Staff or Claim Button -->
            <div class="d-flex align-center">
              <span v-if="store.activeChat.conversation.is_assigned" class="assigned-badge text-xs font-weight-medium d-flex align-center gap-1.5 px-3 py-1 rounded-pill bg-var-theme-background border">
                <VIcon icon="tabler-user-check" size="16" class="text-primary" />
                المستلم: {{ store.activeChat.conversation.assigned_to?.name || 'مستلمة' }}
              </span>
              <VBtn
                v-else
                color="primary"
                variant="elevated"
                size="small"
                prepend-icon="tabler-hand-grab"
                @click="store.claimConversation()"
              >
                استلام التذكرة
              </VBtn>
            </div>

            <!-- Close / Reopen Action Button -->
            <VBtn
              :color="isClosed ? 'success' : 'secondary'"
              variant="outlined"
              size="small"
              :prepend-icon="isClosed ? 'tabler-lock-open' : 'tabler-lock'"
              @click="toggleCloseReopen"
            >
              {{ isClosed ? 'إعادة فتح' : 'إغلاق التذكرة' }}
            </VBtn>

            <IconBtn title="تفاصيل المريض والأرشيف" @click="isActiveChatUserProfileSidebarOpen = true">
              <VIcon icon="tabler-info-circle" />
            </IconBtn>
          </div>
        </div>

        <!-- Chat log -->
        <PerfectScrollbar
          ref="chatLogPS"
          tag="ul"
          :options="{ wheelPropagation: false }"
          class="flex-grow-1"
          @scroll="onChatScroll"
        >
          <ChatLog />
        </PerfectScrollbar>

        <!-- Closed banner -->
        <div v-if="isClosed" class="pa-3 text-center text-error border-t font-weight-medium" style="background: rgba(var(--v-theme-error), 0.08);">
          <VIcon icon="tabler-lock" size="18" class="me-1" />
          <span class="text-body-2">هذه التذكرة مغلقة حالياً — لإرسال رسائل أو إضافة ردود، يجب إعادة فتح التذكرة أولاً</span>
        </div>

        <!-- Message form -->
        <VForm
          v-if="!isClosed"
          class="chat-log-message-form"
          @submit.prevent="sendMessage"
        >
          <!-- File attachment preview -->
          <div v-if="selectedFile" class="mb-2">
            <VChip closable size="small" color="primary" variant="tonal" @click:close="selectedFile = null">
              <VIcon icon="tabler-paperclip" size="14" class="me-1" />
              {{ selectedFile.name }} ({{ Math.round(selectedFile.size / 1024) }} KB)
            </VChip>
          </div>

          <!-- Input row -->
          <div class="d-flex align-center gap-2">
            <!-- Canned Responses trigger -->
            <VMenu location="top start" transition="slide-y-transition">
              <template #activator="{ props: menuProps }">
                <VBtn
                  v-bind="menuProps"
                  variant="tonal"
                  color="primary"
                  size="small"
                  icon
                  rounded
                  title="ردود وقوالب جاهزة"
                >
                  <VIcon icon="tabler-bolt" size="20" />
                </VBtn>
              </template>
              <VList class="py-1" width="340" max-height="380">
                <VListSubheader class="font-weight-bold text-primary">
                  ⚡ القوالب والردود الجاهزة
                </VListSubheader>
                <VListItem
                  v-for="resp in store.cannedResponses"
                  :key="resp.id"
                  class="cursor-pointer"
                  @click="msg = resp.body"
                >
                  <VListItemTitle class="text-body-2 font-weight-semibold text-primary">
                    {{ resp.title }}
                  </VListItemTitle>
                  <VListItemSubtitle class="text-caption text-truncate">
                    {{ resp.body }}
                  </VListItemSubtitle>
                </VListItem>
                <VListItem v-if="!store.cannedResponses.length" disabled>
                  <VListItemTitle class="text-disabled text-caption">جلب القوالب...</VListItemTitle>
                </VListItem>
              </VList>
            </VMenu>

            <!-- Text input -->
            <VTextField
              :key="store.activeChat?.conversation.id"
              v-model="msg"
              variant="outlined"
              density="comfortable"
              class="chat-message-input flex-grow-1"
              placeholder="اكتب ردك للمريض هنا..."
              :loading="store.sending"
              hide-details
              autofocus
            >
              <template #append-inner>
                <IconBtn title="إرفاق ملف أو صورة" @click="refInputEl?.click()">
                  <VIcon icon="tabler-paperclip" size="20" class="text-medium-emphasis" />
                </IconBtn>
              </template>
            </VTextField>

            <!-- Send Button -->
            <VBtn
              icon
              variant="flat"
              color="primary"
              :loading="store.sending"
              :disabled="!msg.trim() && !selectedFile"
              @click="sendMessage"
            >
              <VIcon icon="tabler-send" size="20" />
            </VBtn>
          </div>

          <input
            ref="refInputEl"
            type="file"
            name="file"
            accept=".jpeg,.png,.jpg,.pdf"
            hidden
            @change="onFileChange"
          >
        </VForm>
      </div>

      <!-- 👉 No active chat -->
      <div
        v-else
        class="d-flex h-100 align-center justify-center flex-column"
      >
        <VAvatar
          size="100"
          variant="tonal"
          color="primary"
          class="mb-6"
        >
          <VIcon
            size="52"
            class="rounded-0"
            icon="tabler-message-circle"
          />
        </VAvatar>
        <h4 class="text-h4 text-medium-emphasis mb-2">
          مركز الدعم
        </h4>
        <p
          style="max-inline-size: 40ch; text-wrap: balance;"
          class="text-center text-disabled text-body-1"
        >
          اختر محادثة من القائمة على اليسار للبدء
        </p>
        <VBtn
          v-if="$vuetify.display.smAndDown"
          rounded="pill"
          color="primary"
          class="mt-4"
          @click="startConversation"
        >
          <VIcon icon="tabler-messages" class="me-2" />
          عرض المحادثات
        </VBtn>
      </div>
    </VMain>
  </VLayout>
</template>

<style lang="scss">
@use "@styles/variables/vuetify";
@use "@core-scss/base/mixins";
@use "@layouts/styles/mixins" as layoutsMixins;

// Variables
$chat-app-header-height: 68px;
$chat-list-header-height: 68px;

// Placeholders
%chat-header {
  display: flex;
  align-items: center;
  min-block-size: $chat-app-header-height;
  padding-inline: 1.25rem;
  border-block-end: 1px solid rgba(var(--v-theme-on-surface), 0.06);
}

.chat-start-conversation-btn {
  cursor: default;
}

.chat-app-layout {
  border-radius: vuetify.$card-border-radius;

  @include mixins.elevation(vuetify.$card-elevation);

  $sel-chat-app-layout: &;

  @at-root {
    .skin--bordered {
      @include mixins.bordered-skin($sel-chat-app-layout);
    }
  }

  .active-chat-user-profile-sidebar,
  .user-profile-sidebar {
    .v-navigation-drawer__content {
      display: flex;
      flex-direction: column;
    }
  }

  .active-chat-header {
    min-block-size: $chat-app-header-height;
    padding-inline: 1.25rem;
    border-block-end: 1px solid rgba(var(--v-theme-on-surface), 0.08);
  }

  .chat-list-sidebar {
    .v-navigation-drawer__content {
      display: flex;
      flex-direction: column;
    }
  }
}

.chat-content-container {
  /* stylelint-disable-next-line value-keyword-case */
  background-color: v-bind(chatContentContainerBg);

  // Prevent chat bubbles overflowing the right edge
  overflow-x: hidden;

  .active-chat-header {
    background: rgb(var(--v-theme-surface));

    .v-btn {
      text-transform: none;
    }
  }

  // PerfectScrollbar wraps the chat log — must clip horizontally
  .ps {
    overflow-x: hidden !important;
  }

  // Message form area — clean bottom toolbar
  .chat-log-message-form {
    border-block-start: 1px solid rgba(var(--v-theme-on-surface), 0.07);
    background: rgb(var(--v-theme-surface));
    padding-block: 0.875rem 1rem;
    padding-inline: 1.25rem;
  }

  // Text field
  .chat-message-input {
    .v-field__input {
      font-size: 0.9375rem !important;
      line-height: 1.375rem !important;
      padding-block: 0.6rem 0.5rem;
    }

    .v-field__append-inner {
      align-items: center;
      padding-block-start: 0;
    }

    .v-field--appended {
      padding-inline-end: 8px;
    }

    .v-field {
      border-radius: 10px;
    }
  }
}

.chat-user-profile-badge {
  .v-badge__badge {
    /* stylelint-disable liberty/use-logical-spec */
    min-width: 12px !important;
    height: 0.75rem;
    /* stylelint-enable liberty/use-logical-spec */
  }
}
</style>
