<script lang="ts" setup>
import { useChatStore } from '@/views/apps/chat/useChatStore'
import { $api } from '@/utils/api'
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import PatientTicketArchiveModal from '@/views/apps/chat/PatientTicketArchiveModal.vue'

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'openConversation', id: number): void
}>()

const store = useChatStore()
const isArchiveModalVisible = ref(false)
const activeTab = ref<'info' | 'orders' | 'history'>('info')

// ── Patient full profile (loaded on demand) ──────────
const profile = ref<any>(null)
const profileLoading = ref(false)

const loadProfile = async (patientId: number) => {
  if (profileLoading.value) return
  profileLoading.value = true
  try {
    const res = await $api(`/admin/chat/patient/${patientId}/profile`)
    profile.value = res
  } catch (e) {
    console.error('[Chat] loadProfile error:', e)
  } finally {
    profileLoading.value = false
  }
}

watch(
  () => store.activeChat?.conversation.patient.id,
  (id) => {
    profile.value = null
    activeTab.value = 'info'
    if (id) loadProfile(id)
  },
  { immediate: true },
)

// ── Helpers ──────────────────────────────────────────
const formatDate = (d?: string | null) => {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('ar', { day: 'numeric', month: 'short', year: 'numeric' })
}

const statusColors: Record<string, string> = {
  pending: 'warning', confirmed: 'info', awaiting_technician: 'warning',
  technician_assigned: 'primary', on_the_way: 'primary', sample_collected: 'secondary',
  in_progress: 'primary', completed: 'success', cancelled: 'error',
}
</script>

