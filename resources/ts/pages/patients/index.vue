<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import { $api } from '@/utils/api'
import TablePagination from '@/@core/components/TablePagination.vue'

definePage({
  meta: {
    title: 'إدارة المرضى والمراجعين (Patients)',
  },
})

const router = useRouter()

// نافذة التأكيد الأنيقة (Custom Confirm Dialog) بدلاً من المتصفح
const confirmDialog = ref({
  show: false,
  title: '',
  text: '',
  color: 'error',
  icon: 'tabler-alert-triangle',
  action: null as (() => Promise<void>) | null,
})

// الحالة العامة
const loading = ref(false)
const saving = ref(false)
const patients = ref<any[]>([])
const allDistricts = ref<any[]>([])
const availableAreas = ref<any[]>([])

// شروط التصفية والبحث
const searchQuery = ref('')
const selectedDistrictFilter = ref<number | null>(null)
const selectedStatusFilter = ref<string | null>(null)
const selectedGenderFilter = ref<string | null>(null)

// الترقيم والتصفح (Pagination)
const page = ref(1)
const itemsPerPage = ref(10)

const paginatedPatients = computed(() => {
  const start = (page.value - 1) * itemsPerPage.value
  return patients.value.slice(start, start + itemsPerPage.value)
})

watch([searchQuery, selectedDistrictFilter, selectedStatusFilter, selectedGenderFilter, itemsPerPage], () => {
  page.value = 1
})

const summary = ref({
  total_patients: 0,
  completed_profile: 0,
  pending_profile: 0,
  males: 0,
  females: 0,
})

// إشعار علوي
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

// نسخ رقم الهاتف للحافظة بسرعة
const copyPhone = (phone: string) => {
  navigator.clipboard.writeText(phone)
  showToast(`تم نسخ رقم الهاتف: ${phone}`, 'info')
}

// Dialog تعديل بيانات المريض
const isDialogVisible = ref(false)
const editedItem = ref({
  id: 0,
  name: '',
  phone: '',
  birth_date: '',
  gender: 'male',
  district_id: null as number | null,
  area_id: null as number | null,
})

// جلب قائمة المرضى
const fetchPatients = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams()
    if (searchQuery.value) params.append('search', searchQuery.value)
    if (selectedDistrictFilter.value) params.append('district_id', String(selectedDistrictFilter.value))
    if (selectedStatusFilter.value) params.append('status', selectedStatusFilter.value)
    if (selectedGenderFilter.value) params.append('gender', selectedGenderFilter.value)

    const res = await $api(`/patients?${params.toString()}`)
    if (res?.status) {
      patients.value = res.patients || []
      summary.value = res.summary || {
        total_patients: 0,
        completed_profile: 0,
        pending_profile: 0,
        males: 0,
        females: 0,
      }
    }
  } catch (e: any) {
    showToast('تعذر جلب بيانات المرضى', 'error')
  } finally {
    loading.value = false
  }
}

// إعادة ضبط جميع الفلاتر
const resetFilters = () => {
  searchQuery.value = ''
  selectedDistrictFilter.value = null
  selectedStatusFilter.value = null
  selectedGenderFilter.value = null
  fetchPatients()
}

// جلب الأقضية لتعبئة القوائم المنسدلة
const fetchDistricts = async () => {
  try {
    const res = await $api('/districts')
    if (res?.status) {
      allDistricts.value = res.districts || []
    }
  } catch (e) {
    //
  }
}

// تغيير القضاء عند التعديل لجلب مناطقه
const onDistrictChange = () => {
  editedItem.value.area_id = null
  updateAvailableAreas()
}

const updateAvailableAreas = () => {
  const dist = allDistricts.value.find(d => d.id === editedItem.value.district_id)
  availableAreas.value = dist?.areas || []
}

// فتح نافذة التعديل
const openEditDialog = (item: any) => {
  editedItem.value = {
    id: item.id,
    name: item.name,
    phone: item.phone,
    birth_date: item.birth_date || '',
    gender: item.gender || 'male',
    district_id: item.district_id || null,
    area_id: item.area_id || null,
  }
  updateAvailableAreas()
  isDialogVisible.value = true
}

