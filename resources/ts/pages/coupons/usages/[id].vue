<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const couponId = ref(Number(route.params.id))

const loading = ref(false)
const coupon = ref<any>(null)
const usages = ref<any[]>([])
const totalUsages = ref(0)

const searchQuery = ref('')
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref('used_at')
const orderBy = ref('desc')

const showSimulateForm = ref(false)
const simUserName = ref('')
const simPhone = ref('')
const simTotalBefore = ref<number | null>(null)
const simError = ref('')
const simLoading = ref(false)

const fetchUsagesData = async () => {
  if (!couponId.value) return
  loading.value = true
  try {
    const res = await $api(`/coupons/${couponId.value}`, {
      query: {
        q: searchQuery.value,
        itemsPerPage: itemsPerPage.value,
        page: page.value,
        sortBy: sortBy.value,
        orderBy: orderBy.value,
      },
    })
    coupon.value = res.coupon || null
    usages.value = res.usages || []
    totalUsages.value = res.totalUsages || 0
  } catch (err) {
    console.error('Error fetching coupon usages:', err)
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
  fetchUsagesData()
}

watch(searchQuery, () => {
  page.value = 1
  fetchUsagesData()
})

const handleRecordUsage = async () => {
  if (!simUserName.value || !simTotalBefore.value) {
    simError.value = 'يرجى إدخال اسم المريض والمبلغ الكلي قبل الخصم'
    return
  }
  simError.value = ''
  simLoading.value = true
  try {
    await $api(`/coupons/${couponId.value}/record-usage`, {
      method: 'POST',
      body: {
        user_name: simUserName.value,
        phone: simPhone.value || null,
        total_before_discount: Number(simTotalBefore.value) || 0,
      },
    })
    showSimulateForm.value = false
    simUserName.value = ''
    simPhone.value = ''
    simTotalBefore.value = null
    fetchUsagesData()
  } catch (err: any) {
    simError.value = err?.data?.message || 'تعذر تطبيق الخصم، يرجى التحقق من حالة أو صلاحية الكوبون'
  } finally {
    simLoading.value = false
  }
}

const formatDate = (dateStr: string) => {
  if (!dateStr) return '—'
  try {
    const date = new Date(dateStr)
    return date.toLocaleString('ar-EG', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    })
  } catch {
    return dateStr
  }
}

const getStatusChip = (status: string) => {
  switch (status) {
    case 'active':
      return { color: 'success', text: 'فعّال ومتاح' }
    case 'expired_time':
      return { color: 'error', text: 'منتهي الصلاحية (انتهى الوقت)' }
    case 'expired_limit':
      return { color: 'warning', text: 'منتهي الصلاحية (اكتمل العدد)' }
    case 'upcoming':
      return { color: 'info', text: 'لم يبدأ بعد' }
    case 'inactive':
    default:
      return { color: 'secondary', text: 'موقوف يدويًا' }
  }
}

const headers = [
  { title: '#', key: 'id', sortable: false },
  { title: 'اسم المريض والهاتف', key: 'patient', sortable: true },
  { title: 'الخصم المطبق (د.ع)', key: 'discount_amount', sortable: true },
  { title: 'المبلغ الإجمالي (قبل / بعد الخصم)', key: 'totals', sortable: false },
  { title: 'تاريخ ووقت الاستخدام', key: 'used_at', sortable: true },
]

onMounted(() => {
  fetchUsagesData()
})
</script>

