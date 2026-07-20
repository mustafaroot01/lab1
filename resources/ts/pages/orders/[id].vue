<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { $api } from '@/utils/api'

definePage({
  meta: {
    title: 'تفاصيل ومتابعة الطلب',
  },
})

const route = useRoute()
const router = useRouter()
const orderId = route.params.id

const loading = ref(true)
const order = ref<any>(null)
const technicians = ref<any[]>([])

// إشعارات علوية
const snackbar = ref({
  show: false,
  color: 'success',
  text: '',
})

const toast = (message: string, color = 'success') => {
  snackbar.value = { show: true, color, text: message }
}

// ─── جلب تفاصيل الطلب ────────────────────────────────────────────
const fetchOrder = async () => {
  loading.value = true
  try {
    const res = await $api(`/orders/${orderId}`)
    if (res?.status && res.order) {
      order.value = res.order
    } else {
      toast('لم يتم العثور على الطلب', 'error')
      router.push('/orders')
    }
  } catch {
    toast('حدث خطأ أثناء جلب تفاصيل الطلب', 'error')
  } finally {
    loading.value = false
  }
}

// ─── جلب الفنيين ────────────────────────────────────────────────
const fetchTechnicians = async () => {
  try {
    const res = await $api('/technicians')
    technicians.value = (res?.technicians || res?.data || []).filter((t: any) => t.is_active || t.status === 'active' || true)
  } catch {}
}

const technicianOptions = computed(() => [
  { title: '-- بدون تعيين --', value: null },
  ...technicians.value.map(t => ({
    title: `${t.name} (${t.phone || 'بدون هاتف'})`,
    value: t.id,
  }))
])

// ─── نافذة تحديث الحالة وتعيين الفني ──────────────────────────────
const statusDialog = ref({
  show: false,
  loading: false,
  form: {
    status: '',
    technician_id: null as number | null,
    cancel_reason: '',
  },
})

const changeableStatuses = [
  { title: 'بانتظار',             value: 'pending' },
  { title: 'مؤكد',               value: 'confirmed' },
  { title: 'بانتظار تعيين فني',   value: 'awaiting_technician' },
  { title: 'تم تعيين فني',        value: 'technician_assigned' },
  { title: 'الفني في الطريق',     value: 'on_the_way' },
  { title: 'تم سحب العينة',      value: 'sample_collected' },
  { title: 'قيد التحليل',         value: 'in_progress' },
  { title: 'مكتمل',              value: 'completed' },
  { title: 'ملغي',               value: 'cancelled' },
]

const openStatusDialog = () => {
  if (!order.value) return
  statusDialog.value.form = {
    status: order.value.status,
    technician_id: order.value.technician?.id ?? null,
    cancel_reason: order.value.cancel_reason || '',
  }
  statusDialog.value.show = true
}

const updateStatus = async () => {
  statusDialog.value.loading = true
  try {
    const res = await $api(`/orders/${orderId}/status`, {
      method: 'PATCH',
      body: {
        status:         statusDialog.value.form.status,
        technician_id:  statusDialog.value.form.technician_id,
        cancel_reason:  statusDialog.value.form.cancel_reason,
      },
    })
    if (res?.status) {
      toast(res.message || 'تم تحديث حالة الطلب بنجاح', 'success')
      statusDialog.value.show = false
      order.value = res.order
    } else {
      toast(res?.message || 'فشل التحديث', 'error')
    }
  } catch {
    toast('حدث خطأ أثناء التحديث', 'error')
  } finally {
    statusDialog.value.loading = false
  }
}

// ─── تغيير مباشر للفني من كارت الفني ──────────────────────────────
const changingTech = ref(false)
const selectedTechId = ref<number | null>(null)

