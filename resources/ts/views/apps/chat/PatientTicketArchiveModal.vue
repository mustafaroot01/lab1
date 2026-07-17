<script lang="ts" setup>
import { useChatStore } from '@/views/apps/chat/useChatStore';
import { PerfectScrollbar } from 'vue3-perfect-scrollbar';

const props = defineProps<{
  isDialogVisible: boolean
  patientId: number
  patientName: string
}>()

const emit = defineEmits<{
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'openConversation', id: number): void
}>()

const store = useChatStore()
const search = ref('')
const statusFilter = ref('')

// دالة جلب الأرشيف عند تغير الفلتر أو البحث
const loadArchive = () => {
  store.fetchPatientArchive(props.patientId, {
    q: search.value,
    status: statusFilter.value,
  })
}

// مراقبة فتح النافذة لجلب الأرشيف فوراً
watch(() => props.isDialogVisible, (newVal) => {
  if (newVal && props.patientId) {
    search.value = ''
    statusFilter.value = ''
    loadArchive()
  }
}, { immediate: true })

watch([search, statusFilter], () => {
  if (props.isDialogVisible && props.patientId) {
    loadArchive()
  }
})

const archivePS = ref()
const onScroll = () => {
  const scrollEl = archivePS.value?.$el || archivePS.value
  if (!scrollEl) return

  if (scrollEl.scrollTop + scrollEl.clientHeight >= scrollEl.scrollHeight - 60 && store.archiveHasMore && !store.archiveLoading) {
    store.loadMorePatientArchive(props.patientId, {
      q: search.value,
      status: statusFilter.value,
    })
  }
}

const selectTicket = (id: number) => {
  emit('openConversation', id)
  emit('update:isDialogVisible', false)
}

const statusOptions = [
  { title: 'الكل', value: '' },
  { title: 'مفتوحة 🟢', value: 'open' },
  { title: 'مغلقة 🔒', value: 'closed' },
]
</script>

