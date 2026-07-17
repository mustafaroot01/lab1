<script setup lang="ts">
import AddEditSampleTypeDrawer from '@/views/medical-dictionary/AddEditSampleTypeDrawer.vue'

const searchQuery = ref('')
const samples = ref<any[]>([])
const totalSamples = ref(0)
const loading = ref(false)

const isDrawerOpen = ref(false)
const selectedSample = ref<any>(null)

const fetchSamples = async () => {
  loading.value = true
  try {
    const response = await $api('/medical-dictionary/sample-types', {
      query: {
        q: searchQuery.value,
      },
    })
    samples.value = response.sampleTypes || []
    totalSamples.value = response.totalSampleTypes || 0
  } catch (error) {
    console.error('Error fetching sample types:', error)
  } finally {
    loading.value = false
  }
}

watch(searchQuery, () => {
  fetchSamples()
})

onMounted(() => {
  fetchSamples()
})

const headers = [
  { title: '# الترتيب', key: 'sort_order', width: '90px' },
  { title: 'نوع العينة (عربي / إنجليزي)', key: 'name_ar' },
  { title: 'الرمز البرمجي (Code)', key: 'code', width: '130px' },
  { title: 'عدد التحاليل المرتبطة', key: 'tests_count', width: '160px' },
  { title: 'وصف العينة', key: 'description' },
  { title: 'الإجراءات', key: 'actions', sortable: false, width: '120px' },
]

const openAddDrawer = () => {
  selectedSample.value = null
  isDrawerOpen.value = true
}

const openEditDrawer = (sample: any) => {
  selectedSample.value = { ...sample }
  isDrawerOpen.value = true
}

const handleSampleSubmit = async (sampleData: any) => {
  try {
    if (sampleData.id) {
      await $api(`/medical-dictionary/sample-types/${sampleData.id}`, {
        method: 'PUT',
        body: sampleData,
      })
    } else {
      await $api('/medical-dictionary/sample-types', {
        method: 'POST',
        body: sampleData,
      })
    }
    fetchSamples()
  } catch (error) {
    console.error('Error saving sample type:', error)
  }
}

const deleteSample = async (id: number) => {
  if (confirm('هل أنت متأكد من حذف نوع العينة المخبري هذا؟')) {
    try {
      await $api(`/medical-dictionary/sample-types/${id}`, {
        method: 'DELETE',
      })
      fetchSamples()
    } catch (error) {
      console.error('Error deleting sample type:', error)
    }
  }
}

const widgetData = computed(() => [
  { title: 'إجمالي أنواع العينات', value: totalSamples.value, icon: 'tabler-test-pipe', iconColor: 'primary' },
  { title: 'الفحوصات المرتبطة بعينات', value: samples.value.reduce((acc, s) => acc + (s.tests_count || 0), 0) + ' تحليل', icon: 'tabler-droplet', iconColor: 'error' },
])
</script>

<template>
  <section>
    <!-- 👉 Widgets -->
    <VRow class="mb-6">
      <VCol
        v-for="(data, id) in widgetData"
        :key="id"
        cols="12"
        md="6"
      >
        <VCard>
          <VCardText class="d-flex justify-space-between">
            <div class="d-flex flex-column gap-y-1">
              <span class="text-base text-medium-emphasis">{{ data.title }}</span>
              <h4 class="text-h4 d-flex align-center gap-2">
                {{ data.value }}
              </h4>
            </div>
            <VAvatar
              :color="data.iconColor"
              variant="tonal"
              size="44"
              rounded
            >
              <VIcon :icon="data.icon" size="28" />
            </VAvatar>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- 👉 Table Card -->
    <VCard title="أنواع العينات المخبرية المعتمدة (Sample Types)">
      <VCardText class="d-flex flex-wrap py-4 gap-4 align-center justify-space-between">
        <div class="d-flex align-center gap-3">
          <AppTextField
            v-model="searchQuery"
            placeholder="بحث باسم العينة أو الكود..."
            density="compact"
            style="inline-size: 300px;"
            prepend-inner-icon="tabler-search"
            clearable
          />
        </div>

        <div class="d-flex align-center gap-4">
          <VBtn
            color="primary"
            prepend-icon="tabler-plus"
            @click="openAddDrawer"
          >
            إضافة نوع عينة
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <VDataTable
        :headers="headers"
        :items="samples"
        :loading="loading"
        item-value="id"
        class="text-no-wrap"
      >
        <template #item.sort_order="{ item }">
          <VChip size="small" variant="tonal" color="info">
            #{{ item.sort_order }}
          </VChip>
        </template>

        <template #item.name_ar="{ item }">
          <div class="d-flex align-center gap-x-3 py-1">
            <VAvatar
              :color="item.color || 'primary'"
              variant="tonal"
              size="38"
            >
              <VIcon :icon="item.icon || 'tabler-test-pipe'" size="22" />
            </VAvatar>
            <div class="d-flex flex-column">
              <span class="font-weight-bold text-base">{{ item.name_ar }}</span>
              <span v-if="item.name_en" class="text-caption text-medium-emphasis">{{ item.name_en }}</span>
            </div>
          </div>
        </template>

        <template #item.code="{ item }">
          <VChip v-if="item.code" size="small" variant="outlined" color="primary">
            {{ item.code }}
          </VChip>
          <span v-else class="text-disabled">—</span>
        </template>

        <template #item.tests_count="{ item }">
          <VChip color="success" variant="flat" size="small">
            {{ item.tests_count || 0 }} تحليل مرتبط
          </VChip>
        </template>

        <template #item.description="{ item }">
          <span class="text-body-2 text-truncate" style="max-inline-size: 300px; display: inline-block;">
            {{ item.description || '—' }}
          </span>
        </template>

        <template #item.actions="{ item }">
          <VBtn
            icon
            variant="text"
            color="medium-emphasis"
          >
            <VIcon icon="tabler-dots-vertical" />
            <VMenu activator="parent">
              <VList>
                <VListItem @click="openEditDrawer(item)">
                  <template #prepend>
                    <VIcon icon="tabler-pencil" class="me-2" />
                  </template>
                  <VListItemTitle>تعديل نوع العينة</VListItemTitle>
                </VListItem>

                <VListItem @click="deleteSample(item.id)">
                  <template #prepend>
                    <VIcon icon="tabler-trash" color="error" class="me-2" />
                  </template>
                  <VListItemTitle class="text-error">حذف نوع العينة</VListItemTitle>
                </VListItem>
              </VList>
            </VMenu>
          </VBtn>
        </template>
      </VDataTable>
    </VCard>

    <AddEditSampleTypeDrawer
      v-model:isDrawerOpen="isDrawerOpen"
      :sample-data="selectedSample"
      @submit="handleSampleSubmit"
    />
  </section>
</template>