<template>
  <template v-if="store.activeChat">
    <!-- ── Close Button ── -->
    <div class="d-flex justify-end px-4 pt-4 pb-2">
      <IconBtn size="small" @click="$emit('close')">
        <VIcon icon="tabler-x" size="18" />
      </IconBtn>
    </div>

    <!-- ── Patient Hero ── -->
    <div class="patient-hero px-5 pb-4">
      <VAvatar size="72" color="primary" variant="tonal" class="mb-3">
        <span class="text-2xl font-weight-bold">{{ avatarText(store.activeChat.conversation.patient.name) }}</span>
      </VAvatar>
      <h5 class="text-h6 font-weight-bold mb-1">{{ store.activeChat.conversation.patient.name }}</h5>
      <div class="d-flex align-center gap-2 flex-wrap justify-center">
        <span class="text-caption text-medium-emphasis" dir="ltr">{{ store.activeChat.conversation.patient.phone }}</span>
        <VChip
          :color="store.activeChat.conversation.status === 'open' ? 'success' : 'error'"
          size="x-small"
          variant="flat"
        >
          {{ store.activeChat.conversation.status === 'open' ? 'مفتوحة' : 'مغلقة' }}
        </VChip>
      </div>

      <!-- Quick stats row -->
      <div v-if="profile" class="stats-row mt-3">
        <div class="stat-item">
          <span class="stat-value text-primary">{{ profile.orders_summary?.total ?? 0 }}</span>
          <span class="stat-label">إجمالي الطلبات</span>
        </div>
        <div class="stat-divider" />
        <div class="stat-item">
          <span class="stat-value text-success">{{ profile.orders_summary?.completed ?? 0 }}</span>
          <span class="stat-label">مكتملة</span>
        </div>
        <div class="stat-divider" />
        <div class="stat-item">
          <span class="stat-value text-warning">{{ profile.orders_summary?.total_spent ?? 0 }} د.ع</span>
          <span class="stat-label">إجمالي الإنفاق</span>
        </div>
      </div>
      <div v-else-if="profileLoading" class="d-flex justify-center mt-3">
        <VProgressCircular indeterminate size="20" width="2" color="primary" />
      </div>
    </div>

    <VDivider />

    <!-- ── Tabs ── -->
    <div class="profile-tabs px-2 pt-2">
      <button
        v-for="tab in [{ key: 'info', label: 'البيانات', icon: 'tabler-user' }, { key: 'orders', label: 'الطلبات', icon: 'tabler-clipboard-list' }, { key: 'history', label: 'التذاكر', icon: 'tabler-history' }]"
        :key="tab.key"
        class="profile-tab"
        :class="{ active: activeTab === tab.key }"
        @click="activeTab = tab.key as any"
      >
        <VIcon :icon="tab.icon" size="15" class="me-1" />
        {{ tab.label }}
      </button>
    </div>

    <!-- ── Content ── -->
    <PerfectScrollbar class="profile-scroll px-4 pb-6 pt-3" :options="{ wheelPropagation: false }">

      <!-- ═══ TAB: Info ═══ -->
      <div v-if="activeTab === 'info'">
        <!-- Loading skeleton -->
        <div v-if="profileLoading" class="d-flex flex-column gap-3">
          <VSkeleton v-for="i in 4" :key="i" height="36" rounded="lg" />
        </div>

        <template v-else-if="profile">
          <!-- Basic Info -->
          <div class="section-label">بيانات أساسية</div>
          <div class="info-list">
            <div class="info-row">
              <VIcon icon="tabler-phone" size="16" color="primary" class="flex-shrink-0" />
              <span dir="ltr">{{ profile.patient?.phone || '—' }}</span>
            </div>
            <div v-if="profile.patient?.email" class="info-row">
              <VIcon icon="tabler-mail" size="16" color="primary" class="flex-shrink-0" />
              <span>{{ profile.patient.email }}</span>
            </div>
            <div v-if="profile.patient?.birth_date" class="info-row">
              <VIcon icon="tabler-cake" size="16" color="primary" class="flex-shrink-0" />
              <span>{{ formatDate(profile.patient.birth_date) }}</span>
            </div>
            <div v-if="profile.patient?.gender" class="info-row">
              <VIcon :icon="profile.patient.gender === 'male' ? 'tabler-gender-male' : 'tabler-gender-female'" size="16" color="primary" class="flex-shrink-0" />
              <span>{{ profile.patient.gender === 'male' ? 'ذكر' : 'أنثى' }}</span>
            </div>
            <div v-if="profile.patient?.district?.name" class="info-row">
              <VIcon icon="tabler-map-pin" size="16" color="primary" class="flex-shrink-0" />
              <span>{{ profile.patient.district.name }}<span v-if="profile.patient.area?.name"> — {{ profile.patient.area.name }}</span></span>
            </div>
            <div v-if="profile.patient?.address" class="info-row">
              <VIcon icon="tabler-home" size="16" color="primary" class="flex-shrink-0" />
              <span class="text-truncate">{{ profile.patient.address }}</span>
            </div>
          </div>

          <!-- Chronic Diseases -->
          <template v-if="profile.patient?.chronic_diseases?.length">
            <div class="section-label mt-4">الأمراض المزمنة</div>
            <div class="d-flex flex-wrap gap-1">
              <VChip v-for="d in profile.patient.chronic_diseases" :key="d.id" size="x-small" color="error" variant="tonal">
                {{ d.name_ar || d.name }}
              </VChip>
            </div>
          </template>

          <!-- Medications -->
          <template v-if="profile.patient?.medications?.length">
            <div class="section-label mt-4">الأدوية الحالية</div>
            <div class="d-flex flex-wrap gap-1">
              <VChip v-for="m in profile.patient.medications" :key="m.id" size="x-small" color="warning" variant="tonal">
                {{ m.name_ar || m.name }}
              </VChip>
            </div>
          </template>

          <!-- Allergies -->
          <template v-if="profile.patient?.allergies?.length">
            <div class="section-label mt-4">الحساسية</div>
            <div class="d-flex flex-wrap gap-1">
              <VChip v-for="a in profile.patient.allergies" :key="a.id" size="x-small" color="orange" variant="tonal">
                {{ a.name_ar || a.name }}
              </VChip>
            </div>
          </template>
        </template>

        <div v-else class="empty-msg">لا توجد بيانات</div>
      </div>

      <!-- ═══ TAB: Orders ═══ -->
      <div v-else-if="activeTab === 'orders'">
        <div v-if="profileLoading" class="d-flex flex-column gap-3">
          <VSkeleton v-for="i in 3" :key="i" height="120" rounded="lg" />
        </div>

        <template v-else-if="profile?.orders?.length">
          <div v-for="order in profile.orders" :key="order.id" class="order-card mb-3">
            <!-- Header -->
            <div class="d-flex align-center justify-space-between mb-2">
              <div class="d-flex align-center gap-2">
                <span class="text-body-2 font-weight-bold text-primary">#{{ order.id }}</span>
                <VChip :color="statusColors[order.status] || 'secondary'" size="x-small" variant="flat">
                  {{ order.status_label }}
                </VChip>
              </div>
              <span class="text-xs text-disabled">{{ formatDate(order.created_at) }}</span>
            </div>

            <!-- Visit info -->
            <div v-if="order.visit_date" class="info-row mb-1">
              <VIcon icon="tabler-calendar" size="14" color="primary" class="flex-shrink-0" />
              <span class="text-caption">{{ order.visit_date }} — {{ order.visit_period_label }}</span>
            </div>

            <!-- Technician -->
            <div v-if="order.technician" class="info-row mb-1">
              <VIcon icon="tabler-user-circle" size="14" color="primary" class="flex-shrink-0" />
              <span class="text-caption">الفني: {{ order.technician.name }}</span>
              <span v-if="order.technician.phone" class="text-caption text-disabled" dir="ltr">({{ order.technician.phone }})</span>
            </div>

            <!-- Branch -->
            <div v-if="order.branch" class="info-row mb-2">
              <VIcon icon="tabler-building-hospital" size="14" color="primary" class="flex-shrink-0" />
              <span class="text-caption">{{ order.branch.name_ar }}</span>
            </div>

            <!-- Lab tests (items) -->
            <div v-if="order.items?.length" class="mb-2">
              <div class="text-xs text-disabled mb-1 font-weight-semibold">التحاليل المطلوبة:</div>
              <div class="d-flex flex-wrap gap-1">
                <VChip
                  v-for="item in order.items"
                  :key="item.id"
                  size="x-small"
                  color="primary"
                  variant="tonal"
                >
                  {{ item.name_ar }}
                  <span v-if="item.price" class="ms-1 opacity-70">{{ item.price }} د.ع</span>
                </VChip>
              </div>
            </div>

            <!-- Results -->
            <div v-if="order.results?.length" class="result-list">
              <a
                v-for="res in order.results"
                :key="res.id"
                :href="res.url"
                target="_blank"
                class="result-item"
              >
                <VIcon icon="tabler-file-type-pdf" size="16" color="error" class="flex-shrink-0" />
                <span class="text-caption text-truncate flex-grow-1">{{ res.file_name || 'نتيجة التحليل' }}</span>
                <VIcon icon="tabler-download" size="14" class="text-disabled flex-shrink-0" />
              </a>
            </div>

            <!-- Total -->
            <div class="d-flex justify-end mt-2">
              <span class="text-body-2 font-weight-bold text-primary">{{ order.total }} د.ع</span>
            </div>
          </div>

          <div v-if="profile.orders_summary?.total > 5" class="text-center">
            <VBtn
              size="small"
              variant="tonal"
              color="primary"
              :href="`/patients/${store.activeChat?.conversation.patient.id}`"
              target="_blank"
            >
              عرض جميع الطلبات ({{ profile.orders_summary.total }})
              <VIcon icon="tabler-external-link" size="14" class="ms-1" />
            </VBtn>
          </div>
        </template>

        <div v-else-if="!profileLoading" class="empty-msg">
          <VIcon icon="tabler-clipboard-off" size="36" class="mb-2 opacity-40" />
          <p class="mb-0">لا توجد طلبات سابقة لهذا المريض</p>
        </div>
      </div>

      <!-- ═══ TAB: History (Tickets) ═══ -->
      <div v-else-if="activeTab === 'history'">
        <div class="d-flex align-center justify-space-between mb-3">
          <span class="text-body-2 font-weight-bold">
            التذاكر السابقة
            <VChip size="x-small" color="primary" variant="tonal" class="ms-1">
              {{ store.activeChat.patient_history_total_count ?? 0 }}
            </VChip>
          </span>
        </div>

        <div
          v-for="item in store.activeChat.patient_history"
          :key="item.id"
          class="history-card mb-2 cursor-pointer"
          @click="emit('openConversation', item.id)"
        >
          <div class="d-flex align-center justify-space-between mb-1">
            <span class="text-body-2 font-weight-bold text-primary">#{{ item.id }}</span>
            <VChip :color="item.status === 'open' ? 'success' : 'secondary'" size="x-small" variant="flat">
              {{ item.status === 'open' ? 'مفتوحة' : 'مغلقة' }}
            </VChip>
          </div>
          <p v-if="item.last_message_preview" class="text-caption text-medium-emphasis mb-1 text-truncate">
            {{ item.last_message_preview }}
          </p>
          <span class="text-xs text-disabled">{{ formatDate(item.closed_at || item.created_at) }}</span>
        </div>

        <div v-if="!store.activeChat.patient_history?.length" class="empty-msg">
          <VIcon icon="tabler-folder-off" size="36" class="mb-2 opacity-40" />
          <p class="mb-0">لا توجد تذاكر سابقة</p>
        </div>

        <!-- Full archive -->
        <VBtn
          v-if="(store.activeChat.patient_history_total_count || 0) > 0"
          block
          variant="tonal"
          color="primary"
          prepend-icon="tabler-archive"
          size="small"
          class="mt-3"
          @click="isArchiveModalVisible = true"
        >
          عرض الأرشيف الكامل ({{ store.activeChat.patient_history_total_count }} تذكرة)
        </VBtn>
      </div>

    </PerfectScrollbar>

    <!-- Archive Modal -->
    <PatientTicketArchiveModal
      v-model:isDialogVisible="isArchiveModalVisible"
      :patient-id="store.activeChat.conversation.patient.id"
      :patient-name="store.activeChat.conversation.patient.name"
      @open-conversation="emit('openConversation', $event)"
    />
  </template>
