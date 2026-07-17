<script setup lang="ts">
import { ref, onMounted } from 'vue'

interface Banner {
  id: number
  title: string | null
  position: string
  image_url: string
  link_type: 'none' | 'internal_offer' | 'external_url'
  link_target: string | null
  sort_order: number
  is_active: boolean
  created_at?: string
}

// قائمة البنرات والإحصائيات
const banners = ref<Banner[]>([])
const loading = ref(false)
const positionFilter = ref('all')
const statusFilter = ref('all')
const summary = ref({ total: 0, active: 0, inactive: 0 })
const errorMessage = ref('')
const successMessage = ref('')

// خيارات أماكن ظهور البنر (دروب داون البنرات)
const positionOptions = [
  { title: 'الشاشة الرئيسية للتطبيق', value: 'home' },
]

const filterPositionOptions = [
  { title: 'الشاشة الرئيسية للتطبيق', value: 'all' },
]

const filterStatusOptions = [
  { title: 'جميع الحالات', value: 'all' },
  { title: 'مفعل فقط', value: 'active' },
  { title: 'موقوف فقط', value: 'inactive' },
]

const linkTypeOptions = [
  { title: 'بدون انتقال (عرض صورة فقط)', value: 'none', icon: 'tabler-photo' },
  { title: 'انتقال لعرض / باقة داخل التطبيق', value: 'internal_offer', icon: 'tabler-discount-2' },
  { title: 'انتقال إلى رابط خارجي (موقع ويب / رابط)', value: 'external_url', icon: 'tabler-external-link' },
]

// أداة ترجمة أسماء الأقسام
const getPositionTitle = (val: string) => {
  const item = positionOptions.find(p => p.value === val)
  return item ? item.title : val
}

// Dialog الإضافة / التعديل
const dialog = ref(false)
const isEditing = ref(false)
const editingId = ref<number | null>(null)
const saveLoading = ref(false)

// حقول النموذج
const formTitle      = ref('')
const formPosition   = ref('home')
const formImage      = ref<string | null>(null)
const formLinkType   = ref<'none' | 'internal_offer' | 'external_url'>('none')
const formLinkTarget = ref('')
const formSortOrder  = ref(1)
const formIsActive   = ref(true)
const validationErrors = ref<Record<string, string[]>>({})

// Dialog تأكيد الحذف
const deleteDialog = ref(false)
const deletingBanner = ref<Banner | null>(null)
const deleteLoading = ref(false)

// Dialog معاينة الصورة كبيرة
const previewDialog = ref(false)
const previewImage = ref('')

// جلب البنرات
const fetchBanners = async () => {
  loading.value = true
  try {
    const params: any = {}
    if (positionFilter.value !== 'all') params.position = positionFilter.value
    if (statusFilter.value !== 'all') params.status = statusFilter.value

    const res = await $api('/banners', { params })
    if (res.status) {
      banners.value = res.banners
      summary.value = res.summary
    }
  } catch {
    errorMessage.value = 'تعذر جلب قائمة البنرات'
  } finally {
    loading.value = false
  }
}

// فتح نموذج الإضافة
const openAddDialog = () => {
  isEditing.value = false
  editingId.value = null
  formTitle.value = ''
  formPosition.value = positionFilter.value !== 'all' ? positionFilter.value : 'home'
  formImage.value = null
  formLinkType.value = 'none'
  formLinkTarget.value = ''
  formSortOrder.value = banners.value.length + 1
  formIsActive.value = true
  validationErrors.value = {}
  dialog.value = true
}

// فتح نموذج التعديل
const openEditDialog = (banner: Banner) => {
  isEditing.value = true
  editingId.value = banner.id
  formTitle.value = banner.title || ''
  formPosition.value = banner.position
  formImage.value = banner.image_url
  formLinkType.value = banner.link_type
  formLinkTarget.value = banner.link_target || ''
  formSortOrder.value = banner.sort_order
  formIsActive.value = banner.is_active
  validationErrors.value = {}
  dialog.value = true
}

// تحويل الصورة إلى Base64
const imageToBase64 = (file: File): Promise<string> =>
  new Promise((resolve, reject) => {
    const reader = new FileReader()
    reader.onload = () => resolve(reader.result as string)
    reader.onerror = reject
    reader.readAsDataURL(file)
  })

const handleImagePick = async (event: Event) => {
  const file = (event.target as HTMLInputElement).files?.[0]
  if (!file) return
  formImage.value = await imageToBase64(file)
}