// حفظ التعديلات
const savePatient = async () => {
  if (!editedItem.value.name || !editedItem.value.phone) {
    showToast('يرجى إدخال اسم ورقم هاتف المريض', 'warning')
    return
  }

  saving.value = true
  try {
    const res = await $api(`/patients/${editedItem.value.id}`, {
      method: 'PUT',
      body: editedItem.value,
    })

    if (res?.status) {
      showToast(res.message || 'تم تعديل بيانات المريض بنجاح', 'success')
      isDialogVisible.value = false
      fetchPatients()
    } else {
      showToast(res?.message || 'فشل التعديل', 'error')
    }
  } catch (e: any) {
    showToast(e?.data?.message || 'حدث خطأ أثناء تعديل بيانات المريض', 'error')
  } finally {
    saving.value = false
  }
}

// حذف المريض
const deletePatient = async (id: number) => {
  if (!confirm('هل أنت متأكد من حذف بيانات هذا المريض بالكامل؟')) return

  try {
    const res = await $api(`/patients/${id}`, { method: 'DELETE' })
    if (res?.status) {
      showToast(res.message || 'تم حذف المريض بنجاح', 'success')
      fetchPatients()
    } else {
      showToast(res?.message || 'فشل الحذف', 'error')
    }
  } catch (e: any) {
    showToast('حدث خطأ أثناء الحذف', 'error')
  }
}

// عرض الملف الشخصي للمريض في صفحة منفصلة حسب الـ ID
const viewPatientProfile = (item: any) => {
  router.push(`/patients/${item.id}`)
}

// تفعيل أو إيقاف حساب المريض (وطرده من التطبيق وحذف توكناته عند الإيقاف)
const togglePatientStatus = (item: any) => {
  confirmDialog.value = {
    show: true,
    title: item.is_active ? 'تأكيد إيقاف وحظر حساب المريض' : 'تأكيد إعادة تفعيل حساب المريض',
    text: item.is_active
      ? `هل أنت متأكد من إيقاف وحظر حساب المريض (${item.name})؟ سيتم طرده فوراً من تطبيق الموبايل وإلغاء جميع جلساته وحذف التوكنات.`
      : `هل تريد إعادة تفعيل حساب المريض (${item.name}) والسماح له بتسجيل الدخول مجدداً؟`,
    color: item.is_active ? 'error' : 'success',
    icon: item.is_active ? 'tabler-ban' : 'tabler-check',
    action: async () => {
      try {
        const res = await $api(`/patients/${item.id}/toggle-status`, { method: 'PATCH' })
        if (res?.status) {
          showToast(res.message || 'تم تحديث حالة الحساب بنجاح', 'success')
          fetchPatients()
        } else {
          showToast(res?.message || 'فشل تحديث حالة الحساب', 'error')
        }
      } catch (e: any) {
        showToast('حدث خطأ أثناء تعديل حالة الحساب', 'error')
      } finally {
        confirmDialog.value.show = false
      }
    },
  }
}

// طرد المريض وتسجيل خروجه قسراً من التطبيق (حذف التوكنات والريفرش توكن)
const revokePatientTokens = (item: any) => {
  confirmDialog.value = {
    show: true,
    title: 'تأكيد إنهاء جلسات المريض (طرد قسري)',
    text: `هل أنت متأكد من إنهاء جميع جلسات المريض (${item.name}) وحذف توكن الدخول والريفرش توكن لإجباره على إعادة تسجيل الدخول؟`,
    color: 'warning',
    icon: 'tabler-logout',
    action: async () => {
      try {
        const res = await $api(`/patients/${item.id}/revoke-tokens`, { method: 'POST' })
        if (res?.status) {
          showToast(res.message || 'تم طرد المريض وإنهاء جميع جلساته بنجاح', 'success')
        } else {
          showToast(res?.message || 'فشل إنهاء الجلسات', 'error')
        }
      } catch (e: any) {
        showToast('حدث خطأ أثناء إنهاء جلسات المريض', 'error')
      } finally {
        confirmDialog.value.show = false
      }
    },
  }
}

onMounted(() => {
  fetchPatients()
  fetchDistricts()
})
</script>