</template>

<style lang="scss" scoped>
// ── Hero ─────────────────────────────────────────────
.patient-hero {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.stats-row {
  display: flex;
  align-items: center;
  gap: 0;
  background: rgba(var(--v-theme-on-surface), 0.04);
  border: 1px solid rgba(var(--v-border-color), 0.2);
  border-radius: 10px;
  overflow: hidden;
  width: 100%;
}

.stat-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 8px 6px;
}

.stat-value {
  font-size: 1rem;
  font-weight: 700;
  line-height: 1.2;
}

.stat-label {
  font-size: 0.65rem;
  color: rgba(var(--v-theme-on-surface), 0.45);
  margin-top: 2px;
}

.stat-divider {
  width: 1px;
  height: 32px;
  background: rgba(var(--v-border-color), 0.25);
  flex-shrink: 0;
}

// ── Tabs ─────────────────────────────────────────────
.profile-tabs {
  display: flex;
  gap: 2px;
  border-block-end: 1px solid rgba(var(--v-border-color), 0.15);
}

.profile-tab {
  flex: 1;
  border: none;
  background: transparent;
  border-block-end: 2px solid transparent;
  padding: 8px 4px;
  font-size: 0.75rem;
  font-weight: 500;
  color: rgba(var(--v-theme-on-surface), 0.5);
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s;

  &.active {
    color: rgb(var(--v-theme-primary));
    font-weight: 700;
    border-block-end-color: rgb(var(--v-theme-primary));
  }

  &:hover:not(.active) {
    color: rgba(var(--v-theme-on-surface), 0.8);
  }
}

