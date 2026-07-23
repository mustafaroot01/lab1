<script setup lang="ts">
import { ref, onMounted } from 'vue'

const kpis = ref<any>(null)
const loading = ref(false)

const fetchKpis = async () => {
  loading.value = true
  try {
    const res = await $api('/coverage-logs/kpis')
    kpis.value = res.data
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchKpis()
})
</script>

<template>
  <div>
    <h4 class="text-h4 mb-6">لوحة معلومات التغطية الجغرافية</h4>

    <VRow v-if="loading">
      <VCol cols="12" class="text-center py-10">
        <VProgressCircular indeterminate color="primary" />
      </VCol>
    </VRow>

    <template v-else-if="kpis">
      <VRow>
        <VCol cols="12" md="3" sm="6">
          <VCard class="h-100">
            <VCardText class="d-flex align-center justify-space-between">
              <div>
                <span class="text-body-1">المناطق الفعالة</span>
                <div class="text-h3 font-weight-bold text-primary mt-2">{{ kpis.zones.active }}</div>
                <div class="text-caption mt-1">من أصل {{ kpis.zones.total }}</div>
              </div>
              <VAvatar color="primary" variant="tonal" rounded size="50">
                <VIcon icon="tabler-map" size="28" />
              </VAvatar>
            </VCardText>
          </VCard>
        </VCol>

        <VCol cols="12" md="3" sm="6">
          <VCard class="h-100">
            <VCardText class="d-flex align-center justify-space-between">
              <div>
                <span class="text-body-1">عمليات ناجحة (اليوم)</span>
                <div class="text-h3 font-weight-bold text-success mt-2">{{ kpis.today.success }}</div>
                <div class="text-caption mt-1">طلبات داخل مناطق التغطية</div>
              </div>
              <VAvatar color="success" variant="tonal" rounded size="50">
                <VIcon icon="tabler-check" size="28" />
              </VAvatar>
            </VCardText>
          </VCard>
        </VCol>

        <VCol cols="12" md="3" sm="6">
          <VCard class="h-100">
            <VCardText class="d-flex align-center justify-space-between">
              <div>
                <span class="text-body-1">حالات السماح (Grace)</span>
                <div class="text-h3 font-weight-bold text-warning mt-2">{{ kpis.today.grace_matches }}</div>
                <div class="text-caption mt-1">طلبات قريبة جداً من الحدود</div>
              </div>
              <VAvatar color="warning" variant="tonal" rounded size="50">
                <VIcon icon="tabler-alert-triangle" size="28" />
              </VAvatar>
            </VCardText>
          </VCard>
        </VCol>

        <VCol cols="12" md="3" sm="6">
          <VCard class="h-100">
            <VCardText class="d-flex align-center justify-space-between">
              <div>
                <span class="text-body-1">الطلبات المرفوضة</span>
                <div class="text-h3 font-weight-bold text-error mt-2">{{ kpis.today.rejected }}</div>
                <div class="text-caption mt-1">خارج التغطية والسماح</div>
              </div>
              <VAvatar color="error" variant="tonal" rounded size="50">
                <VIcon icon="tabler-x" size="28" />
              </VAvatar>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>

      <VRow class="mt-6">
        <VCol cols="12">
          <VCard title="أحدث المشاكل (طلبات مرفوضة أو بطيئة)">
            <VDataTable
              :headers="[
                { title: 'رقم السجل', key: 'id' },
                { title: 'زمن الاستجابة', key: 'execution_time_ms' },
                { title: 'السبب', key: 'is_covered' },
                { title: 'تاريخ', key: 'created_at' },
              ]"
              :items="kpis.latest_issues"
              hover
            >
              <template #item.execution_time_ms="{ item }">
                <VChip size="small" :color="item.execution_time_ms > 30 ? 'error' : 'success'">
                  {{ item.execution_time_ms }} ms
                </VChip>
              </template>
              <template #item.is_covered="{ item }">
                <VChip size="small" :color="!item.is_covered ? 'error' : 'info'">
                  {{ !item.is_covered ? 'مرفوض (خارج التغطية)' : 'بطء استجابة' }}
                </VChip>
              </template>
              <template #item.created_at="{ item }">
                {{ new Date(item.created_at).toLocaleString('ar-IQ') }}
              </template>
            </VDataTable>
          </VCard>
        </VCol>
      </VRow>
    </template>
  </div>
</template>