// حفظ البنر (إضافة أو تعديل)
const saveBanner = async () => {
  if (!formImage.value) {
    errorMessage.value = 'يرجى إضافة صورة للبنر'
    return
  }

  saveLoading.value = true
  validationErrors.value = {}
  errorMessage.value = ''

  try {
    const url = isEditing.value ? `/banners/${editingId.value}` : '/banners'
    const method = isEditing.value ? 'PUT' : 'POST'

    const res = await $api(url, {
      method,
      body: {
        title: formTitle.value || null,
        position: formPosition.value,
        image: formImage.value,
        link_type: formLinkType.value,
        link_target: formLinkTarget.value || null,
        sort_order: Number(formSortOrder.value) || 1,
        is_active: formIsActive.value,
      },
    })

    if (res.status) {
      dialog.value = false
      successMessage.value = isEditing.value ? 'تم تحديث البنر بنجاح' : 'تم إضافة البنر بنجاح'
      fetchBanners()
    } else {
      errorMessage.value = res.message || 'حدث خطأ أثناء حفظ البنر'
    }
  } catch (e: any) {
    if (e?.errors) validationErrors.value = e.errors
    else errorMessage.value = e?.message || 'تعذر حفظ البنر'
  } finally {
    saveLoading.value = false
  }
}

// تفعيل / إيقاف سريع
const toggleActive = async (banner: Banner) => {
  try {
    await $api(`/banners/${banner.id}/toggle-active`, { method: 'PATCH' })
    await fetchBanners()
  } catch {
    errorMessage.value = 'تعذر تغيير حالة البنر'
  }
}

// تغيير ترتيب الظهور سريعاً (أعلى / أسفل)
const moveBannerOrder = async (banner: Banner, direction: 'up' | 'down') => {
  const currentIndex = banners.value.findIndex(b => b.id === banner.id)
  if (currentIndex === -1) return

  const targetIndex = direction === 'up' ? currentIndex - 1 : currentIndex + 1
  if (targetIndex < 0 || targetIndex >= banners.value.length) return

  const targetBanner = banners.value[targetIndex]
  const oldOrder = banner.sort_order
  const targetOrder = targetBanner.sort_order

  // تبديل الترتيب بين العنصرين
  try {
    await $api('/banners/reorder', {
      method: 'POST',
      body: {
        orders: [
          { id: banner.id, sort_order: targetOrder },
          { id: targetBanner.id, sort_order: oldOrder },
        ],
      },
    })
    fetchBanners()
  } catch {
    errorMessage.value = 'تعذر تغيير ترتيب الظهور'
  }
}

// تأكيد الحذف
const confirmDelete = (banner: Banner) => {
  deletingBanner.value = banner
  deleteDialog.value = true
}

const deleteBanner = async () => {
  if (!deletingBanner.value) return
  deleteLoading.value = true
  try {
    await $api(`/banners/${deletingBanner.value.id}`, { method: 'DELETE' })
    deleteDialog.value = false
    deletingBanner.value = null
    successMessage.value = 'تم حذف البنر بنجاح'
    fetchBanners()
  } catch {
    errorMessage.value = 'تعذر حذف البنر'
  } finally {
    deleteLoading.value = false
  }
}

onMounted(fetchBanners)
</script>

