<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'

interface Faq {
  id: number
  category: 'general' | 'orders' | 'payments' | 'results' | 'technician'
  question: string
  answer: string
  sort_order: number
  is_active: boolean
}

const faqs = ref<Faq[]>([])
const loading = ref(false)
const totalFaqs = ref(0)
const counts = ref<Record<string, number>>({
  all: 0,
  general: 0,
  orders: 0,
  payments: 0,
  results: 0,
  technician: 0,
})

const selectedCategory = ref('all')
const errorMessage = ref('')
const successMessage = ref('')

const categories = [
  { title: 'أسئلة عامة', value: 'general', icon: 'tabler-help-circle' },
  { title: 'الطلبات والحجز', value: 'orders', icon: 'tabler-calendar-event' },
  { title: 'الدفع والأسعار', value: 'payments', icon: 'tabler-credit-card' },
  { title: 'النتائج والتقارير', value: 'results', icon: 'tabler-file-analytics' },
  { title: 'الفني وسحب العينة', value: 'technician', icon: 'tabler-stethoscope' },
]

const getCategoryTitle = (val: string) => {
  const c = categories.find(item => item.value === val)
  return c ? c.title : val
}

// تصفية الأسئلة حسب التبويب المختار
const displayedCategories = computed(() => {
  if (selectedCategory.value === 'all') {
    return categories.map(cat => ({
      ...cat,
      items: faqs.value.filter(f => f.category === cat.value),
    })).filter(cat => cat.items.length > 0)
  }

  const cat = categories.find(c => c.value === selectedCategory.value)
  return cat
    ? [{ ...cat, items: faqs.value.filter(f => f.category === selectedCategory.value) }]
    : []
})

// Dialog الإضافة / التعديل
const dialog = ref(false)
const isEditing = ref(false)
const editingId = ref<number | null>(null)
const saveLoading = ref(false)

const formCategory  = ref('general')
const formQuestion  = ref('')
const formAnswer    = ref('')
const formSortOrder = ref(1)
const formIsActive  = ref(true)
const validationErrors = ref<Record<string, string[]>>({})

// Dialog تأكيد الحذف
const deleteDialog = ref(false)
const deletingFaq = ref<Faq | null>(null)
const deleteLoading = ref(false)

// جلب الأسئلة
const fetchFaqs = async () => {
  loading.value = true
  try {
    const res = await $api('/faqs')
    if (res.status) {
      faqs.value = res.faqs
      totalFaqs.value = res.totalFaqs
      counts.value = res.counts
    }
  } catch {
    errorMessage.value = 'تعذر جلب الأسئلة الشائعة'
  } finally {
    loading.value = false
  }
}

// فتح نموذج الإضافة
const openAddDialog = () => {
  isEditing.value = false
  editingId.value = null
  formCategory.value = selectedCategory.value !== 'all' ? selectedCategory.value : 'general'
  formQuestion.value = ''
  formAnswer.value = ''
  formSortOrder.value = faqs.value.length + 1
  formIsActive.value = true
  validationErrors.value = {}
  dialog.value = true
}

// فتح نموذج التعديل
const openEditDialog = (faq: Faq) => {
  isEditing.value = true
  editingId.value = faq.id
  formCategory.value = faq.category
  formQuestion.value = faq.question
  formAnswer.value = faq.answer
  formSortOrder.value = faq.sort_order
  formIsActive.value = faq.is_active
  validationErrors.value = {}
  dialog.value = true
}

// حفظ السؤال
const saveFaq = async () => {
  saveLoading.value = true
  validationErrors.value = {}
  errorMessage.value = ''

  try {
    const url = isEditing.value ? `/faqs/${editingId.value}` : '/faqs'
    const method = isEditing.value ? 'PUT' : 'POST'

    const res = await $api(url, {
      method,
      body: {
        category: formCategory.value,
        question: formQuestion.value,
        answer: formAnswer.value,
        sort_order: Number(formSortOrder.value) || 1,
        is_active: formIsActive.value,
      },
    })

    if (res.status) {
      dialog.value = false
      successMessage.value = isEditing.value ? 'تم تعديل السؤال بنجاح' : 'تم إضافة السؤال بنجاح'
      fetchFaqs()
    } else {
      errorMessage.value = res.message || 'حدث خطأ أثناء حفظ السؤال'
    }
  } catch (e: any) {
    if (e?.errors) validationErrors.value = e.errors
    else errorMessage.value = e?.message || 'تعذر حفظ السؤال'
  } finally {
    saveLoading.value = false
  }
}

