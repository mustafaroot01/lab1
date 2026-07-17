<script setup lang="ts">
import { ref, onMounted } from 'vue'

interface ContactInfo {
  id: number
  channel_type: string
  title: string
  value: string
  sort_order: number
  is_active: boolean
}

const infos = ref<ContactInfo[]>([])
const loading = ref(false)
const summary = ref({ total: 0, active: 0, inactive: 0 })
const errorMessage = ref('')
const successMessage = ref('')

// قنوات التواصل والأيقونات والألوان
const channelOptions = [
  { title: 'رقم الهاتف المباشر (اتصل بنا)', value: 'phone', icon: 'tabler-phone-call', color: 'primary' },
  { title: 'واتساب (WhatsApp)', value: 'whatsapp', icon: 'tabler-brand-whatsapp', color: 'success' },
  { title: 'ساعات وأيام العمل (Working Hours)', value: 'working_hours', icon: 'tabler-clock', color: 'warning' },
  { title: 'العنوان والمقر (Address)', value: 'address', icon: 'tabler-map-pin', color: 'error' },
  { title: 'البريد الإلكتروني (Email)', value: 'email', icon: 'tabler-mail', color: 'info' },
  { title: 'فيسبوك (Facebook)', value: 'facebook', icon: 'tabler-brand-facebook', color: 'primary' },
  { title: 'تيليجرام (Telegram)', value: 'telegram', icon: 'tabler-brand-telegram', color: 'info' },
  { title: 'إنستغرام (Instagram)', value: 'instagram', icon: 'tabler-brand-instagram', color: 'error' },
]

const getChannelConfig = (val: string) => {
  return channelOptions.find(c => c.value === val) || {
    title: val,
    icon: 'tabler-address-book',
    color: 'secondary',
  }
}

// Dialog الإضافة / التعديل
const dialog = ref(false)
const isEditing = ref(false)
const editingId = ref<number | null>(null)
const saveLoading = ref(false)

const formChannelType = ref('phone')
const formTitle       = ref('')
const formValue       = ref('')
const formSortOrder   = ref(1)
const formIsActive    = ref(true)
const validationErrors = ref<Record<string, string[]>>({})

// Dialog تأكيد الحذف
const deleteDialog = ref(false)
const deletingInfo = ref<ContactInfo | null>(null)
const deleteLoading = ref(false)

// جلب وسائل التواصل
const fetchInfos = async () => {
  loading.value = true
  try {
    const res = await $api('/contact-infos')
    if (res.status) {
      infos.value = res.infos
      summary.value = res.summary
    }
  } catch {
    errorMessage.value = 'تعذر جلب معلومات التواصل'
  } finally {
    loading.value = false
  }
}

// عند تغيير نوع القناة، نقترح عنوان مناسب إذا كان الحقل فارغاً
const onChannelChange = (newVal: string) => {
  if (!isEditing.value) {
    const cfg = getChannelConfig(newVal)
    formTitle.value = cfg.title
  }
}

// فتح الإضافة
const openAddDialog = () => {
  isEditing.value = false
  editingId.value = null
  formChannelType.value = 'phone'
  formTitle.value = 'اتصل بنا المباشر (خدمة العملاء)'
  formValue.value = ''
  formSortOrder.value = infos.value.length + 1
  formIsActive.value = true
  validationErrors.value = {}
  dialog.value = true
}

// فتح التعديل
const openEditDialog = (info: ContactInfo) => {
  isEditing.value = true
  editingId.value = info.id
  formChannelType.value = info.channel_type
  formTitle.value = info.title
  formValue.value = info.value
  formSortOrder.value = info.sort_order
  formIsActive.value = info.is_active
  validationErrors.value = {}
  dialog.value = true
}

// حفظ
const saveInfo = async () => {
  saveLoading.value = true
  validationErrors.value = {}
  errorMessage.value = ''

  try {
    const url = isEditing.value ? `/contact-infos/${editingId.value}` : '/contact-infos'
    const method = isEditing.value ? 'PUT' : 'POST'

    const res = await $api(url, {
      method,
      body: {
        channel_type: formChannelType.value,
        title: formTitle.value,
        value: formValue.value,
        sort_order: Number(formSortOrder.value) || 1,
        is_active: formIsActive.value,
      },
    })

    if (res.status) {
      dialog.value = false
      successMessage.value = isEditing.value ? 'تم تحديث معلومة التواصل بنجاح' : 'تم إضافة معلومة التواصل بنجاح'
      fetchInfos()
    } else {
      errorMessage.value = res.message || 'حدث خطأ أثناء الحفظ'
    }
  } catch (e: any) {
    if (e?.errors) validationErrors.value = e.errors
    else errorMessage.value = e?.message || 'تعذر حفظ معلومة التواصل'
  } finally {
    saveLoading.value = false
  }
}