<template>
  <div>
    <!-- الإشعار العلوي -->
    <VSnackbar
      v-model="snackbar.show"
      location="top"
      :color="snackbar.color"
      timeout="3500"
    >
      {{ snackbar.text }}
    </VSnackbar>

    <!-- نافذة تأكيد الإجراءات المخصصة والأنيقة (بدلاً من نافذة المتصفح البدائية) -->
    <VDialog v-model="confirmDialog.show" max-width="500">
      <VCard>
        <VCardItem class="text-center pt-6">
          <VAvatar
            :color="confirmDialog.color"
            variant="tonal"
            size="64"
            class="mb-3 mx-auto"
          >
            <VIcon :icon="confirmDialog.icon" size="36" />
          </VAvatar>
          <VCardTitle class="text-h5 font-weight-bold">
            {{ confirmDialog.title }}
          </VCardTitle>
        </VCardItem>

        <VCardText class="text-center text-body-1 text-medium-emphasis pt-2 pb-6">
          {{ confirmDialog.text }}
        </VCardText>

        <VDivider />

        <VCardActions class="pa-4 justify-end gap-2">
          <VBtn
            color="secondary"
            variant="tonal"
            @click="confirmDialog.show = false"
          >
            إلغاء
          </VBtn>
          <VBtn
            :color="confirmDialog.color"
            variant="elevated"
            @click="confirmDialog.action && confirmDialog.action()"
          >
            تأكيد وتنفيذ
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- عنوان الصفحة -->
    <VRow class="mb-4">
      <VCol cols="12" class="d-flex align-center justify-space-between flex-wrap gap-4">
        <div>
          <h4 class="text-h4 font-weight-bold mb-1 d-flex align-center gap-2">
            <VIcon icon="tabler-users" color="primary" size="32" />
            إدارة المرضى والعملاء (Patients DataTable)
          </h4>
          <p class="text-body-1 text-medium-emphasis mb-0">
            جدول متقدم لإدارة المرضى المسجلين وحالات ملفاتهم وتغطيتهم الجغرافية
          </p>
        </div>

        <VBtn
          color="primary"
          variant="tonal"
          prepend-icon="tabler-refresh"
          @click="fetchPatients"
        >
          تحديث الجدول
        </VBtn>
      </VCol>
    </VRow>

    <!-- بطاقات الإحصائيات -->
    <VRow class="mb-6">
      <VCol cols="12" sm="6" md="3">
        <VCard>
          <VCardText class="d-flex align-center justify-space-between">
            <div>
              <div class="text-body-2 text-medium-emphasis mb-1">إجمالي المسجلين</div>
              <div class="text-h4 font-weight-bold text-primary">{{ summary.total_patients }}</div>
            </div>
            <VAvatar color="primary" variant="tonal" size="48">
              <VIcon icon="tabler-users" size="28" />
            </VAvatar>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" sm="6" md="3">
        <VCard>
          <VCardText class="d-flex align-center justify-space-between">
            <div>
              <div class="text-body-2 text-medium-emphasis mb-1">حسابات مكتملة</div>
              <div class="text-h4 font-weight-bold text-success">{{ summary.completed_profile }}</div>
            </div>
            <VAvatar color="success" variant="tonal" size="48">
              <VIcon icon="tabler-user-check" size="28" />
            </VAvatar>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" sm="6" md="3">
        <VCard>
          <VCardText class="d-flex align-center justify-space-between">
            <div>
              <div class="text-body-2 text-medium-emphasis mb-1">قيد التسجيل (الهاتف فقط)</div>
              <div class="text-h4 font-weight-bold text-warning">{{ summary.pending_profile }}</div>
            </div>
            <VAvatar color="warning" variant="tonal" size="48">
              <VIcon icon="tabler-user-question" size="28" />
            </VAvatar>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" sm="6" md="3">
        <VCard>
          <VCardText class="d-flex align-center justify-space-between">
            <div>
              <div class="text-body-2 text-medium-emphasis mb-1">ذكور / إناث</div>
              <div class="text-h4 font-weight-bold text-info">
                {{ summary.males }} / <span class="text-warning">{{ summary.females }}</span>
              </div>
            </div>
            <VAvatar color="info" variant="tonal" size="48">
              <VIcon icon="tabler-gender-male" size="28" />
            </VAvatar>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- شريط الفلترة والبحث المتقدم -->
    <VCard class="mb-6">
      <VCardText>
        <VRow>
          <VCol cols="12" md="3">
            <AppTextField
              v-model="searchQuery"
              placeholder="البحث بالاسم أو الهاتف أو ID..."
              prepend-inner-icon="tabler-search"
              clearable
              @keyup.enter="fetchPatients"
              @click:clear="fetchPatients"
            />
          </VCol>

          <VCol cols="12" md="3">
            <AppSelect
              v-model="selectedDistrictFilter"
              :items="allDistricts"
              item-title="name"
              item-value="id"
              placeholder="تصفية حسب القضاء"
              clearable
              @update:model-value="fetchPatients"
            />
          </VCol>

          <VCol cols="12" md="2">
            <AppSelect
              v-model="selectedStatusFilter"
              :items="[
                { title: 'الكل', value: null },
                { title: 'حساب مكتمل', value: 'completed' },
                { title: 'قيد التسجيل', value: 'pending' },
              ]"
              item-title="title"
              item-value="value"
              placeholder="حالة التسجيل"
              clearable
              @update:model-value="fetchPatients"
            />
          </VCol>

          <VCol cols="12" md="2">
            <AppSelect
              v-model="selectedGenderFilter"
              :items="[
                { title: 'الكل', value: null },
                { title: 'ذكور', value: 'male' },
                { title: 'إناث', value: 'female' },
              ]"
              item-title="title"
              item-value="value"
              placeholder="الجنس"
              clearable
              @update:model-value="fetchPatients"
            />
          </VCol>

          <VCol cols="12" md="2" class="d-flex align-center gap-2">
            <VBtn
              color="primary"
              variant="elevated"
              prepend-icon="tabler-filter"
              class="flex-grow-1"
              @click="fetchPatients"
            >
              تصفية
            </VBtn>
            <VBtn
              icon
              variant="tonal"
              color="secondary"
              title="إعادة ضبط الفلاتر"
              @click="resetFilters"
            >
              <VIcon icon="tabler-refresh-dot" />
            </VBtn>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <!-- جدول المرضى المنسق والمرتب (Data Table) -->
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
        </div>
        <span class="text-body-2 text-medium-emphasis">
          {{ patients.length > 0 ? `إجمالي النتائج: ${patients.length} مريض` : '' }}
        </span>
      </VCardText>

      <VTable class="text-no-wrap table-header-bg">
        <thead>
          <tr>
            <th style="width: 70px;"># ID</th>
            <th>اسم المريض</th>
            <th>رقم الهاتف</th>
            <th>المواليد (العمر)</th>
            <th>الجنس</th>
            <th>القضاء والمنطقة المخبرية</th>
            <th>الفرع المسؤول</th>
            <th>حالة الحساب</th>
            <th>تاريخ الانضمام</th>
            <th class="text-center">الإجراءات</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="patients.length === 0">
            <td colspan="10" class="text-center py-8 text-medium-emphasis">
              <VIcon icon="tabler-user-off" size="48" class="mb-2 d-block mx-auto" />
              لا توجد نتائج تطابق معايير البحث أو لا يوجد مرضى مسجلون حتى الآن
            </td>
          </tr>
          <tr v-for="item in paginatedPatients" :key="item.id" class="align-middle">
            <td>
              <VChip size="small" variant="tonal" color="primary" class="font-weight-bold">
                #{{ item.id }}
              </VChip>
            </td>
            <td>
              <div class="font-weight-bold text-high-emphasis">
                {{ item.name }}
              </div>
            </td>
            <td>
              <div class="d-flex align-center gap-1">
                <span class="font-weight-medium" dir="ltr">{{ item.phone }}</span>
                <VIcon
                  icon="tabler-copy"
                  size="16"
                  class="cursor-pointer text-primary"
                  title="نسخ رقم الهاتف"
                  @click="copyPhone(item.phone)"
                />
              </div>
            </td>
            <td>
              <div class="font-weight-bold">{{ item.birth_date || '—' }}</div>
              <div class="text-caption text-medium-emphasis">{{ item.age }}</div>
            </td>
            <td>
              <VChip
                size="small"
                variant="tonal"
                :color="item.gender === 'female' ? 'warning' : 'info'"
              >
                <VIcon
                  :icon="item.gender === 'female' ? 'tabler-gender-female' : 'tabler-gender-male'"
                  start
                  size="14"
                />
                {{ item.gender_text }}
              </VChip>
            </td>
            <td>
              <div class="font-weight-medium text-high-emphasis">
                {{ item.district?.name || '—' }}
              </div>
              <div class="text-caption text-medium-emphasis">
                {{ item.area?.name || 'غير محدد' }}
              </div>
            </td>
            <td>
              <VChip
                v-if="item.assigned_branch"
                color="info"
                variant="tonal"
                size="small"
                prepend-icon="tabler-building-store"
              >
                {{ item.assigned_branch.name_ar }}
              </VChip>
              <span v-else class="text-medium-emphasis text-caption">—</span>
            </td>
            <td>
              <VChip
                v-if="!item.is_active"
                color="error"
                variant="elevated"
                size="small"
                prepend-icon="tabler-ban"
              >
                موقوف (مطرود من التطبيق)
              </VChip>
              <VChip
                v-else
                :color="item.is_profile_completed ? 'success' : 'warning'"
                variant="tonal"
                size="small"
                :prepend-icon="item.is_profile_completed ? 'tabler-circle-check' : 'tabler-clock'"
              >
                {{ item.is_profile_completed ? 'حساب مكتمل' : 'قيد التسجيل' }}
              </VChip>
            </td>
            <td>
              <div class="text-body-2">{{ item.created_at }}</div>
            </td>
            <td class="text-center">
              <VBtn
                size="small"
                color="info"
                variant="tonal"
                class="font-weight-bold px-3"
                @click="viewPatientProfile(item)"
              >
                <VIcon icon="tabler-user-search" start size="18" />
                <span>عرض ملف المريض</span>
              </VBtn>
            </td>
          </tr>
        </tbody>
      </VTable>

      <TablePagination
        v-if="patients.length > 0"
        v-model:page="page"
        :items-per-page="itemsPerPage"
        :total-items="patients.length"
      />
    </VCard>

    <!-- نافذة تعديل بيانات المريض -->
    <VDialog v-model="isDialogVisible" max-width="600">
      <VCard title="تعديل بيانات المريض والملف الشخصي">
        <VCardText class="pt-4">
          <VRow>
            <VCol cols="12" md="6">
              <AppTextField
                v-model="editedItem.name"
                label="الاسم الكامل"
                placeholder="اسم المريض"
              />
            </VCol>

            <VCol cols="12" md="6">
              <AppTextField
                v-model="editedItem.phone"
                label="رقم الهاتف"
                placeholder="07xxxxxxxxx"
              />
            </VCol>

            <VCol cols="12" md="6">
              <AppTextField
                v-model="editedItem.birth_date"
                label="المواليد (تاريخ الميلاد)"
                type="date"
              />
            </VCol>

            <VCol cols="12" md="6">
              <AppSelect
                v-model="editedItem.gender"
                label="الجنس"
                :items="[
                  { title: 'ذكر', value: 'male' },
                  { title: 'أنثى', value: 'female' },
                ]"
              />
            </VCol>

            <VCol cols="12" md="6">
              <AppSelect
                v-model="editedItem.district_id"
                label="القضاء المخبري"
                :items="allDistricts"
                item-title="name"
                item-value="id"
                clearable
                @update:model-value="onDistrictChange"
              />
            </VCol>

            <VCol cols="12" md="6">
              <AppSelect
                v-model="editedItem.area_id"
                label="المنطقة / الحي المخبري"
                :items="availableAreas"
                item-title="name"
                item-value="id"
                clearable
              />
            </VCol>
          </VRow>
        </VCardText>

        <VDivider />

        <VCardActions class="pa-4 justify-end">
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isDialogVisible = false"
          >
            إلغاء
          </VBtn>
          <VBtn
            color="primary"
            variant="elevated"
            :loading="saving"
            @click="savePatient"
          >
            حفظ التغييرات
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
