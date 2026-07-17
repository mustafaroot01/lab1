<script setup lang="ts">
import AddEditTestDrawer from '@/views/medical-dictionary/AddEditTestDrawer.vue'

// States
const searchQuery = ref('')
const selectedGroupFilter = ref('all')
const selectedSampleFilter = ref('all')
const selectedFastingFilter = ref('all')

const tests = ref<any[]>([])
const totalTests = ref(0)
const loading = ref(false)

const groups = ref<any[]>([])
const summary = ref<any>({
  totalTests: 0,
  totalGroups: 0,
  fastingTests: 0,
  sampleTypes: [],
})

// Pagination & Sorting options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref('sort_order')
const orderBy = ref('asc')

const isDrawerOpen = ref(false)
const selectedTest = ref<any>(null)

// Fetch groups & summary
const fetchInitialData = async () => {
  try {
    const [groupsRes, summaryRes] = await Promise.all([
      $api('/medical-dictionary/groups'),
      $api('/medical-dictionary/summary')
    ])
    groups.value = groupsRes.groups || []
    summary.value = summaryRes || { totalTests: 0, totalGroups: 0, fastingTests: 0, sampleTypes: [] }
  } catch (error) {
    console.error('Error fetching initial data:', error)
  }
}

// Fetch tests
const fetchTests = async () => {
  loading.value = true
  try {
    const response = await $api('/medical-dictionary/tests', {
      query: {
        q: searchQuery.value,
        group_id: selectedGroupFilter.value,
        sample_type: selectedSampleFilter.value,
        fasting_required: selectedFastingFilter.value,
        itemsPerPage: itemsPerPage.value,
        page: page.value,
        sortBy: sortBy.value,
        orderBy: orderBy.value,
      },
    })
    tests.value = response.tests || []
    totalTests.value = response.totalTests || 0
  } catch (error) {
    console.error('Error fetching tests:', error)
  } finally {
    loading.value = false
  }
}

const updateOptions = (options: any) => {
  page.value = options.page
  itemsPerPage.value = options.itemsPerPage
  if (options.sortBy && options.sortBy.length > 0) {
    sortBy.value = options.sortBy[0].key
    orderBy.value = options.sortBy[0].order
  }
  fetchTests()
}

watch([searchQuery, selectedGroupFilter, selectedSampleFilter, selectedFastingFilter], () => {
  page.value = 1
  fetchTests()
})

onMounted(() => {
  fetchInitialData()
})

const headers = [
  { title: '# الترتيب', key: 'sort_order', width: '80px' },
  { title: 'اسم التحليل (عربي/إنجليزي)', key: 'name_ar' },
  { title: 'المجموعة المخبرية', key: 'group.name_ar', sortable: false },
  { title: 'المفتاح (Code)', key: 'key' },
  { title: 'نوع العينة والأنبوب', key: 'sample_info', sortable: false },
  { title: 'يتطلب صيام؟', key: 'fasting_required', width: '110px' },
  { title: 'سعر المختبر', key: 'price', width: '115px' },
  { title: 'سعر المنصة', key: 'platform_price', width: '115px' },
  { title: 'السعر الكلي للزبون', key: 'total_price', width: '140px' },
  { title: 'حالة التفعيل للمرضى', key: 'is_active', width: '160px' },
  { title: 'وقت النتيجة', key: 'result_time', width: '100px' },
  { title: 'الإجراءات', key: 'actions', sortable: false, width: '100px' },
]

const openAddDrawer = () => {
  selectedTest.value = null
  isDrawerOpen.value = true
}

const openEditDrawer = (test: any) => {
  selectedTest.value = { ...test }
  isDrawerOpen.value = true
}

const toggleTestStatus = async (test: any) => {
  try {
    await $api(`/medical-dictionary/tests/${test.id}/toggle-status`, {
      method: 'PUT',
      body: { is_active: test.is_active },
    })
  } catch (error) {
    console.error('Error toggling test status:', error)
    test.is_active = !test.is_active
  }
}

const handleTestSubmit = async (testData: any) => {
  try {
    if (testData.id) {
      await $api(`/medical-dictionary/tests/${testData.id}`, {
        method: 'PUT',
        body: testData,
      })
    } else {
      await $api('/medical-dictionary/tests', {
        method: 'POST',
        body: testData,
      })
    }
    fetchTests()
    fetchInitialData()
  } catch (error) {
    console.error('Error saving test:', error)
  }
}

