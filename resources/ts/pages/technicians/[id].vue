<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { $api } from '@/utils/api'

definePage({
  meta: {
    title: 'الملف الشخصي للفني وسجل الطلبات',
  },
})

const route = useRoute()
const router = useRouter()
const technicianId = route.params.id

const loading = ref(true)
const ordersLoading = ref(false)
const technician = ref<any>(null)
const orders = ref<any[]>([])
const totalOrders = ref(0)
const page = ref(1)
const itemsPerPage = ref(10)
const searchQuery = ref('')
const statusFilter = ref('all')

const summary = ref({
  total: 0,
  completed: 0,
  in_progress: 0,
  pending: 0,
  cancelled: 0,
  total_earnings: 0,
})

const ordersHeaders = [
  { title: '# رقم الطلب', key: 'id', sortable: false, width: '100px' },
  { title: 'المريض والموبايل', key: 'user', sortable: false, minWidth: '180px' },
  { title: 'الفرع المخبري', key: 'branch', sortable: false },
  { title: 'موعد الزيارة المحدد', key: 'visit', sortable: false, minWidth: '180px' },
  { title: 'الإجمالي', key: 'total', sortable: false },
  { title: 'حالة الطلب', key: 'status', sortable: false },
  { title: 'إجراءات', key: 'actions', sortable: false, align: 'center', width: '120px' },
]

const statusOptions = [
  { title: 'جميع الحالات', value: 'all' },
  { title: 'قيد التنفيذ / بالطريق', value: 'in_progress_group' },
  { title: 'بانتظار البدء', value: 'pending_group' },
  { title: 'مكتمل', value: 'completed' },
  { title: 'ملغي', value: 'cancelled' },
]

// إشعارات
const snackbar = ref({
  show: false,
  color: 'success',
  text: '',
})

const showToast = (message: string, color = 'success') => {
  snackbar.value = { show: true, color, text: message }
}

const fetchTechnicianProfile = async () => {
  if (!technician.value) loading.value = true
  ordersLoading.value = true
  try {
    const params: any = {
      page: page.value,
      itemsPerPage: itemsPerPage.value,
    }
    if (searchQuery.value) params.search = searchQuery.value
    if (statusFilter.value !== 'all') params.status = statusFilter.value

    const res = await $api(`/technicians/${technicianId}`, { params })
    if (res?.status && res.technician) {
      technician.value = res.technician
      orders.value = res.orders || []
      totalOrders.value = res.totalOrders || res.orders?.length || 0
      if (res.orders_summary) {
        summary.value = res.orders_summary
      }
    } else {
      showToast('لم يتم العثور على بيانات الفني', 'error')
      router.push('/technicians')
    }
  } catch {
    showToast('حدث خطأ أثناء جلب ملف الفني', 'error')
  } finally {
    loading.value = false
    ordersLoading.value = false
  }
}

const onOrdersOptionsUpdate = (options: any) => {
  page.value = options.page
  itemsPerPage.value = options.itemsPerPage
  fetchTechnicianProfile()
}


// تنسيق العملة
const formatCurrency = (amount: any) => {
  if (!amount) return '0 د.ع'
  return Number(amount).toLocaleString('ar-IQ') + ' د.ع'
}

// ألوان الحالات
const statusColor = (status: string) => ({
  pending:             'warning',
  confirmed:           'info',
  awaiting_technician: 'warning',
  technician_assigned: 'primary',
  on_the_way:          'primary',
  sample_collected:    'secondary',
  in_progress:         'info',
  completed:           'success',
  cancelled:           'error',
}[status] ?? 'default')

const statusLabel = (status: string) => ({
  pending:             'بانتظار',
  confirmed:           'مؤكد',
  awaiting_technician: 'بانتظار تعيين فني',
  technician_assigned: 'تم تعيين فني',
  on_the_way:          'الفني في الطريق',
  sample_collected:    'تم سحب العينة',
  in_progress:         'قيد التحليل',
  completed:           'مكتمل',
  cancelled:           'ملغي',
}[status] ?? status)

// ألوان حالات الفني
const techStatusColor = (s: string) => ({
  active:     'success',
  suspended:  'error',
  on_leave:   'warning',
}[s] ?? 'default')

