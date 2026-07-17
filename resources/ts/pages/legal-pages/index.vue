<script setup lang="ts">
import { ref, onMounted } from 'vue'

interface LegalPage {
  id: number
  title: string
  slug: string
  content: string
  is_active: boolean
  last_updated_at: string
}

const pages = ref<LegalPage[]>([])
const loading = ref(false)
const summary = ref({ total: 0, active: 0, inactive: 0 })
const errorMessage = ref('')
const successMessage = ref('')

// Dialog الإضافة / التعديل
const dialog = ref(false)
const isEditing = ref(false)
const editingId = ref<number | null>(null)
const saveLoading = ref(false)

// حقول النموذج
const formTitle   = ref('')
const formSlug    = ref('')
const formContent = ref('')
const formIsActive = ref(true)
const activeTab    = ref<'edit' | 'preview'>('edit')
const validationErrors = ref<Record<string, string[]>>({})

// Dialog تأكيد الحذف
const deleteDialog = ref(false)
const deletingPage = ref<LegalPage | null>(null)
const deleteLoading = ref(false)

// جلب الصفحات
const fetchPages = async () => {
  loading.value = true
  try {
    const res = await $api('/legal-pages')
    if (res.status) {
      pages.value = res.pages
      summary.value = res.summary
    }
  } catch {
    errorMessage.value = 'تعذر جلب قائمة الصفحات القانونية'
  } finally {
    loading.value = false
  }
}

// فتح نموذج الإضافة
const openAddDialog = () => {
  isEditing.value = false
  editingId.value = null
  formTitle.value = ''
  formSlug.value = ''
  formContent.value = ''
  formIsActive.value = true
  activeTab.value = 'edit'
  validationErrors.value = {}
  dialog.value = true
}

// فتح نموذج التعديل (مثل الشروط والأحكام أو سياسة الخصوصية)
const openEditDialog = (page: LegalPage) => {
  isEditing.value = true
  editingId.value = page.id
  formTitle.value = page.title
  formSlug.value = page.slug
  formContent.value = page.content || ''
  formIsActive.value = page.is_active
  activeTab.value = 'edit'
  validationErrors.value = {}
  dialog.value = true
}

// إنشاء slug تلقائي من العنوان (اختياري بالإنجليزية)
const autoGenerateSlug = () => {
  if (isEditing.value || formSlug.value) return
  // إذا كان إنجليزي يحوله، وإلا يترك المستخدم يكتبه
}

// حفظ الصفحة
const savePage = async () => {
  saveLoading.value = true
  validationErrors.value = {}
  errorMessage.value = ''

  try {
    const url = isEditing.value ? `/legal-pages/${editingId.value}` : '/legal-pages'
    const method = isEditing.value ? 'PUT' : 'POST'

    const res = await $api(url, {
      method,
      body: {
        title: formTitle.value,
        slug: formSlug.value,
        content: formContent.value,
        is_active: formIsActive.value,
      },
    })

    if (res.status) {
      dialog.value = false
      successMessage.value = isEditing.value ? 'تم حفظ وتحديث الصفحة القانونية بنجاح' : 'تم إضافة الصفحة القانونية بنجاح'
      fetchPages()
    } else {
      errorMessage.value = res.message || 'حدث خطأ أثناء حفظ الصفحة'
    }
  } catch (e: any) {
    if (e?.errors) validationErrors.value = e.errors
    else errorMessage.value = e?.message || 'تعذر حفظ الصفحة'
  } finally {
    saveLoading.value = false
  }
}

// تفعيل / إيقاف سريع
const toggleActive = async (page: LegalPage) => {
  try {
    await $api(`/legal-pages/${page.id}/toggle-active`, { method: 'PATCH' })
    fetchPages()
  } catch {
    errorMessage.value = 'تعذر تغيير حالة الصفحة'
  }
}

// تأكيد الحذف
const confirmDelete = (page: LegalPage) => {
  deletingPage.value = page
  deleteDialog.value = true
}

const deletePage = async () => {
  if (!deletingPage.value) return
  deleteLoading.value = true
  try {
    await $api(`/legal-pages/${deletingPage.value.id}`, { method: 'DELETE' })
    deleteDialog.value = false
    deletingPage.value = null
    successMessage.value = 'تم حذف الصفحة القانونية بنجاح'
    fetchPages()
  } catch {
    errorMessage.value = 'تعذر حذف الصفحة'
  } finally {
    deleteLoading.value = false
  }
}

onMounted(fetchPages)
</script>

