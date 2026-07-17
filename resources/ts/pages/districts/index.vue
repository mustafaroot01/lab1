<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'

interface Area {
  id: number
  district_id: number
  name: string
  sort_order: number
  is_active: boolean
}

interface BranchOption {
  id: number
  name_ar: string
  phone: string
  is_active: boolean
}

interface District {
  id: number
  name: string
  governorate: string
  branch_id?: number | null
  service_fee?: number | null
  free_threshold?: number | null
  branch?: {
    id: number
    name_ar: string
    phone: string
    service_fee?: number
    free_threshold?: number
  } | null
  sort_order: number
  is_active: boolean
  areas: Area[]
}

const districts = ref<District[]>([])
const branchesList = ref<BranchOption[]>([])
const loading = ref(false)
const summary = ref({
  activeDistricts: 0,
  inactiveDistricts: 0,
  activeAreas: 0,
  inactiveAreas: 0,
})
const totalDistricts = ref(0)
const totalAreas = ref(0)

const selectedDistrictTab = ref<number | 'all'>('all')

const errorMessage = ref('')
const successMessage = ref('')

// إشعار عائم فوري أعلى الشاشة (Snackbar)
const snackbar = ref(false)
const snackbarText = ref('')
const snackbarColor = ref<'success' | 'error' | 'primary'>('success')

const showToast = (text: string, color: 'success' | 'error' | 'primary' = 'success') => {
  snackbarText.value = text
  snackbarColor.value = color
  snackbar.value = true
}

// Dialog القضاء
const districtDialog = ref(false)
const isEditingDistrict = ref(false)
const editingDistrictId = ref<number | null>(null)
const districtSaveLoading = ref(false)
const districtForm = ref({
  name: '',
  governorate: 'ديالى',
  branch_id: null as number | null,
  service_fee: null as number | null,
  free_threshold: null as number | null,
  sort_order: 1,
  is_active: true,
})
const districtErrors = ref<Record<string, string[]>>({})

// Dialog المنطقة
const areaDialog = ref(false)
const isEditingArea = ref(false)
const editingAreaId = ref<number | null>(null)
const selectedDistrictId = ref<number | null>(null)
const areaSaveLoading = ref(false)
const areaForm = ref({
  name: '',
  sort_order: 1,
  is_active: true,
})
const areaErrors = ref<Record<string, string[]>>({})

// Dialog الحذف
const deleteDialog = ref(false)
const deleteType = ref<'district' | 'area'>('district')
const targetItem = ref<any>(null)
const deleteLoading = ref(false)

// جلب الأقضية والمناطق
const fetchDistricts = async () => {
  loading.value = true
  try {
    const res = await $api('/districts')
    if (res.status) {
      districts.value = res.districts
      branchesList.value = res.branches || []
      totalDistricts.value = res.totalDistricts
      totalAreas.value = res.totalAreas
      summary.value = res.summary
    }
  } catch {
    errorMessage.value = 'تعذر تحميل قائمة الأقضية والمناطق'
  } finally {
    loading.value = false
  }
}

// تصفية الأقضية المعروضة حسب التبويب
const displayedDistricts = computed(() => {
  if (selectedDistrictTab.value === 'all') {
    return districts.value
  }
  return districts.value.filter(d => d.id === selectedDistrictTab.value)
})

// فتح إضافة قضاء
const openAddDistrict = () => {
  isEditingDistrict.value = false
  editingDistrictId.value = null
  districtForm.value = {
    name: '',
    governorate: 'ديالى',
    branch_id: null,
    service_fee: null,
    free_threshold: null,
    sort_order: districts.value.length + 1,
    is_active: true,
  }
  districtErrors.value = {}
  districtDialog.value = true
}

// فتح تعديل قضاء
const openEditDistrict = (d: District) => {
  isEditingDistrict.value = true
  editingDistrictId.value = d.id
  districtForm.value = {
    name: d.name,
    governorate: d.governorate || 'ديالى',
    branch_id: d.branch_id || null,
    service_fee: d.service_fee !== undefined && d.service_fee !== null ? Number(d.service_fee) : null,
    free_threshold: d.free_threshold !== undefined && d.free_threshold !== null ? Number(d.free_threshold) : null,
    sort_order: d.sort_order,
    is_active: d.is_active,
  }
  districtErrors.value = {}
  districtDialog.value = true
}