const techStatusLabel = (s: string) => ({
  active:     'متاح / نشط',
  suspended:  'موقوف عن العمل',
  on_leave:   'في إجازة',
}[s] ?? s)

const openWhatsApp = (phone?: string) => {
  if (!phone) return
  let cleaned = phone.replace(/\D/g, '')
  if (cleaned.startsWith('0')) cleaned = '964' + cleaned.substring(1)
  else if (!cleaned.startsWith('964')) cleaned = '964' + cleaned
  window.open(`https://wa.me/${cleaned}`, '_blank')
}

onMounted(() => {
  fetchTechnicianProfile()
})
</script>

<template>
  <div>
    <VSnackbar v-model="snackbar.show" location="top" :color="snackbar.color" timeout="3000">
      {{ snackbar.text }}
    </VSnackbar>

    <div v-if="loading" class="text-center py-16">
      <VProgressCircular indeterminate color="primary" size="48" />
    </div>

    <div v-else-if="technician">
      <!-- شريط العنوان العلوي -->
      <div class="d-flex justify-space-between align-center flex-wrap gap-4 mb-6">
        <div>
          <h4 class="text-h4 font-weight-bold mb-1 d-flex align-center gap-2">
            <VIcon icon="tabler-user-check" color="primary" size="32" />
            الملف الشخصي وسجلات الفني
          </h4>
          <p class="text-body-1 text-medium-emphasis mb-0">
            متابعة أداء وحالات الطلبات المكلف بها الفني الميداني {{ technician.name }}.
          </p>
        </div>

        <div class="d-flex gap-3">
          <VBtn
            color="secondary"
            variant="tonal"
            prepend-icon="tabler-arrow-right"
            @click="router.push('/technicians')"
          >
            رجوع للقائمة
          </VBtn>
          <VBtn
            color="primary"
            variant="elevated"
            prepend-icon="tabler-edit"
            @click="router.push(`/technicians/edit/${technician.id}`)"
          >
            تعديل بيانات الفني
          </VBtn>
        </div>
      </div>

      <!-- بطاقة الملف الشخصي للفني -->
      <VCard class="mb-6">
        <VCardText class="pa-6">
          <VRow class="align-center">
            <VCol cols="12" md="7" class="d-flex align-center gap-4 flex-wrap">
              <VAvatar color="purple" variant="tonal" size="76" rounded>
                <VIcon icon="tabler-stethoscope" size="42" />
              </VAvatar>
              <div>
                <div class="d-flex align-center gap-3 mb-1 flex-wrap">
                  <h4 class="text-h4 font-weight-bold mb-0">{{ technician.name }}</h4>
                  <VChip :color="techStatusColor(technician.status)" variant="elevated" size="small" class="font-weight-bold">
                    {{ techStatusLabel(technician.status) }}
                  </VChip>
                  <VChip v-if="technician.specialty" color="info" variant="tonal" size="small" label>
                    {{ technician.specialty }}
                  </VChip>
                </div>
                <div class="d-flex align-center gap-x-4 gap-y-2 flex-wrap text-body-1 text-medium-emphasis mt-2">
                  <div class="d-flex align-center gap-1" dir="ltr">
                    <VIcon icon="tabler-phone" size="18" class="text-primary" />
                    <span class="font-weight-bold text-high-emphasis">{{ technician.phone || '—' }}</span>
                  </div>
                  <div v-if="technician.address" class="d-flex align-center gap-1">
                    <VIcon icon="tabler-map-pin" size="18" class="text-primary" />
                    <span>{{ technician.address }}</span>
                  </div>
                </div>
              </div>
            </VCol>

            <VCol cols="12" md="5" class="d-flex justify-md-end justify-start align-center gap-3 flex-wrap">
              <VBtn
                v-if="technician.phone"
                color="success"
                variant="elevated"
                prepend-icon="tabler-brand-whatsapp"
                @click="openWhatsApp(technician.phone)"
              >
                مراسلة عبر واتساب
              </VBtn>
              <div class="d-flex gap-2">
                <VChip
                  :color="technician.has_transport ? 'success' : 'default'"
                  :variant="technician.has_transport ? 'tonal' : 'outlined'"
                  size="large"
                  label
                >
                  <VIcon :icon="technician.has_transport ? 'tabler-car' : 'tabler-car-off'" size="18" class="me-1" />
                  وسيلة نقل
                </VChip>
                <VChip
                  :color="technician.has_equipment ? 'success' : 'default'"
                  :variant="technician.has_equipment ? 'tonal' : 'outlined'"
                  size="large"
                  label
                >
                  <VIcon :icon="technician.has_equipment ? 'tabler-briefcase-medical' : 'tabler-briefcase-off'" size="18" class="me-1" />
                  معدات سحب
                </VChip>
              </div>
            </VCol>
          </VRow>

          <VDivider v-if="technician.notes" class="my-4" />
          <div v-if="technician.notes" class="text-body-2 bg-grey-lighten-5 pa-3 rounded border text-medium-emphasis">
            <span class="font-weight-bold text-high-emphasis">ملاحظات الفني: </span>
            {{ technician.notes }}
          </div>
        </VCardText>
      </VCard>

      <!-- شريط بطاقات الإحصائيات (6 ملخصات لأداء الفني) -->
      <VRow class="mb-6">
        <VCol cols="12" sm="6" md="2">
          <VCard class="pa-4 text-center border">
            <VAvatar color="primary" variant="tonal" size="44" class="mx-auto mb-2" rounded>
              <VIcon icon="tabler-clipboard-list" size="24" />
            </VAvatar>
            <div class="text-h4 font-weight-bold text-primary">{{ summary.total }}</div>
            <div class="text-caption text-medium-emphasis font-weight-medium">إجمالي الطلبات</div>
          </VCard>
        </VCol>

        <VCol cols="12" sm="6" md="2">
          <VCard class="pa-4 text-center border">
            <VAvatar color="success" variant="tonal" size="44" class="mx-auto mb-2" rounded>
              <VIcon icon="tabler-circle-check" size="24" />
            </VAvatar>
            <div class="text-h4 font-weight-bold text-success">{{ summary.completed }}</div>
            <div class="text-caption text-medium-emphasis font-weight-medium">طلبات مكتملة</div>
          </VCard>
        </VCol>

        <VCol cols="12" sm="6" md="2">
          <VCard class="pa-4 text-center border">
            <VAvatar color="info" variant="tonal" size="44" class="mx-auto mb-2" rounded>
              <VIcon icon="tabler-clock-play" size="24" />
            </VAvatar>
            <div class="text-h4 font-weight-bold text-info">{{ summary.in_progress }}</div>
            <div class="text-caption text-medium-emphasis font-weight-medium">قيد التنفيذ / بالطريق</div>
          </VCard>
        </VCol>

        <VCol cols="12" sm="6" md="2">
          <VCard class="pa-4 text-center border">
            <VAvatar color="warning" variant="tonal" size="44" class="mx-auto mb-2" rounded>
              <VIcon icon="tabler-hourglass-empty" size="24" />
            </VAvatar>
            <div class="text-h4 font-weight-bold text-warning">{{ summary.pending }}</div>
            <div class="text-caption text-medium-emphasis font-weight-medium">بانتظار البدء</div>
          </VCard>
        </VCol>

        <VCol cols="12" sm="6" md="2">
          <VCard class="pa-4 text-center border">
            <VAvatar color="error" variant="tonal" size="44" class="mx-auto mb-2" rounded>
              <VIcon icon="tabler-circle-x" size="24" />
            </VAvatar>
            <div class="text-h4 font-weight-bold text-error">{{ summary.cancelled }}</div>
            <div class="text-caption text-medium-emphasis font-weight-medium">طلبات ملغية</div>
          </VCard>
        </VCol>

        <VCol cols="12" sm="6" md="2">
          <VCard class="pa-4 text-center border bg-purple-lighten-5 border-purple">
            <VAvatar color="purple" variant="tonal" size="44" class="mx-auto mb-2" rounded>
              <VIcon icon="tabler-coin" size="24" />
            </VAvatar>
            <div class="text-h5 font-weight-bold text-purple-darken-3">{{ formatCurrency(summary.total_earnings) }}</div>
            <div class="text-caption text-purple-darken-2 font-weight-bold">إجمالي أجور الزيارات المنجزة</div>
          </VCard>
        </VCol>
      </VRow>

      <!-- جدول قائمة الطلبات المكلف بها الفني مع باجنيشن وفلترة -->
      <VCard title="طلبات وسجلات الزيارات المنزلية المكلف بها هذا الفني">

        <VCardText class="d-flex flex-wrap gap-4 align-center justify-space-between py-4">
          <AppTextField
            v-model="searchQuery"
            placeholder="ابحث برقم الطلب أو اسم المريض أو الهاتف..."
            prepend-inner-icon="tabler-search"
            style="max-inline-size: 300px;"
            clearable
            @update:model-value="fetchTechnicianProfile"
          />
          <AppSelect
            v-model="statusFilter"
            :items="statusOptions"
            item-title="title"
            item-value="value"
            style="max-inline-size: 220px;"
            @update:model-value="fetchTechnicianProfile"
          />
        </VCardText>

        <VDivider />

        <VDataTableServer
          :headers="ordersHeaders"
          :items="orders"
          :items-length="totalOrders"
          :loading="ordersLoading"
          :items-per-page="itemsPerPage"
          class="text-no-wrap"
          @update:options="onOrdersOptionsUpdate"
        >
          <!-- # رقم الطلب -->
          <template #item.id="{ item }">
            <RouterLink :to="`/orders/${item.id}`" class="font-weight-bold text-primary text-decoration-none">
              #{{ item.id }}
            </RouterLink>
          </template>

          <!-- المريض والموبايل -->
          <template #item.user="{ item }">
            <div>
              <div class="font-weight-bold text-body-1">{{ item.user?.name || 'مراجع' }}</div>
              <div class="text-caption text-medium-emphasis" dir="ltr">{{ item.user?.phone || '—' }}</div>
            </div>
          </template>

          <!-- الفرع المخبري -->
          <template #item.branch="{ item }">
            <VChip v-if="item.branch" color="primary" variant="tonal" size="small" label>
              {{ item.branch.name_ar }}
            </VChip>
            <span v-else class="text-caption text-medium-emphasis">الفرع الرئيسي</span>
          </template>

          <!-- موعد الزيارة المحدد -->
          <template #item.visit="{ item }">
            <div>
              <div class="d-flex align-center gap-1 font-weight-bold">
                <VIcon icon="tabler-calendar" size="14" class="text-primary" />
                <span>{{ item.visit_date || '—' }} — {{ item.visit_time || '—' }}</span>
              </div>
              <div class="text-caption text-medium-emphasis">{{ item.visit_period_label || '—' }}</div>
            </div>
          </template>

          <!-- الإجمالي -->
          <template #item.total="{ item }">
            <span class="font-weight-bold text-success">
              {{ formatCurrency(item.total) }}
            </span>
          </template>

          <!-- حالة الطلب -->
          <template #item.status="{ item }">
            <VChip :color="statusColor(item.status)" variant="tonal" size="small" label class="font-weight-bold">
              {{ item.status_label || statusLabel(item.status) }}
            </VChip>
          </template>

          <!-- إجراءات -->
          <template #item.actions="{ item }">
            <VBtn
              color="primary"
              variant="tonal"
              size="small"
              prepend-icon="tabler-eye"
              @click="router.push(`/orders/${item.id}`)"
            >
              تفاصيل الطلب
            </VBtn>
          </template>

          <!-- رسالة فارغة -->
          <template #no-data>
            <div class="text-center py-8 text-medium-emphasis">
              <VIcon icon="tabler-clipboard-off" size="44" class="mb-2 d-block mx-auto text-medium-emphasis" />
              <div class="font-weight-bold text-body-1">لا توجد طلبات زيارة تطابق هذا البحث أو الفرز</div>
            </div>
          </template>

          <template #bottom>
            <TablePagination
              v-if="totalOrders > 0"
              v-model:page="page"
              :items-per-page="itemsPerPage"
              :total-items="totalOrders"
            />
          </template>
        </VDataTableServer>
      </VCard>


    </div>
  </div>
</template>