<template>
  <VDialog
    :model-value="props.isDialogVisible"
    max-width="720"
    scrollable
    @update:model-value="emit('update:isDialogVisible', $event)"
  >
    <VCard class="pa-2">
      <!-- Header -->
      <VCardTitle class="d-flex align-center justify-space-between pb-3 pt-4 px-6 border-b">
        <div class="d-flex align-center gap-3">
          <VAvatar size="44" color="primary" variant="tonal">
            <VIcon icon="tabler-archive" size="24" />
          </VAvatar>
          <div>
            <h5 class="text-h5 mb-0 font-weight-bold">أرشيف تذاكر المريض</h5>
            <p class="text-caption text-medium-emphasis mb-0">
              {{ props.patientName }} (إجمالي السجل: {{ store.archiveTotalCount }} تذكرة)
            </p>
          </div>
        </div>
        <IconBtn @click="emit('update:isDialogVisible', false)">
          <VIcon icon="tabler-x" />
        </IconBtn>
      </VCardTitle>

      <!-- Filters & Search -->
      <VCardText class="pt-4 px-6 pb-2">
        <VRow class="mb-2">
          <VCol cols="12" md="7">
            <AppTextField
              v-model="search"
              placeholder="ابحث برقم التذكرة # أو كلمة في المحادثة..."
              prepend-inner-icon="tabler-search"
              clearable
              density="compact"
            />
          </VCol>
          <VCol cols="12" md="5" class="d-flex align-center gap-1">
            <VChip
              v-for="opt in statusOptions"
              :key="opt.value"
              :color="statusFilter === opt.value ? 'primary' : 'default'"
              size="medium"
              variant="tonal"
              class="cursor-pointer flex-grow-1 justify-center"
              @click="statusFilter = opt.value"
            >
              {{ opt.title }}
            </VChip>
          </VCol>
        </VRow>
      </VCardText>

      <!-- Archive List -->
      <VDivider />
      <VCardText class="pa-0" style="height: 480px;">
        <PerfectScrollbar
          ref="archivePS"
          tag="div"
          class="h-100 pa-4 d-flex flex-column gap-3"
          :options="{ wheelPropagation: false }"
          @scroll="onScroll"
        >
          <!-- Loading Spinner (initial) -->
          <div v-if="store.archiveLoading && !store.archiveTickets.length" class="d-flex align-center justify-center py-12 flex-column">
            <VProgressCircular indeterminate color="primary" size="36" class="mb-3" />
            <span class="text-caption text-medium-emphasis">جلب أرشيف المحادثات بسرعة فائقة...</span>
          </div>

          <!-- Empty state -->
          <div v-else-if="!store.archiveTickets.length && !store.archiveLoading" class="pa-8 text-center text-disabled">
            <VIcon icon="tabler-folder-off" size="44" class="mb-2 opacity-50" />
            <h6 class="text-h6 text-disabled">لا توجد محادثات سابقة مطابقة</h6>
            <p class="text-caption">حاول تغيير كلمات البحث أو فلتر الحالة</p>
          </div>

          <!-- Ticket Items -->
          <div
            v-for="item in store.archiveTickets"
            :key="item.id"
            class="ticket-archive-card pa-4 rounded-lg border cursor-pointer transition-all d-flex align-center justify-space-between"
            :class="store.activeChat?.conversation.id === item.id ? 'active-ticket-card' : ''"
            @click="selectTicket(item.id)"
          >
            <div class="d-flex align-center gap-3 overflow-hidden flex-grow-1">
              <VAvatar
                size="42"
                :color="item.status === 'open' ? 'success' : 'secondary'"
                variant="tonal"
                class="flex-shrink-0"
              >
                <VIcon :icon="item.status === 'open' ? 'tabler-message-circle-2' : 'tabler-lock'" size="22" />
              </VAvatar>
              <div class="overflow-hidden flex-grow-1">
                <div class="d-flex align-center gap-2 mb-1">
                  <span class="text-h6 font-weight-bold text-primary">تذكرة #{{ item.id }}</span>
                  <VChip
                    :color="item.status === 'open' ? 'success' : 'secondary'"
                    size="x-small"
                    variant="flat"
                  >
                    {{ item.status === 'open' ? 'مفتوحة حالياً' : 'مغلقة' }}
                  </VChip>
                  <span v-if="store.activeChat?.conversation.id === item.id" class="text-xs text-info font-weight-bold">(المفتوحة الآن)</span>
                </div>
                <p class="text-body-2 text-medium-emphasis mb-0 text-truncate" style="max-inline-size: 450px;">
                  {{ item.last_message_preview || 'محادثة سابقة بدون نص لمعاينة' }}
                </p>
              </div>
            </div>

            <div class="text-end ms-3 flex-shrink-0 d-flex flex-column align-end">
              <span class="text-caption text-disabled mb-1 d-block">
                🕒 {{ item.closed_at ? 'أغلقت: ' + new Date(item.closed_at).toLocaleDateString('ar') : 'آخر نشاط: ' + new Date(item.last_message_at || item.created_at || '').toLocaleDateString('ar') }}
              </span>
              <VBtn size="small" variant="tonal" color="primary" append-icon="tabler-chevron-left">
                فتح التذكرة
              </VBtn>
            </div>
          </div>

          <!-- Load More Spinner -->
          <div v-if="store.archiveLoading && store.archiveTickets.length" class="d-flex justify-center py-4">
            <VProgressCircular indeterminate color="primary" size="24" />
          </div>
        </PerfectScrollbar>
      </VCardText>
    </VCard>
  </VDialog>
</template>

<style lang="scss" scoped>
.ticket-archive-card {
  background: rgba(var(--v-theme-on-surface), 0.02);
  border-color: rgba(var(--v-border-color), 0.4) !important;

  &:hover {
    background: rgba(var(--v-theme-primary), 0.06);
    border-color: rgba(var(--v-theme-primary), 0.4) !important;
    transform: translateY(-1px);
  }

  &.active-ticket-card {
    background: rgba(var(--v-theme-primary), 0.12);
    border-color: rgb(var(--v-theme-primary)) !important;
  }
}
</style>
