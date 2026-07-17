<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { $api } from '@/utils/api'

definePage({
  meta: { title: 'إدارة الطلبات' },
})

const router = useRouter()

// ─── البيانات ────────────────────────────────────────────────────
const loading     = ref(false)
const orders      = ref<any[]>([])
const summary     = ref<any>({})
const totalOrders = ref(0)
const page        = ref(1)
const itemsPerPage = ref(10)

const filterStatus    = ref('')
const filterVisitDate = ref('')
const search          = ref('')

const technicians = ref<any[]>([])

// ─── الإشعار ─────────────────────────────────────────────────────
const snackbar = ref({ show: false, color: 'success', text: '' })
const toast = (text: string, color = 'success') => {
  snackbar.value = { show: true, color, text }
}

// ─── نافذة تفاصيل الطلب ──────────────────────────────────────────
const detailsDialog = ref({ show: false, order: null as any })

// ─── نافذة تحديث الحالة ──────────────────────────────────────────
const statusDialog = ref({
  show: false,
  order: null as any,
  loading: false,
  form: { status: '', technician_id: null as number | null, cancel_reason: '' },
})

// ─── جلب الطلبات ─────────────────────────────────────────────────
const fetchOrders = async () => {
  loading.value = true
  try {
    const params: any = {
      page: page.value,
      itemsPerPage: itemsPerPage.value,
    }
    if (filterStatus.value)    params.status     = filterStatus.value
    if (filterVisitDate.value) params.visit_date = filterVisitDate.value
    if (search.value)          params.q          = search.value

    const res = await $api('/orders', { params })
    if (res?.status) {
      orders.value  = res.orders || []
      summary.value = res.summary || {}
      totalOrders.value = res.totalOrders || 0
    }
  } catch {
    toast('حدث خطأ أثناء جلب الطلبات', 'error')
  } finally {
    loading.value = false
  }
}

// ─── جلب الفنيين (لتعيينهم في الطلب) ────────────────────────────
const fetchTechnicians = async () => {
  try {
    const res = await $api('/technicians')
    technicians.value = (res?.technicians || res?.data || []).filter((t: any) => t.is_active)
  } catch {}
}

// ─── فتح تفاصيل الطلب ────────────────────────────────────────────
const openDetails = (item: any) => {
  router.push(`/orders/${item.id}`)
}

// ─── فتح نافذة تحديث الحالة ──────────────────────────────────────
const openStatusDialog = (item: any) => {
  statusDialog.value = {
    show: true,
    order: item,
    loading: false,
    form: {
      status: item.status,
      technician_id: item.technician?.id || null,
      cancel_reason: '',
    },
  }
}

// ─── تحديث الحالة ────────────────────────────────────────────────
const updateStatus = async () => {
  const { order, form } = statusDialog.value
  statusDialog.value.loading = true
  try {
    const res = await $api(`/orders/${order.id}/status`, {
      method: 'PATCH',
      body: {
        status:         form.status,
        technician_id:  form.technician_id,
        cancel_reason:  form.cancel_reason,
      },
    })
    if (res?.status) {
      toast(res.message || 'تم تحديث حالة الطلب بنجاح', 'success')
      statusDialog.value.show = false
      fetchOrders()
    } else {
      toast(res?.message || 'فشل التحديث', 'error')
    }
  } catch {
    toast('حدث خطأ أثناء التحديث', 'error')
  } finally {
    statusDialog.value.loading = false
  }
}