// ── Scroll area ───────────────────────────────────────
.profile-scroll {
  flex: 1;
  overflow-y: auto;
}

// ── Section labels ────────────────────────────────────
.section-label {
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: rgba(var(--v-theme-on-surface), 0.4);
  margin-block-end: 8px;
}

// ── Info rows ─────────────────────────────────────────
.info-list {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.info-row {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.825rem;
  color: rgba(var(--v-theme-on-surface), 0.8);

  span {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
}

// ── Order card ────────────────────────────────────────
.order-card {
  background: rgba(var(--v-theme-on-surface), 0.03);
  border: 1px solid rgba(var(--v-border-color), 0.18);
  border-radius: 10px;
  padding: 12px;
}

// ── Result files ─────────────────────────────────────
.result-list {
  display: flex;
  flex-direction: column;
  gap: 4px;
  margin-block-start: 6px;
}

.result-item {
  display: flex;
  align-items: center;
  gap: 6px;
  background: rgba(var(--v-theme-on-surface), 0.04);
  border: 1px solid rgba(var(--v-border-color), 0.18);
  border-radius: 6px;
  padding: 5px 8px;
  text-decoration: none;
  color: rgba(var(--v-theme-on-surface), 0.8);
  font-size: 0.78rem;
  transition: background 0.15s;

  &:hover {
    background: rgba(var(--v-theme-primary), 0.08);
  }
}

// ── History card ──────────────────────────────────────
.history-card {
  background: rgba(var(--v-theme-on-surface), 0.03);
  border: 1px solid rgba(var(--v-border-color), 0.18);
  border-radius: 8px;
  padding: 10px 12px;
  transition: border-color 0.15s, background 0.15s;

  &:hover {
    background: rgba(var(--v-theme-primary), 0.05);
    border-color: rgba(var(--v-theme-primary), 0.3);
  }
}

// ── Empty state ───────────────────────────────────────
.empty-msg {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2.5rem 1rem;
  color: rgba(var(--v-theme-on-surface), 0.35);
  text-align: center;
  font-size: 0.825rem;
}
</style>
