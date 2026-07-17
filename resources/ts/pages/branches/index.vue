<script setup lang="ts">
import { ref, onMounted } from 'vue'

interface District {
  id: number
  name: string
}

interface Branch {
  id: number
  name_ar: string
  address: string | null
  phone: string | null
  is_active: boolean
  opens_at: string | null
  closes_at: string | null
  notes: string | null
  districts?: District[]
  created_at: string
}

const branches = ref<Branch[]>([])
const allDistricts = ref<{ id: number; name: string; governorate: string }[]>([])
const loading = ref(false)
const totalBranches = ref(0)
const page = ref(1)
const itemsPerPage = ref(10)
const searchQuery = ref('')
const statusFilter = ref('all')

const summary = ref({ totalBranches: 0, activeBranches: 0, inactiveBranches: 0 })
const errorMessage = ref('')

// إشعار عائم
const snackbar = ref(false)
const snackbarText = ref('')
const snackbarColor = ref<'success' | 'error' | 'primary'>('success')

const showToast = (text: string, color: 'success' | 'error' = 'success') => {
  snackbarText.value = text
  snackbarColor.value = color
  snackbar.value = true
}

// Dialog الفرع
const branchDialog = ref(false)
const isEditing = ref(false)
const editingBranchId = ref<number | null>(null)
const saveLoading = ref(false)
const form = ref({
  name_ar: '',
  phone: '',
  address: '',
  opens_at: '08:00',
  closes_at: '22:00',
  district_ids: [] as number[],
  is_active: true,
  notes: '',
})
const formErrors = ref<Record<string, string[]>>({})

// Dialog الحذف
const deleteDialog = ref(false)
const targetBranch = ref<Branch | null>(null)
const deleteLoading = ref(false)

const headers = [
  { title: '#', key: 'id', sortable: true, width: '60px' },
  { title: 'اسم الفرع المخبري', key: 'name_ar', sortable: true },
  { title: 'رقم الهاتف والتواصل', key: 'phone', sortable: false },
  { title: 'العنوان', key: 'address', sortable: false },
  { title: 'الأقضية المرتبطة بالفرع (التغطية)', key: 'districts', sortable: false },
  { title: 'الحالة', key: 'is_active', sortable: true, align: 'center' },
  { title: 'إجراءات', key: 'actions', sortable: false, align: 'end' },
]

const fetchBranches = async () => {
  loading.value = true
  try {
    const params: any = {
      page: page.value,
      itemsPerPage: itemsPerPage.value,
    }
    if (searchQuery.value) params.q = searchQuery.value
    if (statusFilter.value !== 'all') params.status = statusFilter.value

    const res = await $api('/branches', { params })
    if (res.status) {
      branches.value = res.branches
      allDistricts.value = res.allDistricts || []
      totalBranches.value = res.totalBranches
      summary.value = res.summary
    }
  } catch {
    errorMessage.value = 'تعذر تحميل قائمة الفروع'
  } finally {
    loading.value = false
  }
}

const onBranchesOptionsUpdate = (options: any) => {
  page.value = options.page
  itemsPerPage.value = options.itemsPerPage
  fetchBranches()
}

// إضافة فرع جديد
const openAddBranch = () => {

  isEditing.value = false
  editingBranchId.value = null
  form.value = {
    name_ar: '',
    phone: '',
    address: '',
    opens_at: '08:00',
    closes_at: '22:00',
    district_ids: [],
    is_active: true,
    notes: '',
  }
  formErrors.value = {}
  branchDialog.value = true
}

// تعديل فرع
const openEditBranch = (branch: Branch) => {
  isEditing.value = true
  editingBranchId.value = branch.id
  form.value = {
    name_ar: branch.name_ar,
    phone: branch.phone || '',
    address: branch.address || '',
    opens_at: branch.opens_at || '08:00',
    closes_at: branch.closes_at || '22:00',
    district_ids: branch.districts?.map(d => d.id) || [],
    is_active: branch.is_active,
    notes: branch.notes || '',
  }
  formErrors.value = {}
  branchDialog.value = true
}

// حفظ الفرع
const saveBranch = async () => {
  saveLoading.value = true
  formErrors.value = {}
  try {
    const url = isEditing.value ? `/branches/${editingBranchId.value}` : '/branches'
    const method = isEditing.value ? 'PUT' : 'POST'

    const res = await $api(url, {
      method,
      body: {
        ...form.value,
        radius_km: 15, // القيمة الافتراضية لقاعدة البيانات
        coverage_type: 'circle',
      },
    })

    if (res.status) {
      branchDialog.value = false
      showToast(isEditing.value ? 'تم تحديث بيانات الفرع بنجاح' : 'تم إضافة الفرع بنجاح', 'success')
      fetchBranches()
    } else {
      showToast(res.message || 'حدث خطأ أثناء الحفظ', 'error')
    }
  } catch (e: any) {
    if (e?.errors) formErrors.value = e.errors
    else showToast(e?.message || 'تعذر حفظ الفرع', 'error')
  } finally {
    saveLoading.value = false
  }
}

