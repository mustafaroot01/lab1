<script setup lang="ts">
import AddEditTubeTypeDrawer from '@/views/medical-dictionary/AddEditTubeTypeDrawer.vue'

const searchQuery = ref('')
const tubes = ref<any[]>([])
const totalTubes = ref(0)
const loading = ref(false)

const isDrawerOpen = ref(false)
const selectedTube = ref<any>(null)

const fetchTubes = async () => {
  loading.value = true
  try {
    const response = await $api('/medical-dictionary/tube-types', {
      query: {
        q: searchQuery.value,
      },
    })
    tubes.value = response.tubeTypes || []
    totalTubes.value = response.totalTubeTypes || 0
  } catch (error) {
    console.error('Error fetching tube types:', error)
  } finally {
    loading.value = false
  }
}

watch(searchQuery, () => {
  fetchTubes()
})

onMounted(() => {
  fetchTubes()
})

const headers = [
  { title: '# الترتيب', key: 'sort_order', width: '90px' },
  { title: 'اسم الأنبوب (عربي / إنجليزي)', key: 'name_ar' },
  { title: 'لون الغطاء واللون المميز', key: 'cap_color', width: '200px' },
  { title: 'المادة المضافة (Additive)', key: 'additive', width: '200px' },
  { title: 'الكود المخبري', key: 'code', width: '130px' },
  { title: 'عدد التحاليل باستخدام الأنبوب', key: 'tests_count', width: '180px' },
  { title: 'الإجراءات', key: 'actions', sortable: false, width: '120px' },
]

const openAddDrawer = () => {
  selectedTube.value = null
  isDrawerOpen.value = true
}

const openEditDrawer = (tube: any) => {
  selectedTube.value = { ...tube }
  isDrawerOpen.value = true
}

const handleTubeSubmit = async (tubeData: any) => {
  try {
    if (tubeData.id) {
      await $api(`/medical-dictionary/tube-types/${tubeData.id}`, {
        method: 'PUT',
        body: tubeData,
      })
    } else {
      await $api('/medical-dictionary/tube-types', {
        method: 'POST',
        body: tubeData,
      })
    }
    fetchTubes()
  } catch (error) {
    console.error('Error saving tube type:', error)
  }
}

const deleteTube = async (id: number) => {
  if (confirm('هل أنت متأكد من حذف أنبوب السحب هذا؟')) {
    try {
      await $api(`/medical-dictionary/tube-types/${id}`, {
        method: 'DELETE',
      })
      fetchTubes()
    } catch (error) {
      console.error('Error deleting tube type:', error)
    }
  }
}

const widgetData = computed(() => [
  { title: 'أنابيب السحب المعتمدة', value: totalTubes.value, icon: 'tabler-color-swatch', iconColor: 'info' },
  { title: 'الفحوصات المرتبطة بالأنابيب', value: tubes.value.reduce((acc, t) => acc + (t.tests_count || 0), 0) + ' فحص', icon: 'tabler-test-pipe', iconColor: 'success' },
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
    <VCard title="قاموس وأنابيب سحب العينات المخبرية (Tube Types)">
      <VCardText class="d-flex flex-wrap py-4 gap-4 align-center justify-space-between">
        <div class="d-flex align-center gap-3">
          <AppTextField
            v-model="searchQuery"
            placeholder="بحث باسم الأنبوب، الكود، أو اللون..."
            density="compact"
            style="inline-size: 320px;"
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
            إضافة أنبوب سحب جديد
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <VDataTable
        :headers="headers"
        :items="tubes"
        :loading="loading"
        item-value="id"
        class="text-no-wrap"
      >
        <template #item.sort_order="{ item }">
          <VChip size="small" variant="tonal" color="secondary">
            #{{ item.sort_order }}
          </VChip>
        </template>

        <template #item.name_ar="{ item }">
          <div class="d-flex align-center gap-x-3 py-1">
            <div
              class="rounded-circle d-flex align-center justify-center"
              :style="{ backgroundColor: item.color_hex || '#607d8b', width: '38px', height: '38px', color: '#fff' }"
            >
              <VIcon icon="tabler-color-swatch" size="20" />
            </div>
            <div class="d-flex flex-column">
              <span class="font-weight-bold text-base">{{ item.name_ar }}</span>
              <span v-if="item.name_en" class="text-caption text-medium-emphasis">{{ item.name_en }}</span>
            </div>
          </div>
        </template>

        <template #item.cap_color="{ item }">
          <VChip
            size="small"
            variant="tonal"
            :style="{ borderColor: item.color_hex || '#607d8b', color: item.color_hex || 'inherit' }"
          >
            {{ item.cap_color || 'غير محدد' }}
          </VChip>
        </template>

        <template #item.additive="{ item }">
          <span class="text-body-2 font-weight-medium text-high-emphasis">
            {{ item.additive || '—' }}
          </span>
        </template>

        <template #item.code="{ item }">
          <VChip v-if="item.code" size="small" variant="outlined" color="info">
            {{ item.code }}
          </VChip>
          <span v-else class="text-disabled">—</span>
        </template>

        <template #item.tests_count="{ item }">
          <VChip color="success" variant="flat" size="small">
            {{ item.tests_count || 0 }} فحص مرتبط
          </VChip>
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
                  <VListItemTitle>تعديل أنبوب السحب</VListItemTitle>
                </VListItem>

                <VListItem @click="deleteTube(item.id)">
                  <template #prepend>
                    <VIcon icon="tabler-trash" color="error" class="me-2" />
                  </template>
                  <VListItemTitle class="text-error">حذف أنبوب السحب</VListItemTitle>
                </VListItem>
              </VList>
            </VMenu>
          </VBtn>
        </template>
      </VDataTable>
    </VCard>

    <AddEditTubeTypeDrawer
      v-model:isDrawerOpen="isDrawerOpen"
      :tube-data="selectedTube"
      @submit="handleTubeSubmit"
    />
  </section>
</template>
