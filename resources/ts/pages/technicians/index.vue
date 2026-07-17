<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

interface Technician {
  id: number
  name: string
  phone: string
  address: string | null
  specialty: string | null
  has_transport: boolean
  has_equipment: boolean
  status: 'active' | 'suspended' | 'on_leave'
  id_front_image: string | null
  total_orders_count?: number
  completed_orders_count?: number
  active_orders_count?: number
  created_at: string
}


const technicians = ref<Technician[]>([])
const loading = ref(false)
const totalTechnicians = ref(0)
const page = ref(1)
const itemsPerPage = ref(10)
const searchQuery = ref('')
const statusFilter = ref('all')
const sortBy = ref([{ key: 'id', order: 'asc' }])
const summary = ref({ total: 0, active: 0, suspended: 0, on_leave: 0 })
const errorMessage = ref('')

// Delete dialog
const deleteDialog = ref(false)
const deletingTechnician = ref<Technician | null>(null)
const deleteLoading = ref(false)

const headers = [
  { title: '#', key: 'id', sortable: true, width: '60px' },
  { title: 'الفني', key: 'name', sortable: true, minWidth: '220px' },
  { title: 'التخصص', key: 'specialty', sortable: false },
  { title: 'المؤهلات', key: 'capabilities', sortable: false },
  { title: 'إحصائيات المهام', key: 'orders_stats', sortable: false, minWidth: '180px' },
  { title: 'الحالة', key: 'status', sortable: true },
  { title: 'إجراءات', key: 'actions', sortable: false, align: 'center', width: '80px' },
]


const statusOptions = [
  { title: 'جميع الحالات', value: 'all' },
  { title: 'نشط', value: 'active' },
  { title: 'موقوف', value: 'suspended' },
  { title: 'إجازة', value: 'on_leave' },
]

const statusConfig: Record<string, { color: string; label: string; icon: string }> = {
  active:    { color: 'success', label: 'نشط',   icon: 'tabler-circle-check' },
  suspended: { color: 'error',   label: 'موقوف', icon: 'tabler-ban' },
  on_leave:  { color: 'warning', label: 'إجازة', icon: 'tabler-calendar-off' },
}

const fetchTechnicians = async () => {
  loading.value = true
  try {
    const params: any = {
      page: page.value,
      itemsPerPage: itemsPerPage.value,
      sortBy: sortBy.value[0]?.key ?? 'id',
      orderBy: sortBy.value[0]?.order ?? 'asc',
    }
    if (searchQuery.value) params.search = searchQuery.value
    if (statusFilter.value !== 'all') params.status = statusFilter.value

    const res = await $api('/technicians', { params })
    if (res.status) {
      technicians.value = res.technicians
      totalTechnicians.value = res.totalTechnicians
      summary.value = res.summary
    }
  } catch {
    errorMessage.value = 'تعذر جلب قائمة الفنيين'
  } finally {
    loading.value = false
  }
}

// Change status explicitly
const setStatus = async (technician: Technician, newStatus: string) => {
  try {
    await $api(`/technicians/${technician.id}/toggle-status`, {
      method: 'PATCH',
      body: { status: newStatus },
    })
    await fetchTechnicians()
  } catch {
    errorMessage.value = 'تعذر تغيير الحالة'
  }
}

const confirmDelete = (technician: Technician) => {
  deletingTechnician.value = technician
  deleteDialog.value = true
}

const deleteTechnician = async () => {
  if (!deletingTechnician.value) return
  deleteLoading.value = true
  try {
    await $api(`/technicians/${deletingTechnician.value.id}`, { method: 'DELETE' })
    deleteDialog.value = false
    deletingTechnician.value = null
    await fetchTechnicians()
  } catch {
    errorMessage.value = 'تعذر حذف الفني'
  } finally {
    deleteLoading.value = false
  }
}

const onOptionsUpdate = ({ page: p, itemsPerPage: ipp, sortBy: sb }: any) => {
  page.value = p
  itemsPerPage.value = ipp
  if (sb?.length) sortBy.value = sb
  fetchTechnicians()
}

onMounted(fetchTechnicians)
</script>

