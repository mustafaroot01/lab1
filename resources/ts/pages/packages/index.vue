<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'

interface MedicalTestSnippet {
  id: number
  name_ar: string
  name_en?: string
  price: number
}

interface PackageOffer {
  id: number
  name_ar: string
  name_en?: string
  description_ar?: string
  description_en?: string
  original_price: number
  discount_price?: number | null
  image?: string
  sort_order: number
  is_active: boolean
  tests_count: number
  tests?: MedicalTestSnippet[]
}

const router = useRouter()

// Data states
const packages = ref<PackageOffer[]>([])
const totalPackages = ref(0)
const loading = ref(false)

// Global Section Switch
const globalSectionActive = ref(true)
const globalSwitchLoading = ref(false)

// Summary Stats
const summary = ref({
  totalPackages: 0,
  activePackages: 0,
  inactivePackages: 0,
})

// Filters & Pagination
const searchQuery = ref('')
const selectedStatusFilter = ref('all')
const statusFilterItems = [
  { title: 'جميع الحالات', value: 'all' },
  { title: 'فعّال ومتاح في التطبيق', value: 'active' },
  { title: 'مخفي أو متوقف', value: 'inactive' },
]

const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref([{ key: 'sort_order', order: 'asc' }])

// Headers for VDataTableServer
const headers = [
  { title: 'الترتيب', key: 'sort_order', sortable: true },
  { title: 'الباقة / العرض', key: 'name_ar', sortable: true },
  { title: 'التحاليل المشمولة', key: 'tests_count', sortable: false },
  { title: 'السعر (سعر الخصم)', key: 'prices', sortable: true },
  { title: 'الحالة في التطبيق', key: 'is_active', sortable: true },
  { title: 'إجراءات', key: 'actions', sortable: false, align: 'end' },
]

// Fetch Packages
const fetchPackages = async () => {
  loading.value = true
  try {
    const sortKey = sortBy.value?.[0]?.key || 'sort_order'
    const sortOrder = sortBy.value?.[0]?.order || 'asc'

    const res = await $api('/package-offers', {
      method: 'GET',
      query: {
        q: searchQuery.value || undefined,
        status: selectedStatusFilter.value !== 'all' ? selectedStatusFilter.value : undefined,
        page: page.value,
        itemsPerPage: itemsPerPage.value,
        sortBy: sortKey,
        orderBy: sortOrder,
      },
    })

    if (res.status) {
      packages.value = res.package_offers || []
      totalPackages.value = res.totalPackageOffers || 0
      globalSectionActive.value = res.global_section_active ?? true
      if (res.summary) {
        summary.value = res.summary
      }
    }
  } catch (error) {
    console.error('Error fetching package offers:', error)
  } finally {
    loading.value = false
  }
}

// Watch filters
watch([searchQuery, selectedStatusFilter, itemsPerPage], () => {
  page.value = 1
  fetchPackages()
})

const updateOptions = (options: any) => {
  if (options.page) page.value = options.page
  if (options.itemsPerPage) itemsPerPage.value = options.itemsPerPage
  if (options.sortBy && options.sortBy.length > 0) {
    sortBy.value = options.sortBy
  }
  fetchPackages()
}

// Toggle Global Section Status
const toggleGlobalSection = async (newVal: boolean | null) => {
  if (newVal === null) return
  globalSwitchLoading.value = true
  try {
    const res = await $api('/package-offers/toggle-global-status', {
      method: 'PUT',
      body: { is_active: newVal },
    })
    if (res.status && res.global_section_active !== undefined) {
      globalSectionActive.value = res.global_section_active
    }
  } catch (error) {
    console.error('Error toggling global section status:', error)
    globalSectionActive.value = !newVal
  } finally {
    globalSwitchLoading.value = false
  }
}

// Toggle Individual Package Status
const togglePackageStatus = async (item: PackageOffer) => {
  try {
    const res = await $api(`/package-offers/${item.id}/toggle-status`, {
      method: 'PUT',
      body: { is_active: item.is_active },
    })
    if (res.status && res.package_offer) {
      item.is_active = res.package_offer.is_active
    }
    fetchPackages()
  } catch (error) {
    console.error('Error toggling package status:', error)
    item.is_active = !item.is_active
  }
}

// Delete Package
const deletePackage = async (id: number) => {
  if (!confirm('هل أنت متأكد من رغبتك في حذف هذه الباقة أو العرض المخبري نهائياً؟')) return
  try {
    await $api(`/package-offers/${id}`, { method: 'DELETE' })
    fetchPackages()
  } catch (error) {
    console.error('Error deleting package:', error)
  }
}

const navigateToAdd = () => {
  router.push('/packages/add')
}

const navigateToEdit = (id: number) => {
  router.push(`/packages/edit/${id}`)
}