<template>
  <div>
    <!-- Page Header -->
    <div class="d-flex flex-wrap justify-start justify-sm-space-between gap-y-4 gap-x-6 mb-6">
      <div>
        <h4 class="text-h4 font-weight-medium mb-1">إدارة المحتوى والبنرات الإعلانية</h4>
        <div class="text-body-1 text-medium-emphasis">
          إدارة البنرات، أزرار الانتقال للعروض والروابط الخارجية، وترتيب الظهور في تطبيق الجوال
        </div>
      </div>
      <VBtn color="primary" prepend-icon="tabler-plus" @click="openAddDialog">
        إضافة بنر جديد
      </VBtn>
    </div>

    <!-- Summary Cards -->
    <VRow class="mb-6">
      <VCol cols="12" sm="4">
        <VCard>
          <VCardText class="d-flex align-center gap-4">
            <VAvatar color="primary" variant="tonal" size="52" rounded>
              <VIcon icon="tabler-photo-star" size="28" />
            </VAvatar>
            <div>
              <div class="text-h4 font-weight-bold">{{ summary.total }}</div>
              <div class="text-body-2 text-medium-emphasis">إجمالي البنرات</div>
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
              <div class="text-body-2 text-medium-emphasis">البنرات المفعلة</div>
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
              <div class="text-body-2 text-medium-emphasis">البنرات الموقوفة</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Alerts -->
    <VAlert
      v-if="errorMessage"
      type="error"
      variant="tonal"
      closable
      class="mb-4"
      @click:close="errorMessage = ''"
    >
      {{ errorMessage }}
    </VAlert>

    <VAlert
      v-if="successMessage"
      type="success"
      variant="tonal"
      closable
      class="mb-4"
      @click:close="successMessage = ''"
    >
      {{ successMessage }}
    </VAlert>

    <!-- Filters Card -->
    <VCard class="mb-6">
      <VCardText class="d-flex flex-wrap gap-4 align-center justify-space-between py-4">
        <div class="d-flex flex-wrap gap-4 align-center">
          <AppSelect
            v-model="positionFilter"
            :items="filterPositionOptions"
            item-title="title"
            item-value="value"
            label="فلترة حسب مكان الظهور"
            style="min-inline-size: 260px;"
            @update:model-value="fetchBanners"
          />

          <AppSelect
            v-model="statusFilter"
            :items="filterStatusOptions"
            item-title="title"
            item-value="value"
            label="فلترة حسب الحالة"
            style="min-inline-size: 180px;"
            @update:model-value="fetchBanners"
          />
        </div>

        <div class="text-caption text-medium-emphasis">
          * يمكنك تغيير ترتيب الظهور باستخدام أزرار الرفع والخفض في الجدول
        </div>
      </VCardText>
    </VCard>

    <!-- Banners Table Card -->
    <VCard>
      <VTable class="text-no-wrap">
        <thead>
          <tr>
            <th class="text-center" style="width: 100px;">ترتيب الظهور</th>
            <th style="width: 180px;">صورة البنر</th>
            <th>العنوان ومكان الظهور</th>
            <th>إعدادات الانتقال عند الضغط</th>
            <th class="text-center" style="width: 120px;">الحالة</th>
            <th class="text-center" style="width: 140px;">إجراءات</th>
          </tr>
        </thead>

        <tbody>
          <tr v-if="loading">
            <td colspan="6" class="text-center py-8">
              <VProgressCircular indeterminate color="primary" class="mb-2" />
              <div class="text-body-2 text-medium-emphasis">جاري تحميل المحتوى والبنرات...</div>
            </td>
          </tr>

          <tr v-else-if="banners.length === 0">
            <td colspan="6" class="text-center py-10">
              <VIcon icon="tabler-photo-off" size="52" color="secondary" class="mb-3 opacity-40" />
              <div class="text-h6 font-weight-medium mb-1">لا توجد بنرات مسجلة</div>
              <div class="text-body-2 text-medium-emphasis mb-4">اضغط على "إضافة بنر جديد" للبدء في نشر المحتوى الإعلاني</div>
              <VBtn color="primary" size="small" prepend-icon="tabler-plus" @click="openAddDialog">
                إضافة بنر جديد
              </VBtn>
            </td>
          </tr>

          <tr v-for="(banner, index) in banners" :key="banner.id">
            <!-- ترتيب الظهور -->
            <td class="text-center">
              <div class="d-flex align-center justify-center gap-1">
                <VBtn
                  icon="tabler-chevron-up"
                  variant="text"
                  size="x-small"
                  color="secondary"
                  :disabled="index === 0"
                  @click="moveBannerOrder(banner, 'up')"
                />
                <VChip color="primary" variant="tonal" size="small" class="font-weight-bold px-3">
                  #{{ banner.sort_order }}
                </VChip>
                <VBtn
                  icon="tabler-chevron-down"
                  variant="text"
                  size="x-small"
                  color="secondary"
                  :disabled="index === banners.length - 1"
                  @click="moveBannerOrder(banner, 'down')"
                />
              </div>
            </td>

            <!-- صورة البنر -->
            <td>
              <div
                class="rounded border overflow-hidden my-2 cursor-pointer position-relative"
                style="inline-size: 140px; block-size: 68px; background: rgba(var(--v-theme-surface-variant), 0.3);"
                @click="previewImage = banner.image_url; previewDialog = true"
              >
                <img
                  :src="banner.image_url"
                  alt="Banner"
                  class="w-100 h-100"
                  style="object-fit: cover;"
                />
                <div
                  class="position-absolute d-flex align-center justify-center w-100 h-100 top-0 left-0 opacity-0 hover-opacity-100 transition-all"
                  style="background: rgba(0,0,0,0.4);"
                >
                  <VIcon icon="tabler-eye" color="white" size="20" />
                </div>
              </div>
            </td>

            <!-- العنوان ومكان الظهور -->
            <td>
              <div class="font-weight-semibold text-body-1 mb-1">
                {{ banner.title || 'بنر إعلاني بدون عنوان' }}
              </div>
              <VChip color="secondary" variant="tonal" size="small" label prepend-icon="tabler-layout">
                {{ getPositionTitle(banner.position) }}
              </VChip>
            </td>

            <!-- الانتقال عند الضغط -->
            <td>
              <div v-if="banner.link_type === 'internal_offer'" class="d-flex align-center gap-2">
                <VChip color="info" variant="tonal" size="small" prepend-icon="tabler-discount-2">
                  انتقال لعرض داخلي
                </VChip>
                <span class="text-caption font-weight-medium">
                  معرف العرض: {{ banner.link_target }}
                </span>
              </div>

              <div v-else-if="banner.link_type === 'external_url'" class="d-flex align-center gap-2">
                <VChip color="warning" variant="tonal" size="small" prepend-icon="tabler-external-link">
                  رابط خارجي
                </VChip>
                <a
                  :href="banner.link_target || '#'"
                  target="_blank"
                  class="text-caption text-primary font-weight-medium text-truncate"
                  style="max-inline-size: 200px;"
                >
                  {{ banner.link_target }}
                </a>
              </div>

              <div v-else>
                <VChip color="default" variant="outlined" size="small" prepend-icon="tabler-photo">
                  صورة للعرض فقط
                </VChip>
              </div>
            </td>

            <!-- الحالة -->
            <td class="text-center">
              <VChip
                :color="banner.is_active ? 'success' : 'error'"
                variant="tonal"
                size="small"
                :prepend-icon="banner.is_active ? 'tabler-circle-check' : 'tabler-ban'"
              >
                {{ banner.is_active ? 'مفعل' : 'موقوف' }}
              </VChip>
            </td>

            <!-- الإجراءات -->
            <td class="text-center">
              <div class="d-flex align-center justify-center gap-1">
                <VTooltip :text="banner.is_active ? 'إيقاف البنر' : 'تفعيل البنر'" location="top">
                  <template #activator="{ props }">
                    <VBtn
                      v-bind="props"
                      :icon="banner.is_active ? 'tabler-player-pause' : 'tabler-player-play'"
                      :color="banner.is_active ? 'warning' : 'success'"
                      variant="text"
                      size="small"
                      @click="toggleActive(banner)"
                    />
                  </template>
                </VTooltip>

                <VTooltip text="تعديل البنر" location="top">
                  <template #activator="{ props }">
                    <VBtn
                      v-bind="props"
                      icon="tabler-edit"
                      color="primary"
                      variant="text"
                      size="small"
                      @click="openEditDialog(banner)"
                    />
                  </template>
                </VTooltip>

                <VTooltip text="حذف البنر" location="top">
                  <template #activator="{ props }">
                    <VBtn
                      v-bind="props"
                      icon="tabler-trash"
                      color="error"
                      variant="text"
                      size="small"
                      @click="confirmDelete(banner)"
                    />
                  </template>
                </VTooltip>
              </div>
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <!-- Dialog إضافة / تعديل بنر -->
    <VDialog v-model="dialog" max-width="680" persistent>
      <VCard :title="isEditing ? 'تعديل البنر الإعلاني' : 'إضافة بنر إعلاني جديد'">
        <VCardText class="pt-2">
          <VRow>
            <!-- دروب داون البنرات (مكان الظهور) -->
            <VCol cols="12" md="7">
              <AppSelect
                v-model="formPosition"
                :items="positionOptions"
                item-title="title"
                item-value="value"
                label="مكان ظهور البنر *"
                placeholder="اختر الشاشة التي سيظهر فيها البنر"
              />
            </VCol>

            <!-- ترتيب الظهور -->
            <VCol cols="12" md="5">
              <AppTextField
                v-model="formSortOrder"
                label="ترتيب الظهور (الرقم الأقل يظهر أولاً)"
                type="number"
                min="1"
              />
            </VCol>

            <!-- عنوان البنر -->
            <VCol cols="12">
              <AppTextField
                v-model="formTitle"
                label="عنوان أو وصف البنر (اختياري للإدارة)"
                placeholder="مثال: خصم 20% على باقة الفحوصات الشاملة"
                :error-messages="validationErrors.title"
              />
            </VCol>

            <!-- إضافة الصورة -->
            <VCol cols="12">
              <label class="v-label text-body-2 font-weight-medium mb-2 d-block">
                صورة البنر الإعلاني *
              </label>

              <div
                class="rounded border d-flex flex-column align-center justify-center position-relative overflow-hidden pa-4"
                style="min-block-size: 160px; border-style: dashed !important; cursor: pointer; background: rgba(var(--v-theme-surface-variant), 0.1);"
                @click="($refs.imageInput as HTMLInputElement)?.click()"
              >
                <img
                  v-if="formImage"
                  :src="formImage"
                  class="w-100 rounded"
                  style="max-block-size: 200px; object-fit: contain;"
                />

                <div v-else class="text-center my-4">
                  <VIcon icon="tabler-photo-plus" size="42" color="primary" class="mb-2" />
                  <div class="text-body-1 font-weight-medium">انقر هنا لاختيار ورفع صورة البنر</div>
                  <div class="text-caption text-medium-emphasis">ينصح بصور أفقية عالية الجودة (PNG أو JPEG)</div>
                </div>

                <VBtn
                  v-if="formImage"
                  icon="tabler-x"
                  color="error"
                  size="x-small"
                  class="position-absolute"
                  style="top: 8px; right: 8px;"
                  @click.stop="formImage = null"
                />
              </div>

              <input
                ref="imageInput"
                type="file"
                accept="image/*"
                class="d-none"
                @change="handleImagePick"
              />
            </VCol>

            <!-- إعدادات الانتقال (زر انتقال لعرض / رابط خارجي) -->
            <VCol cols="12">
              <VCard variant="outlined" class="pa-4">
                <div class="text-subtitle-2 font-weight-bold mb-3">
                  إجراء الضغط على البنر (زر الانتقال)
                </div>

                <AppSelect
                  v-model="formLinkType"
                  :items="linkTypeOptions"
                  item-title="title"
                  item-value="value"
                  label="نوع الانتقال عند ضغط المستخدم على البنر"
                  class="mb-4"
                />

                <AppTextField
                  v-if="formLinkType === 'internal_offer'"
                  v-model="formLinkTarget"
                  label="معرف العرض أو اسم الباقة في التطبيق *"
                  placeholder="مثال: OFFER-2026 أو 15"
                  prepend-inner-icon="tabler-discount-2"
                />

                <AppTextField
                  v-if="formLinkType === 'external_url'"
                  v-model="formLinkTarget"
                  label="الرابط الخارجي *"
                  placeholder="https://example.com/promotion"
                  prepend-inner-icon="tabler-external-link"
                />
              </VCard>
            </VCol>

            <!-- تفعيل أو إيقاف -->
            <VCol cols="12">
              <div class="d-flex align-center justify-space-between pa-3 rounded border">
                <div>
                  <div class="font-weight-medium text-body-1">تفعيل البنر فور حفظه</div>
                  <div class="text-caption text-medium-emphasis">سيكون البنر مرئياً للمستخدمين داخل التطبيق</div>
                </div>
                <VSwitch v-model="formIsActive" color="success" hide-details />
              </div>
            </VCol>
          </VRow>
        </VCardText>

        <VCardActions class="pa-4 justify-end gap-2">
          <VBtn variant="tonal" color="secondary" @click="dialog = false">إلغاء</VBtn>
          <VBtn color="primary" prepend-icon="tabler-device-floppy" :loading="saveLoading" @click="saveBanner">
            حفظ البنر
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Dialog معاينة الصورة كبيرة -->
    <VDialog v-model="previewDialog" max-width="700">
      <VCard>
        <VCardTitle class="d-flex justify-space-between align-center pa-4">
          <span>معاينة صورة البنر</span>
          <VBtn icon="tabler-x" variant="text" size="small" @click="previewDialog = false" />
        </VCardTitle>
        <VCardText class="pa-4 text-center">
          <img :src="previewImage" class="w-100 rounded" style="max-block-size: 500px; object-fit: contain;" />
        </VCardText>
      </VCard>
    </VDialog>

    <!-- Delete Confirmation Dialog -->
    <VDialog v-model="deleteDialog" max-width="440">
      <VCard>
        <VCardText class="text-center pt-8 pb-4">
          <VAvatar color="error" variant="tonal" size="70" class="mb-4">
            <VIcon icon="tabler-trash-x" size="34" />
          </VAvatar>
          <h5 class="text-h5 font-weight-bold mb-2">تأكيد حذف البنر</h5>
          <p class="text-body-1 text-medium-emphasis mb-0">
            هل أنت متأكد من حذف هذا البنر الإعلاني؟ لن يظهر بعد ذلك في تطبيق الجوال.
          </p>
        </VCardText>
        <VCardActions class="justify-center gap-4 pb-6">
          <VBtn variant="tonal" color="secondary" @click="deleteDialog = false">إلغاء</VBtn>
          <VBtn color="error" :loading="deleteLoading" @click="deleteBanner">حذف</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
