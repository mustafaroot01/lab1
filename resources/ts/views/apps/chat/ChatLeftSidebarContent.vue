<script lang="ts" setup>
import { useChatStore } from '@/views/apps/chat/useChatStore';
import { PerfectScrollbar } from 'vue3-perfect-scrollbar';

const props = defineProps<{
  search: string
  isDrawerOpen: boolean
  filterStatus?: string
  filterAssigned?: string
}>()

const emit = defineEmits<{
  (e: 'openConversation', id: number): void
  (e: 'close'): void
  (e: 'update:search', value: string): void
  (e: 'update:filterStatus', value: string): void
  (e: 'update:filterAssigned', value: string): void
  (e: 'update:isDrawerOpen', value: boolean): void
}>()

const search = useVModel(props, 'search', emit)
const filterStatus = useVModel(props, 'filterStatus', emit)
const filterAssigned = useVModel(props, 'filterAssigned', emit)

const store = useChatStore()
const sidebarPS = ref()

const onSidebarScroll = () => {
  const scrollEl = sidebarPS.value?.$el || sidebarPS.value
  if (!scrollEl) return
  if (scrollEl.scrollTop + scrollEl.clientHeight >= scrollEl.scrollHeight - 60
    && store.hasMoreConversations && !store.loadingMore)
    store.loadMoreConversations({ q: search.value, status: filterStatus.value, assigned_status: filterAssigned.value })
}

const formatTime = (iso?: string) => {
  if (!iso) return ''
  const d = new Date(iso)
  const now = new Date()
  if (d.toDateString() === now.toDateString())
    return d.toLocaleTimeString('ar', { hour: 'numeric', minute: 'numeric' })
  return d.toLocaleDateString('ar', { day: 'numeric', month: 'short' })
}

const claimItem = (id: number) => store.claimConversationById(id)
</script>

