<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'

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
  branch?: {
    id: number
    name_ar: string
    phone: string
  } | null
  sort_order: number
  is_active: boolean
}

const districts = ref<District[]>([])
const branchesList = ref<BranchOption[]>([])
const loading = ref(false)
const summary = ref({
  activeDistricts: 0,
  inactiveDistricts: 0,
})
const totalDistricts = ref(0)

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
  sort_order: 1,
  is_active: true,
})
const districtErrors = ref<Record<string, string[]>>({})

// Dialog المنطقة


// Dialog الحذف
const deleteDialog = ref(false)
const deleteType = ref<'district'>('district')
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



// تأكيد الحذف
const confirmDelete = (type: 'district', item: any) => {
  deleteType.value = type
  targetItem.value = item
  deleteDialog.value = true
}

// الحذف
const executeDelete = async () => {
  if (!targetItem.value) return
  deleteLoading.value = true
  try {
    const endpoint = `/districts/${targetItem.value.id}`

    await $api(endpoint, { method: 'DELETE' })
    deleteDialog.value = false
    showToast('تم حذف القضاء بنجاح', 'success')
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
      <VCol cols="12" sm="6">
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
        قضاء {{ d.name }}
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

    <!-- Districts Table Layout -->
    <VCard v-else>
      <VTable class="text-no-wrap">
        <thead>
          <tr>
            <th class="text-uppercase text-caption font-weight-bold">#</th>
            <th class="text-uppercase text-caption font-weight-bold">اسم القضاء</th>
            <th class="text-uppercase text-caption font-weight-bold">المحافظة</th>
            <th class="text-uppercase text-caption font-weight-bold">الفرع المسؤول</th>
            <th class="text-uppercase text-caption font-weight-bold text-center">الظهور في التطبيق</th>
            <th class="text-uppercase text-caption font-weight-bold text-center">الإجراءات</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(district, idx) in displayedDistricts" :key="district.id">
            <td class="text-body-2 font-weight-bold">{{ idx + 1 }}</td>
            <td>
              <div class="d-flex align-center gap-3 py-2">
                <VAvatar :color="district.is_active ? 'primary' : 'secondary'" variant="tonal" size="38" rounded>
                  <VIcon icon="tabler-map-pin" size="22" />
                </VAvatar>
                <span class="text-body-1 font-weight-bold">{{ district.name }}</span>
              </div>
            </td>
            <td class="text-body-2">{{ district.governorate }}</td>
            <td>
              <VChip
                v-if="district.branch"
                color="info"
                size="small"
                variant="tonal"
                prepend-icon="tabler-building-store"
              >
                {{ district.branch.name_ar }}
              </VChip>
              <span v-else class="text-medium-emphasis text-caption">غير محدد</span>
            </td>
            <td class="text-center">
              <VChip
                :color="district.is_active ? 'success' : 'error'"
                size="small"
                variant="tonal"
                :prepend-icon="district.is_active ? 'tabler-check' : 'tabler-eye-off'"
              >
                {{ district.is_active ? 'ظاهر' : 'مخفي' }}
              </VChip>
            </td>
            <td class="text-center">
              <div class="d-flex align-center justify-center gap-2">
                <VTooltip :text="district.is_active ? 'إخفاء' : 'إظهار'" location="top">
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

                <VTooltip text="تعديل القضاء" location="top">
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

                <VTooltip text="حذف القضاء" location="top">
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
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

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



    <!-- Delete Dialog -->
    <VDialog v-model="deleteDialog" max-width="440">
      <VCard>
        <VCardText class="text-center pt-8 pb-4">
          <VAvatar color="error" variant="tonal" size="70" class="mb-4">
            <VIcon icon="tabler-trash-x" size="34" />
          </VAvatar>
          <h5 class="text-h5 font-weight-bold mb-2">تأكيد الحذف</h5>
          <p class="text-body-1 text-medium-emphasis mb-0">
            هل أنت متأكد من حذف القضاء نهائياً؟
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
