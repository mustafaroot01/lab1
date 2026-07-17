<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { $api } from '@/utils/api'
import TablePagination from '@/@core/components/TablePagination.vue'

definePage({
  meta: {
    title: 'الملف الشخصي للمريض',
  },
})

const route = useRoute()
const router = useRouter()
const patientId = route.params.id

const loading = ref(true)
const patient = ref<any>(null)
const orders = ref<any[]>([])
const ordersSummary = ref<any>({
  total: 0,
  completed: 0,
  in_progress: 0,
  pending: 0,
  cancelled: 0,
  total_spent: 0,
})

// التبويب النشط داخل السجل الطبي السريري فقط (الأمراض، الأدوية، الحساسية)
const medicalTab = ref('chronic_diseases')

// إعدادات البحث والفلترة والباجنيشن لسجل الطلبيات المنفصل
const ordersSearch = ref('')
const ordersPage = ref(1)
const ordersPerPage = ref(10)

// أعمدة جدول سجل الطلبات
const ordersHeaders = [
  { title: '# رقم الطلب', key: 'id' },
  { title: 'الفرع المسؤول', key: 'branch' },
  { title: 'الفني المكلف بالزيارة', key: 'technician' },
  { title: 'موعد الزيارة المحدد', key: 'visit_date' },
  { title: 'المبلغ', key: 'total' },
  { title: 'حالة الطلب', key: 'status' },
  { title: 'إجراءات', key: 'actions', align: 'center', sortable: false },
]

// تصفية الطلبات بالبحث
const filteredOrders = computed(() => {
  if (!ordersSearch.value) return orders.value
  const q = ordersSearch.value.toLowerCase().trim()
  return orders.value.filter(o => 
    String(o.id).includes(q) ||
    o.branch?.name_ar?.toLowerCase().includes(q) ||
    o.technician?.name?.toLowerCase().includes(q) ||
    o.status_label?.toLowerCase().includes(q) ||
    o.visit_date?.includes(q)
  )
})

// إعادة ضبط الصفحة الأولى عند تغيير البحث أو عدد العناصر
watch([ordersSearch, ordersPerPage], () => {
  ordersPage.value = 1
})

// إشعارات علوية
const snackbar = ref({
  show: false,
  color: 'success',
  text: '',
})

const showToast = (message: string, color = 'success') => {
  snackbar.value = {
    show: true,
    color,
    text: message,
  }
}

// Dialog لتأكيد الإجراءات
const confirmDialog = ref({
  show: false,
  title: '',
  text: '',
  color: 'error',
  icon: 'tabler-alert-triangle',
  action: null as (() => Promise<void>) | null,
})

// Dialog لإضافة سجل طبي
const addDialog = ref({
  show: false,
  type: 'chronic_disease',
  title: '',
  loading: false,
  form: {
    disease_name: '',
    medication_name: '',
    allergen: '',
    severity: 'medium',
    dosage: '',
    frequency: '',
    diagnosis_date: '',
    start_date: '',
    reaction: '',
    notes: '',
  },
})

// جلب تفاصيل المريض
const fetchPatientDetails = async () => {
  loading.value = true
  try {
    const res = await $api(`/patients/${patientId}`)
    if (res?.status) {
      patient.value = res.data || res.patient
      orders.value = res.orders || []
      if (res.orders_summary) {
        ordersSummary.value = res.orders_summary
      }
    }
  } catch (e: any) {
    showToast('تعذر جلب بيانات المريض', 'error')
  } finally {
    loading.value = false
  }
}

// تنسيق العملة
const formatCurrency = (amount: any) => {
  if (!amount) return '0 د.ع'
  return Number(amount).toLocaleString('ar-IQ') + ' د.ع'
}

// ألوان حالات الطلب
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
  awaiting_technician: 'بانتظار فني',
  technician_assigned: 'تم تعيين فني',
  on_the_way:          'الفني في الطريق',
  sample_collected:    'تم سحب العينة',
  in_progress:         'قيد التحليل',
  completed:           'مكتمل',
  cancelled:           'ملغي',
}[status] ?? status)

// نسخ الموبايل
const copyPhone = (phone: string) => {
  if (!phone) return
  navigator.clipboard.writeText(phone)
  showToast(`تم نسخ رقم الهاتف: ${phone}`, 'info')
}

// فتح واتساب
const openWhatsApp = (phone?: string) => {
  if (!phone) return
  let cleaned = phone.replace(/\D/g, '')
  if (cleaned.startsWith('0')) cleaned = '964' + cleaned.substring(1)
  else if (!cleaned.startsWith('964')) cleaned = '964' + cleaned
  window.open(`https://wa.me/${cleaned}`, '_blank')
}