<template>
  <div>
    <!-- Top Header & Action Buttons -->
    <div class="d-flex flex-wrap justify-start justify-sm-space-between align-center gap-y-4 gap-x-6 mb-6">
      <div class="d-flex flex-column justify-center">
        <div class="d-flex align-center gap-2 mb-1">
          <h4 class="text-h4 font-weight-medium">
            سجل الاستخدام والمستفيدين
          </h4>
          <VChip
            v-if="coupon"
            color="primary"
            size="small"
            variant="elevated"
            class="font-weight-bold"
          >
            {{ coupon.code }}
          </VChip>
        </div>
        <div v-if="coupon" class="text-body-1 text-medium-emphasis">
          متابعة الخصومات الممنوحة للمرضى وتفاصيل استخدام الرمز: <strong>{{ coupon.name_ar }}</strong>
        </div>
      </div>

      <div class="d-flex gap-4 align-center flex-wrap">
        <VBtn
          variant="tonal"
          color="secondary"
          to="/coupons"
          prepend-icon="tabler-arrow-right"
        >
          رجوع لصفحة الكوبونات
        </VBtn>

        <VBtn
          color="primary"
          prepend-icon="tabler-plus"
          @click="showSimulateForm = !showSimulateForm"
        >
          {{ showSimulateForm ? 'إخفاء نموذج التسجيل' : 'إدخال استخدام مباشر لمريض' }}
        </VBtn>
      </div>
    </div>

    <!-- Coupon Summary Stats Cards -->
    <VRow v-if="coupon" class="mb-6">
      <!-- Status Card -->
      <VCol cols="12" sm="6" md="3">
        <VCard>
          <VCardText class="d-flex justify-space-between align-center">
            <div class="d-flex flex-column">
              <span class="text-body-2 text-medium-emphasis mb-1">حالة الكوبون</span>
              <div class="d-flex align-center gap-2">
                <VChip
                  :color="getStatusChip(coupon.status).color"
                  size="small"
                  variant="flat"
                  class="font-weight-bold"
                >
                  {{ getStatusChip(coupon.status).text }}
                </VChip>
              </div>
            </div>
            <VAvatar
              color="primary"
              variant="tonal"
              rounded
              size="42"
            >
              <VIcon icon="tabler-ticket" size="24" />
            </VAvatar>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Discount Value Card -->
      <VCol cols="12" sm="6" md="3">
        <VCard>
          <VCardText class="d-flex justify-space-between align-center">
            <div class="d-flex flex-column">
              <span class="text-body-2 text-medium-emphasis mb-1">قيمة الخصم</span>
              <h5 class="text-h5 font-weight-bold text-success">
                {{ coupon.discount_type === 'percentage' ? `${coupon.discount_value}%` : `${Number(coupon.discount_value).toLocaleString()} د.ع` }}
              </h5>
            </div>
            <VAvatar
              color="success"
              variant="tonal"
              rounded
              size="42"
            >
              <VIcon icon="tabler-discount-2" size="24" />
            </VAvatar>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Usages Count Card -->
      <VCol cols="12" sm="6" md="3">
        <VCard>
          <VCardText class="d-flex justify-space-between align-center">
            <div class="d-flex flex-column">
              <span class="text-body-2 text-medium-emphasis mb-1">المستفيدون حتى الآن</span>
              <h5 class="text-h5 font-weight-bold">
                {{ coupon.used_count }} <span class="text-body-2 text-medium-emphasis font-weight-normal">/ {{ coupon.usage_limit || 'مفتوح' }}</span>
              </h5>
            </div>
            <VAvatar
              color="info"
              variant="tonal"
              rounded
              size="42"
            >
              <VIcon icon="tabler-users-group" size="24" />
            </VAvatar>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Progress Card -->
      <VCol cols="12" sm="6" md="3">
        <VCard>
          <VCardText class="d-flex justify-space-between align-center">
            <div class="d-flex flex-column w-100 me-3">
              <div class="d-flex justify-space-between text-body-2 mb-1">
                <span class="text-medium-emphasis">نسبة الاستهلاك</span>
                <span class="font-weight-bold text-primary">{{ coupon.usage_limit ? Math.round((coupon.used_count / coupon.usage_limit) * 100) + '%' : (coupon.used_count > 0 ? '100%' : '0%') }}</span>
              </div>
              <VProgressLinear
                :model-value="coupon.usage_limit ? (coupon.used_count / coupon.usage_limit) * 100 : (coupon.used_count > 0 ? 100 : 0)"
                :color="coupon.usage_limit && coupon.used_count >= coupon.usage_limit ? 'error' : 'primary'"
                height="6"
                rounded
              />
            </div>
            <VAvatar
              color="warning"
              variant="tonal"
              rounded
              size="42"
            >
              <VIcon icon="tabler-chart-pie" size="24" />
            </VAvatar>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Direct Record Usage Form -->
    <VCard
      v-if="showSimulateForm"
      variant="outlined"
      color="primary"
      class="mb-6"
    >
      <VCardItem class="pb-3">
        <template #title>
          <div class="d-flex align-center gap-2 text-primary font-weight-bold">
            <VIcon icon="tabler-discount-check" size="22" />
            <span>تسجيل استخدام وتطبيق خصم مباشر من قبل مريض</span>
          </div>
        </template>
        <template #append>
          <VBtn
            icon
            size="small"
            variant="text"
            color="medium-emphasis"
            @click="showSimulateForm = false"
          >
            <VIcon icon="tabler-x" />
          </VBtn>
        </template>
      </VCardItem>

      <VDivider />

      <VCardText class="pt-5">
        <VAlert
          v-if="simError"
          type="error"
          variant="tonal"
          class="mb-4 text-body-2"
        >
          {{ simError }}
        </VAlert>

        <VRow>
          <VCol cols="12" md="4">
            <AppTextField
              v-model="simUserName"
              label="اسم المريض / الزبون"
              placeholder="مثال: أحمد محمد العبيدي"
            />
          </VCol>

          <VCol cols="12" md="4">
            <AppTextField
              v-model="simPhone"
              label="رقم الهاتف (اختياري)"
              placeholder="0770..."
            />
          </VCol>

          <VCol cols="12" md="4">
            <AppTextField
              v-model="simTotalBefore"
              type="number"
              label="المبلغ الكلي قبل الخصم (د.ع)"
              placeholder="مثال: 50000"
            />
          </VCol>

          <VCol cols="12">
            <div class="d-flex justify-end gap-3 mt-2">
              <VBtn
                variant="tonal"
                color="secondary"
                @click="showSimulateForm = false"
              >
                إلغاء
              </VBtn>
              <VBtn
                color="primary"
                :loading="simLoading"
                prepend-icon="tabler-check"
                @click="handleRecordUsage"
              >
                تطبيق الخصم وحفظ السجل
              </VBtn>
            </div>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <!-- Usages Data Table with Pagination -->
    <VCard title="سجل العمليات ومستفيدي الخصم المخبري">
      <VCardText class="d-flex flex-wrap py-4 gap-4 align-center justify-space-between">
        <div class="d-flex flex-wrap align-center gap-4 flex-grow-1">
          <div style="inline-size: 18rem;">
            <AppTextField
              v-model="searchQuery"
              placeholder="بحث باسم المريض أو الهاتف..."
              prepend-inner-icon="tabler-search"
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
        </div>
      </VCardText>

      <VDivider />

      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :headers="headers"
        :items="usages"
        :items-length="totalUsages"
        :loading="loading"
        item-value="id"
        class="text-no-wrap"
        @update:options="updateOptions"
      >
        <!-- Index -->
        <template #item.id="{ index }">
          <span class="text-body-2 text-medium-emphasis">
            #{{ index + 1 + (page - 1) * itemsPerPage }}
          </span>
        </template>

        <!-- Patient Name & Phone -->
        <template #item.patient="{ item }">
          <div class="d-flex flex-column">
            <span class="font-weight-medium text-body-1">{{ item.user_name }}</span>
            <span class="text-caption text-medium-emphasis">{{ item.phone || 'بدون رقم هاتف' }}</span>
          </div>
        </template>

        <!-- Discount Amount -->
        <template #item.discount_amount="{ item }">
          <span class="text-success font-weight-bold text-body-1">
            - {{ Number(item.discount_amount).toLocaleString() }} د.ع
          </span>
        </template>

        <!-- Totals Before & After -->
        <template #item.totals="{ item }">
          <div class="text-body-2">
            <div><span class="text-medium-emphasis text-caption">قبل الخصم:</span> <span class="text-decoration-line-through">{{ Number(item.total_before_discount).toLocaleString() }} د.ع</span></div>
            <div class="font-weight-bold text-primary"><span class="text-medium-emphasis text-caption">النهائي بعد الخصم:</span> {{ Number(item.total_after_discount).toLocaleString() }} د.ع</div>
          </div>
        </template>

        <!-- Used At -->
        <template #item.used_at="{ item }">
          <span class="text-body-2">{{ formatDate(item.used_at) }}</span>
        </template>

        <template #bottom>
          <TablePagination
            v-if="totalUsages > 0"
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalUsages"
          />
        </template>
      </VDataTableServer>
    </VCard>

  </div>
</template>
