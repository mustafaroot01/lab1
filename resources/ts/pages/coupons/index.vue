<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const coupons = ref<any[]>([])
const totalCoupons = ref(0)
const loading = ref(false)

const searchQuery = ref('')
const selectedStatusFilter = ref('all')
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref('id')
const orderBy = ref('desc')

const summary = ref({
  totalCoupons: 0,
  activeCoupons: 0,
  expiredCoupons: 0,
  totalUsages: 0,
})

const fetchCoupons = async () => {
  loading.value = true
  try {
    const response = await $api('/coupons', {
      query: {
        q: searchQuery.value,
        status: selectedStatusFilter.value,
        itemsPerPage: itemsPerPage.value,
        page: page.value,
        sortBy: sortBy.value,
        orderBy: orderBy.value,
      },
    })
    coupons.value = response.coupons || []
    totalCoupons.value = response.totalCoupons || 0
    if (response.summary) {
      summary.value = response.summary
    }
  } catch (error) {
    console.error('Error fetching coupons:', error)
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
  fetchCoupons()
}

watch([searchQuery, selectedStatusFilter], () => {
  page.value = 1
  fetchCoupons()
})

const navigateToAdd = () => {
  router.push('/coupons/add')
}

const navigateToEdit = (item: any) => {
  router.push(`/coupons/edit/${item.id}`)
}

const navigateToUsages = (item: any) => {
  router.push(`/coupons/usages/${item.id}`)
}

const toggleCouponStatus = async (item: any) => {
  try {
    await $api(`/coupons/${item.id}/toggle-status`, {
      method: 'PUT',
      body: {
        is_active: !item.is_active,
      },
    })
    fetchCoupons()
  } catch (error) {
    console.error('Error toggling coupon status:', error)
  }
}

const deleteCoupon = async (id: number) => {
  if (confirm('هل أنت متأكد من حذف هذا الكوبون نهائياً مع كافة سجلات الاستخدام الخاصة به؟')) {
    try {
      await $api(`/coupons/${id}`, {
        method: 'DELETE',
      })
      fetchCoupons()
    } catch (error) {
      console.error('Error deleting coupon:', error)
    }
  }
}

const formatDateShort = (dateStr: string) => {
  if (!dateStr) return '—'
  try {
    const d = new Date(dateStr)
    return d.toLocaleDateString('ar-EG', { year: 'numeric', month: 'short', day: 'numeric' })
  } catch {
    return dateStr
  }
}

const getStatusColor = (status: string) => {
  switch (status) {
    case 'active':
      return 'success'
    case 'expired_time':
      return 'error'
    case 'expired_limit':
      return 'warning'
    case 'upcoming':
      return 'info'
    case 'inactive':
    default:
      return 'secondary'
  }
}

const getStatusText = (status: string) => {
  switch (status) {
    case 'active':
      return 'فعّال ومتاح'
    case 'expired_time':
      return 'منتهي (الوقت)'
    case 'expired_limit':
      return 'منتهي (العدد)'
    case 'upcoming':
      return 'لم يبدأ بعد'
    case 'inactive':
    default:
      return 'موقوف يدويًا'
  }
}

const headers = [
  { title: 'رمز الخصم (Code)', key: 'code', sortable: true },
  { title: 'اسم / عنوان الكوبون', key: 'name_ar', sortable: true },
  { title: 'نوع وقيمة الخصم', key: 'discount_info', sortable: false },
  { title: 'صلاحية الوقت (البدء / الانتهاء)', key: 'validity', sortable: false },
  { title: 'الاستخدام (المستفيدين / الحد)', key: 'usage_progress', sortable: true },
  { title: 'الحالة', key: 'status', sortable: false },
  { title: 'الإجراءات', key: 'actions', sortable: false, align: 'center' },
]

const statusFilterItems = [
  { title: 'جميع الحالات', value: 'all' },
  { title: 'فعّال ومتاح (Active)', value: 'active' },
  { title: 'منتهي الصلاحية - انتهى الوقت', value: 'expired_time' },
  { title: 'منتهي الصلاحية - اكتمل العدد', value: 'expired_limit' },
  { title: 'موقوف يدويًا (Inactive)', value: 'inactive' },
  { title: 'لم يبدأ بعد (Upcoming)', value: 'upcoming' },
]

const widgetData = computed(() => [
  { title: 'إجمالي الكوبونات', value: summary.value.totalCoupons, icon: 'tabler-discount-2', iconColor: 'primary' },
  { title: 'الكوبونات الفعّالة', value: summary.value.activeCoupons, icon: 'tabler-circle-check', iconColor: 'success' },
  { title: 'الكوبونات المنتهية (Expired)', value: summary.value.expiredCoupons, icon: 'tabler-alert-triangle', iconColor: 'error' },
  { title: 'إجمالي عمليات الاستخدام', value: summary.value.totalUsages + ' مريض', icon: 'tabler-users', iconColor: 'info' },
])

onMounted(() => {
  fetchCoupons()
})
</script>

<template>
  <section>
    <!-- 👉 Top Header & Action Buttons -->
    <div class="d-flex flex-wrap justify-start justify-sm-space-between align-center gap-y-4 gap-x-6 mb-6">
      <div class="d-flex flex-column justify-center">
        <h4 class="text-h4 font-weight-medium mb-1">
          الكوبونات ورموز الخصم الترويجية
        </h4>
        <div class="text-body-1 text-medium-emphasis">
          إدارة وتجهيز رموز الخصم الترويجية للمرضى وتحديد صلاحيات التواريخ وحدود الاستخدام المخبرية
        </div>
      </div>

      <div class="d-flex gap-4 align-center flex-wrap">
        <VBtn
          color="primary"
          prepend-icon="tabler-plus"
          @click="navigateToAdd"
        >
          إضافة كوبون جديد
        </VBtn>
      </div>
    </div>

    <!-- 👉 Summary Stats Widgets -->
    <VRow class="mb-6">
      <VCol
        v-for="(data, id) in widgetData"
        :key="id"
        cols="12"
        sm="6"
        md="3"
      >
        <VCard>
          <VCardText class="d-flex justify-space-between align-center">
            <div class="d-flex flex-column">
              <span class="text-body-2 text-medium-emphasis mb-1">{{ data.title }}</span>
              <h4 class="text-h4 font-weight-bold">
                {{ data.value }}
              </h4>
            </div>
            <VAvatar
              :color="data.iconColor"
              variant="tonal"
              size="44"
              rounded
            >
              <VIcon :icon="data.icon" size="26" />
            </VAvatar>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- 👉 Coupons Table Card -->
    <VCard title="قائمة الكوبونات والخصومات المتاحة">
      <VCardText class="d-flex flex-wrap py-4 gap-4 align-center justify-space-between">
        <div class="d-flex flex-wrap align-center gap-4 flex-grow-1">
          <div style="inline-size: 16rem;">
            <AppTextField
              v-model="searchQuery"
              placeholder="بحث برمز الكوبون أو الاسم..."
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
            إضافة كوبون
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <!-- 👉 Data Table -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :headers="headers"
        :items="coupons"
        :items-length="totalCoupons"
        :loading="loading"
        item-value="id"
        class="text-no-wrap"
        @update:options="updateOptions"
      >
        <!-- Code -->
        <template #item.code="{ item }">
          <div class="d-flex align-center gap-2">
            <VChip
              variant="tonal"
              color="primary"
              size="small"
              class="font-weight-bold letter-spacing-1 px-3"
            >
              {{ item.code }}
            </VChip>
            <VTooltip text="عرض سجل استخدام هذا الكوبون والمستفيدين">
              <template #activator="{ props }">
                <VBtn
                  v-bind="props"
                  icon
                  size="x-small"
                  variant="text"
                  color="medium-emphasis"
                  @click="navigateToUsages(item)"
                >
                  <VIcon icon="tabler-external-link" size="18" />
                </VBtn>
              </template>
            </VTooltip>
          </div>
        </template>

        <!-- Name -->
        <template #item.name_ar="{ item }">
          <div class="d-flex flex-column">
            <span class="font-weight-medium text-body-1">{{ item.name_ar }}</span>
            <span v-if="item.name_en" class="text-caption text-medium-emphasis">{{ item.name_en }}</span>
          </div>
        </template>

        <!-- Discount Info -->
        <template #item.discount_info="{ item }">
          <VChip
            :color="item.discount_type === 'percentage' ? 'info' : 'success'"
            variant="tonal"
            size="small"
            class="font-weight-medium"
          >
            {{ item.discount_type === 'percentage' ? `نسبة: ${item.discount_value}%` : `مبلغ: ${Number(item.discount_value).toLocaleString()} د.ع` }}
          </VChip>
        </template>

        <!-- Validity Dates -->
        <template #item.validity="{ item }">
          <div class="text-body-2">
            <div><span class="text-medium-emphasis text-caption">من:</span> {{ formatDateShort(item.start_date) }}</div>
            <div><span class="text-medium-emphasis text-caption">إلى:</span> {{ item.end_date ? formatDateShort(item.end_date) : 'دائم (بدون انتهاء)' }}</div>
          </div>
        </template>

        <!-- Usage Progress -->
        <template #item.usage_progress="{ item }">
          <div style="min-inline-size: 140px; max-inline-size: 180px;">
            <div class="d-flex justify-space-between text-caption mb-1">
              <span class="font-weight-bold text-primary">{{ item.used_count }} مستخدم</span>
              <span class="text-medium-emphasis">{{ item.usage_limit ? `/ ${item.usage_limit}` : ' / مفتوح' }}</span>
            </div>
            <VProgressLinear
              :model-value="item.usage_limit ? (item.used_count / item.usage_limit) * 100 : (item.used_count > 0 ? 100 : 0)"
              :color="item.usage_limit && item.used_count >= item.usage_limit ? 'error' : 'primary'"
              height="6"
              rounded
            />
          </div>
        </template>

        <!-- Status -->
        <template #item.status="{ item }">
          <VChip
            :color="getStatusColor(item.status)"
            size="small"
            variant="flat"
            class="font-weight-bold"
          >
            {{ getStatusText(item.status) }}
          </VChip>
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
                <VListItem @click="navigateToUsages(item)">
                  <template #prepend>
                    <VIcon icon="tabler-users" color="info" class="me-2" />
                  </template>
                  <VListItemTitle>سجل الاستخدام والمستفيدين</VListItemTitle>
                </VListItem>

                <VListItem @click="navigateToEdit(item)">
                  <template #prepend>
                    <VIcon icon="tabler-pencil" class="me-2" />
                  </template>
                  <VListItemTitle>تعديل الكوبون</VListItemTitle>
                </VListItem>

                <VListItem @click="toggleCouponStatus(item)">
                  <template #prepend>
                    <VIcon :icon="item.is_active ? 'tabler-power' : 'tabler-check'" :color="item.is_active ? 'warning' : 'success'" class="me-2" />
                  </template>
                  <VListItemTitle>{{ item.is_active ? 'إيقاف مؤقت' : 'تفعيل الكوبون' }}</VListItemTitle>
                </VListItem>

                <VDivider class="my-1" />

                <VListItem @click="deleteCoupon(item.id)">
                  <template #prepend>
                    <VIcon icon="tabler-trash" color="error" class="me-2" />
                  </template>
                  <VListItemTitle class="text-error">حذف الكوبون</VListItemTitle>
                </VListItem>
              </VList>
            </VMenu>
          </VBtn>
        </template>

        <template #bottom>
          <TablePagination
            v-if="totalCoupons > 0"
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalCoupons"
          />
        </template>
      </VDataTableServer>
    </VCard>

  </section>
</template>