// حفظ قضاء
const saveDistrict = async () => {
  districtSaveLoading.value = true
  districtErrors.value = {}
  errorMessage.value = ''

  try {
    const url = isEditingDistrict.value ? `/districts/${editingDistrictId.value}` : '/districts'
    const method = isEditingDistrict.value ? 'PUT' : 'POST'

    const res = await $api(url, {
      method,
      body: districtForm.value,
    })

    if (res.status) {
      districtDialog.value = false
      showToast(isEditingDistrict.value ? 'تم تحديث القضاء بنجاح' : 'تم إضافة القضاء بنجاح', 'success')
      fetchDistricts()
    } else {
      showToast(res.message || 'حدث خطأ أثناء حفظ القضاء', 'error')
    }
  } catch (e: any) {
    if (e?.errors) districtErrors.value = e.errors
    else showToast(e?.message || 'تعذر حفظ القضاء', 'error')
  } finally {
    districtSaveLoading.value = false
  }
}

// تفعيل/إخفاء قضاء
const toggleDistrictActive = async (d: District) => {
  try {
    const res = await $api(`/districts/${d.id}/toggle-active`, { method: 'PATCH' })
    showToast(res.message || 'تم تغيير حالة ظهور القضاء بنجاح', 'success')
    fetchDistricts()
  } catch {
    showToast('تعذر تغيير حالة القضاء', 'error')
  }
}

// فتح إضافة منطقة لقضاء
const openAddArea = (d: District) => {
  isEditingArea.value = false
  editingAreaId.value = null
  selectedDistrictId.value = d.id
  areaForm.value = {
    name: '',
    sort_order: (d.areas?.length || 0) + 1,
    is_active: true,
  }
  areaErrors.value = {}
  areaDialog.value = true
}

// فتح تعديل منطقة
const openEditArea = (area: Area) => {
  isEditingArea.value = true
  editingAreaId.value = area.id
  selectedDistrictId.value = area.district_id
  areaForm.value = {
    name: area.name,
    sort_order: area.sort_order,
    is_active: area.is_active,
  }
  areaErrors.value = {}
  areaDialog.value = true
}

// حفظ منطقة
const saveArea = async () => {
  if (!selectedDistrictId.value) return
  areaSaveLoading.value = true
  areaErrors.value = {}
  errorMessage.value = ''

  try {
    const url = isEditingArea.value ? `/areas/${editingAreaId.value}` : '/areas'
    const method = isEditingArea.value ? 'PUT' : 'POST'

    const res = await $api(url, {
      method,
      body: {
        district_id: selectedDistrictId.value,
        ...areaForm.value,
      },
    })

    if (res.status) {
      areaDialog.value = false
      showToast(isEditingArea.value ? 'تم تحديث المنطقة بنجاح' : 'تم إضافة المنطقة بنجاح', 'success')
      fetchDistricts()
    } else {
      showToast(res.message || 'حدث خطأ أثناء حفظ المنطقة', 'error')
    }
  } catch (e: any) {
    if (e?.errors) areaErrors.value = e.errors
    else showToast(e?.message || 'تعذر حفظ المنطقة', 'error')
  } finally {
    areaSaveLoading.value = false
  }
}

// تفعيل/إخفاء منطقة
const toggleAreaActive = async (area: Area) => {
  try {
    const res = await $api(`/areas/${area.id}/toggle-active`, { method: 'PATCH' })
    showToast(res.message || 'تم تغيير حالة ظهور المنطقة بنجاح', 'success')
    fetchDistricts()
  } catch {
    showToast('تعذر تغيير حالة المنطقة', 'error')
  }
}

// تأكيد الحذف
const confirmDelete = (type: 'district' | 'area', item: any) => {
  deleteType.value = type
  targetItem.value = item
  deleteDialog.value = true
}