// ─── تنسيق العرض ─────────────────────────────────────────────────
const statusOptions = [
  { title: 'كل الحالات', value: '' },
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

const formatCurrency = (v: any) => v ? Number(v).toLocaleString('ar-IQ') + ' د.ع' : '—'

const periodLabel = (p: string) => ({ morning: 'صباحاً', noon: 'ظهراً', evening: 'مساءً' }[p] ?? p)

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

watch([page, itemsPerPage], () => {
  fetchOrders()
})

onMounted(() => {
  fetchOrders()
  fetchTechnicians()
})
</script>

<template>
  <div>
    <!-- إشعار -->
    <VSnackbar v-model="snackbar.show" location="top" :color="snackbar.color" timeout="3000">
      {{ snackbar.text }}
    </VSnackbar>

    <!-- ──────────── نافذة تفاصيل الطلب ──────────── -->
    <VDialog v-model="detailsDialog.show" max-width="700" scrollable>
      <VCard v-if="detailsDialog.order">
        <VCardItem>
          <VCardTitle class="text-h5 font-weight-bold d-flex align-center gap-2">
            <VIcon icon="tabler-clipboard-list" color="primary" />
            تفاصيل الطلب #{{ detailsDialog.order.id }}
          </VCardTitle>
          <template #append>
            <VChip :color="statusColor(detailsDialog.order.status)" variant="elevated" size="small">
              {{ statusLabel(detailsDialog.order.status) }}
            </VChip>
          </template>
        </VCardItem>
        <VDivider />
        <VCardText class="pt-4">
          <VRow>
            <!-- بيانات المريض -->
            <VCol cols="12" md="6">
              <div class="text-caption text-medium-emphasis mb-1">المريض</div>
              <div class="font-weight-bold text-h6">{{ detailsDialog.order.user?.name || '—' }}</div>
              <div class="text-body-2 text-medium-emphasis" dir="ltr">{{ detailsDialog.order.user?.phone || '—' }}</div>
            </VCol>
            <!-- الفرع -->
            <VCol cols="12" md="6">
              <div class="text-caption text-medium-emphasis mb-1">الفرع</div>
              <div class="font-weight-bold">{{ detailsDialog.order.branch?.name_ar || 'غير محدد' }}</div>
            </VCol>
            <!-- الزيارة -->
            <VCol cols="12" md="6">
              <div class="text-caption text-medium-emphasis mb-1">تاريخ ووقت الزيارة</div>
              <div class="font-weight-bold">
                {{ detailsDialog.order.visit_date }}
                — {{ detailsDialog.order.visit_time }}
                ({{ periodLabel(detailsDialog.order.visit_period) }})
              </div>
            </VCol>
            <!-- الموقع -->
            <VCol cols="12" md="6">
              <div class="text-caption text-medium-emphasis mb-1">الموقع</div>
              <div class="font-weight-bold text-body-2">
                {{ detailsDialog.order.address_text || (detailsDialog.order.lat ? `${detailsDialog.order.lat}, ${detailsDialog.order.lng}` : '—') }}
              </div>
            </VCol>
            <!-- الطبيب المرسل -->
            <VCol v-if="detailsDialog.order.doctor_name" cols="12" md="6">
              <div class="text-caption text-medium-emphasis mb-1">الطبيب المرسِل</div>
              <div class="font-weight-bold">{{ detailsDialog.order.doctor_name }}</div>
            </VCol>
            <!-- الملاحظات -->
            <VCol v-if="detailsDialog.order.notes" cols="12">
              <div class="text-caption text-medium-emphasis mb-1">ملاحظات للفني</div>
              <div class="text-body-2 pa-2 rounded bg-grey-lighten-4">{{ detailsDialog.order.notes }}</div>
            </VCol>
            <!-- صورة الراجعة -->
            <VCol v-if="detailsDialog.order.referral_image" cols="12">
              <div class="text-caption text-medium-emphasis mb-2">صورة الراجعة الطبية</div>
              <a :href="detailsDialog.order.referral_image" target="_blank">
                <VImg :src="detailsDialog.order.referral_image" max-height="200" cover class="rounded-lg" />
              </a>
            </VCol>
          </VRow>

          <VDivider class="my-4" />

          <!-- بنود الطلب -->
          <div class="text-subtitle-1 font-weight-bold mb-3">التحاليل والباقات المطلوبة</div>
          <VTable density="compact">
            <thead>
              <tr>
                <th>الاسم</th>
                <th>النوع</th>
                <th class="text-end">السعر</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in detailsDialog.order.items" :key="item.id">
                <td>{{ item.name_ar }}</td>
                <td>
                  <VChip size="x-small" :color="item.item_type === 'package' ? 'secondary' : 'primary'" variant="tonal">
                    {{ item.item_type === 'package' ? 'باقة' : 'تحليل' }}
                  </VChip>
                </td>
                <td class="text-end font-weight-bold">{{ formatCurrency(item.price) }}</td>
              </tr>
            </tbody>
          </VTable>

          <!-- ملخص الأسعار -->
          <div class="mt-4 pa-4 rounded-lg bg-grey-lighten-5">
            <div class="d-flex justify-space-between mb-2">
              <span class="text-medium-emphasis">مجموع التحاليل</span>
              <span class="font-weight-bold">{{ formatCurrency(detailsDialog.order.subtotal) }}</span>
            </div>
            <div class="d-flex justify-space-between mb-2">
              <span class="text-medium-emphasis">أجور الخدمة</span>
              <span :class="Number(detailsDialog.order.service_fee) > 0 ? 'font-weight-bold' : 'text-success font-weight-bold'">
                {{ Number(detailsDialog.order.service_fee) > 0 ? formatCurrency(detailsDialog.order.service_fee) : 'مجاناً' }}
              </span>
            </div>
            <div v-if="Number(detailsDialog.order.discount_amount) > 0" class="d-flex justify-space-between mb-2">
              <span class="text-success">خصم الكوبون</span>
              <span class="text-success font-weight-bold">- {{ formatCurrency(detailsDialog.order.discount_amount) }}</span>
            </div>
            <VDivider class="my-2" />
            <div class="d-flex justify-space-between">
              <span class="font-weight-bold text-h6">الإجمالي</span>
              <span class="font-weight-bold text-h6 text-primary">{{ formatCurrency(detailsDialog.order.total) }}</span>
            </div>
          </div>
        </VCardText>
        <VCardActions class="pa-4 justify-end">
          <VBtn color="secondary" variant="tonal" @click="detailsDialog.show = false">إغلاق</VBtn>
          <VBtn color="primary" variant="elevated" prepend-icon="tabler-edit" @click="openStatusDialog(detailsDialog.order); detailsDialog.show = false">
            تحديث الحالة
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- ──────────── نافذة تحديث الحالة ──────────── -->
    <VDialog v-model="statusDialog.show" max-width="480">
      <VCard :title="`تحديث حالة الطلب #${statusDialog.order?.id}`">
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
              <AppSelect
                v-model="statusDialog.form.technician_id"
                label="الفني المكلف (اختياري)"
                :items="[
                  { title: 'بدون تعيين', value: null },
                  ...technicians.map(t => ({ title: t.name, value: t.id }))
                ]"
              />
            </VCol>
            <VCol v-if="statusDialog.form.status === 'cancelled'" cols="12">
              <AppTextField
                v-model="statusDialog.form.cancel_reason"
                label="سبب الإلغاء"
                placeholder="يرجى ذكر سبب إلغاء الطلب..."
              />
            </VCol>
          </VRow>
        </VCardText>
        <VCardActions class="pa-4 justify-end gap-2">
          <VBtn color="secondary" variant="tonal" @click="statusDialog.show = false">إلغاء</VBtn>
          <VBtn color="primary" variant="elevated" :loading="statusDialog.loading" @click="updateStatus">
            حفظ التغيير
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- ──────────── رأس الصفحة ──────────── -->
    <VRow class="mb-4">
      <VCol cols="12">
        <h4 class="text-h4 font-weight-bold mb-1 d-flex align-center gap-2">
          <VIcon icon="tabler-clipboard-list" color="primary" size="32" />
          إدارة طلبات التحاليل المنزلية
        </h4>
        <p class="text-body-1 text-medium-emphasis mb-0">
          متابعة ومراقبة جميع طلبات الزيارات والتحاليل المنزلية وتحديث حالاتها وتعيين الفنيين
        </p>
      </VCol>
    </VRow>

    <!-- ──────────── بطاقات ملخص الحالات ──────────── -->
    <VRow class="mb-5">
      <VCol v-for="s in [
        { key: 'pending', label: 'بانتظار', icon: 'tabler-clock', color: 'warning' },
        { key: 'confirmed', label: 'مؤكد', icon: 'tabler-circle-check', color: 'info' },
        { key: 'awaiting_technician', label: 'بانتظار فني', icon: 'tabler-user-search', color: 'warning' },
        { key: 'technician_assigned', label: 'تم تعيين فني', icon: 'tabler-user-check', color: 'primary' },
        { key: 'on_the_way', label: 'في الطريق', icon: 'tabler-car', color: 'primary' },
        { key: 'sample_collected', label: 'عينة مسحوبة', icon: 'tabler-test-pipe', color: 'secondary' },
        { key: 'in_progress', label: 'قيد التحليل', icon: 'tabler-flask', color: 'info' },
        { key: 'completed', label: 'مكتمل', icon: 'tabler-circle-check-filled', color: 'success' },
        { key: 'cancelled', label: 'ملغي', icon: 'tabler-x', color: 'error' },
      ]" :key="s.key" cols="6" sm="4" lg="3" xl="auto">
        <VCard
          :class="filterStatus === s.key ? 'border-2 border-' + s.color : ''"
          class="cursor-pointer"
          style="min-width:120px"
          @click="filterStatus = filterStatus === s.key ? '' : s.key; fetchOrders()"
        >
          <VCardText class="text-center pa-3">
            <VIcon :icon="s.icon" :color="s.color" size="24" class="mb-1" />
            <div class="text-h5 font-weight-bold" :class="`text-${s.color}`">
              {{ summary[s.key] ?? 0 }}
            </div>
            <div class="text-caption text-medium-emphasis">{{ s.label }}</div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- ──────────── فلاتر البحث ──────────── -->
    <VCard class="mb-4">
      <VCardText>
        <VRow align="center">
          <VCol cols="12" md="4">
            <AppTextField
              v-model="search"
              label="بحث باسم المريض أو رقم الطلب"
              placeholder="اكتب للبحث..."
              prepend-inner-icon="tabler-search"
              clearable
              @keyup.enter="fetchOrders"
              @click:clear="search = ''; fetchOrders()"
            />
          </VCol>
          <VCol cols="12" md="3">
            <AppSelect
              v-model="filterStatus"
              label="فلتر الحالة"
              :items="statusOptions"
              @update:model-value="fetchOrders"
            />
          </VCol>
          <VCol cols="12" md="3">
            <AppTextField
              v-model="filterVisitDate"
              type="date"
              label="تاريخ الزيارة"
              @update:model-value="fetchOrders"
            />
          </VCol>
          <VCol cols="12" md="2">
            <VBtn color="primary" variant="tonal" block prepend-icon="tabler-refresh" @click="fetchOrders">
              تحديث
            </VBtn>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <!-- ──────────── جدول الطلبات ──────────── -->
    <VCard :loading="loading">
      <!-- شريط خيارات عدد الصفوف -->
      <VCardText class="d-flex flex-wrap gap-4 align-center justify-space-between py-4 border-b">
        <div class="d-flex align-center gap-2">
          <span class="text-body-2 text-medium-emphasis me-2">عرض:</span>
          <div style="inline-size: 6rem;">
            <AppSelect
              v-model="itemsPerPage"
              :items="[
                { title: '10', value: 10 },
                { title: '25', value: 25 },
                { title: '50', value: 50 },
                { title: '100', value: 100 }
              ]"
            />
          </div>
          <span class="text-body-2 text-medium-emphasis">سجل لكل صفحة</span>
        </div>
        <span class="text-body-2 text-medium-emphasis">
          {{ totalOrders > 0 ? `إجمالي الطلبات: ${totalOrders} طلب` : '' }}
        </span>
      </VCardText>

      <VTable v-if="orders.length > 0" class="text-no-wrap table-header-bg">
        <thead>
          <tr>
            <th style="width: 70px;">#</th>
            <th>المريض</th>
            <th>الفرع</th>
            <th>موعد الزيارة</th>
            <th>التحاليل</th>
            <th>الإجمالي</th>
            <th>الحالة</th>
            <th>الفني</th>
            <th class="text-center">إجراءات</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in orders" :key="order.id" class="align-middle">
            <td>
              <VChip size="small" variant="tonal" color="primary" class="font-weight-bold">
                #{{ order.id }}
              </VChip>
            </td>

            <td>
              <div class="font-weight-bold text-high-emphasis">{{ order.user?.name || '—' }}</div>
              <div class="text-caption text-medium-emphasis" dir="ltr">{{ order.user?.phone }}</div>
            </td>

            <td>
              <span class="text-body-2 font-weight-medium">{{ order.branch?.name_ar || 'غير محدد' }}</span>
            </td>

            <td>
              <div class="font-weight-bold">{{ order.visit_date }}</div>
              <div class="text-caption text-medium-emphasis">{{ order.visit_time }} — {{ periodLabel(order.visit_period) }}</div>
            </td>

            <td>
              <VChip color="primary" variant="tonal" size="small" class="font-weight-bold">
                {{ order.items_count || (order.items ? order.items.length : 0) }} عنصر
              </VChip>
            </td>

            <td class="font-weight-bold text-success">{{ formatCurrency(order.total) }}</td>

            <td>
              <VChip :color="statusColor(order.status)" variant="elevated" size="small" class="font-weight-bold">
                {{ statusLabel(order.status) }}
              </VChip>
            </td>

            <td>
              <span class="text-body-2 text-medium-emphasis font-weight-medium">{{ order.technician?.name || '— غير معين —' }}</span>
            </td>

            <td class="text-center">
              <VBtn
                color="primary"
                variant="tonal"
                size="small"
                prepend-icon="tabler-eye"
                @click="openDetails(order)"
              >
                عرض الطلب
              </VBtn>
            </td>
          </tr>
        </tbody>
      </VTable>

      <div v-else-if="!loading" class="text-center py-12 text-medium-emphasis">
        <VIcon icon="tabler-clipboard-off" size="64" class="mb-3 d-block mx-auto" />
        <div class="text-h6 font-weight-bold mb-2">لا توجد طلبات حالياً</div>
        <p class="text-body-2">ستظهر هنا طلبات الزيارات المنزلية فور إنشائها من التطبيق</p>
      </div>

      <TablePagination
        v-if="totalOrders > 0"
        v-model:page="page"
        :items-per-page="itemsPerPage"
        :total-items="totalOrders"
      />
    </VCard>
  </div>
</template>

<style scoped>
.table-header-bg th {
  background-color: rgba(var(--v-theme-on-surface), 0.04) !important;
  font-weight: 700 !important;
}
</style>