// تأكيد الحذف
const confirmDelete = (faq: Faq) => {
  deletingFaq.value = faq
  deleteDialog.value = true
}

const deleteFaq = async () => {
  if (!deletingFaq.value) return
  deleteLoading.value = true
  try {
    await $api(`/faqs/${deletingFaq.value.id}`, { method: 'DELETE' })
    deleteDialog.value = false
    deletingFaq.value = null
    successMessage.value = 'تم حذف السؤال بنجاح'
    fetchFaqs()
  } catch {
    errorMessage.value = 'تعذر حذف السؤال'
  } finally {
    deleteLoading.value = false
  }
}

onMounted(fetchFaqs)
</script>

<template>
  <div>
    <!-- Page Header (مطابق للصورة تماماً) -->
    <div class="d-flex flex-wrap justify-space-between align-center gap-4 mb-6">
      <div>
        <h3 class="text-h3 font-weight-bold mb-1">الأسئلة الشائعة</h3>
        <div class="text-body-1 text-medium-emphasis">
          {{ totalFaqs }} سؤال في 5 تصنيف
        </div>
      </div>

      <VBtn color="primary" prepend-icon="tabler-plus" size="large" @click="openAddDialog">
        إضافة سؤال
      </VBtn>
    </div>

    <!-- Alerts -->
    <VAlert v-if="errorMessage" type="error" variant="tonal" closable class="mb-4" @click:close="errorMessage = ''">
      {{ errorMessage }}
    </VAlert>
    <VAlert v-if="successMessage" type="success" variant="tonal" closable class="mb-4" @click:close="successMessage = ''">
      {{ successMessage }}
    </VAlert>

    <!-- Category Tabs Chips (مطابقة للصورة) -->
    <div class="d-flex flex-wrap justify-end gap-3 mb-6">
      <VBtn
        :variant="selectedCategory === 'all' ? 'elevated' : 'outlined'"
        :color="selectedCategory === 'all' ? 'primary' : 'secondary'"
        rounded="lg"
        @click="selectedCategory = 'all'"
      >
        الكل ({{ counts.all || 0 }})
      </VBtn>

      <VBtn
        v-for="cat in categories"
        :key="cat.value"
        :variant="selectedCategory === cat.value ? 'elevated' : 'outlined'"
        :color="selectedCategory === cat.value ? 'primary' : 'secondary'"
        rounded="lg"
        @click="selectedCategory = cat.value"
      >
        {{ cat.title }} ({{ counts[cat.value] || 0 }})
      </VBtn>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <VProgressCircular indeterminate color="primary" size="44" class="mb-3" />
      <div class="text-body-1 text-medium-emphasis">جاري تحميل الأسئلة الشائعة...</div>
    </div>

    <!-- Empty State -->
    <div v-else-if="displayedCategories.length === 0" class="text-center py-12">
      <VIcon icon="tabler-help-circle" size="56" color="secondary" class="mb-3 opacity-40" />
      <div class="text-h6 font-weight-medium mb-1">لا توجد أسئلة في هذا التصنيف</div>
      <VBtn color="primary" size="small" prepend-icon="tabler-plus" class="mt-3" @click="openAddDialog">
        إضافة سؤال جديد
      </VBtn>
    </div>

    <!-- Accordion Lists per Category -->
    <div v-else class="d-flex flex-column gap-6">
      <VCard v-for="catGroup in displayedCategories" :key="catGroup.value" class="pa-4">
        <!-- Category Title Header -->
        <div class="d-flex align-center gap-2 pa-2 mb-2 text-primary font-weight-bold text-h6">
          <VIcon :icon="catGroup.icon" size="22" />
          <span>{{ catGroup.title }}</span>
          <span class="text-body-2 text-medium-emphasis ms-1">({{ catGroup.items.length }} سؤال)</span>
        </div>

        <!-- Expansion Panels -->
        <VExpansionPanels variant="accordion">
          <VExpansionPanel
            v-for="faq in catGroup.items"
            :key="faq.id"
            class="mb-2 border rounded"
          >
            <VExpansionPanelTitle class="font-weight-semibold text-body-1 py-3">
              {{ faq.question }}
            </VExpansionPanelTitle>

            <VExpansionPanelText>
              <div class="pt-2 pb-3 text-body-1 text-high-emphasis" style="line-height: 1.8; white-space: pre-line;">
                {{ faq.answer }}
              </div>

              <!-- Action Buttons (تعديل / حذف في أسفل اليسار كما في الصورة) -->
              <VDivider class="my-3" />
              <div class="d-flex justify-end align-center gap-4">
                <VBtn
                  variant="text"
                  color="error"
                  size="small"
                  prepend-icon="tabler-trash"
                  @click="confirmDelete(faq)"
                >
                  حذف
                </VBtn>

                <VBtn
                  variant="text"
                  color="primary"
                  size="small"
                  prepend-icon="tabler-edit"
                  @click="openEditDialog(faq)"
                >
                  تعديل
                </VBtn>
              </div>
            </VExpansionPanelText>
          </VExpansionPanel>
        </VExpansionPanels>
      </VCard>
    </div>

    <!-- Dialog إضافة / تعديل سؤال (مطابق لنافذة الصورة تماماً) -->
    <VDialog v-model="dialog" max-width="580" persistent>
      <VCard>
        <!-- Header مع زر إغلاق -->
        <VCardTitle class="d-flex justify-space-between align-center pa-6 pb-2">
          <span class="text-h5 font-weight-bold">{{ isEditing ? 'تعديل السؤال' : 'إضافة سؤال جديد' }}</span>
          <VBtn icon="tabler-x" variant="text" size="small" @click="dialog = false" />
        </VCardTitle>

        <VCardText class="pa-6 pt-3">
          <VRow>
            <!-- التصنيف -->
            <VCol cols="12">
              <AppSelect
                v-model="formCategory"
                :items="categories"
                item-title="title"
                item-value="value"
                label="التصنيف"
                :error-messages="validationErrors.category"
              />
            </VCol>

            <!-- السؤال -->
            <VCol cols="12">
              <AppTextField
                v-model="formQuestion"
                label="السؤال"
                placeholder="اكتب السؤال بوضوح..."
                :error-messages="validationErrors.question"
              />
            </VCol>

            <!-- الجواب -->
            <VCol cols="12">
              <label class="v-label text-body-2 font-weight-medium mb-1 d-block">الجواب</label>
              <VTextarea
                v-model="formAnswer"
                placeholder="اكتب الإجابة المفصلة هنا..."
                rows="5"
                :error-messages="validationErrors.answer"
              />
            </VCol>

            <!-- الترّتيب ومفتاح التفعيل -->
            <VCol cols="12">
              <div class="d-flex align-center justify-space-between gap-4">
                <div class="d-flex align-center gap-3">
                  <span class="font-weight-medium text-body-1">نشط</span>
                  <VSwitch v-model="formIsActive" color="primary" hide-details />
                </div>

                <div style="max-inline-size: 160px;">
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

        <!-- الأزرار السفلى: حفظ أزرق و إلغاء فاتح -->
        <VCardActions class="pa-6 pt-2 justify-start gap-3">
          <VBtn color="primary" variant="elevated" :loading="saveLoading" class="px-6" @click="saveFaq">
            حفظ
          </VBtn>
          <VBtn variant="outlined" color="secondary" class="px-6" @click="dialog = false">
            إلغاء
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
            هل أنت متأكد من حذف هذا السؤال نهائياً؟
          </p>
        </VCardText>
        <VCardActions class="justify-center gap-4 pb-6">
          <VBtn variant="tonal" color="secondary" @click="deleteDialog = false">إلغاء</VBtn>
          <VBtn color="error" :loading="deleteLoading" @click="deleteFaq">حذف</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