<template>
  <!-- ══ Header ══ -->
  <div class="sidebar-header px-4 pt-4 pb-3">
    <div class="d-flex align-center justify-space-between mb-3">
      <div class="d-flex align-center gap-2">
        <VAvatar size="34" color="primary" variant="tonal">
          <VIcon icon="tabler-headset" size="18" />
        </VAvatar>
        <div>
          <div class="d-flex align-center gap-1.5">
            <span class="text-body-1 font-weight-bold text-high-emphasis">الدعم المباشر</span>
            <span class="live-dot" />
          </div>
          <span class="text-xs text-disabled">{{ store.conversations.length }} تذكرة</span>
        </div>
      </div>
      <IconBtn v-if="$vuetify.display.smAndDown" size="small" @click="$emit('close')">
        <VIcon icon="tabler-x" size="18" />
      </IconBtn>
    </div>

    <!-- Search -->
    <VTextField
      id="chat-search"
      v-model="search"
      placeholder="ابحث باسم المريض أو رقم التذكرة..."
      prepend-inner-icon="tabler-search"
      clearable
      density="compact"
      variant="outlined"
      hide-details
      class="sidebar-search"
    />
  </div>

  <!-- ══ Filter Strip ══ -->
  <div class="filter-strip px-3 pb-2 border-b">
    <!-- Status tabs -->
    <div class="status-tabs mb-1.5">
      <button
        class="status-tab"
        :class="{ active: (filterStatus || '') === '' }"
        @click="filterStatus = ''"
      >الكل</button>
      <button
        class="status-tab"
        :class="{ active: filterStatus === 'open' }"
        @click="filterStatus = 'open'"
      >🟢 مفتوحة</button>
      <button
        class="status-tab"
        :class="{ active: filterStatus === 'closed' }"
        @click="filterStatus = 'closed'"
      >🔒 مغلقة</button>
    </div>

    <!-- Assignment micro-filter -->
    <div class="d-flex align-center gap-1.5">
      <button
        v-for="opt in [{ label: 'الكل', val: '' }, { label: 'مستلماتي', val: 'my_assigned' }, { label: 'بانتظار استلام', val: 'unassigned' }]"
        :key="opt.val"
        class="assign-tab"
        :class="{ active: (filterAssigned || '') === opt.val }"
        @click="filterAssigned = opt.val"
      >
        {{ opt.label }}
      </button>
    </div>
  </div>

  <!-- ══ Conversations List ══ -->
  <PerfectScrollbar
    ref="sidebarPS"
    tag="ul"
    class="conv-list px-2 py-2"
    :options="{ wheelPropagation: false }"
    @scroll="onSidebarScroll"
  >
    <li v-if="store.loading" class="text-center py-4">
      <VProgressCircular indeterminate size="20" width="2" color="primary" />
    </li>

    <li
      v-for="conv in store.conversations"
      :key="`conv-${conv.id}`"
      class="conv-card"
      :class="{ 'conv-card--active': store.activeChat?.conversation.id === conv.id }"
      @click="$emit('openConversation', conv.id)"
    >
      <!-- Avatar -->
      <div class="conv-avatar">
        <VAvatar
          size="38"
          variant="tonal"
          :color="conv.status === 'open' ? 'primary' : 'secondary'"
        >
          <span class="text-xs font-weight-bold">{{ avatarText(conv.patient.name) }}</span>
        </VAvatar>
        <span
          class="status-dot"
          :class="conv.status === 'open' ? 'status-dot--open' : 'status-dot--closed'"
        />
      </div>

      <!-- Content -->
      <div class="conv-content">
        <!-- Row 1: Name + Time -->
        <div class="conv-row1">
          <span class="conv-name text-truncate">{{ conv.patient.name }}</span>
          <span class="conv-time">{{ formatTime(conv.last_message_at) }}</span>
        </div>

        <!-- Row 2: Preview + unread -->
        <div class="conv-row2">
          <span class="conv-preview text-truncate">{{ conv.last_message_preview || '—' }}</span>
          <span v-if="conv.unread_count" class="unread-badge">{{ conv.unread_count }}</span>
        </div>

        <!-- Row 3: Ticket # + Assigned / Claim -->
        <div class="conv-row3">
          <span class="ticket-id">#{{ conv.id }}</span>
          <span v-if="conv.is_assigned" class="assigned-info">
            <VIcon icon="tabler-user-check" size="12" />
            {{ conv.assigned_to?.name || 'مستلمة' }}
          </span>
          <button
            v-else
            class="claim-btn"
            @click.stop="claimItem(conv.id)"
          >
            + استلام
          </button>
        </div>
      </div>
    </li>

    <!-- Empty -->
    <div v-if="!store.conversations.length && !store.loading" class="empty-state">
      <VIcon icon="tabler-messages-off" size="36" class="mb-2" />
      <p class="text-body-2 text-disabled mb-0">لا توجد تذاكر مطابقة</p>
    </div>

    <!-- Load more -->
    <li v-if="store.hasMoreConversations" class="load-more-item">
      <VProgressCircular v-if="store.loadingMore" indeterminate size="20" width="2" color="primary" />
      <button
        v-else
        class="load-more-btn"
        @click="store.loadMoreConversations({ q: search, status: filterStatus, assigned_status: filterAssigned })"
      >
        <VIcon icon="tabler-chevron-down" size="16" class="me-1" />
        تحميل المزيد
      </button>
    </li>
  </PerfectScrollbar>
</template>

<style lang="scss" scoped>
// ── Header ──────────────────────────────────────────
.sidebar-header {
  flex-shrink: 0;
}

.live-dot {
  display: inline-block;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: rgb(var(--v-theme-success));
  box-shadow: 0 0 0 2px rgba(var(--v-theme-success), 0.3);
}

.sidebar-search {
  :deep(.v-field) {
    border-radius: 8px;
  }
}

// ── Filter Strip ──────────────────────────────────
.filter-strip {
  flex-shrink: 0;
  padding-block-start: 0;
}

.status-tabs {
  display: flex;
  gap: 4px;
  background: rgba(var(--v-theme-on-surface), 0.04);
  border-radius: 8px;
  padding: 3px;
}

.status-tab {
  flex: 1;
  border: none;
  background: transparent;
  border-radius: 6px;
  padding: 5px 6px;
  font-size: 0.75rem;
  font-weight: 500;
  color: rgba(var(--v-theme-on-surface), 0.55);
  cursor: pointer;
  transition: all 0.15s;
  white-space: nowrap;

  &.active {
    background: rgb(var(--v-theme-surface));
    color: rgb(var(--v-theme-primary));
    font-weight: 700;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  }

  &:hover:not(.active) {
    color: rgba(var(--v-theme-on-surface), 0.8);
  }
}