const deleteTest = async (id: number) => {
  if (confirm('هل أنت متأكد من حذف هذا التحليل المخبري نهائياً؟')) {
    try {
      await $api(`/medical-dictionary/tests/${id}`, {
        method: 'DELETE',
      })
      fetchTests()
      fetchInitialData()
    } catch (error) {
      console.error('Error deleting test:', error)
    }
  }
}

const groupFilterItems = computed(() => [
  { title: 'جميع المجموعات', value: 'all' },
  ...groups.value.map(g => ({ title: g.name_ar, value: g.id }))
])

const sampleFilterItems = computed(() => {
  const list = [ { title: 'جميع أنواع العينات', value: 'all' } ]
  if (summary.value.sampleTypes && summary.value.sampleTypes.length) {
    summary.value.sampleTypes.forEach((s: any) => {
      const name = typeof s === 'string' ? s : s.name_ar
      list.push({ title: name, value: name })
    })
  } else {
    list.push(
      { title: 'دم', value: 'دم' },
      { title: 'بول / ادرار', value: 'بول' },
      { title: 'مسحة (Swab)', value: 'مسحة' },
      { title: 'براز', value: 'براز' }
    )
  }
  return list
})

const fastingFilterItems = [
  { title: 'الكل (صيام وغير صيام)', value: 'all' },
  { title: 'يتطلب صيام (نعم)', value: 'true' },
  { title: 'لا يتطلب صيام', value: 'false' },
]