const quickAssignTechnician = async () => {
  if (!order.value) return
  changingTech.value = true
  try {
    const res = await $api(`/orders/${orderId}/status`, {
      method: 'PATCH',
      body: {
        status: order.value.status === 'pending' || order.value.status === 'awaiting_technician' ? 'technician_assigned' : order.value.status,
        technician_id: selectedTechId.value,
      },
    })
    if (res?.status) {
      toast('تم تعيين / تغيير الفني بنجاح', 'success')
      order.value = res.order
    } else {
      toast(res?.message || 'فشل التحديث', 'error')
    }
  } catch {
    toast('حدث خطأ أثناء تعيين الفني', 'error')
  } finally {
    changingTech.value = false
  }
}

// ─── رفع نتائج التحاليل ─────────────────────────────────────────
const uploadingResult = ref(false)
const uploadProgress = ref(0)
const fileInputRef = ref<HTMLInputElement | null>(null)

const triggerFileUpload = () => {
  fileInputRef.value?.click()
}

const handleFileUpload = async (e: Event) => {
  const target = e.target as HTMLInputElement
  if (!target.files?.length || !order.value) return
  const file = target.files[0]
  
  const formData = new FormData()
  formData.append('file', file)

  uploadingResult.value = true
  uploadProgress.value = 10
  
  const progressTimer = setInterval(() => {
    if (uploadProgress.value < 85) uploadProgress.value += 15
  }, 200)

  try {
    const res = await $api(`/orders/${orderId}/results`, {
      method: 'POST',
      body: formData,
    })
    clearInterval(progressTimer)
    uploadProgress.value = 100
    if (res?.status) {
      toast('تم رفع وحفظ نتيجة التحليل بنجاح وستظهر للمراجع', 'success')
      order.value = res.order
    } else {
      toast(res?.message || 'فشل رفع الملف', 'error')
    }
  } catch {
    clearInterval(progressTimer)
    toast('حدث خطأ أثناء رفع ملف نتيجة التحليل', 'error')
  } finally {
    uploadingResult.value = false
    setTimeout(() => { uploadProgress.value = 0 }, 1000)
    if (target) target.value = ''
  }
}

const deletingResultId = ref<number | null>(null)
const deleteResult = async (resultId: number) => {
  if (!confirm('هل أنت متأكد من حذف هذا الملف؟ لن يعود متاحاً للمراجع.')) return
  deletingResultId.value = resultId
  try {
    const res = await $api(`/orders/${orderId}/results/${resultId}`, {
      method: 'DELETE',
    })
    if (res?.status) {
      toast('تم حذف نتيجة التحليل بنجاح', 'success')
      order.value = res.order
    } else {
      toast(res?.message || 'فشل الحذف', 'error')
    }
  } catch {
    toast('حدث خطأ أثناء الحذف', 'error')
  } finally {
    deletingResultId.value = null
  }
}

const formatSize = (bytes?: number) => {
  if (!bytes) return ''
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB'
  return (bytes / (1024 * 1024)).toFixed(2) + ' MB'
}

// ─── تنسيقات مساعدة ──────────────────────────────────────────────
const formatCurrency = (v: any) => v ? Number(v).toLocaleString('ar-IQ') + ' د.ع' : '0 د.ع'

const cleanPhoneForWhatsApp = (phone: string) => {
  if (!phone) return ''
  let cleaned = phone.replace(/\D/g, '')
  if (cleaned.startsWith('0')) cleaned = '964' + cleaned.substring(1)
  else if (!cleaned.startsWith('964')) cleaned = '964' + cleaned
  return cleaned
}

const openWhatsApp = (phone: string) => {
  const clean = cleanPhoneForWhatsApp(phone)
  if (clean) window.open(`https://wa.me/${clean}`, '_blank')
}

const openGoogleMaps = () => {
  if (!order.value) return
  if (order.value.address_text) {
    window.open(`https://maps.google.com/?q=${encodeURIComponent(order.value.address_text)}`, '_blank')
  } else {
    toast('لا يوجد عنوان أو إحداثيات متاحة للفتح على الخريطة', 'warning')
  }
}