// فتح نافذة الإضافة الطبية
const openAddDialog = (type: 'chronic_disease' | 'medication' | 'allergy') => {
  addDialog.value.type = type
  addDialog.value.form = {
    disease_name: '',
    medication_name: '',
    allergen: '',
    severity: 'medium',
    dosage: '',
    frequency: '',
    diagnosis_date: '',
    start_date: '',
    reaction: '',
    notes: '',
  }

  if (type === 'chronic_disease') {
    addDialog.value.title = 'إضافة مرض مزمن للمريض'
  } else if (type === 'medication') {
    addDialog.value.title = 'إضافة دواء / علاج للمريض'
  } else {
    addDialog.value.title = 'إضافة حساسية ومادة مسببة للمريض'
  }

  addDialog.value.show = true
}

// حفظ السجل الطبي
const saveMedicalRecord = async () => {
  const { type, form } = addDialog.value
  addDialog.value.loading = true

  try {
    let endpoint = ''
    let payload: any = { notes: form.notes }

    if (type === 'chronic_disease') {
      if (!form.disease_name.trim()) {
        showToast('يرجى إدخال اسم المرض المزمن', 'warning')
        addDialog.value.loading = false
        return
      }
      endpoint = `/patients/${patientId}/medical-records/chronic-diseases`
      payload.disease_name = form.disease_name
      payload.diagnosis_date = form.diagnosis_date || null
      payload.severity = form.severity
    } else if (type === 'medication') {
      if (!form.medication_name.trim()) {
        showToast('يرجى إدخال اسم الدواء', 'warning')
        addDialog.value.loading = false
        return
      }
      endpoint = `/patients/${patientId}/medical-records/medications`
      payload.medication_name = form.medication_name
      payload.dosage = form.dosage
      payload.frequency = form.frequency
      payload.start_date = form.start_date || null
    } else if (type === 'allergy') {
      if (!form.allergen.trim()) {
        showToast('يرجى إدخال المادة المسببة للحساسية', 'warning')
        addDialog.value.loading = false
        return
      }
      endpoint = `/patients/${patientId}/medical-records/allergies`
      payload.allergen = form.allergen
      payload.severity = form.severity
      payload.reaction = form.reaction
    }

    const res = await $api(endpoint, {
      method: 'POST',
      body: payload,
    })

    if (res?.status) {
      showToast(res.message || 'تم الإضافة بنجاح', 'success')
      addDialog.value.show = false
      fetchPatientDetails()
    } else {
      showToast(res?.message || 'تعذر الإضافة', 'error')
    }
  } catch (e: any) {
    showToast('حدث خطأ أثناء حفظ السجل', 'error')
  } finally {
    addDialog.value.loading = false
  }
}

// حذف السجل الطبي
const deleteMedicalRecord = (type: string, id: number, name: string) => {
  confirmDialog.value = {
    show: true,
    title: 'تأكيد الحذف',
    text: `هل أنت متأكد من حذف (${name}) من ملف المريض؟`,
    color: 'error',
    icon: 'tabler-trash',
    action: async () => {
      try {
        const res = await $api(`/patients/medical-records/${type}/${id}`, { method: 'DELETE' })
        if (res?.status) {
          showToast(res.message || 'تم الحذف بنجاح', 'success')
          fetchPatientDetails()
        }
      } catch (e) {
        showToast('حدث خطأ أثناء الحذف', 'error')
      } finally {
        confirmDialog.value.show = false
      }
    },
  }
}

// تغيير حالة الحساب
const confirmToggleStatus = () => {
  if (!patient.value) return
  const isActivating = !patient.value.is_active

  confirmDialog.value = {
    show: true,
    title: isActivating ? 'تفعيل حساب المريض' : 'إيقاف حساب المريض',
    text: isActivating
      ? `هل تريد تفعيل حساب المريض (${patient.value.name}) مجدداً؟`
      : `هل تريد إيقاف حساب المريض (${patient.value.name}) وتوقيف جلساته في التطبيق؟`,
    color: isActivating ? 'success' : 'error',
    icon: isActivating ? 'tabler-check' : 'tabler-ban',
    action: async () => {
      try {
        const res = await $api(`/patients/${patientId}/toggle-status`, { method: 'PATCH' })
        if (res?.status) {
          showToast(res.message || 'تم تحديث الحالة بنجاح', 'success')
          fetchPatientDetails()
        }
      } catch (e) {
        showToast('حدث خطأ أثناء تحديث الحالة', 'error')
      } finally {
        confirmDialog.value.show = false
      }
    },
  }
}

