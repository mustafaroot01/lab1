<script setup lang="ts">
import { ref, watch, computed } from 'vue'

interface Props {
  isDrawerOpen: boolean
  couponId: number | null
}

interface Emit {
  (e: 'update:isDrawerOpen', value: boolean): void
  (e: 'refresh'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const loading = ref(false)
const coupon = ref<any>(null)
const showSimulateForm = ref(false)

const simUserName = ref('')
const simPhone = ref('')
const simTotalBefore = ref<number | null>(null)
const simError = ref('')

const fetchCouponDetails = async () => {
  if (!props.couponId) return
  loading.value = true
  try {
    const res = await $api(`/coupons/${props.couponId}`)
    coupon.value = res.coupon || null
  } catch (err) {
    console.error('Error fetching coupon usages:', err)
  } finally {
    loading.value = false
  }
}

watch(
  () => props.isDrawerOpen,
  (val) => {
    if (val && props.couponId) {
      showSimulateForm.value = false
      simError.value = ''
      simUserName.value = ''
      simPhone.value = ''
      simTotalBefore.value = null
      fetchCouponDetails()
    } else {
      coupon.value = null
    }
  },
  { immediate: true }
)

const handleRecordUsage = async () => {
  if (!simUserName.value || !simTotalBefore.value) {
    simError.value = 'يرجى إدخال اسم المريض والمبلغ الكلي قبل الخصم'
    return
  }
  simError.value = ''
  try {
    await $api(`/coupons/${props.couponId}/record-usage`, {
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
    fetchCouponDetails()
    emit('refresh')
  } catch (err: any) {
    simError.value = err?.data?.message || 'تعذر تطبيق الاستخدام، يرجى التحقق من حالة أو صلاحية الكوبون'
  }
}

const closeDrawer = () => {
  emit('update:isDrawerOpen', false)
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
</script>

<template>
  <VNavigationDrawer
    data-allow-mismatch
    temporary
    :width="600"
    location="end"
    class="scrollable-content"
    :model-value="props.isDrawerOpen"
    @update:model-value="closeDrawer"
  >
    <AppDrawerHeaderSection
      title="سجل استخدام الكوبون والزبائن المستفيدين"
      @cancel="closeDrawer"
    />

    <VDivider />

    <PerfectScrollbar :options="{ wheelPropagation: false }">
      <div v-if="loading" class="p-6 text-center">
        <VProgressCircular indeterminate color="primary" />
        <div class="mt-3 text-body-2">جاري تحميل السجل والتفاصيل...</div>
      </div>

      <div v-else-if="coupon" class="p-5">
        <!-- Coupon Summary Banner -->
        <VCard color="primary" variant="tonal" class="mb-5 p-4">
          <div class="d-flex justify-space-between align-center mb-2">
            <span class="text-h6 font-weight-bold text-primary">{{ coupon.name_ar }}</span>
            <VChip color="primary" variant="elevated" size="small" class="font-weight-bold">
              {{ coupon.code }}
            </VChip>
          </div>
          
          <div class="d-flex flex-wrap gap-2 align-center mt-3">
            <VChip :color="getStatusChip(coupon.status).color" size="small" variant="flat">
              {{ getStatusChip(coupon.status).text }}
            </VChip>

            <VChip size="small" variant="outlined" color="primary">
              {{ coupon.discount_type === 'percentage' ? `خصم: ${coupon.discount_value}%` : `خصم: ${Number(coupon.discount_value).toLocaleString()} د.ع` }}
            </VChip>

            <VChip size="small" variant="outlined" color="medium-emphasis">
              الاستخدام: {{ coupon.used_count }} / {{ coupon.usage_limit || 'مفتوح (غير محدد)' }}
            </VChip>
          </div>

          <div v-if="coupon.notes" class="text-caption mt-2 text-medium-emphasis">
            📝 {{ coupon.notes }}
          </div>
        </VCard>

        <!-- Toggle Record Usage Form Button -->
        <div class="d-flex justify-space-between align-center mb-4">
          <span class="text-subtitle-1 font-weight-bold">سجل العمليات والزبائن الذين استخدموه</span>
          <VBtn
            size="small"
            color="primary"
            variant="tonal"
            prepend-icon="tabler-plus"
            @click="showSimulateForm = !showSimulateForm"
          >
            {{ showSimulateForm ? 'إخفاء نموذج التسجيل' : 'تسجيل استخدام لمريض' }}
          </VBtn>
        </div>

        <!-- Simulate Usage Form -->
        <VCard v-if="showSimulateForm" variant="outlined" color="primary" class="mb-5 p-4">
          <div class="text-subtitle-2 font-weight-bold mb-3">إدخال استخدام مباشر للكوبون من قبل مريض</div>

          <VAlert v-if="simError" type="error" variant="tonal" class="mb-3 text-caption" density="compact">
            {{ simError }}
          </VAlert>

          <VRow>
            <VCol cols="12" md="6">
              <AppTextField
                v-model="simUserName"
                label="اسم المريض / الزبون"
                placeholder="مثال: أحمد محمد العبيدي"
                density="compact"
              />
            </VCol>
            <VCol cols="12" md="6">
              <AppTextField
                v-model="simPhone"
                label="رقم الهاتف"
                placeholder="0770..."
                density="compact"
              />
            </VCol>
            <VCol cols="12">
              <AppTextField
                v-model="simTotalBefore"
                type="number"
                label="المبلغ الكلي قبل الخصم (د.ع)"
                placeholder="مثال: 50000"
                density="compact"
              />
            </VCol>
            <VCol cols="12">
              <VBtn color="primary" block size="small" @click="handleRecordUsage">
                تطبيق الخصم وحفظ السجل
              </VBtn>
            </VCol>
          </VRow>
        </VCard>

        <!-- Usages List Table -->
        <VCard v-if="coupon.usages && coupon.usages.length > 0" variant="outlined">
          <VTable class="text-no-wrap">
            <thead>
              <tr>
                <th class="text-subtitle-2">#</th>
                <th class="text-subtitle-2">اسم المريض والهاتف</th>
                <th class="text-subtitle-2">المبلغ والخصم</th>
                <th class="text-subtitle-2">وقت الاستخدام</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(usage, index) in coupon.usages" :key="usage.id">
                <td>{{ index + 1 }}</td>
                <td>
                  <div class="font-weight-medium text-body-2">{{ usage.user_name }}</div>
                  <div class="text-caption text-medium-emphasis">{{ usage.phone || 'بدون رقم' }}</div>
                </td>
                <td>
                  <div class="text-success font-weight-bold text-body-2">
                    - {{ Number(usage.discount_amount).toLocaleString() }} د.ع
                  </div>
                  <div class="text-caption text-medium-emphasis">
                    الإجمالي: {{ Number(usage.total_after_discount).toLocaleString() }} د.ع
                  </div>
                </td>
                <td>
                  <div class="text-body-2">{{ formatDate(usage.used_at) }}</div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>

        <VCard v-else variant="flat" class="text-center py-8 text-medium-emphasis">
          <VIcon icon="tabler-users" size="48" class="mb-2 text-disabled" />
          <div class="text-body-1 font-weight-medium">لم يتم استخدام هذا الكوبون حتى الآن</div>
          <div class="text-caption">عند قيام أي مريض باستخدام الكوبون، سيظهر اسمه ووقت استخدامه هنا</div>
        </VCard>
      </div>
    </PerfectScrollbar>
  </VNavigationDrawer>
</template>