<template>
  <div>
    <!-- Header -->
    <div class="d-flex flex-wrap justify-start justify-sm-space-between gap-y-4 gap-x-6 mb-6">
      <div>
        <h4 class="text-h4 font-weight-medium mb-1">إدارة الفنيين الميدانيين</h4>
        <div class="text-body-1 text-medium-emphasis">قائمة شاملة بجميع الفنيين المسجلين وحالاتهم الوظيفية</div>
      </div>
      <VBtn color="primary" prepend-icon="tabler-plus" @click="router.push('/technicians/add')">
        إضافة فني جديد
      </VBtn>
    </div>

    <!-- Summary Cards -->
    <VRow class="mb-6">
      <VCol cols="6" md="3">
        <VCard>
          <VCardText class="d-flex align-center gap-4">
            <VAvatar color="primary" variant="tonal" size="52" rounded>
              <VIcon icon="tabler-stethoscope" size="28" />
            </VAvatar>
            <div>
              <div class="text-h4 font-weight-bold">{{ summary.total }}</div>
              <div class="text-body-2 text-medium-emphasis">إجمالي الفنيين</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="6" md="3">
        <VCard>
          <VCardText class="d-flex align-center gap-4">
            <VAvatar color="success" variant="tonal" size="52" rounded>
              <VIcon icon="tabler-circle-check" size="28" />
            </VAvatar>
            <div>
              <div class="text-h4 font-weight-bold text-success">{{ summary.active }}</div>
              <div class="text-body-2 text-medium-emphasis">نشط</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="6" md="3">
        <VCard>
          <VCardText class="d-flex align-center gap-4">
            <VAvatar color="warning" variant="tonal" size="52" rounded>
              <VIcon icon="tabler-calendar-off" size="28" />
            </VAvatar>
            <div>
              <div class="text-h4 font-weight-bold text-warning">{{ summary.on_leave }}</div>
              <div class="text-body-2 text-medium-emphasis">في إجازة</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="6" md="3">
        <VCard>
          <VCardText class="d-flex align-center gap-4">
            <VAvatar color="error" variant="tonal" size="52" rounded>
              <VIcon icon="tabler-ban" size="28" />
            </VAvatar>
            <div>
              <div class="text-h4 font-weight-bold text-error">{{ summary.suspended }}</div>
              <div class="text-body-2 text-medium-emphasis">موقوف</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <VAlert v-if="errorMessage" type="error" variant="tonal" closable class="mb-4" @click:close="errorMessage = ''">
      {{ errorMessage }}
    </VAlert>

    <!-- Table Card -->
    <VCard>
      <VCardText class="d-flex flex-wrap gap-4 align-center justify-space-between py-4">
        <AppTextField
          v-model="searchQuery"
          placeholder="ابحث بالاسم أو الهاتف أو التخصص..."
          prepend-inner-icon="tabler-search"
          style="max-inline-size: 300px;"
          clearable
          @update:model-value="fetchTechnicians"
        />
        <AppSelect
          v-model="statusFilter"
          :items="statusOptions"
          item-title="title"
          item-value="value"
          style="max-inline-size: 200px;"
          @update:model-value="fetchTechnicians"
        />
      </VCardText>

      <VDivider />

      <VDataTableServer
        :headers="headers"
        :items="technicians"
        :items-length="totalTechnicians"
        :loading="loading"
        :items-per-page="itemsPerPage"
        @update:options="onOptionsUpdate"
      >
        <!-- # Sequential -->
        <template #item.id="{ index }">
          <span class="text-body-2 text-medium-emphasis font-weight-medium">
            {{ (page - 1) * itemsPerPage + index + 1 }}
          </span>
        </template>

        <!-- Technician: Circle Avatar with Icon + Name + Contact Info -->
        <template #item.name="{ item }">
          <div class="d-flex align-center gap-3 py-2">
            <VAvatar
              color="primary"
              variant="tonal"
              size="42"
              rounded
            >
              <VIcon icon="tabler-user-cog" size="24" />
            </VAvatar>
            <div>
              <RouterLink :to="`/technicians/${item.id}`" class="font-weight-bold text-body-1 text-primary text-decoration-none d-block mb-1">
                {{ item.name }}
              </RouterLink>
              <div class="d-flex flex-wrap align-center gap-x-3 gap-y-1">
                <div class="d-flex align-center gap-1">
                  <VIcon icon="tabler-phone" size="14" color="medium-emphasis" />
                  <span class="text-caption font-weight-medium text-medium-emphasis" dir="ltr">{{ item.phone }}</span>
                </div>
                <div v-if="item.address" class="d-flex align-center gap-1">
                  <VIcon icon="tabler-map-pin" size="14" color="medium-emphasis" />
                  <span class="text-caption text-medium-emphasis">{{ item.address }}</span>
                </div>
              </div>
            </div>
          </div>
        </template>

        <!-- Specialty -->
        <template #item.specialty="{ item }">
          <VChip v-if="item.specialty" color="info" variant="tonal" size="small" label class="font-weight-medium">
            <VIcon icon="tabler-stethoscope" size="14" start />
            {{ item.specialty }}
          </VChip>
          <span v-else class="text-caption text-medium-emphasis">—</span>
        </template>

        <!-- Capabilities -->
        <template #item.capabilities="{ item }">
          <div class="d-flex align-center gap-2">
            <VChip
              :color="item.has_transport ? 'success' : 'secondary'"
              :variant="item.has_transport ? 'tonal' : 'outlined'"
              size="small"
              label
            >
              <VIcon :icon="item.has_transport ? 'tabler-car' : 'tabler-car-off'" size="14" start />
              نقل
            </VChip>
            <VChip
              :color="item.has_equipment ? 'success' : 'secondary'"
              :variant="item.has_equipment ? 'tonal' : 'outlined'"
              size="small"
              label
            >
              <VIcon :icon="item.has_equipment ? 'tabler-briefcase-medical' : 'tabler-briefcase-off'" size="14" start />
              معدات
            </VChip>
          </div>
        </template>

        <!-- Orders Stats -->
        <template #item.orders_stats="{ item }">
          <div class="d-flex flex-column gap-1 py-1">
            <div class="d-flex align-center gap-2">
              <VChip v-if="(item.active_orders_count ?? 0) > 0" color="warning" variant="elevated" size="x-small" class="font-weight-bold">
                <VIcon icon="tabler-loader" size="12" start />
                جاري: {{ item.active_orders_count }}
              </VChip>
              <VChip v-else color="success" variant="tonal" size="x-small" class="font-weight-bold">
                <VIcon icon="tabler-circle-check" size="12" start />
                متاح الآن
              </VChip>
            </div>
            <div class="text-caption text-medium-emphasis font-weight-medium">
              إنجاز: <strong class="text-high-emphasis">{{ item.completed_orders_count || 0 }}</strong> من <strong class="text-high-emphasis">{{ item.total_orders_count || 0 }}</strong> طلب
            </div>
          </div>
        </template>

        <!-- Status -->
        <template #item.status="{ item }">
          <VChip
            :color="statusConfig[item.status]?.color"
            :prepend-icon="statusConfig[item.status]?.icon"
            variant="tonal"
            size="small"
            class="font-weight-medium"
          >
            {{ statusConfig[item.status]?.label }}
          </VChip>
        </template>


        <!-- Actions: dropdown menu -->
        <template #item.actions="{ item }">
          <VMenu>
            <template #activator="{ props }">
              <VBtn
                v-bind="props"
                icon="tabler-dots-vertical"
                variant="text"
                size="small"
                color="secondary"
              />
            </template>
            <VList density="compact" min-width="180">
              <!-- View Profile & Orders -->
              <VListItem
                prepend-icon="tabler-user-check"
                title="عرض الملف وسجل الطلبات"
                @click="router.push(`/technicians/${item.id}`)"
              />
              <!-- Edit -->
              <VListItem
                prepend-icon="tabler-edit"
                title="تعديل البيانات"
                @click="router.push(`/technicians/edit/${item.id}`)"
              />

              <VDivider class="my-1" />

              <!-- Status Actions -->
              <VListItem
                v-if="item.status !== 'active'"
                prepend-icon="tabler-circle-check"
                title="تفعيل"
                base-color="success"
                @click="setStatus(item, 'active')"
              />
              <VListItem
                v-if="item.status !== 'on_leave'"
                prepend-icon="tabler-calendar-off"
                title="منح إجازة"
                base-color="warning"
                @click="setStatus(item, 'on_leave')"
              />
              <VListItem
                v-if="item.status !== 'suspended'"
                prepend-icon="tabler-ban"
                title="إيقاف"
                base-color="error"
                @click="setStatus(item, 'suspended')"
              />

              <VDivider class="my-1" />

              <!-- Delete -->
              <VListItem
                prepend-icon="tabler-trash"
                title="حذف الفني"
                base-color="error"
                @click="confirmDelete(item)"
              />
            </VList>
          </VMenu>
        </template>

        <!-- Empty State -->
        <template #no-data>
          <div class="text-center py-10">
            <VIcon icon="tabler-users-group" size="56" color="secondary" class="mb-3 opacity-40" />
            <div class="text-h6 font-weight-medium mb-1">لا يوجد فنيون مسجلون</div>
            <div class="text-body-2 text-medium-emphasis mb-4">ابدأ بإضافة أول فني ميداني</div>
            <VBtn color="primary" size="small" prepend-icon="tabler-plus" @click="router.push('/technicians/add')">
              إضافة فني
            </VBtn>
          </div>
        </template>

        <template #bottom>
          <TablePagination
            v-if="totalTechnicians > 0"
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalTechnicians"
          />
        </template>
      </VDataTableServer>
    </VCard>


    <!-- Delete Dialog -->
    <VDialog v-model="deleteDialog" max-width="440">
      <VCard>
        <VCardText class="text-center pt-8 pb-4">
          <VAvatar color="error" variant="tonal" size="70" class="mb-4">
            <VIcon icon="tabler-trash-x" size="34" />
          </VAvatar>
          <h5 class="text-h5 font-weight-bold mb-2">تأكيد الحذف</h5>
          <p class="text-body-1 text-medium-emphasis mb-0">
            هل أنت متأكد من حذف الفني
            <strong class="text-high-emphasis">{{ deletingTechnician?.name }}</strong>؟
          </p>
        </VCardText>
        <VCardActions class="justify-center gap-4 pb-6">
          <VBtn variant="tonal" color="secondary" @click="deleteDialog = false">إلغاء</VBtn>
          <VBtn color="error" :loading="deleteLoading" @click="deleteTechnician">حذف</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