const widgetData = computed(() => [
  { title: 'إجمالي التحاليل المتاحة', value: summary.value.totalTests, icon: 'tabler-flask', iconColor: 'primary' },
  { title: 'المجموعات المخبرية', value: summary.value.totalGroups, icon: 'tabler-category', iconColor: 'success' },
  { title: 'تحاليل تتطلب صياماً', value: summary.value.fastingTests, icon: 'tabler-clock', iconColor: 'warning' },
  { title: 'أنواع العينات والأنابيب', value: (summary.value.sampleTypes?.length || 4) + ' أنواع', icon: 'tabler-test-pipe', iconColor: 'info' },
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
        sm="6"
        md="3"
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

    <!-- 👉 Tests Table Card -->
    <VCard title="قاموس التحاليل المخبرية المعتمد">
      <VCardText class="d-flex flex-wrap py-4 gap-4 align-center justify-space-between">
        <div class="d-flex flex-wrap align-center gap-3 flex-grow-1">
          <AppTextField
            v-model="searchQuery"
            placeholder="بحث باسم التحليل أو الكود..."
            density="compact"
            style="min-inline-size: 240px; max-inline-size: 300px;"
            prepend-inner-icon="tabler-search"
            clearable
          />

          <AppSelect
            v-model="selectedGroupFilter"
            :items="groupFilterItems"
            density="compact"
            style="min-inline-size: 200px; max-inline-size: 250px;"
            label="المجموعة المخبرية"
          />

          <AppSelect
            v-model="selectedSampleFilter"
            :items="sampleFilterItems"
            density="compact"
            style="min-inline-size: 160px; max-inline-size: 200px;"
            label="نوع العينة"
          />

          <AppSelect
            v-model="selectedFastingFilter"
            :items="fastingFilterItems"
            density="compact"
            style="min-inline-size: 160px; max-inline-size: 200px;"
            label="شرط الصيام"
          />
        </div>

        <div class="d-flex align-center gap-4">
          <VBtn
            color="primary"
            prepend-icon="tabler-plus"
            @click="openAddDrawer"
          >
            إضافة تحليل جديد
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <!-- 👉 Data Table -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :headers="headers"
        :items="tests"
        :items-length="totalTests"
        :loading="loading"
        item-value="id"
        class="text-no-wrap"
        @update:options="updateOptions"
      >
        <!-- sort_order -->
        <template #item.sort_order="{ item }">
          <VChip size="small" variant="tonal" color="secondary">
            #{{ item.sort_order }}
          </VChip>
        </template>

        <!-- name_ar & name_en -->
        <template #item.name_ar="{ item }">
          <div class="d-flex flex-column py-1">
            <span class="font-weight-bold text-base text-high-emphasis">{{ item.name_ar }}</span>
            <span v-if="item.name_en" class="text-caption text-medium-emphasis">{{ item.name_en }}</span>
          </div>
        </template>

        <!-- group.name_ar -->
        <template #item.group.name_ar="{ item }">
          <VChip
            v-if="item.group"
            size="small"
            variant="tonal"
            :color="item.group.color ? 'primary' : 'primary'"
          >
            {{ item.group.name_ar }}
          </VChip>
          <span v-else class="text-disabled">—</span>
        </template>

        <!-- key -->
        <template #item.key="{ item }">
          <VChip v-if="item.key" size="small" variant="outlined" color="info">
            {{ item.key }}
          </VChip>
          <span v-else class="text-disabled">—</span>
        </template>

        <!-- sample_info -->
        <template #item.sample_info="{ item }">
          <div class="d-flex flex-column gap-1 py-1">
            <div class="d-flex align-center gap-1">
              <VChip
                size="x-small"
                variant="tonal"
                :color="item.sample_type_obj?.color || 'primary'"
              >
                <VIcon :icon="item.sample_type_obj?.icon || 'tabler-test-pipe'" size="14" class="me-1" />
                {{ item.sample_type_obj?.name_ar || item.sample_type || 'دم' }}
              </VChip>
            </div>
            <div v-if="item.tube_type_obj || item.tube_type" class="d-flex align-center gap-1 text-caption text-medium-emphasis">
              <span
                v-if="item.tube_type_obj?.color_hex"
                class="rounded-circle d-inline-block"
                :style="{ backgroundColor: item.tube_type_obj.color_hex, width: '10px', height: '10px' }"
              />
              <VIcon v-else icon="tabler-color-swatch" size="14" />
              <span>{{ item.tube_type_obj?.name_ar || item.tube_type }}</span>
            </div>
          </div>
        </template>

        <!-- fasting_required -->
        <template #item.fasting_required="{ item }">
          <VChip
            :color="item.fasting_required ? 'warning' : 'success'"
            variant="tonal"
            size="small"
          >
            {{ item.fasting_required ? 'نعم (صيام)' : 'لا يتطلب' }}
          </VChip>
        </template>

        <!-- price -->
        <template #item.price="{ item }">
          <span v-if="item.price && item.price > 0" class="font-weight-medium text-body-2">
            {{ Number(item.price).toLocaleString() }} د.ع
          </span>
          <span v-else class="text-disabled text-caption">غير محدد</span>
        </template>

        <!-- platform_price -->
        <template #item.platform_price="{ item }">
          <span v-if="item.platform_price && item.platform_price > 0" class="font-weight-medium text-body-2 text-info">
            {{ Number(item.platform_price).toLocaleString() }} د.ع
          </span>
          <span v-else class="text-disabled text-caption">0 د.ع</span>
        </template>

        <!-- total_price -->
        <template #item.total_price="{ item }">
          <VChip
            v-if="item.total_price && item.total_price > 0"
            color="success"
            variant="tonal"
            size="small"
            class="font-weight-bold"
          >
            {{ Number(item.total_price).toLocaleString() }} د.ع
          </VChip>
          <VChip v-else color="secondary" variant="outlined" size="small">
            غير محدد
          </VChip>
        </template>

        <!-- is_active -->
        <template #item.is_active="{ item }">
          <VSwitch
            v-model="item.is_active"
            color="success"
            hide-details
            @change="toggleTestStatus(item)"
          />
        </template>

        <!-- result_time -->
        <template #item.result_time="{ item }">
          <span class="text-body-2">{{ item.result_time || '—' }}</span>
        </template>

        <!-- Actions -->
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
                  <VListItemTitle>تعديل التحليل</VListItemTitle>
                </VListItem>

                <VListItem @click="deleteTest(item.id)">
                  <template #prepend>
                    <VIcon icon="tabler-trash" color="error" class="me-2" />
                  </template>
                  <VListItemTitle class="text-error">حذف التحليل</VListItemTitle>
                </VListItem>
              </VList>
            </VMenu>
          </VBtn>
        </template>
      </VDataTableServer>
    </VCard>

    <!-- 👉 Add / Edit Test Drawer -->
    <AddEditTestDrawer
      v-model:isDrawerOpen="isDrawerOpen"
      :test-data="selectedTest"
      :groups="groups"
      :sample-types="summary.sampleTypes"
      :tube-types="summary.tubeTypes"
      @submit="handleTestSubmit"
    />
  </section>
</template>