// تفعيل / إيقاف سريع
const toggleActive = async (info: ContactInfo) => {
  try {
    await $api(`/contact-infos/${info.id}/toggle-active`, { method: 'PATCH' })
    fetchInfos()
  } catch {
    errorMessage.value = 'تعذر تغيير حالة التواصل'
  }
}

// تأكيد الحذف
const confirmDelete = (info: ContactInfo) => {
  deletingInfo.value = info
  deleteDialog.value = true
}

const deleteInfo = async () => {
  if (!deletingInfo.value) return
  deleteLoading.value = true
  try {
    await $api(`/contact-infos/${deletingInfo.value.id}`, { method: 'DELETE' })
    deleteDialog.value = false
    deletingInfo.value = null
    successMessage.value = 'تم حذف معلومة التواصل بنجاح'
    fetchInfos()
  } catch {
    errorMessage.value = 'تعذر حذف معلومة التواصل'
  } finally {
    deleteLoading.value = false
  }
}

onMounted(fetchInfos)
</script>

<template>
  <div>
    <!-- Page Header -->
    <div class="d-flex flex-wrap justify-space-between align-center gap-4 mb-6">
      <div>
        <h4 class="text-h4 font-weight-bold mb-1">إدارة معلومات وقنوات التواصل</h4>
        <div class="text-body-1 text-medium-emphasis">
          إدارة أرقام الهاتف (اتصل بنا)، واتساب، ساعات العمل، العنوان، والبريد الإلكتروني الظاهرة في التطبيق
        </div>
      </div>
      <VBtn color="primary" prepend-icon="tabler-plus" size="large" @click="openAddDialog">
        إضافة وسيلة تواصل
      </VBtn>
    </div>

    <!-- Summary Cards -->
    <VRow class="mb-6">
      <VCol cols="12" sm="4">
        <VCard>
          <VCardText class="d-flex align-center gap-4">
            <VAvatar color="primary" variant="tonal" size="52" rounded>
              <VIcon icon="tabler-address-book" size="28" />
            </VAvatar>
            <div>
              <div class="text-h4 font-weight-bold">{{ summary.total }}</div>
              <div class="text-body-2 text-medium-emphasis">إجمالي القنوات والبيانات</div>
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
              <div class="text-h4 font-weight-bold text-success">{{ summary.active }}</div>
              <div class="text-body-2 text-medium-emphasis">قنوات مفعلة ونشطة</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" sm="4">
        <VCard>
          <VCardText class="d-flex align-center gap-4">
            <VAvatar color="error" variant="tonal" size="52" rounded>
              <VIcon icon="tabler-ban" size="28" />
            </VAvatar>
            <div>
              <div class="text-h4 font-weight-bold text-error">{{ summary.inactive }}</div>
              <div class="text-body-2 text-medium-emphasis">قنوات متوقفة</div>
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

    <!-- Table Card -->
    <VCard>
      <VTable class="text-no-wrap">
        <thead>
          <tr>
            <th class="text-center" style="width: 70px;">الترتيب</th>
            <th>نوع وسيلة التواصل</th>
            <th>العنوان الظاهر للمستخدم</th>
            <th>القيمة (رقم / رابط / نص ساعات العمل والعنوان)</th>
            <th class="text-center">الحالة</th>
            <th class="text-center" style="width: 140px;">إجراءات</th>
          </tr>
        </thead>

        <tbody>
          <tr v-if="loading">
            <td colspan="6" class="text-center py-8">
              <VProgressCircular indeterminate color="primary" class="mb-2" />
              <div class="text-body-2 text-medium-emphasis">جاري تحميل معلومات التواصل...</div>
            </td>
          </tr>

          <tr v-else-if="infos.length === 0">
            <td colspan="6" class="text-center py-10">
              <VIcon icon="tabler-address-book" size="52" color="secondary" class="mb-3 opacity-40" />
              <div class="text-h6 font-weight-medium mb-1">لا توجد وسائل تواصل مسجلة</div>
              <VBtn color="primary" size="small" prepend-icon="tabler-plus" class="mt-3" @click="openAddDialog">
                إضافة وسيلة تواصل
              </VBtn>
            </td>
          </tr>

          <tr v-for="info in infos" :key="info.id">
            <!-- الترتيب -->
            <td class="text-center font-weight-bold text-body-1">
              #{{ info.sort_order }}
            </td>

            <!-- نوع القناة مع الأيقونة -->
            <td>
              <div class="d-flex align-center gap-3 py-2">
                <VAvatar
                  :color="getChannelConfig(info.channel_type).color"
                  variant="tonal"
                  size="44"
                  rounded
                >
                  <VIcon :icon="getChannelConfig(info.channel_type).icon" size="24" />
                </VAvatar>
                <div>
                  <div class="font-weight-semibold text-body-1">
                    {{ getChannelConfig(info.channel_type).title }}
                  </div>
                  <div class="text-caption text-medium-emphasis text-uppercase">
                    {{ info.channel_type }}
                  </div>
                </div>
              </div>
            </td>

            <!-- العنوان الظاهر -->
            <td class="font-weight-medium text-body-1">
              {{ info.title }}
            </td>

            <!-- القيمة -->
            <td>
              <div class="text-body-1 font-weight-semibold text-high-emphasis text-truncate" style="max-inline-size: 380px;">
                {{ info.value }}
              </div>
            </td>

            <!-- الحالة -->
            <td class="text-center">
              <VChip
                :color="info.is_active ? 'success' : 'error'"
                variant="tonal"
                size="small"
                :prepend-icon="info.is_active ? 'tabler-circle-check' : 'tabler-ban'"
              >
                {{ info.is_active ? 'نشط' : 'موقوف' }}
              </VChip>
            </td>

            <!-- الإجراءات -->
            <td class="text-center">
              <div class="d-flex align-center justify-center gap-1">
                <VTooltip :text="info.is_active ? 'إيقاف وسيلة التواصل' : 'تفعيل وسيلة التواصل'" location="top">
                  <template #activator="{ props }">
                    <VBtn
                      v-bind="props"
                      :icon="info.is_active ? 'tabler-player-pause' : 'tabler-player-play'"
                      :color="info.is_active ? 'warning' : 'success'"
                      variant="text"
                      size="small"
                      @click="toggleActive(info)"
                    />
                  </template>
                </VTooltip>

                <VTooltip text="تعديل القناة / الرقم" location="top">
                  <template #activator="{ props }">
                    <VBtn
                      v-bind="props"
                      icon="tabler-edit"
                      color="primary"
                      variant="text"
                      size="small"
                      @click="openEditDialog(info)"
                    />
                  </template>
                </VTooltip>

                <VTooltip text="حذف وسيلة التواصل" location="top">
                  <template #activator="{ props }">
                    <VBtn
                      v-bind="props"
                      icon="tabler-trash"
                      color="error"
                      variant="text"
                      size="small"
                      @click="confirmDelete(info)"
                    />
                  </template>
                </VTooltip>
              </div>
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <!-- Dialog إضافة / تعديل وسيلة تواصل -->
    <VDialog v-model="dialog" max-width="600" persistent>
      <VCard :title="isEditing ? 'تعديل وسيلة التواصل' : 'إضافة وسيلة تواصل جديدة'">
        <VCardText class="pt-2">
          <VRow>
            <!-- نوع القناة -->
            <VCol cols="12">
              <AppSelect
                v-model="formChannelType"
                :items="channelOptions"
                item-title="title"
                item-value="value"
                label="نوع وسيلة التواصل / القناة *"
                @update:model-value="onChannelChange"
              />
            </VCol>

            <!-- العنوان الظاهر -->
            <VCol cols="12">
              <AppTextField
                v-model="formTitle"
                label="العنوان الظاهر للمستخدم *"
                placeholder="مثال: اتصل بنا، الدعم الفني عبر واتساب، أوقات العمل"
                :error-messages="validationErrors.title"
              />
            </VCol>

            <!-- القيمة أو النص -->
            <VCol cols="12">
              <label class="v-label text-body-2 font-weight-medium mb-1 d-block">
                القيمة (رقم الهاتف / رقم واتساب / الرابط / نص العنوان وساعات العمل) *
              </label>
              <VTextarea
                v-model="formValue"
                placeholder="مثال رقم هاتف: 07700000000 أو نص ساعات العمل..."
                rows="3"
                :error-messages="validationErrors.value"
              />
            </VCol>

            <!-- الترتيب ومفتاح التفعيل -->
            <VCol cols="12">
              <div class="d-flex align-center justify-space-between gap-4 pa-3 rounded border">
                <div class="d-flex align-center gap-3">
                  <span class="font-weight-medium text-body-1">تفعيل وسيلة التواصل في التطبيق</span>
                  <VSwitch v-model="formIsActive" color="success" hide-details />
                </div>

                <div style="max-inline-size: 140px;">
                  <AppTextField
                    v-model="formSortOrder"
                    label="الترتيب"
                    type="number"
                    min="1"
                  />
                </div>
              </div>
            </VCol>
          </VRow>
        </VCardText>

        <VCardActions class="pa-4 justify-end gap-2">
          <VBtn variant="tonal" color="secondary" @click="dialog = false">إلغاء</VBtn>
          <VBtn color="primary" prepend-icon="tabler-device-floppy" :loading="saveLoading" @click="saveInfo">
            حفظ البيانات
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
            هل أنت متأكد من حذف وسيلة التواصل هذه؟
          </p>
        </VCardText>
        <VCardActions class="justify-center gap-4 pb-6">
          <VBtn variant="tonal" color="secondary" @click="deleteDialog = false">إلغاء</VBtn>
          <VBtn color="error" :loading="deleteLoading" @click="deleteInfo">حذف</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