onMounted(() => {
  fetchPackages()
})
</script>

<template>
  <div class="packages-index-page">
    <!-- Top Header -->
    <VRow class="mb-6 align-center justify-space-between">
      <VCol cols="12" md="7">
        <div class="d-flex align-center gap-3">
          <VAvatar color="primary" variant="tonal" size="48" rounded>
            <VIcon icon="tabler-packages" size="28" />
          </VAvatar>
          <div>
            <h3 class="text-h3 font-weight-bold mb-1">
              إدارة الباقات والعروض المخبرية
            </h3>
            <span class="text-body-2 text-medium-emphasis">
              إنشاء عروض خاصة وباقات تحاليل وتحديد الأسعار وتخفيضاتها والتحكم بظهورها في التطبيق
            </span>
          </div>
        </div>
      </VCol>

      <VCol cols="12" md="5" class="d-flex justify-md-end justify-start gap-4 align-center flex-wrap">
        <VCard variant="outlined" class="px-4 py-2 d-flex align-center gap-3 border-primary" style="border-radius: 8px;">
          <div class="d-flex flex-column text-end">
            <span class="text-caption font-weight-bold text-primary">صفحة العروض في التطبيق</span>
            <span class="text-xs text-medium-emphasis">{{ globalSectionActive ? 'متاحة للزوار' : 'مخفية حالياً' }}</span>
          </div>
          <VSwitch
            v-model="globalSectionActive"
            color="primary"
            hide-details
            :loading="globalSwitchLoading"
            @update:model-value="toggleGlobalSection"
          />
        </VCard>

        <VBtn
          color="primary"
          prepend-icon="tabler-plus"
          @click="navigateToAdd"
        >
          إضافة باقة / عرض
        </VBtn>
      </VCol>
    </VRow>

    <!-- Summary Widgets Grid -->
    <VRow class="mb-6">
      <VCol cols="12" sm="6" md="3">
        <VCard>
          <VCardText class="d-flex justify-space-between align-center">
            <div class="d-flex flex-column">
              <span class="text-body-2 text-medium-emphasis mb-1">إجمالي العروض والباقات</span>
              <h4 class="text-h4 font-weight-bold">
                {{ summary.totalPackages }}
              </h4>
            </div>
            <VAvatar color="primary" variant="tonal" size="44" rounded>
              <VIcon icon="tabler-packages" size="26" />
            </VAvatar>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" sm="6" md="3">
        <VCard>
          <VCardText class="d-flex justify-space-between align-center">
            <div class="d-flex flex-column">
              <span class="text-body-2 text-medium-emphasis mb-1">العروض النشطة والمشورة</span>
              <h4 class="text-h4 font-weight-bold text-success">
                {{ summary.activePackages }}
              </h4>
            </div>
            <VAvatar color="success" variant="tonal" size="44" rounded>
              <VIcon icon="tabler-discount-check" size="26" />
            </VAvatar>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" sm="6" md="3">
        <VCard>
          <VCardText class="d-flex justify-space-between align-center">
            <div class="d-flex flex-column">
              <span class="text-body-2 text-medium-emphasis mb-1">العروض المخفية مؤقتاً</span>
              <h4 class="text-h4 font-weight-bold text-warning">
                {{ summary.inactivePackages }}
              </h4>
            </div>
            <VAvatar color="warning" variant="tonal" size="44" rounded>
              <VIcon icon="tabler-eye-off" size="26" />
            </VAvatar>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" sm="6" md="3">
        <VCard>
          <VCardText class="d-flex justify-space-between align-center">
            <div class="d-flex flex-column">
              <span class="text-body-2 text-medium-emphasis mb-1">حالة ظهور العروض في التطبيق</span>
              <h4 class="text-h5 font-weight-bold" :class="globalSectionActive ? 'text-primary' : 'text-error'">
                {{ globalSectionActive ? 'شغال ومتاح' : 'متوقف ومخفي' }}
              </h4>
            </div>
            <VAvatar :color="globalSectionActive ? 'primary' : 'error'" variant="tonal" size="44" rounded>
              <VIcon :icon="globalSectionActive ? 'tabler-app-window' : 'tabler-lock'" size="26" />
            </VAvatar>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Packages Data Table Card -->
    <VCard title="قائمة الباقات والعروض المخبرية">
      <!-- Perfectly aligned search and filter bar without label -->
      <VCardText class="d-flex flex-wrap py-4 gap-4 align-center justify-space-between">
        <div class="d-flex flex-wrap align-center gap-4 flex-grow-1">
          <div style="inline-size: 18rem;">
            <AppTextField
              v-model="searchQuery"
              placeholder="بحث باسم الباقة أو العرض..."
              prepend-inner-icon="tabler-search"
              clearable
            />
          </div>

          <div style="inline-size: 15rem;">
            <AppSelect
              v-model="selectedStatusFilter"
              :items="statusFilterItems"
              placeholder="تصفية حسب الحالة"
              clearable
            />
          </div>
        </div>

        <div class="d-flex align-center gap-4 flex-wrap">
          <div style="inline-size: 6.5rem;">
            <AppSelect
              :model-value="itemsPerPage"
              :items="[
                { value: 10, title: '10' },
                { value: 25, title: '25' },
                { value: 50, title: '50' },
                { value: 100, title: '100' },
                { value: -1, title: 'الكل' },
              ]"
              @update:model-value="itemsPerPage = parseInt($event, 10)"
            />
          </div>

          <VBtn
            variant="tonal"
            color="primary"
            prepend-icon="tabler-plus"
            @click="navigateToAdd"
          >
            إضافة باقة / عرض
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        v-model:sort-by="sortBy"
        :headers="headers"
        :items="packages"
        :items-length="totalPackages"
        :loading="loading"
        item-value="id"
        class="text-no-wrap"
        @update:options="updateOptions"
      >
        <!-- Sort Order -->
        <template #item.sort_order="{ item }">
          <VChip variant="tonal" color="secondary" size="small" class="font-weight-bold">
            #{{ item.sort_order }}
          </VChip>
        </template>

        <!-- Package Name & Image -->
        <template #item.name_ar="{ item }">
          <div class="d-flex align-center gap-3 py-2">
            <div
              class="rounded border d-flex align-center justify-center overflow-hidden flex-shrink-0"
              style="width:44px; height:44px; background: rgba(var(--v-theme-primary), 0.08);"
            >
              <VImg
                v-if="item.image"
                :src="item.image"
                width="44"
                height="44"
                cover
                alt=""
              />
              <VIcon v-else icon="tabler-package" size="24" color="primary" />
            </div>
            <div class="d-flex flex-column">
              <span class="font-weight-bold text-high-emphasis">{{ item.name_ar }}</span>
              <span v-if="item.description_ar" class="text-caption text-medium-emphasis text-truncate" style="max-inline-size: 260px;">
                {{ item.description_ar }}
              </span>
            </div>
          </div>
        </template>

        <!-- Tests Count -->
        <template #item.tests_count="{ item }">
          <VChip variant="tonal" color="info" size="small" prepend-icon="tabler-flask">
            {{ item.tests_count }} تحاليل مشمولة
          </VChip>
        </template>

        <!-- Prices (Original vs Discount) -->
        <template #item.prices="{ item }">
          <div class="d-flex flex-column">
            <template v-if="item.discount_price && item.discount_price > 0 && item.discount_price < item.original_price">
              <span class="text-caption text-medium-emphasis text-decoration-line-through">
                {{ item.original_price.toLocaleString() }} د.ع
              </span>
              <div class="d-flex align-center gap-1">
                <span class="font-weight-bold text-success text-body-1">
                  {{ item.discount_price.toLocaleString() }} د.ع
                </span>
                <VChip size="x-small" color="success" variant="elevated">خصم</VChip>
              </div>
            </template>
            <template v-else>
              <span class="font-weight-bold text-primary text-body-1">
                {{ item.original_price.toLocaleString() }} د.ع
              </span>
            </template>
          </div>
        </template>

        <!-- Is Active Switch -->
        <template #item.is_active="{ item }">
          <div class="d-flex align-center gap-2">
            <VSwitch
              v-model="item.is_active"
              color="success"
              hide-details
              @update:model-value="togglePackageStatus(item)"
            />
            <span class="text-caption" :class="item.is_active ? 'text-success' : 'text-medium-emphasis'">
              {{ item.is_active ? 'منشور في التطبيق' : 'مخفي حالياً' }}
            </span>
          </div>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <div class="d-flex gap-1 justify-end">
            <VBtn
              icon
              variant="text"
              size="small"
              color="primary"
              title="تعديل الباقة / العرض"
              @click="navigateToEdit(item.id)"
            >
              <VIcon icon="tabler-edit" />
            </VBtn>
            <VBtn
              icon
              variant="text"
              size="small"
              color="error"
              title="حذف الباقة / العرض"
              @click="deletePackage(item.id)"
            >
              <VIcon icon="tabler-trash" />
            </VBtn>
          </div>
        </template>

        <!-- Empty state -->
        <template #no-data>
          <div class="py-8 text-center">
            <VIcon icon="tabler-package-off" size="48" color="medium-emphasis" class="mb-2" />
            <div class="text-body-1 text-medium-emphasis">لا توجد باقات أو عروض مخبرية حالياً</div>
          </div>
        </template>

        <template #bottom>
          <TablePagination
            v-if="totalPackages > 0"
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalPackages"
          />
        </template>
      </VDataTableServer>
    </VCard>

  </div>
</template>