const statusColor = (s: string) => ({
  pending:              'warning',
  confirmed:            'info',
  awaiting_technician:  'warning',
  technician_assigned:  'primary',
  on_the_way:           'primary',
  sample_collected:     'secondary',
  in_progress:          'info',
  completed:            'success',
  cancelled:            'error',
}[s] ?? 'default')

const statusLabel = (s: string) => ({
  pending:              'بانتظار',
  confirmed:            'مؤكد',
  awaiting_technician:  'بانتظار تعيين فني',
  technician_assigned:  'تم تعيين فني',
  on_the_way:           'الفني في الطريق',
  sample_collected:     'تم سحب العينة',
  in_progress:          'قيد التحليل',
  completed:            'مكتمل',
  cancelled:            'ملغي',
}[s] ?? s)

onMounted(() => {
  fetchOrder()
  fetchTechnicians()
})
</script>

<template>
  <div>
    <!-- إشعار -->
    <VSnackbar v-model="snackbar.show" location="top" :color="snackbar.color" timeout="3000">
      {{ snackbar.text }}
    </VSnackbar>

    <!-- مؤشر التحميل -->
    <div v-if="loading" class="text-center py-16">
      <VProgressCircular indeterminate color="primary" size="48" />
    </div>

    <!-- المحتوى المرتب بدقة حسب معايير قالب Materio -->
    <div v-else-if="order">
      <!-- 👉 Header Row (شريط العنوان العلوي) -->
      <div class="d-flex justify-space-between align-center flex-wrap gap-y-4 mb-6">
        <div>
          <div class="d-flex gap-3 align-center mb-1 flex-wrap">
            <h4 class="text-h4 font-weight-bold mb-0">
              طلب زيارة منزلية #{{ orderId }}
            </h4>
            <VChip
              variant="tonal"
              :color="statusColor(order.status)"
              label
              size="small"
              class="font-weight-bold"
            >
              {{ order.status_label || statusLabel(order.status) }}
            </VChip>
          </div>
          <div class="text-body-2 text-medium-emphasis">
            تاريخ ووقت إنشاء الطلب: {{ order.created_at || '—' }}
          </div>
        </div>

        <div class="d-flex gap-3">
          <VBtn
            variant="tonal"
            color="secondary"
            prepend-icon="tabler-arrow-right"
            @click="router.push('/orders')"
          >
            رجوع للقائمة
          </VBtn>
          <VBtn
            color="primary"
            variant="elevated"
            prepend-icon="tabler-edit"
            @click="openStatusDialog"
          >
            تحديث حالة الطلب
          </VBtn>
        </div>
      </div>

      <VRow>
        <!-- 👉 Left Column (عمود التحاليل المخبرية والنتائج والخط الزمني): 8 أعمدة -->
        <VCol cols="12" md="8">
          <!-- 1. كارت التحاليل والفاتورة (Order Items & Billing) -->
          <VCard class="mb-6">
            <VCardItem class="pb-4">
              <template #title>
                <h5 class="text-h5 font-weight-bold">
                  التحاليل والباقات المطلوبة
                </h5>
              </template>
              <template #append>
                <VChip size="small" color="primary" variant="tonal" label>
                  عدد البنود: {{ (order.items || []).length }}
                </VChip>
              </template>
            </VCardItem>

            <VDivider />

            <VTable class="text-no-wrap">
              <thead>
                <tr class="bg-grey-lighten-5">
                  <th style="width: 50px;">#</th>
                  <th>اسم التحليل / الباقة</th>
                  <th>النوع</th>
                  <th class="text-end">السعر</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in (order.items || [])" :key="item.id">
                  <td>{{ index + 1 }}</td>
                  <td>
                    <div class="d-flex gap-x-3 align-center py-2">
                      <VAvatar
                        size="36"
                        :color="item.item_type === 'package' ? 'purple' : 'primary'"
                        variant="tonal"
                        rounded
                      >
                        <VIcon :icon="item.item_type === 'package' ? 'tabler-package' : 'tabler-test-pipe'" size="20" />
                      </VAvatar>
                      <div class="d-flex flex-column align-start">
                        <h6 class="text-h6 font-weight-bold mb-0">
                          {{ item.name_ar }}
                        </h6>
                        <span class="text-caption text-medium-emphasis">
                          {{ item.item_type === 'package' ? 'باقة تحاليل مجمعة' : 'فحص مخبري مفرد' }}
                        </span>
                      </div>
                    </div>
                  </td>
                  <td>
                    <VChip size="x-small" :color="item.item_type === 'package' ? 'purple' : 'primary'" variant="tonal" label>
                      {{ item.item_type === 'package' ? 'باقة' : 'تحليل' }}
                    </VChip>
                  </td>
                  <td class="text-end text-body-1 font-weight-bold text-success">
                    {{ formatCurrency(item.price) }}
                  </td>
                </tr>
                <tr v-if="!order.items?.length">
                  <td colspan="4" class="text-center text-medium-emphasis py-6">لا توجد بنود مسجلة في هذا الطلب</td>
                </tr>
              </tbody>
            </VTable>

            <VDivider />

            <!-- ملخص الفاتورة المنسق بدقة -->
            <VCardText class="bg-grey-lighten-5 pa-6">
              <div class="d-flex align-end flex-column">
                <table class="text-high-emphasis" style="width: 100%; max-width: 340px;">
                  <tbody>
                    <tr>
                      <td class="py-1 text-medium-emphasis">مجموع التحاليل:</td>
                      <td class="py-1 text-end font-weight-medium">{{ formatCurrency(order.subtotal) }}</td>
                    </tr>
                    <tr>
                      <td class="py-1 text-medium-emphasis">
                        أجور الزيارة المنزلية <span class="text-caption text-primary font-weight-bold" v-if="order.branch">({{ order.branch.name_ar }})</span>:
                      </td>
                      <td class="py-1 text-end font-weight-medium">
                        <VChip v-if="order.service_fee == 0" size="small" color="success" variant="tonal" class="font-weight-bold">
                          مجاني 🎁
                        </VChip>
                        <span v-else>{{ formatCurrency(order.service_fee) }}</span>
                      </td>
                    </tr>
                    <tr v-if="order.discount_amount > 0">
                      <td class="py-1 text-error font-weight-medium">
                        خصم الكوبون <span v-if="order.coupon">({{ order.coupon.code }})</span>:
                      </td>
                      <td class="py-1 text-end font-weight-medium text-error">- {{ formatCurrency(order.discount_amount) }}</td>
                    </tr>
                    <tr>
                      <td colspan="2"><VDivider class="my-2" /></td>
                    </tr>
                    <tr>
                      <td class="py-1 text-h6 font-weight-bold text-primary">الإجمالي المطلوب:</td>
                      <td class="py-1 text-end text-h5 font-weight-bold text-primary">{{ formatCurrency(order.total) }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </VCardText>
          </VCard>

          <!-- صورة الراجيتة / الوصفة المرفقة إن وجدت -->
          <VCard v-if="order.referral_image" class="mb-6">
            <VCardItem class="pb-3">
              <VCardTitle class="text-h5">الوصفة الطبية (الراجيتة) المرفقة</VCardTitle>
            </VCardItem>
            <VDivider />
            <VCardText class="pa-5 text-center">
              <a :href="order.referral_image" target="_blank" title="اضغط لفتح الصورة بالحجم الكامل">
                <VImg :src="order.referral_image" max-height="260" cover class="rounded-lg border mx-auto cursor-pointer" />
              </a>
              <div class="text-caption text-medium-emphasis mt-2">اضغط على الصورة لفتحها في نافذة مستقلة وتكبيرها</div>
            </VCardText>
          </VCard>

          <!-- 2. كارت نتائج التحاليل المرفوعة للمراجع (Lab Results Upload) -->
          <VCard class="mb-6">
            <VCardItem class="pb-4">
              <template #title>
                <h5 class="text-h5 font-weight-bold">
                  نتائج التحاليل المرفوعة للمراجع
                </h5>
              </template>
              <template #append>
                <VBtn
                  color="primary"
                  size="small"
                  variant="elevated"
                  prepend-icon="tabler-upload"
                  :loading="uploadingResult"
                  @click="triggerFileUpload"
                >
                  رفع ملف أو صورة نتيجة
                </VBtn>
              </template>
            </VCardItem>

            <VDivider />

            <VCardText class="pa-6">
              <input
                ref="fileInputRef"
                type="file"
                accept=".pdf,.jpg,.jpeg,.png"
                class="d-none"
                @change="handleFileUpload"
              />

              <!-- شريط تقدم الرفع -->
              <div v-if="uploadProgress > 0" class="mb-6 bg-grey-lighten-5 pa-4 rounded-lg border">
                <div class="d-flex justify-space-between text-body-2 font-weight-bold mb-2">
                  <span>جاري رفع وحفظ ملف نتيجة التحليل...</span>
                  <span>{{ uploadProgress }}%</span>
                </div>
                <VProgressLinear :model-value="uploadProgress" color="success" height="10" striped animated rounded />
              </div>

              <!-- تنبيه وإرشاد عند وصول الطلب لمرحلة متقدمة -->
              <VAlert
                v-if="order.status === 'completed' || order.status === 'in_progress'"
                variant="tonal"
                :color="order.results?.length ? 'success' : 'warning'"
                icon="tabler-report-medical"
                class="mb-6"
              >
                <div class="font-weight-bold text-body-1 mb-1">
                  {{ order.status === 'completed' ? 'الطلب مكتمل — جاهز لتسليم النتائج للزبون:' : 'الطلب قيد التحليل المخبري:' }}
                </div>
                <div class="text-body-2">
                  قم برفع ملفات PDF أو صور نتائج التحاليل من الزر بالأعلى، وستظهر فور حفظها في تطبيق الموبايل الخاص بالمراجع ليتمكن من عرضها وتحميلها ومشاركتها مع طبيبه.
                </div>
              </VAlert>

              <!-- جدول عرض النتائج المرفوعة -->
              <VTable v-if="order.results?.length > 0" class="text-no-wrap">
                <thead>
                  <tr class="bg-grey-lighten-5">
                    <th style="width: 50px;">النوع</th>
                    <th>اسم الملف المرفوع</th>
                    <th>الحجم</th>
                    <th>تاريخ ووقت الرفع</th>
                    <th class="text-center">إجراءات (عرض / حذف)</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="r in order.results" :key="r.id">
                    <td>
                      <VAvatar
                        :color="r.file_type === 'pdf' ? 'error' : 'info'"
                        variant="tonal"
                        size="36"
                        rounded
                      >
                        <VIcon :icon="r.file_type === 'pdf' ? 'tabler-file-type-pdf' : 'tabler-photo'" size="20" />
                      </VAvatar>
                    </td>
                    <td class="font-weight-bold text-body-1">{{ r.file_name }}</td>
                    <td class="text-body-2 text-medium-emphasis" dir="ltr">{{ formatSize(r.file_size) }}</td>
                    <td class="text-body-2 text-medium-emphasis" dir="ltr">{{ r.created_at }}</td>
                    <td class="text-center">
                      <div class="d-flex align-center justify-center gap-2">
                        <VBtn
                          color="primary"
                          variant="tonal"
                          size="small"
                          prepend-icon="tabler-eye"
                          :href="r.url"
                          target="_blank"
                        >
                          عرض الملف
                        </VBtn>
                        <VBtn
                          icon
                          color="error"
                          variant="text"
                          size="small"
                          title="حذف الملف"
                          :loading="deletingResultId === r.id"
                          @click="deleteResult(r.id)"
                        >
                          <VIcon icon="tabler-trash" />
                        </VBtn>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </VTable>

              <!-- الحالة الفارغة -->
              <div
                v-else
                class="text-center py-10 text-medium-emphasis border border-dashed rounded-lg bg-grey-lighten-5 cursor-pointer"
                @click="triggerFileUpload"
              >
                <VAvatar color="primary" variant="tonal" size="56" class="mb-3">
                  <VIcon icon="tabler-cloud-upload" size="32" />
                </VAvatar>
                <div class="font-weight-bold text-h6 mb-1">لم يتم رفع نتائج تحاليل لهذا الطلب بعد</div>
                <div class="text-body-2 text-medium-emphasis">اضغط هنا أو على زر "رفع ملف أو صورة نتيجة" لاختيار ملف ورفعه للمراجع</div>
              </div>
            </VCardText>
          </VCard>

          <!-- 3. سجل الحركات والخط الزمني (Status Timeline) -->
          <VCard title="سجل تغييرات ومتابعة حالة الطلب">
            <VCardText class="pa-6">
              <VTimeline
                truncate-line="both"
                line-inset="9"
                align="start"
                side="end"
                line-color="primary"
                density="compact"
              >
                <VTimelineItem
                  v-for="log in (order.status_logs || [])"
                  :key="log.id"
                  :dot-color="statusColor(log.to_status)"
                  size="small"
                >
                  <div class="d-flex justify-space-between align-center flex-wrap gap-2 mb-1">
                    <span class="font-weight-bold text-body-1">
                      {{ log.to_status_label }}
                      <span v-if="log.from_status" class="text-caption text-medium-emphasis font-weight-normal">
                        (تغيير من: {{ log.from_status_label }})
                      </span>
                    </span>
                    <span class="text-caption text-medium-emphasis" dir="ltr">{{ log.created_at }}</span>
                  </div>
                  <div class="text-body-2 text-medium-emphasis mb-2">
                    بواسطة المسؤول: <span class="font-weight-bold text-high-emphasis">{{ log.changed_by_name }}</span>
                  </div>
                  <div v-if="log.notes" class="text-body-2 bg-grey-lighten-5 pa-3 rounded border text-high-emphasis">
                    <VIcon icon="tabler-note" size="16" class="me-1 text-primary" />
                    {{ log.notes }}
                  </div>
                </VTimelineItem>

                <VTimelineItem v-if="!order.status_logs?.length" dot-color="info" size="small">
                  <div class="text-body-2 text-medium-emphasis">لا توجد سجلات تغيير أو حركات مسجلة حتى الآن</div>
                </VTimelineItem>
              </VTimeline>
            </VCardText>
          </VCard>
        </VCol>

        <!-- 👉 Right Column (عمود بيانات المراجع وموعد الزيارة والفني): 4 أعمدة -->
        <VCol cols="12" md="4">
          <!-- 1. بيانات المراجع والتواصل والموقع (Customer & Address Details) -->
          <VCard class="mb-6">
            <VCardItem class="pb-3">
              <VCardTitle class="text-h5 font-weight-bold">بيانات المراجع والموقع</VCardTitle>
            </VCardItem>
            <VDivider />
            <VCardText class="pa-6 d-flex flex-column gap-y-5">
              <!-- المراجع والهاتف -->
              <div class="d-flex align-center gap-3">
                <VAvatar
                  variant="tonal"
                  color="primary"
                  rounded
                  size="44"
                >
                  <VIcon icon="tabler-user" size="24" />
                </VAvatar>
                <div>
                  <h6 class="text-h6 font-weight-bold mb-1">
                    <RouterLink :to="`/patients/${order.user?.id}`" class="text-primary text-decoration-none">
                      {{ order.user?.name || 'مراجع غير مسجل' }}
                    </RouterLink>
                  </h6>
                  <div class="text-body-2 text-medium-emphasis d-flex align-center gap-1" dir="ltr">
                    <VIcon icon="tabler-phone" size="14" />
                    <span>{{ order.user?.phone || '—' }}</span>
                  </div>
                </div>
              </div>

              <!-- زر واتساب المراجع -->
              <div
                v-if="order.user?.phone"
                class="d-flex gap-x-3 align-center bg-success-lighten-5 pa-3 rounded-lg border border-success cursor-pointer"
                @click="openWhatsApp(order.user.phone)"
              >
                <VAvatar variant="tonal" color="success" rounded size="38">
                  <VIcon icon="tabler-brand-whatsapp" size="22" />
                </VAvatar>
                <div>
                  <h6 class="text-body-1 font-weight-bold text-success mb-0">تواصل عبر واتساب</h6>
                  <span class="text-caption text-success-darken-1">فتح المحادثة المباشرة مع المراجع</span>
                </div>
              </div>

              <VDivider />

              <!-- معلومات الموقع الجغرافي والفرع -->
              <div class="d-flex flex-column gap-y-3">
                <div class="d-flex justify-space-between align-center">
                  <h6 class="text-h6 font-weight-bold mb-0">العنوان الجغرافي والنطاق</h6>
                  <VBtn color="primary" variant="text" size="small" prepend-icon="tabler-map-2" class="px-0" @click="openGoogleMaps">
                    الخريطة
                  </VBtn>
                </div>

                <div class="d-flex justify-space-between text-body-2">
                  <span class="text-medium-emphasis">الفرع المختص:</span>
                  <span class="font-weight-bold">{{ order.branch?.name_ar || 'الفرع الرئيسي' }}</span>
                </div>

                <div class="d-flex justify-space-between text-body-2">
                  <span class="text-medium-emphasis">القضاء:</span>
                  <span class="font-weight-bold">{{ order.user?.district_name || '—' }}</span>
                </div>

                <div v-if="order.doctor_name" class="d-flex justify-space-between text-body-2">
                  <span class="text-medium-emphasis">الطبيب المرسل:</span>
                  <span class="font-weight-bold text-primary">{{ order.doctor_name }}</span>
                </div>

                <div class="mt-1">
                  <div class="text-caption text-medium-emphasis mb-1">العنوان التفصيلي من المراجع:</div>
                  <div class="text-body-2 bg-grey-lighten-5 pa-3 rounded border">
                    {{ order.address_text || 'لم يتم كتابة عنوان نصي تفصيلي' }}
                  </div>
                </div>

                <div v-if="order.notes" class="mt-1">
                  <div class="text-caption text-warning-darken-2 font-weight-bold mb-1">ملاحظات المراجع عند الطلب:</div>
                  <div class="text-body-2 bg-warning-lighten-5 text-warning-darken-2 pa-3 rounded border border-warning">
                    {{ order.notes }}
                  </div>
                </div>
              </div>
            </VCardText>
          </VCard>

          <!-- 2. موعد الزيارة والفني المكلف بسحب العينة (Visit Schedule & Technician Assignment) -->
          <VCard class="mb-6">
            <VCardItem class="pb-3">
              <VCardTitle class="text-h5 font-weight-bold">موعد الزيارة والفني المكلف</VCardTitle>
            </VCardItem>
            <VDivider />
            <VCardText class="pa-6 d-flex flex-column gap-y-5">
              <!-- موعد الزيارة المحدد -->
              <div class="bg-info-lighten-5 pa-4 rounded-lg border border-info">
                <div class="text-caption text-info-darken-2 font-weight-bold mb-1">موعد سحب العينة المطلوب:</div>
                <div class="d-flex align-center gap-2 text-h6 font-weight-bold text-info-darken-3">
                  <VIcon icon="tabler-calendar-time" size="22" />
                  <span>{{ order.visit_date || '—' }} — {{ order.visit_time || '—' }}</span>
                </div>
                <div class="text-body-2 font-weight-medium text-info-darken-2 mt-1">
                  الفترة: {{ order.visit_period_label || '—' }}
                </div>
              </div>

              <!-- الفني المكلف حالياً -->
              <div>
                <div class="text-caption text-medium-emphasis mb-2">الفني الميداني المسؤول حالياً:</div>
                <div v-if="order.technician" class="d-flex align-center gap-3 bg-grey-lighten-5 pa-3 rounded border">
                  <VAvatar variant="tonal" color="purple" rounded size="42">
                    <VIcon icon="tabler-stethoscope" size="22" />
                  </VAvatar>
                  <div class="flex-grow-1">
                    <h6 class="text-h6 font-weight-bold mb-0">{{ order.technician.name }}</h6>
                    <div class="text-body-2 text-medium-emphasis" dir="ltr">{{ order.technician.phone || '—' }}</div>
                  </div>
                  <VBtn
                    v-if="order.technician?.phone"
                    icon
                    color="success"
                    variant="tonal"
                    size="small"
                    title="تواصل واتساب مع الفني"
                    @click="openWhatsApp(order.technician.phone)"
                  >
                    <VIcon icon="tabler-brand-whatsapp" size="18" />
                  </VBtn>
                </div>
                <div v-else class="text-center py-4 text-medium-emphasis border rounded-lg bg-grey-lighten-5">
                  <VIcon icon="tabler-user-off" size="28" class="mb-1 text-warning" />
                  <div class="font-weight-bold text-body-2">لم يتم تعيين فني ميداني لسحب العينة بعد</div>
                </div>
              </div>

              <VDivider />

              <!-- تغيير أو تعيين الفني مع بحث واختيار -->
              <div>
                <div class="text-caption font-weight-bold text-medium-emphasis mb-2">تغيير أو تعيين فني للطلب (ابحث واختار):</div>
                <AppAutocomplete
                  v-model="selectedTechId"
                  :items="technicianOptions"
                  item-title="title"
                  item-value="value"
                  placeholder="ابحث باسم الفني أو رقم هاتفه..."
                  clearable
                  class="mb-3"
                />
                <VBtn
                  color="primary"
                  variant="elevated"
                  block
                  :loading="changingTech"
                  @click="quickAssignTechnician"
                >
                  حفظ وتعيين الفني
                </VBtn>
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
    </div>

    <!-- نافذة تحديث حالة الطلب -->
    <VDialog v-model="statusDialog.show" max-width="500">
      <VCard :title="`تحديث حالة الطلب #${orderId}`">
        <VDivider />
        <VCardText class="pt-6">
          <VRow>
            <VCol cols="12">
              <AppSelect
                v-model="statusDialog.form.status"
                label="الحالة الجديدة *"
                :items="changeableStatuses"
              />
            </VCol>
            <VCol cols="12">
              <AppAutocomplete
                v-model="statusDialog.form.technician_id"
                label="الفني الميداني المكلف (ابحث واختار)"
                :items="technicianOptions"
                item-title="title"
                item-value="value"
                clearable
                placeholder="اكتب اسم الفني للبحث..."
              />
            </VCol>
            <VCol v-if="statusDialog.form.status === 'completed'" cols="12">
              <VAlert color="success" variant="tonal" icon="tabler-check" class="mb-0">
                <div class="font-weight-bold mb-1">تنبيه عند اكتمال الطلب:</div>
                <div class="text-body-2">
                  عند تحويل الحالة إلى "مكتمل"، يمكنك أيضاً رفع ملفات أو صور نتائج التحاليل من قسم "نتائج التحاليل المرفوعة" في صفحة الطلب لتظهر لدى الزبون في التطبيق مباشرة.
                </div>
              </VAlert>
            </VCol>
            <VCol v-if="statusDialog.form.status === 'cancelled'" cols="12">
              <AppTextField
                v-model="statusDialog.form.cancel_reason"
                label="سبب إلغاء الطلب"
                placeholder="اذكر سبب الإلغاء..."
              />
            </VCol>
          </VRow>
        </VCardText>
        <VDivider />
        <VCardActions class="pa-4">
          <VSpacer />
          <VBtn color="secondary" variant="tonal" @click="statusDialog.show = false">إلغاء</VBtn>
          <VBtn color="primary" variant="elevated" :loading="statusDialog.loading" @click="updateStatus">
            حفظ التغييرات
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