<template>
  <div>
    <!-- Page Header -->
    <div class="d-flex flex-wrap justify-start justify-sm-space-between gap-y-4 gap-x-6 mb-6">
      <div>
        <h4 class="text-h4 font-weight-medium mb-1">الصفحات القانونية والشروط والأحكام</h4>
        <div class="text-body-1 text-medium-emphasis">
          إدارة نصوص الشروط والأحكام وسياسة الخصوصية والاتفاقيات القانونية (تدعم تنسيق Markdown) مع توقيت آخر تحديث
        </div>
      </div>
      <VBtn color="primary" prepend-icon="tabler-plus" @click="openAddDialog">
        إضافة صفحة قانونية جديدة
      </VBtn>
    </div>

    <!-- Summary Cards -->
    <VRow class="mb-6">
      <VCol cols="12" sm="4">
        <VCard>
          <VCardText class="d-flex align-center gap-4">
            <VAvatar color="primary" variant="tonal" size="52" rounded>
              <VIcon icon="tabler-scale" size="28" />
            </VAvatar>
            <div>
              <div class="text-h4 font-weight-bold">{{ summary.total }}</div>
              <div class="text-body-2 text-medium-emphasis">إجمالي الصفحات</div>
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
              <div class="text-body-2 text-medium-emphasis">الصفحات المفعلة</div>
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
              <div class="text-body-2 text-medium-emphasis">الصفحات الموقوفة</div>
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
            <th style="width: 70px;">#</th>
            <th>اسم الصفحة القانونية</th>
            <th>المعرف (Slug)</th>
            <th>مقتطف المحتوى</th>
            <th>آخر تحديث</th>
            <th class="text-center">الحالة</th>
            <th class="text-center" style="width: 140px;">إجراءات</th>
          </tr>
        </thead>

        <tbody>
          <tr v-if="loading">
            <td colspan="7" class="text-center py-8">
              <VProgressCircular indeterminate color="primary" class="mb-2" />
              <div class="text-body-2 text-medium-emphasis">جاري تحميل الصفحات القانونية والشروط والأحكام...</div>
            </td>
          </tr>

          <tr v-else-if="pages.length === 0">
            <td colspan="7" class="text-center py-10">
              <VIcon icon="tabler-file-text" size="52" color="secondary" class="mb-3 opacity-40" />
              <div class="text-h6 font-weight-medium mb-1">لا توجد صفحات مسجلة</div>
              <div class="text-body-2 text-medium-emphasis mb-4">اضغط على زر الإضافة لإنشاء الشروط والأحكام أو سياسة الخصوصية</div>
              <VBtn color="primary" size="small" prepend-icon="tabler-plus" @click="openAddDialog">
                إضافة صفحة قانونية
              </VBtn>
            </td>
          </tr>

          <tr v-for="(page, idx) in pages" :key="page.id">
            <!-- # -->
            <td class="text-body-2 font-weight-medium">{{ idx + 1 }}</td>

            <!-- الاسم -->
            <td>
              <div class="d-flex align-center gap-3 py-2">
                <VAvatar color="primary" variant="tonal" size="40" rounded>
                  <VIcon :icon="page.slug === 'terms' ? 'tabler-file-description' : page.slug === 'privacy' ? 'tabler-shield-lock' : 'tabler-file-text'" size="22" />
                </VAvatar>
                <div>
                  <div class="font-weight-semibold text-body-1">{{ page.title }}</div>
                  <div class="text-caption text-medium-emphasis">يدعم Markdown</div>
                </div>
              </div>
            </td>

            <!-- Slug -->
            <td>
              <VChip color="secondary" variant="tonal" size="small" label prepend-icon="tabler-link">
                {{ page.slug }}
              </VChip>
            </td>

            <!-- مقتطف المحتوى -->
            <td>
              <div class="text-caption text-medium-emphasis text-truncate" style="max-inline-size: 260px;">
                {{ page.content ? page.content.replace(/[#*_-]/g, '').slice(0, 75) + '...' : 'بدون محتوى' }}
              </div>
            </td>

            <!-- آخر تحديث -->
            <td>
              <div class="d-flex align-center gap-1 text-body-2">
                <VIcon icon="tabler-clock" size="16" color="medium-emphasis" />
                <span>{{ page.last_updated_at || '—' }}</span>
              </div>
            </td>

            <!-- الحالة -->
            <td class="text-center">
              <VChip
                :color="page.is_active ? 'success' : 'error'"
                variant="tonal"
                size="small"
                :prepend-icon="page.is_active ? 'tabler-circle-check' : 'tabler-ban'"
              >
                {{ page.is_active ? 'نشط' : 'موقوف' }}
              </VChip>
            </td>

            <!-- الإجراءات -->
            <td class="text-center">
              <div class="d-flex align-center justify-center gap-1">
                <VTooltip :text="page.is_active ? 'إيقاف الصفحة' : 'تفعيل الصفحة'" location="top">
                  <template #activator="{ props }">
                    <VBtn
                      v-bind="props"
                      :icon="page.is_active ? 'tabler-player-pause' : 'tabler-player-play'"
                      :color="page.is_active ? 'warning' : 'success'"
                      variant="text"
                      size="small"
                      @click="toggleActive(page)"
                    />
                  </template>
                </VTooltip>

                <VTooltip text="تعديل المحتوى (Markdown)" location="top">
                  <template #activator="{ props }">
                    <VBtn
                      v-bind="props"
                      icon="tabler-edit"
                      color="primary"
                      variant="text"
                      size="small"
                      @click="openEditDialog(page)"
                    />
                  </template>
                </VTooltip>

                <VTooltip text="حذف الصفحة" location="top">
                  <template #activator="{ props }">
                    <VBtn
                      v-bind="props"
                      icon="tabler-trash"
                      color="error"
                      variant="text"
                      size="small"
                      @click="confirmDelete(page)"
                    />
                  </template>
                </VTooltip>
              </div>
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <!-- Dialog تحرير الصفحة القانونية -->
    <VDialog v-model="dialog" max-width="860" persistent>
      <VCard :title="isEditing ? 'تعديل الصفحة القانونية والمحتوى' : 'إضافة صفحة قانونية جديدة'">
        <VCardText class="pt-2">
          <VRow>
            <!-- اسم الصفحة -->
            <VCol cols="12" md="7">
              <AppTextField
                v-model="formTitle"
                label="اسم الصفحة القانونية *"
                placeholder="مثال: الشروط والأحكام أو سياسة الخصوصية"
                :error-messages="validationErrors.title"
              />
            </VCol>

            <!-- المعرف Slug -->
            <VCol cols="12" md="5">
              <AppTextField
                v-model="formSlug"
                label="المعرف البرمجي (Slug) *"
                placeholder="مثال: terms أو privacy"
                :error-messages="validationErrors.slug"
              />
            </VCol>

            <!-- تبويبات التحرير / المعاينة -->
            <VCol cols="12">
              <div class="d-flex align-center justify-space-between mb-2">
                <label class="v-label text-body-2 font-weight-medium">
                  محتوى الصفحة (يدعم تنسيقات Markdown) *
                </label>
                <div class="d-flex gap-2">
                  <VBtn
                    size="x-small"
                    :variant="activeTab === 'edit' ? 'elevated' : 'tonal'"
                    :color="activeTab === 'edit' ? 'primary' : 'secondary'"
                    @click="activeTab = 'edit'"
                  >
                    <VIcon icon="tabler-edit" size="14" class="me-1" />
                    تحرير النص
                  </VBtn>
                  <VBtn
                    size="x-small"
                    :variant="activeTab === 'preview' ? 'elevated' : 'tonal'"
                    :color="activeTab === 'preview' ? 'primary' : 'secondary'"
                    @click="activeTab = 'preview'"
                  >
                    <VIcon icon="tabler-eye" size="14" class="me-1" />
                    معاينة النص
                  </VBtn>
                </div>
              </div>

              <!-- تحرير -->
              <VTextarea
                v-if="activeTab === 'edit'"
                v-model="formContent"
                placeholder="# عنوان رئيسي&#10;### عنوان فرعي&#10;- بند أول&#10;- بند ثانٍ"
                rows="11"
                class="font-monospace"
              />

              <!-- معاينة بسيطة -->
              <VCard
                v-else
                variant="outlined"
                class="pa-4 overflow-y-auto"
                style="block-size: 280px; background: rgba(var(--v-theme-surface-variant), 0.05);"
              >
                <div v-if="formContent" class="text-body-1" style="white-space: pre-wrap; line-height: 1.8;">
                  {{ formContent }}
                </div>
                <div v-else class="text-medium-emphasis text-center py-10">
                  لا يوجد محتوى للمعاينة حتى الآن
                </div>
              </VCard>
            </VCol>

            <!-- الحالة -->
            <VCol cols="12">
              <div class="d-flex align-center justify-space-between pa-3 rounded border">
                <div>
                  <div class="font-weight-medium text-body-1">تفعيل الصفحة (نشط)</div>
                  <div class="text-caption text-medium-emphasis">ستكون هذه الصفحة متاحة للجمهور والتطبيق الميداني</div>
                </div>
                <VSwitch v-model="formIsActive" color="success" hide-details />
              </div>
            </VCol>
          </VRow>
        </VCardText>

        <VCardActions class="pa-4 justify-end gap-2">
          <VBtn variant="tonal" color="secondary" @click="dialog = false">إلغاء</VBtn>
          <VBtn color="primary" prepend-icon="tabler-device-floppy" :loading="saveLoading" @click="savePage">
            حفظ المحتوى والتحديث
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
          <h5 class="text-h5 font-weight-bold mb-2">تأكيد حذف الصفحة</h5>
          <p class="text-body-1 text-medium-emphasis mb-0">
            هل أنت متأكد من حذف هذه الصفحة القانونية؟
          </p>
        </VCardText>
        <VCardActions class="justify-center gap-4 pb-6">
          <VBtn variant="tonal" color="secondary" @click="deleteDialog = false">إلغاء</VBtn>
          <VBtn color="error" :loading="deleteLoading" @click="deletePage">حذف</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