// الحذف
const executeDelete = async () => {
  if (!targetItem.value) return
  deleteLoading.value = true
  try {
    const endpoint = deleteType.value === 'district'
      ? `/districts/${targetItem.value.id}`
      : `/areas/${targetItem.value.id}`

    await $api(endpoint, { method: 'DELETE' })
    deleteDialog.value = false
    showToast(deleteType.value === 'district' ? 'تم حذف القضاء بنجاح' : 'تم حذف المنطقة بنجاح', 'success')
    fetchDistricts()
  } catch {
    showToast('تعذر تنفيذ عملية الحذف', 'error')
  } finally {
    deleteLoading.value = false
  }
}

onMounted(fetchDistricts)
</script>

<template>
  <div>
    <!-- الإشعار العائم الفوري أعلى الشاشة -->
    <VSnackbar
      v-model="snackbar"
      location="top"
      :color="snackbarColor"
      timeout="3500"
      elevation="4"
    >
      <div class="d-flex align-center gap-2 font-weight-bold text-body-1">
        <VIcon :icon="snackbarColor === 'success' ? 'tabler-circle-check' : 'tabler-alert-circle'" size="22" />
        <span>{{ snackbarText }}</span>
      </div>
    </VSnackbar>

    <!-- Page Header الأنيق -->
    <div class="d-flex flex-wrap justify-space-between align-center gap-4 mb-6">
      <div>
        <h3 class="text-h3 font-weight-bold mb-1">إدارة الأقضية والمناطق (نطاق التغطية)</h3>
        <div class="text-body-1 text-medium-emphasis">
          إدارة الأقضية والأحياء الظاهرة للزبائن في التطبيق عند إدخال عنوان الحجز وسحب العينة
        </div>
      </div>

      <VBtn color="primary" prepend-icon="tabler-plus" size="large" @click="openAddDistrict">
        إضافة قضاء جديد
      </VBtn>
    </div>

    <!-- Summary Cards -->
    <VRow class="mb-6">
      <VCol cols="12" sm="3">
        <VCard>
          <VCardText class="d-flex align-center gap-4">
            <VAvatar color="primary" variant="tonal" size="52" rounded>
              <VIcon icon="tabler-map-pin" size="28" />
            </VAvatar>
            <div>
              <div class="text-h4 font-weight-bold">{{ totalDistricts }}</div>
              <div class="text-body-2 text-medium-emphasis">إجمالي الأقضية</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" sm="3">
        <VCard>
          <VCardText class="d-flex align-center gap-4">
            <VAvatar color="info" variant="tonal" size="52" rounded>
              <VIcon icon="tabler-building-community" size="28" />
            </VAvatar>
            <div>
              <div class="text-h4 font-weight-bold">{{ totalAreas }}</div>
              <div class="text-body-2 text-medium-emphasis">إجمالي المناطق والأحياء</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" sm="3">
        <VCard>
          <VCardText class="d-flex align-center gap-4">
            <VAvatar color="success" variant="tonal" size="52" rounded>
              <VIcon icon="tabler-circle-check" size="28" />
            </VAvatar>
            <div>
              <div class="text-h4 font-weight-bold text-success">{{ summary.activeAreas }}</div>
              <div class="text-body-2 text-medium-emphasis">مناطق مفعلة بالتطبيق</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" sm="3">
        <VCard>
          <VCardText class="d-flex align-center gap-4">
            <VAvatar color="error" variant="tonal" size="52" rounded>
              <VIcon icon="tabler-eye-off" size="28" />
            </VAvatar>
            <div>
              <div class="text-h4 font-weight-bold text-error">{{ summary.inactiveAreas }}</div>
              <div class="text-body-2 text-medium-emphasis">مناطق مخفية</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Alerts -->
    <VAlert v-if="errorMessage" type="error" variant="tonal" closable class="mb-4" @click:close="errorMessage = ''">
      {{ errorMessage }}
    </VAlert>
    <VAlert v-if="successMessage" type="success" variant="tonal" closable class="mb-4" @click:close="successMessage = ''">
      {{ successMessage }}
    </VAlert>

    <!-- District Filter Tabs (أزرار Chips أفقية لتصفح الأقضية بسرعة) -->
    <div class="d-flex flex-wrap justify-start gap-3 mb-6">
      <VBtn
        :variant="selectedDistrictTab === 'all' ? 'elevated' : 'outlined'"
        :color="selectedDistrictTab === 'all' ? 'primary' : 'secondary'"
        rounded="lg"
        @click="selectedDistrictTab = 'all'"
      >
        جميع الأقضية ({{ totalDistricts }})
      </VBtn>

      <VBtn
        v-for="d in districts"
        :key="d.id"
        :variant="selectedDistrictTab === d.id ? 'elevated' : 'outlined'"
        :color="selectedDistrictTab === d.id ? 'primary' : 'secondary'"
        rounded="lg"
        @click="selectedDistrictTab = d.id"
      >
        قضاء {{ d.name }} ({{ d.areas?.length || 0 }})
      </VBtn>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <VProgressCircular indeterminate color="primary" size="44" class="mb-3" />
      <div class="text-body-1 text-medium-emphasis">جاري تحميل الأقضية والمناطق...</div>
    </div>

    <!-- Empty State -->
    <div v-else-if="displayedDistricts.length === 0" class="text-center py-12">
      <VIcon icon="tabler-map-off" size="56" color="secondary" class="mb-3 opacity-40" />
      <div class="text-h6 font-weight-medium mb-1">لا توجد أقضية معروضة</div>
      <VBtn color="primary" size="small" prepend-icon="tabler-plus" class="mt-3" @click="openAddDistrict">
        إضافة قضاء جديد
      </VBtn>
    </div>

    <!-- Districts Cards Layout -->
    <div v-else class="d-flex flex-column gap-6">
      <VCard v-for="district in displayedDistricts" :key="district.id" elevation="2">
        <!-- District Top Header Bar -->
        <div class="pa-5 d-flex flex-wrap justify-space-between align-center gap-4 border-b" style="background: rgba(var(--v-theme-primary), 0.04);">
          <div class="d-flex align-center gap-4">
            <VAvatar :color="district.is_active ? 'primary' : 'secondary'" variant="tonal" size="48" rounded>
              <VIcon icon="tabler-map-pin" size="26" />
            </VAvatar>

            <div>
              <div class="d-flex align-center gap-2 mb-1">
                <span class="text-h5 font-weight-bold">قضاء {{ district.name }}</span>
                <VChip
                  :color="district.is_active ? 'success' : 'error'"
                  size="small"
                  variant="tonal"
                  :prepend-icon="district.is_active ? 'tabler-check' : 'tabler-eye-off'"
                >
                  {{ district.is_active ? 'ظاهر في التطبيق' : 'مخفي من التطبيق' }}
                </VChip>

                <VChip
                  v-if="district.branch"
                  color="info"
                  size="small"
                  variant="tonal"
                  prepend-icon="tabler-building-store"
                >
                  الفرع المخبري المسؤول: {{ district.branch.name_ar }}
                </VChip>

                <VChip
                  color="warning"
                  size="small"
                  variant="tonal"
                  prepend-icon="tabler-coin"
                >
                  أجور الزيارة: {{ district.service_fee !== null && district.service_fee !== undefined ? `${district.service_fee.toLocaleString()} د.ع` : (district.branch?.service_fee ? `${district.branch.service_fee.toLocaleString()} د.ع (حسب الفرع)` : '0 د.ع') }}
                </VChip>

                <VChip
                  v-if="district.free_threshold || district.branch?.free_threshold"
                  color="success"
                  size="small"
                  variant="tonal"
                  prepend-icon="tabler-gift"
                >
                  الحد المجاني: {{ district.free_threshold !== null && district.free_threshold !== undefined ? `${district.free_threshold.toLocaleString()} د.ع` : `${district.branch?.free_threshold?.toLocaleString()} د.ع (حسب الفرع)` }}
                </VChip>
              </div>
              <div class="text-body-2 text-medium-emphasis">
                محافظة {{ district.governorate }} — يحتوي على {{ district.areas.length }} منطقة مسجلة
              </div>
            </div>
          </div>

          <!-- أزرار التحكم بالقضاء -->
          <div class="d-flex align-center gap-2">
            <VBtn
              color="primary"
              variant="elevated"
              size="small"
              prepend-icon="tabler-plus"
              @click="openAddArea(district)"
            >
              إضافة منطقة للحي
            </VBtn>

            <VTooltip :text="district.is_active ? 'إخفاء القضاء في التطبيق' : 'إظهار القضاء في التطبيق'" location="top">
              <template #activator="{ props }">
                <VBtn
                  v-bind="props"
                  :icon="district.is_active ? 'tabler-eye-off' : 'tabler-eye'"
                  :color="district.is_active ? 'warning' : 'success'"
                  variant="tonal"
                  size="small"
                  @click="toggleDistrictActive(district)"
                />
              </template>
            </VTooltip>

            <VTooltip text="تعديل اسم القضاء" location="top">
              <template #activator="{ props }">
                <VBtn
                  v-bind="props"
                  icon="tabler-edit"
                  color="primary"
                  variant="tonal"
                  size="small"
                  @click="openEditDistrict(district)"
                />
              </template>
            </VTooltip>

            <VTooltip text="حذف القضاء بالكامل" location="top">
              <template #activator="{ props }">
                <VBtn
                  v-bind="props"
                  icon="tabler-trash"
                  color="error"
                  variant="tonal"
                  size="small"
                  @click="confirmDelete('district', district)"
                />
              </template>
            </VTooltip>
          </div>
        </div>

        <!-- Areas Table -->
        <VTable class="text-no-wrap">
          <thead>
            <tr>
              <th style="width: 70px;">#</th>
              <th>اسم المنطقة / الحي</th>
              <th class="text-center" style="width: 160px;">تفعيل الظهور</th>
              <th class="text-center" style="width: 140px;">الحالة</th>
              <th class="text-center" style="width: 140px;">إجراءات</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="district.areas.length === 0">
              <td colspan="5" class="text-center py-8 text-medium-emphasis">
                لا توجد مناطق مضافة داخل قضاء {{ district.name }}. اضغط على زر (إضافة منطقة للحي) أعلاه.
              </td>
            </tr>

            <tr v-for="(area, idx) in district.areas" :key="area.id">
              <td class="text-body-2 font-weight-bold">{{ idx + 1 }}</td>
              <td>
                <div class="d-flex align-center gap-2 py-2">
                  <VIcon icon="tabler-point" color="primary" size="20" />
                  <span class="font-weight-semibold text-body-1">{{ area.name }}</span>
                </div>
              </td>
              <td class="text-center">
                <VSwitch
                  :model-value="area.is_active"
                  color="success"
                  hide-details
                  class="d-inline-flex"
                  @change="toggleAreaActive(area)"
                />
              </td>
              <td class="text-center">
                <VChip
                  :color="area.is_active ? 'success' : 'error'"
                  size="small"
                  variant="tonal"
                >
                  {{ area.is_active ? 'نشط ومتاح للطلب' : 'مخفي' }}
                </VChip>
              </td>
              <td class="text-center">
                <div class="d-flex align-center justify-center gap-1">
                  <VTooltip text="تعديل اسم المنطقة" location="top">
                    <template #activator="{ props }">
                      <VBtn
                        v-bind="props"
                        icon="tabler-edit"
                        color="primary"
                        variant="text"
                        size="small"
                        @click="openEditArea(area)"
                      />
                    </template>
                  </VTooltip>

                  <VTooltip text="حذف المنطقة" location="top">
                    <template #activator="{ props }">
                      <VBtn
                        v-bind="props"
                        icon="tabler-trash"
                        color="error"
                        variant="text"
                        size="small"
                        @click="confirmDelete('area', area)"
                      />
                    </template>
                  </VTooltip>
                </div>
              </td>
            </tr>
          </tbody>
        </VTable>
      </VCard>
    </div>

    <!-- Dialog إضافة / تعديل قضاء -->
    <VDialog v-model="districtDialog" max-width="500" persistent>
      <VCard :title="isEditingDistrict ? 'تعديل القضاء' : 'إضافة قضاء جديد'">
        <VCardText class="pt-2">
          <VRow>
            <VCol cols="12">
              <AppTextField
                v-model="districtForm.name"
                label="اسم القضاء *"
                placeholder="مثال: بعقوبة، كنعان، الخالص"
                :error-messages="districtErrors.name"
              />
            </VCol>

            <VCol cols="12">
              <AppTextField
                v-model="districtForm.governorate"
                label="المحافظة"
                placeholder="ديالى"
              />
            </VCol>

            <VCol cols="12">
              <AppSelect
                v-model="districtForm.branch_id"
                :items="branchesList"
                item-title="name_ar"
                item-value="id"
                label="الفرع المخبري المسؤول عن هذا القضاء"
                placeholder="اختر الفرع المخبري لتوجيه طلبات وسحب عينات هذا القضاء إليه"
                clearable
              />
            </VCol>

            <VCol cols="12" md="6">
              <AppTextField
                v-model.number="districtForm.service_fee"
                label="أجور الزيارة المنزلية لهذا القضاء (د.ع)"
                placeholder="اتركه فارغاً لاعتماد أجور الفرع"
                type="number"
                clearable
                :error-messages="districtErrors.service_fee"
              />
            </VCol>

            <VCol cols="12" md="6">
              <AppTextField
                v-model.number="districtForm.free_threshold"
                label="الحد الأدنى للزيارة المجانية في القضاء (د.ع)"
                placeholder="اتركه فارغاً لاعتماد حد الفرع"
                type="number"
                clearable
                :error-messages="districtErrors.free_threshold"
              />
            </VCol>

            <VCol cols="12">
              <div class="d-flex align-center justify-space-between pa-3 rounded border">
                <div>
                  <div class="font-weight-medium text-body-1">إظهار القضاء في التطبيق</div>
                  <div class="text-caption text-medium-emphasis">عند تفعيله، يظهر القضاء للزبون أثناء التسجيل</div>
                </div>
                <VSwitch v-model="districtForm.is_active" color="success" hide-details />
              </div>
            </VCol>
          </VRow>
        </VCardText>

        <VCardActions class="pa-4 justify-end gap-2">
          <VBtn variant="tonal" color="secondary" @click="districtDialog = false">إلغاء</VBtn>
          <VBtn color="primary" :loading="districtSaveLoading" @click="saveDistrict">
            حفظ
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Dialog إضافة / تعديل منطقة -->
    <VDialog v-model="areaDialog" max-width="500" persistent>
      <VCard :title="isEditingArea ? 'تعديل المنطقة / الحي' : 'إضافة منطقة جديدة'">
        <VCardText class="pt-2">
          <VRow>
            <VCol cols="12">
              <AppTextField
                v-model="areaForm.name"
                label="اسم المنطقة أو الحي *"
                placeholder="مثال: مركز بعقوبة، حي المعلمين، التحرير"
                :error-messages="areaErrors.name"
              />
            </VCol>

            <VCol cols="12">
              <div class="d-flex align-center justify-space-between pa-3 rounded border">
                <div>
                  <div class="font-weight-medium text-body-1">إظهار المنطقة في التطبيق</div>
                  <div class="text-caption text-medium-emphasis">تتيح للعملاء اختيار هذه المنطقة لسحب العينة</div>
                </div>
                <VSwitch v-model="areaForm.is_active" color="success" hide-details />
              </div>
            </VCol>
          </VRow>
        </VCardText>

        <VCardActions class="pa-4 justify-end gap-2">
          <VBtn variant="tonal" color="secondary" @click="areaDialog = false">إلغاء</VBtn>
          <VBtn color="primary" :loading="areaSaveLoading" @click="saveArea">
            حفظ
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Delete Dialog -->
    <VDialog v-model="deleteDialog" max-width="440">
      <VCard>
        <VCardText class="text-center pt-8 pb-4">
          <VAvatar color="error" variant="tonal" size="70" class="mb-4">
            <VIcon icon="tabler-trash-x" size="34" />
          </VAvatar>
          <h5 class="text-h5 font-weight-bold mb-2">تأكيد الحذف</h5>
          <p class="text-body-1 text-medium-emphasis mb-0">
            {{ deleteType === 'district' ? 'هل أنت متأكد من حذف القضاء وجميع المناطق التابعة له نهائياً؟' : 'هل أنت متأكد من حذف هذه المنطقة؟' }}
          </p>
        </VCardText>
        <VCardActions class="justify-center gap-4 pb-6">
          <VBtn variant="tonal" color="secondary" @click="deleteDialog = false">إلغاء</VBtn>
          <VBtn color="error" :loading="deleteLoading" @click="executeDelete">حذف</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
