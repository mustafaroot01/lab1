<script setup lang="ts">
import { ref, onMounted } from 'vue'

const logs = ref<any[]>([])
const loading = ref(false)
const totalLogs = ref(0)
const options = ref({ page: 1, itemsPerPage: 20 })
const sidePanel = ref(false)
const selectedLog = ref<any>(null)

const filters = ref({
  status: null,
  match_type: null,
  is_slow: false
})

const fetchLogs = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: options.value.page.toString(),
      ...(filters.value.status && { status: filters.value.status }),
      ...(filters.value.match_type && { match_type: filters.value.match_type }),
      ...(filters.value.is_slow && { is_slow: 'true' })
    })

    const res = await $api(`/coverage-logs?${params.toString()}`)
    logs.value = res.data.data
    totalLogs.value = res.data.total
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

const openLogDetails = (log: any) => {
  selectedLog.value = log
  sidePanel.value = true
}

const formatDate = (dateString: string) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleString('ar-IQ')
}

onMounted(() => {
  fetchLogs()
})
</script>

<template>
  <div>
    <VCard title="سجلات محرك التغطية الجغرافية" class="mb-6">
      <VCardText>
        <VRow>
          <VCol cols="12" md="3">
            <AppSelect
              v-model="filters.status"
              :items="[
                { title: 'الكل', value: null },
                { title: 'مغطاة', value: 'COVERED' },
                { title: 'مرفوضة', value: 'NOT_COVERED' },
              ]"
              label="حالة الطلب"
              clearable
              @update:model-value="fetchLogs"
            />
          </VCol>
          <VCol cols="12" md="3">
            <AppSelect
              v-model="filters.match_type"
              :items="[
                { title: 'الكل', value: null },
                { title: 'تطابق مباشر (Polygon)', value: 'polygon_exact' },
                { title: 'مسافة سماح (Grace)', value: 'grace_distance' },
              ]"
              label="نوع التطابق"
              clearable
              @update:model-value="fetchLogs"
            />
          </VCol>
          <VCol cols="12" md="3" class="d-flex align-center">
            <VSwitch
              v-model="filters.is_slow"
              label="الطلبات البطيئة فقط (>30ms)"
              color="error"
              @update:model-value="fetchLogs"
            />
          </VCol>
        </VRow>
      </VCardText>

      <VDataTableServer
        v-model:items-per-page="options.itemsPerPage"
        v-model:page="options.page"
        :headers="[
          { title: 'رقم الطلب', key: 'id' },
          { title: 'حالة التغطية', key: 'is_covered' },
          { title: 'نوع التطابق', key: 'match_type' },
          { title: 'رسوم الزيارة', key: 'service_fee_applied' },
          { title: 'زمن التنفيذ', key: 'execution_time_ms' },
          { title: 'تاريخ الطلب', key: 'created_at' },
        ]"
        :items="logs"
        :items-length="totalLogs"
        :loading="loading"
        @update:options="fetchLogs"
        hover
        @click:row="(_: any, row: any) => openLogDetails(row.item)"
        class="cursor-pointer"
      >
        <template #item.is_covered="{ item }">
          <VChip size="small" :color="item.is_covered ? 'success' : 'error'">
            {{ item.is_covered ? 'مغطاة' : 'مرفوضة' }}
          </VChip>
        </template>

        <template #item.match_type="{ item }">
          <VChip v-if="item.match_type" size="small" :color="item.match_type === 'polygon_exact' ? 'primary' : 'warning'" variant="tonal">
            {{ item.match_type === 'polygon_exact' ? 'مضلع مباشر' : 'مسافة سماح' }}
          </VChip>
          <span v-else>-</span>
        </template>

        <template #item.execution_time_ms="{ item }">
          <VChip size="small" :color="item.execution_time_ms > 30 ? 'error' : 'default'" variant="tonal">
            {{ item.execution_time_ms }} ms
          </VChip>
        </template>

        <template #item.created_at="{ item }">
          {{ formatDate(item.created_at) }}
        </template>
      </VDataTableServer>
    </VCard>

    <!-- Side Panel (Navigation Drawer) for Log Details -->
    <VNavigationDrawer
      v-model="sidePanel"
      location="end"
      temporary
      width="500"
    >
      <div class="pa-4 d-flex align-center justify-space-between border-b">
        <h6 class="text-h6">تفاصيل السجل #{{ selectedLog?.id }}</h6>
        <VBtn icon="tabler-x" variant="text" size="small" @click="sidePanel = false" />
      </div>

      <div class="pa-4" v-if="selectedLog">
        <VAlert :type="selectedLog.is_covered ? 'success' : 'error'" variant="tonal" class="mb-4">
          <div class="font-weight-bold">{{ selectedLog.is_covered ? 'المنطقة مغطاة' : 'المنطقة غير مغطاة' }}</div>
        </VAlert>

        <VList lines="two" class="mb-4 border rounded">
          <VListItem title="موقع المريض">
            <template #subtitle>
              <div dir="ltr" class="text-right">{{ selectedLog.lat }}, {{ selectedLog.lng }}</div>
            </template>
          </VListItem>
          <VDivider />
          <VListItem title="المنطقة المتطابقة">
            <template #subtitle>
              {{ selectedLog.zone_id ? `المنطقة رقم ${selectedLog.zone_id}` : 'لا يوجد تطابق' }}
            </template>
          </VListItem>
          <VDivider />
          <VListItem title="خوارزمية التطابق">
            <template #subtitle>
              {{ selectedLog.match_type === 'polygon_exact' ? 'تطابق مضلع' : (selectedLog.match_type === 'grace_distance' ? 'مسافة سماح' : 'فشل') }}
            </template>
          </VListItem>
          <VDivider />
          <VListItem title="زمن الاستجابة">
            <template #subtitle>
              <span :class="{'text-error font-weight-bold': selectedLog.execution_time_ms > 30}">
                {{ selectedLog.execution_time_ms }} ميلي ثانية
              </span>
            </template>
          </VListItem>
        </VList>

        <h6 class="text-subtitle-1 mb-2">Raw JSON (Payload)</h6>
        <div class="bg-grey-100 pa-3 rounded border text-caption" style="direction: ltr; font-family: monospace; white-space: pre-wrap; overflow-x: auto;">
          {{ JSON.stringify(selectedLog, null, 2) }}
        </div>
      </div>
    </VNavigationDrawer>
  </div>
</template>