// إنهاء الجلسات
const confirmRevokeTokens = () => {
  if (!patient.value) return

  confirmDialog.value = {
    show: true,
    title: 'إنهاء الجلسات المفتوحة',
    text: `هل تريد تسجيل خروج المريض قسرياً من جميع الأجهزة المحمولة؟`,
    color: 'warning',
    icon: 'tabler-logout',
    action: async () => {
      try {
        const res = await $api(`/patients/${patientId}/revoke-tokens`, { method: 'POST' })
        if (res?.status) {
          showToast('تم إنهاء جميع الجلسات المفتوحة للمريض بنجاح', 'success')
        }
      } catch (e) {
        showToast('حدث خطأ أثناء إنهاء الجلسات', 'error')
      } finally {
        confirmDialog.value.show = false
      }
    },
  }
}

onMounted(() => {
  fetchPatientDetails()
})
</script>

<template>
  <div>
    <!-- الإشعار العلوي -->
    <VSnackbar v-model="snackbar.show" location="top" :color="snackbar.color" timeout="3000">
      {{ snackbar.text }}
    </VSnackbar>

    <!-- حوار التأكيد -->
    <VDialog v-model="confirmDialog.show" max-width="450">
      <VCard>
        <VCardItem class="text-center pt-6">
          <VAvatar :color="confirmDialog.color" variant="tonal" size="64" class="mb-3 mx-auto">
            <VIcon :icon="confirmDialog.icon" size="36" />
          </VAvatar>
          <VCardTitle class="text-h5 font-weight-bold">{{ confirmDialog.title }}</VCardTitle>
        </VCardItem>
        <VCardText class="text-center text-body-1 text-medium-emphasis pb-6">
          {{ confirmDialog.text }}
        </VCardText>
        <VDivider />
        <VCardActions class="pa-4 justify-end gap-2">
          <VBtn color="secondary" variant="tonal" @click="confirmDialog.show = false">إلغاء</VBtn>
          <VBtn :color="confirmDialog.color" variant="elevated" @click="confirmDialog.action && confirmDialog.action()">تأكيد</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- نافذة إضافة عنصر طبي -->
    <VDialog v-model="addDialog.show" max-width="500">
      <VCard :title="addDialog.title">
        <VDivider />
        <VCardText class="pt-6">
          <VRow>
            <!-- أمراض مزمنة -->
            <template v-if="addDialog.type === 'chronic_disease'">
              <VCol cols="12">
                <AppTextField v-model="addDialog.form.disease_name" label="اسم المرض المزمن *" placeholder="مثال: ضغط الدم، السكري..." required />
              </VCol>
              <VCol cols="12" md="6">
                <AppTextField v-model="addDialog.form.diagnosis_date" type="date" label="تاريخ التشخيص" />
              </VCol>
              <VCol cols="12" md="6">
                <AppSelect v-model="addDialog.form.severity" label="درجة الخطورة" :items="[{ title: 'منخفضة', value: 'low' }, { title: 'متوسطة', value: 'medium' }, { title: 'عالية', value: 'high' }]" />
              </VCol>
            </template>

            <!-- أدوية -->
            <template v-if="addDialog.type === 'medication'">
              <VCol cols="12">
                <AppTextField v-model="addDialog.form.medication_name" label="اسم الدواء *" placeholder="مثال: Metformin..." required />
              </VCol>
              <VCol cols="12" md="6">
                <AppTextField v-model="addDialog.form.dosage" label="الجرعة" placeholder="500mg" />
              </VCol>
              <VCol cols="12" md="6">
                <AppTextField v-model="addDialog.form.frequency" label="التكرار" placeholder="مرتين يومياً" />
              </VCol>
              <VCol cols="12">
                <AppTextField v-model="addDialog.form.start_date" type="date" label="تاريخ البدء" />
              </VCol>
            </template>

            <!-- حساسية -->
            <template v-if="addDialog.type === 'allergy'">
              <VCol cols="12" md="6">
                <AppTextField v-model="addDialog.form.allergen" label="المادة المسببة للحساسية *" placeholder="مثال: البنسلين..." required />
              </VCol>
              <VCol cols="12" md="6">
                <AppSelect v-model="addDialog.form.severity" label="الشدة" :items="[{ title: 'منخفضة', value: 'low' }, { title: 'متوسطة', value: 'medium' }, { title: 'عالية', value: 'high' }]" />
              </VCol>
              <VCol cols="12">
                <AppTextField v-model="addDialog.form.reaction" label="رد الفعل التحسسي" placeholder="مثال: طفح جلدي" />
              </VCol>
            </template>

            <VCol cols="12">
              <AppTextField v-model="addDialog.form.notes" label="ملاحظات إضافية" />
            </VCol>
          </VRow>
        </VCardText>
        <VDivider />
        <VCardActions class="pa-4 justify-end gap-2">
          <VBtn color="secondary" variant="tonal" @click="addDialog.show = false">إلغاء</VBtn>
          <VBtn color="primary" variant="elevated" :loading="addDialog.loading" @click="saveMedicalRecord">إضافة للسجل</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <div v-if="loading" class="text-center py-16">
      <VProgressCircular indeterminate color="primary" size="48" />
    </div>

    <!-- المحتوى المنظم بأسلوب Materio النظيف -->
    <div v-else-if="patient">
      <!-- 1. شريط العنوان والأزرار العلوية -->
      <div class="d-flex justify-space-between align-center flex-wrap gap-4 mb-6">
        <div class="d-flex align-center gap-3">
          <VBtn icon variant="tonal" color="primary" size="large" @click="router.push('/patients')">
            <VIcon icon="tabler-arrow-right" size="24" />
          </VBtn>
          <div>
            <h4 class="text-h4 font-weight-bold mb-1 d-flex align-center gap-2">
              <VIcon icon="tabler-user-circle" color="primary" size="32" />
              <span>الملف الشخصي: {{ patient.name }}</span>
              <span class="text-body-1 text-medium-emphasis">#{{ patient.id }}</span>
            </h4>
            <p class="text-body-2 text-medium-emphasis mb-0">
              إدارة السجل الطبي الشامل وتفاصيل الحساب، ومتابعة سجل الطلبيات المخبرية.
            </p>
          </div>
        </div>

        <div class="d-flex align-center gap-3 flex-wrap">
          <VBtn
            v-if="patient.phone"
            color="success"
            variant="elevated"
            prepend-icon="tabler-brand-whatsapp"
            @click="openWhatsApp(patient.phone)"
          >
            مراسلة واتساب
          </VBtn>

          <VBtn
            :color="patient.is_active ? 'error' : 'success'"
            variant="tonal"
            :prepend-icon="patient.is_active ? 'tabler-ban' : 'tabler-check'"
            @click="confirmToggleStatus"
          >
            {{ patient.is_active ? 'إيقاف الحساب' : 'تفعيل الحساب' }}
          </VBtn>

          <VBtn
            color="warning"
            variant="tonal"
            prepend-icon="tabler-logout"
            @click="confirmRevokeTokens"
          >
            إنهاء الجلسات
          </VBtn>
        </div>
      </div>

      <!-- 2. شبكة الملف الشخصي والسجل الطبي (4 vs 8) -->
      <VRow>
        <!-- العمود الأيسر: بطاقة التفاصيل الشخصية والتغطية -->
        <VCol cols="12" md="4">
          <!-- بطاقة التعريف الشخصية -->
          <VCard class="mb-6">
            <VCardText class="text-center pt-8 pb-4">
              <VAvatar
                rounded
                :size="96"
                :color="patient.gender === 'female' ? 'warning' : 'primary'"
                variant="tonal"
                class="mb-3"
              >
                <VIcon :icon="patient.gender === 'female' ? 'tabler-user-heart' : 'tabler-user'" size="52" />
              </VAvatar>

              <h5 class="text-h5 font-weight-bold mb-1">
                {{ patient.name }}
              </h5>

              <div class="d-flex align-center justify-center gap-2 flex-wrap mt-2">
                <VChip label :color="patient.is_active ? 'success' : 'error'" size="small" class="font-weight-bold">
                  {{ patient.is_active ? 'حساب نشط' : 'حساب موقوف' }}
                </VChip>
                <VChip label :color="patient.is_profile_completed ? 'info' : 'warning'" size="small" class="font-weight-bold">
                  {{ patient.is_profile_completed ? 'ملف مكتمل' : 'قيد التسجيل' }}
                </VChip>
              </div>
            </VCardText>

            <VDivider />

            <!-- قائمة البيانات الشخصية متراصة ونظيفة متوافقة مع RTL -->
            <VCardText class="pa-6">
              <VList class="card-list density-compact pa-0" style="--v-card-list-gap: 0.8rem;">
                <VListItem class="px-0">
                  <VListItemTitle>
                    <span class="text-h6 font-weight-medium me-2">رقم الموبايل:</span>
                    <span class="text-body-1 text-high-emphasis font-weight-bold" dir="ltr">{{ patient.phone || '—' }}</span>
                    <VIcon icon="tabler-copy" size="16" class="ms-1 cursor-pointer text-primary" title="نسخ الرقم" @click="copyPhone(patient.phone)" />
                  </VListItemTitle>
                </VListItem>

                <VListItem class="px-0">
                  <VListItemTitle>
                    <span class="text-h6 font-weight-medium me-2">تاريخ الميلاد:</span>
                    <span class="text-body-1 text-high-emphasis font-weight-bold">{{ patient.birth_date || '—' }} ({{ patient.age }})</span>
                  </VListItemTitle>
                </VListItem>

                <VListItem class="px-0">
                  <VListItemTitle>
                    <span class="text-h6 font-weight-medium me-2">الجنس:</span>
                    <span class="text-body-1 text-high-emphasis font-weight-bold">{{ patient.gender_text }}</span>
                  </VListItemTitle>
                </VListItem>

                <VListItem class="px-0">
                  <VListItemTitle>
                    <span class="text-h6 font-weight-medium me-2">تاريخ التسجيل:</span>
                    <span class="text-body-1 text-high-emphasis font-weight-bold">{{ patient.created_at }}</span>
                  </VListItemTitle>
                </VListItem>
              </VList>
            </VCardText>
          </VCard>

          <!-- بطاقة التغطية الجغرافية والفرع المسؤول -->
          <VCard title="التغطية الجغرافية والفرع">
            <VDivider />
            <VCardText class="pa-6">
              <VList class="card-list density-compact pa-0" style="--v-card-list-gap: 0.8rem;">
                <VListItem class="px-0">
                  <VListItemTitle>
                    <span class="text-h6 font-weight-medium me-2">القضاء:</span>
                    <span class="text-body-1 text-high-emphasis font-weight-bold">{{ patient.district?.name || 'غير محدد' }}</span>
                  </VListItemTitle>
                </VListItem>

                <VListItem class="px-0">
                  <VListItemTitle>
                    <span class="text-h6 font-weight-medium me-2">المنطقة السكنية:</span>
                    <span class="text-body-1 text-high-emphasis font-weight-bold">{{ patient.area?.name || 'غير محدد' }}</span>
                  </VListItemTitle>
                </VListItem>

                <VListItem class="px-0">
                  <VListItemTitle class="d-flex align-center flex-wrap gap-2">
                    <span class="text-h6 font-weight-medium me-1">الفرع المسؤول:</span>
                    <VChip v-if="patient.assigned_branch" color="primary" variant="tonal" size="small" label class="font-weight-bold">
                      {{ patient.assigned_branch.name_ar }}
                    </VChip>
                    <span v-else class="text-body-1 text-high-emphasis font-weight-bold">الفرع الرئيسي</span>
                  </VListItemTitle>
                </VListItem>
              </VList>
            </VCardText>
          </VCard>
        </VCol>

        <!-- العمود الأيمن: السجل الطبي الشامل (الأمراض، الأدوية، الحساسية) -->
        <VCol cols="12" md="8">
          <VCard>
            <!-- شريط تبويبات السجل الطبي -->
            <VTabs v-model="medicalTab" color="primary" class="v-tabs-pill pa-4 border-b" grow>
              <VTab value="chronic_diseases">
                <VIcon icon="tabler-heart-rate-monitor" class="me-2" size="20" />
                <span>الأمراض المزمنة</span>
                <VChip size="x-small" color="error" variant="tonal" class="ms-2 font-weight-bold">{{ patient.chronic_diseases?.length || 0 }}</VChip>
              </VTab>

              <VTab value="medications">
                <VIcon icon="tabler-pill" class="me-2" size="20" />
                <span>الأدوية الحالية</span>
                <VChip size="x-small" color="info" variant="tonal" class="ms-2 font-weight-bold">{{ patient.medications?.length || 0 }}</VChip>
              </VTab>

              <VTab value="allergies">
                <VIcon icon="tabler-shield-alert" class="me-2" size="20" />
                <span>سجل الحساسية</span>
                <VChip size="x-small" color="warning" variant="tonal" class="ms-2 font-weight-bold">{{ patient.allergies?.length || 0 }}</VChip>
              </VTab>
            </VTabs>

            <!-- محتوى السجل الطبي المباشر والنظيف بدون مساحات زائدة -->
            <VWindow v-model="medicalTab">
              <!-- 1. الأمراض المزمنة -->
              <VWindowItem value="chronic_diseases">
                <div class="pa-5">
                  <div class="d-flex align-center justify-space-between mb-4 flex-wrap gap-2">
                    <div>
                      <h6 class="text-h6 font-weight-bold">قائمة الأمراض المزمنة المشخصة</h6>
                      <p class="text-caption text-medium-emphasis mb-0">التاريخ المرضي المزمن للمساعد في دقة قراءة نتائج التحاليل</p>
                    </div>
                    <VBtn color="primary" variant="elevated" size="small" prepend-icon="tabler-plus" @click="openAddDialog('chronic_disease')">
                      إضافة مرض
                    </VBtn>
                  </div>

                  <VTable class="text-no-wrap border rounded">
                    <thead>
                      <tr class="bg-grey-lighten-5">
                        <th>المرض المزمن</th>
                        <th>تاريخ التشخيص</th>
                        <th>درجة الخطورة</th>
                        <th>ملاحظات</th>
                        <th class="text-center">إجراءات</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="item in patient.chronic_diseases" :key="item.id">
                        <td class="font-weight-bold">{{ item.disease_name }}</td>
                        <td>{{ item.diagnosis_date || 'غير مسجل' }}</td>
                        <td>
                          <VChip
                            :color="item.severity === 'high' ? 'error' : (item.severity === 'medium' ? 'warning' : 'info')"
                            variant="tonal"
                            size="small"
                            class="font-weight-bold"
                          >
                            {{ item.severity_text || item.severity }}
                          </VChip>
                        </td>
                        <td class="text-caption text-medium-emphasis">{{ item.notes || '—' }}</td>
                        <td class="text-center">
                          <VBtn icon="tabler-trash" color="error" size="small" variant="text" @click="deleteMedicalRecord('chronic-diseases', item.id, item.disease_name)" />
                        </td>
                      </tr>
                      <tr v-if="!patient.chronic_diseases?.length">
                        <td colspan="5" class="text-center py-10 text-medium-emphasis">
                          <VIcon icon="tabler-heart-off" size="42" class="mb-2 d-block mx-auto text-medium-emphasis" />
                          <div>لا يوجد سجل بالأمراض المزمنة لهذا المريض</div>
                        </td>
                      </tr>
                    </tbody>
                  </VTable>
                </div>
              </VWindowItem>

              <!-- 2. الأدوية الحالية -->
              <VWindowItem value="medications">
                <div class="pa-5">
                  <div class="d-flex align-center justify-space-between mb-4 flex-wrap gap-2">
                    <div>
                      <h6 class="text-h6 font-weight-bold">الأدوية والعلاجات المستمرة</h6>
                      <p class="text-caption text-medium-emphasis mb-0">الأدوية التي يتناولها المريض والتي قد تؤثر على القراءات المخبرية</p>
                    </div>
                    <VBtn color="primary" variant="elevated" size="small" prepend-icon="tabler-plus" @click="openAddDialog('medication')">
                      إضافة دواء
                    </VBtn>
                  </div>

                  <VTable class="text-no-wrap border rounded">
                    <thead>
                      <tr class="bg-grey-lighten-5">
                        <th>اسم الدواء / العلاج</th>
                        <th>الجرعة</th>
                        <th>التكرار</th>
                        <th>تاريخ البدء</th>
                        <th>ملاحظات</th>
                        <th class="text-center">إجراءات</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="item in patient.medications" :key="item.id">
                        <td class="font-weight-bold">{{ item.medication_name }}</td>
                        <td><VChip color="info" variant="tonal" size="small">{{ item.dosage || 'غير محدد' }}</VChip></td>
                        <td>{{ item.frequency || '—' }}</td>
                        <td>{{ item.start_date || '—' }}</td>
                        <td class="text-caption text-medium-emphasis">{{ item.notes || '—' }}</td>
                        <td class="text-center">
                          <VBtn icon="tabler-trash" color="error" size="small" variant="text" @click="deleteMedicalRecord('medications', item.id, item.medication_name)" />
                        </td>
                      </tr>
                      <tr v-if="!patient.medications?.length">
                        <td colspan="6" class="text-center py-10 text-medium-emphasis">
                          <VIcon icon="tabler-pill-off" size="42" class="mb-2 d-block mx-auto text-medium-emphasis" />
                          <div>لا يوجد سجل بالأدوية والعلاجات الحالية</div>
                        </td>
                      </tr>
                    </tbody>
                  </VTable>
                </div>
              </VWindowItem>

              <!-- 3. الحساسية -->
              <VWindowItem value="allergies">
                <div class="pa-5">
                  <div class="d-flex align-center justify-space-between mb-4 flex-wrap gap-2">
                    <div>
                      <h6 class="text-h6 font-weight-bold">سجل الحساسية والمواد المسببة</h6>
                      <p class="text-caption text-medium-emphasis mb-0">أنواع الحساسية الدوائية أو الغذائية لحماية المريض أثناء سحب العينة</p>
                    </div>
                    <VBtn color="primary" variant="elevated" size="small" prepend-icon="tabler-plus" @click="openAddDialog('allergy')">
                      إضافة حساسية
                    </VBtn>
                  </div>

                  <VTable class="text-no-wrap border rounded">
                    <thead>
                      <tr class="bg-grey-lighten-5">
                        <th>المادة المسببة للحساسية</th>
                        <th>شدة الحساسية</th>
                        <th>رد الفعل التحسسي</th>
                        <th>ملاحظات</th>
                        <th class="text-center">إجراءات</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="item in patient.allergies" :key="item.id">
                        <td class="font-weight-bold">{{ item.allergen }}</td>
                        <td>
                          <VChip
                            :color="item.severity === 'high' ? 'error' : (item.severity === 'medium' ? 'warning' : 'info')"
                            variant="tonal"
                            size="small"
                            class="font-weight-bold"
                          >
                            {{ item.severity_text || item.severity }}
                          </VChip>
                        </td>
                        <td>{{ item.reaction || '—' }}</td>
                        <td class="text-caption text-medium-emphasis">{{ item.notes || '—' }}</td>
                        <td class="text-center">
                          <VBtn icon="tabler-trash" color="error" size="small" variant="text" @click="deleteMedicalRecord('allergies', item.id, item.allergen)" />
                        </td>
                      </tr>
                      <tr v-if="!patient.allergies?.length">
                        <td colspan="5" class="text-center py-10 text-medium-emphasis">
                          <VIcon icon="tabler-shield-off" size="42" class="mb-2 d-block mx-auto text-medium-emphasis" />
                          <div>لا يوجد سجل بالحساسية لهذا المريض</div>
                        </td>
                      </tr>
                    </tbody>
                  </VTable>
                </div>
              </VWindowItem>
            </VWindow>
          </VCard>
        </VCol>
      </VRow>

      <!-- 3. سجل الطلبيات المستقل مع استخدام TablePagination القياسي الخاص بالمشروع -->
      <VRow class="mt-6">
        <VCol cols="12">
          <VCard title="سجل طلبيات الزيارات المنزلية والفحوصات المخبرية للمريض">
            <!-- شريط إحصائيات مالية وطلبات سريع أعلى جدول الطلبيات -->
            <VCardText class="py-4 bg-grey-lighten-5 border-y">
              <VRow>
                <VCol cols="12" sm="3">
                  <div class="d-flex align-center gap-3">
                    <VAvatar color="primary" variant="elevated" size="42" rounded>
                      <VIcon icon="tabler-files" size="22" />
                    </VAvatar>
                    <div>
                      <div class="text-h5 font-weight-bold text-primary">{{ ordersSummary.total }}</div>
                      <div class="text-caption text-medium-emphasis font-weight-medium">إجمالي الطلبيات</div>
                    </div>
                  </div>
                </VCol>

                <VCol cols="12" sm="3">
                  <div class="d-flex align-center gap-3">
                    <VAvatar color="success" variant="elevated" size="42" rounded>
                      <VIcon icon="tabler-check" size="22" />
                    </VAvatar>
                    <div>
                      <div class="text-h5 font-weight-bold text-success">{{ ordersSummary.completed }}</div>
                      <div class="text-caption text-medium-emphasis font-weight-medium">طلبات مكتملة</div>
                    </div>
                  </div>
                </VCol>

                <VCol cols="12" sm="3">
                  <div class="d-flex align-center gap-3">
                    <VAvatar color="info" variant="elevated" size="42" rounded>
                      <VIcon icon="tabler-clock-hour-4" size="22" />
                    </VAvatar>
                    <div>
                      <div class="text-h5 font-weight-bold text-info">{{ ordersSummary.in_progress }}</div>
                      <div class="text-caption text-medium-emphasis font-weight-medium">قيد التنفيذ / في الطريق</div>
                    </div>
                  </div>
                </VCol>

                <VCol cols="12" sm="3">
                  <div class="d-flex align-center gap-3">
                    <VAvatar color="warning" variant="elevated" size="42" rounded>
                      <VIcon icon="tabler-coin" size="22" />
                    </VAvatar>
                    <div>
                      <div class="text-h5 font-weight-bold text-warning-darken-2">{{ formatCurrency(ordersSummary.total_spent) }}</div>
                      <div class="text-caption text-medium-emphasis font-weight-medium">إجمالي ما أنفقه المريض</div>
                    </div>
                  </div>
                </VCol>
              </VRow>
            </VCardText>

            <!-- أدوات البحث والتصفية المتطابقة مع صفحات الماتريو القياسية -->
            <VCardText class="d-flex flex-wrap py-4 gap-4 align-center justify-space-between">
              <div style="inline-size: 18rem;">
                <AppTextField
                  v-model="ordersSearch"
                  placeholder="بحث برقم الطلب، الفني، الفرع، أو الحالة..."
                  prepend-inner-icon="tabler-search"
                  clearable
                />
              </div>

              <div class="d-flex align-center gap-2">
                <span class="text-body-2 text-medium-emphasis me-2">عرض:</span>
                <div style="inline-size: 6rem;">
                  <AppSelect
                    v-model="ordersPerPage"
                    :items="[
                      { title: '10', value: 10 },
                      { title: '25', value: 25 },
                      { title: '50', value: 50 },
                      { title: '100', value: 100 }
                    ]"
                  />
                </div>
              </div>
            </VCardText>

            <VDivider />

            <!-- استخدام VDataTable مع مكون TablePagination في الـ bottom slot -->
            <VDataTable
              v-model:page="ordersPage"
              v-model:items-per-page="ordersPerPage"
              :headers="ordersHeaders"
              :items="filteredOrders"
              item-value="id"
              class="text-no-wrap"
            >
              <!-- رقم الطلب -->
              <template #item.id="{ item }">
                <RouterLink :to="`/orders/${item.id}`" class="font-weight-bold text-primary text-decoration-none text-body-1">
                  #{{ item.id }}
                </RouterLink>
              </template>

              <!-- الفرع -->
              <template #item.branch="{ item }">
                <VChip v-if="item.branch" color="primary" variant="tonal" size="small" label>
                  {{ item.branch.name_ar }}
                </VChip>
                <span v-else class="text-caption text-medium-emphasis">الفرع الرئيسي</span>
              </template>

              <!-- الفني المكلف -->
              <template #item.technician="{ item }">
                <div v-if="item.technician" class="d-flex align-center gap-1 font-weight-medium">
                  <VAvatar color="success" variant="tonal" size="26" rounded>
                    <VIcon icon="tabler-user-check" size="15" />
                  </VAvatar>
                  <span class="text-body-2 font-weight-medium ms-1">{{ item.technician.name }}</span>
                </div>
                <span v-else class="text-caption text-medium-emphasis">بانتظار تعيين فني</span>
              </template>

              <!-- موعد الزيارة -->
              <template #item.visit_date="{ item }">
                <div class="font-weight-bold text-body-2">{{ item.visit_date || '—' }} — {{ item.visit_time || '—' }}</div>
                <span class="text-caption text-medium-emphasis">{{ item.visit_period_label || '—' }}</span>
              </template>

              <!-- المبلغ -->
              <template #item.total="{ item }">
                <span class="font-weight-bold text-success text-body-1">
                  {{ formatCurrency(item.total) }}
                </span>
              </template>

              <!-- الحالة -->
              <template #item.status="{ item }">
                <VChip :color="statusColor(item.status)" variant="tonal" size="small" label class="font-weight-bold">
                  {{ item.status_label || statusLabel(item.status) }}
                </VChip>
              </template>

              <!-- الإجراءات -->
              <template #item.actions="{ item }">
                <VBtn
                  color="primary"
                  variant="tonal"
                  size="small"
                  prepend-icon="tabler-eye"
                  @click="router.push(`/orders/${item.id}`)"
                >
                  تفاصيل
                </VBtn>
              </template>

              <!-- في حالة عدم وجود طلبات -->
              <template #no-data>
                <div class="py-12 text-center text-medium-emphasis">
                  <VIcon icon="tabler-clipboard-off" size="48" class="mb-2 d-block mx-auto text-medium-emphasis" />
                  <div class="font-weight-bold text-h6 mb-1">لا توجد طلبات تتطابق مع البحث الحالي</div>
                  <p class="text-body-2 mb-0">لم يقم المريض بطلب أي زيارة منزلية أو فحص مخبري حتى الآن.</p>
                </div>
              </template>

              <!-- مكون الباجنيشن القياسي للمشروع TablePagination في الـ bottom slot -->
              <template #bottom>
                <TablePagination
                  v-model:page="ordersPage"
                  :items-per-page="ordersPerPage"
                  :total-items="filteredOrders.length"
                />
              </template>
            </VDataTable>
          </VCard>
        </VCol>
      </VRow>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.card-list {
  --v-card-list-gap: 0.75rem;
}
</style>