// تفعيل / إيقاف الفرع
const toggleActive = async (branch: Branch) => {
  try {
    const res = await $api(`/branches/${branch.id}/toggle-active`, { method: 'PATCH' })
    showToast(res.message || 'تم تحديث حالة الفرع بنجاح', 'success')
    fetchBranches()
  } catch {
    showToast('تعذر تغيير حالة الفرع', 'error')
  }
}

// تأكيد الحذف
const confirmDelete = (branch: Branch) => {
  targetBranch.value = branch
  deleteDialog.value = true
}

const executeDelete = async () => {
  if (!targetBranch.value) return
  deleteLoading.value = true
  try {
    await $api(`/branches/${targetBranch.value.id}`, { method: 'DELETE' })
    deleteDialog.value = false
    showToast('تم حذف الفرع بنجاح', 'success')
    fetchBranches()
  } catch {
    showToast('تعذر حذف الفرع', 'error')
  } finally {
    deleteLoading.value = false
  }
}

onMounted(fetchBranches)
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

    <!-- Header -->
    <div class="d-flex flex-wrap justify-space-between align-center gap-4 mb-6">
      <div>
        <h3 class="text-h3 font-weight-bold mb-1">إدارة الفروع المخبرية (مربوطة بالأقضية)</h3>
        <div class="text-body-1 text-medium-emphasis">
          إدارة الفروع واستقبال الطلبات بناءً على قضاء العميل مباشرة دون الحاجة لحسابات إحداثيات أو خريطة
        </div>
      </div>

      <VBtn color="primary" prepend-icon="tabler-plus" size="large" @click="openAddBranch">
        إضافة فرع مخبري جديد
      </VBtn>
    </div>

    <!-- Summary Cards -->
    <VRow class="mb-6">
      <VCol cols="12" sm="4">
        <VCard>
          <VCardText class="d-flex align-center gap-4">
            <VAvatar color="primary" variant="tonal" size="52" rounded>
              <VIcon icon="tabler-building-store" size="28" />
            </VAvatar>
            <div>
              <div class="text-h4 font-weight-bold">{{ summary.totalBranches }}</div>
              <div class="text-body-2 text-medium-emphasis">إجمالي الفروع المخبرية</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" sm="4">
        <VCard>
          <VCardText class="d-flex align-center gap-4">
            <VAvatar color="success" variant="tonal" size="52" rounded>
              <VIcon icon="tabler-circle-check" size="28" />
            </VAvatar>
            <div>
              <div class="text-h4 font-weight-bold text-success">{{ summary.activeBranches }}</div>
              <div class="text-body-2 text-medium-emphasis">فروع نشطة ومستعدة لاستقبال العينات</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" sm="4">
        <VCard>
          <VCardText class="d-flex align-center gap-4">
            <VAvatar color="error" variant="tonal" size="52" rounded>
              <VIcon icon="tabler-player-pause" size="28" />
            </VAvatar>
            <div>
              <div class="text-h4 font-weight-bold text-error">{{ summary.inactiveBranches }}</div>
              <div class="text-body-2 text-medium-emphasis">فروع متوقفة مؤقتاً</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Filters Bar -->
    <VCard class="mb-6">
      <VCardText class="d-flex flex-wrap align-center gap-4">
        <div style="flex: 1; min-width: 240px;">
          <AppTextField
            v-model="searchQuery"
            placeholder="ابحث باسم الفرع، العنوان، أو رقم الهاتف..."
            prepend-inner-icon="tabler-search"
            clearable
            @keyup.enter="fetchBranches"
          />
        </div>

        <div style="width: 200px;">
          <AppSelect
            v-model="statusFilter"
            :items="[
              { title: 'جميع الحالات', value: 'all' },
              { title: 'فروع نشطة', value: 'active' },
              { title: 'فروع متوقفة', value: 'inactive' }
            ]"
            item-title="title"
            item-value="value"
            @update:model-value="fetchBranches"
          />
        </div>

        <VBtn color="primary" variant="tonal" prepend-icon="tabler-refresh" @click="fetchBranches">
          تحديث
        </VBtn>
      </VCardText>
    </VCard>

    <!-- Branches Table -->
    <VCard>
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :headers="headers"
        :items="branches"
        :items-length="totalBranches"
        :loading="loading"
        class="text-no-wrap"
        @update:options="onBranchesOptionsUpdate"
      >
        <!-- اسم الفرع -->
        <template #item.name_ar="{ item }">
          <div class="d-flex align-center gap-3 py-2">
            <VAvatar :color="item.is_active ? 'primary' : 'secondary'" variant="tonal" size="42" rounded>
              <VIcon icon="tabler-building-store" size="24" />
            </VAvatar>
            <div>
              <div class="font-weight-bold text-body-1">{{ item.name_ar }}</div>
              <div class="text-caption text-medium-emphasis">ساعات العمل: {{ item.opens_at || '08:00' }} - {{ item.closes_at || '22:00' }}</div>
            </div>
          </div>
        </template>

        <!-- الهاتف -->
        <template #item.phone="{ item }">
          <div class="d-flex align-center gap-2">
            <VIcon icon="tabler-phone" size="16" color="primary" />
            <span class="font-weight-medium">{{ item.phone || 'غير مسجل' }}</span>
          </div>
        </template>

        <!-- الأقضية المرتبطة بالفرع -->
        <template #item.districts="{ item }">
          <div class="d-flex flex-wrap gap-1 py-1">
            <VChip
              v-for="d in item.districts"
              :key="d.id"
              color="info"
              size="small"
              variant="tonal"
              prepend-icon="tabler-map-pin"
            >
              {{ d.name }}
            </VChip>
            <span v-if="!item.districts || item.districts.length === 0" class="text-caption text-medium-emphasis">
              لا توجد أقضية مرتبطة (يمكنك ربط القضاء من صفحة الأقضية)
            </span>
          </div>
        </template>

        <!-- الحالة -->
        <template #item.is_active="{ item }">
          <div class="d-flex align-center justify-center">
            <VSwitch
              :model-value="item.is_active"
              color="success"
              hide-details
              @change="toggleActive(item)"
            />
          </div>
        </template>

        <!-- الإجراءات -->
        <template #item.actions="{ item }">
          <div class="d-flex align-center justify-end gap-1">
            <VTooltip text="تعديل بيانات الفرع" location="top">
              <template #activator="{ props }">
                <VBtn
                  v-bind="props"
                  icon="tabler-edit"
                  variant="text"
                  color="primary"
                  size="small"
                  @click="openEditBranch(item)"
                />
              </template>
            </VTooltip>

            <VTooltip text="حذف الفرع" location="top">
              <template #activator="{ props }">
                <VBtn
                  v-bind="props"
                  icon="tabler-trash"
                  variant="text"
                  color="error"
                  size="small"
                  @click="confirmDelete(item)"
                />
              </template>
            </VTooltip>
          </div>
        </template>

        <template #bottom>
          <TablePagination
            v-if="totalBranches > 0"
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalBranches"
          />
        </template>
      </VDataTableServer>
    </VCard>


    <!-- Dialog إضافة / تعديل الفرع -->
    <VDialog v-model="branchDialog" max-width="560" persistent>
      <VCard :title="isEditing ? 'تعديل بيانات الفرع المخبري' : 'إضافة فرع مخبري جديد'">
        <VCardText class="pt-2">
          <VRow>
            <VCol cols="12">
              <AppTextField
                v-model="form.name_ar"
                label="اسم الفرع المخبري *"
                placeholder="مثال: فرع ديالى الرئيسي - بعقوبة"
                :error-messages="formErrors.name_ar"
              />
            </VCol>

            <VCol cols="12" sm="6">
              <AppTextField
                v-model="form.phone"
                label="رقم الهاتف للتواصل"
                placeholder="077XXXXXXXX"
              />
            </VCol>

            <VCol cols="12" sm="6">
              <AppTextField
                v-model="form.address"
                label="العنوان الوصفي"
                placeholder="بعقوبة - شارع المحافظة"
              />
            </VCol>

            <VCol cols="12" sm="6">
              <AppTextField
                v-model="form.opens_at"
                label="وقت الفتح"
                placeholder="08:00"
              />
            </VCol>

            <VCol cols="12" sm="6">
              <AppTextField
                v-model="form.closes_at"
                label="وقت الإغلاق"
                placeholder="22:00"
              />
            </VCol>

            <VCol cols="12">
              <AppSelect
                v-model="form.district_ids"
                :items="allDistricts"
                item-title="name"
                item-value="id"
                label="الأقضية المرتبطة بهذا الفرع (نطاق التغطية)"
                placeholder="اختر الأقضية التي يستقبل هذا الفرع طلباتها"
                multiple
                chips
                closable-chips
                clearable
              />
            </VCol>

            <VCol cols="12">
              <AppTextField
                v-model="form.notes"
                label="ملاحظات وتفاصيل إضافية عن الفرع"
                placeholder="مثال: الفرع الرئيسي في المحافظة يعمل طوال أيام الأسبوع"
              />
            </VCol>

            <VCol cols="12">
              <div class="d-flex align-center justify-space-between pa-3 rounded border">
                <div>
                  <div class="font-weight-medium text-body-1">تفعيل الفرع واستقبال العينات</div>
                  <div class="text-caption text-medium-emphasis">عند التفعيل يستقبل الفرع طلبات الأقضية المرتبطة به</div>
                </div>
                <VSwitch v-model="form.is_active" color="success" hide-details />
              </div>
            </VCol>
          </VRow>
        </VCardText>

        <VCardActions class="pa-4 justify-end gap-2">
          <VBtn variant="tonal" color="secondary" @click="branchDialog = false">إلغاء</VBtn>
          <VBtn color="primary" :loading="saveLoading" @click="saveBranch">حفظ</VBtn>
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
            هل أنت متأكد من حذف الفرع "{{ targetBranch?.name_ar }}"؟
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