.assign-tab {
  border: none;
  background: transparent;
  border-radius: 20px;
  padding: 2px 10px;
  font-size: 0.7rem;
  color: rgba(var(--v-theme-on-surface), 0.5);
  cursor: pointer;
  transition: all 0.15s;
  white-space: nowrap;

  &.active {
    background: rgba(var(--v-theme-primary), 0.12);
    color: rgb(var(--v-theme-primary));
    font-weight: 700;
  }

  &:hover:not(.active) {
    background: rgba(var(--v-theme-on-surface), 0.05);
    color: rgba(var(--v-theme-on-surface), 0.8);
  }
}

// ── Conversation List ─────────────────────────────
.conv-list {
  flex: 1;
  list-style: none;
  padding: 0;
  margin: 0;
}

// ── Conversation Card ─────────────────────────────
.conv-card {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 10px 10px;
  border-radius: 10px;
  cursor: pointer;
  margin-block-end: 2px;
  transition: background 0.15s;
  border: 1px solid transparent;

  &:hover {
    background: rgba(var(--v-theme-on-surface), 0.04);
    border-color: rgba(var(--v-border-color), 0.25);
  }

  &--active {
    background: rgba(var(--v-theme-primary), 0.08) !important;
    border-color: rgba(var(--v-theme-primary), 0.35) !important;
  }
}

// Avatar + status dot
.conv-avatar {
  position: relative;
  flex-shrink: 0;
}

.status-dot {
  position: absolute;
  bottom: 0;
  inset-inline-end: 0;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  border: 2px solid rgb(var(--v-theme-surface));

  &--open { background: rgb(var(--v-theme-success)); }
  &--closed { background: rgba(var(--v-theme-on-surface), 0.3); }
}

// Content area
.conv-content {
  flex: 1;
  min-width: 0;
}

.conv-row1 {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 4px;
  margin-block-end: 2px;
}

.conv-name {
  font-size: 0.875rem;
  font-weight: 700;
  color: rgba(var(--v-theme-on-surface), 0.9);
  flex: 1;
  min-width: 0;
}

.conv-time {
  font-size: 0.7rem;
  color: rgba(var(--v-theme-on-surface), 0.4);
  flex-shrink: 0;
  white-space: nowrap;
}

.conv-row2 {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 6px;
  margin-block-end: 4px;
}

.conv-preview {
  font-size: 0.78rem;
  color: rgba(var(--v-theme-on-surface), 0.55);
  flex: 1;
  min-width: 0;
  line-height: 1.4;
}

.unread-badge {
  flex-shrink: 0;
  min-width: 18px;
  height: 18px;
  border-radius: 20px;
  background: rgb(var(--v-theme-error));
  color: #fff;
  font-size: 0.65rem;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding-inline: 4px;
}

.conv-row3 {
  display: flex;
  align-items: center;
  gap: 6px;
}

.ticket-id {
  font-size: 0.68rem;
  font-weight: 700;
  color: rgba(var(--v-theme-primary), 0.8);
}

.assigned-info {
  font-size: 0.68rem;
  color: rgba(var(--v-theme-on-surface), 0.45);
  display: inline-flex;
  align-items: center;
  gap: 3px;
}

.claim-btn {
  border: 1px solid rgba(var(--v-theme-primary), 0.4);
  background: rgba(var(--v-theme-primary), 0.07);
  color: rgb(var(--v-theme-primary));
  border-radius: 20px;
  padding: 1px 8px;
  font-size: 0.68rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.15s;

  &:hover {
    background: rgba(var(--v-theme-primary), 0.15);
  }
}

// ── Empty + Load More ─────────────────────────────
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem 1rem;
  color: rgba(var(--v-theme-on-surface), 0.35);
}

.load-more-item {
  list-style: none;
  display: flex;
  justify-content: center;
  padding: 8px;
}

.load-more-btn {
  border: 1px solid rgba(var(--v-border-color), 0.3);
  background: transparent;
  border-radius: 8px;
  padding: 5px 16px;
  font-size: 0.78rem;
  color: rgba(var(--v-theme-on-surface), 0.6);
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  transition: all 0.15s;

  &:hover {
    background: rgba(var(--v-theme-on-surface), 0.05);
    color: rgba(var(--v-theme-on-surface), 0.9);
  }
}
</style>
