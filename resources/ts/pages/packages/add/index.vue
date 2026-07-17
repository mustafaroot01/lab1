<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

interface MedicalTestOption {
  id: number
  name_ar: string
  name_en?: string
  key: string
  price: number
  platform_price: number
  total_price: number
  display_name: string
}

const router = useRouter()

// Form Fields
const nameAr = ref('')
const descriptionAr = ref('')
const originalPrice = ref<number | ''>('')
const discountPrice = ref<number | ''>('')
const image = ref('')
const sortOrder = ref<number>(1)
const isActive = ref(true)

// Image upload states
const imageUploading = ref(false)
const imagePreview = ref('')
const fileInput = ref<File[] | null>(null)

// Selected Medical Tests
const selectedTestIds = ref<number[]>([])
const availableTests = ref<MedicalTestOption[]>([])
const testsLoading = ref(false)
const quickSearchQuery = ref('')

// UI / Submission states
const loading = ref(false)
const errorMessage = ref('')
const validationErrors = ref<Record<string, string[]>>({})

// Fetch available medical tests
const fetchAvailableTests = async () => {
  testsLoading.value = true
  try {
    const res = await $api('/package-offers/available-tests')
    if (res.status && res.tests) {
      availableTests.value = res.tests.map((t: any) => ({
        ...t,
        display_name: `${t.name_ar} — [${t.key}] (السعر الكلي للمريض: ${Number(t.total_price || t.price).toLocaleString()} د.ع)`,
      }))
    }
  } catch (error) {
    console.error('Error fetching available tests:', error)
  } finally {
    testsLoading.value = false
  }
}

// Custom filter for VAutocomplete so Arabic, English, and key all match easily
const customTestFilter = (itemTitle: string, queryText: string, item: any) => {
  const text = queryText.toLowerCase()
  const raw = item.raw || item
  const nameAr = (raw.name_ar || '').toLowerCase()
  const nameEn = (raw.name_en || '').toLowerCase()
  const key = (raw.key || '').toLowerCase()
  return nameAr.includes(text) || nameEn.includes(text) || key.includes(text)
}

// Computed filtered list for the Quick Search Table
const quickFilteredTests = computed(() => {
  if (!quickSearchQuery.value.trim()) return []
  const text = quickSearchQuery.value.toLowerCase().trim()
  return availableTests.value.filter(t => {
    return (t.name_ar && t.name_ar.toLowerCase().includes(text)) ||
           (t.name_en && t.name_en.toLowerCase().includes(text)) ||
           (t.key && t.key.toLowerCase().includes(text))
  }).slice(0, 15) // Top 15 matches for speed
})

// Computed selected test objects
const selectedTestsList = computed(() => {
  return availableTests.value.filter(test => selectedTestIds.value.includes(test.id))
})

// Sum of individual selected tests
const sumOfSelectedTests = computed(() => {
  return selectedTestsList.value.reduce((acc, test) => acc + (Number(test.total_price || test.price) || 0), 0)
})

const addTestToSelection = (testId: number) => {
  if (!selectedTestIds.value.includes(testId)) {
    selectedTestIds.value = [...selectedTestIds.value, testId]
  }
}

const removeTestFromSelection = (testId: number) => {
  selectedTestIds.value = selectedTestIds.value.filter(id => id !== testId)
}

const toggleTestSelection = (testId: number) => {
  if (selectedTestIds.value.includes(testId)) {
    removeTestFromSelection(testId)
  } else {
    addTestToSelection(testId)
  }
}

// Set original price equal to sum of tests automatically if user wants
const applySumAsOriginalPrice = () => {
  originalPrice.value = sumOfSelectedTests.value
}

// Handle Image File Upload from Device
const onImageFileChange = async (event: any) => {
  const files = event.target.files || (event as File[])
  if (!files || files.length === 0) return

  const file = files[0]
  if (!file) return

  imageUploading.value = true
  errorMessage.value = ''

  // 1. Instant local preview before saving
  const reader = new FileReader()
  reader.onload = (e) => {
    if (e.target?.result) {
      imagePreview.value = e.target.result as string
    }
  }
  reader.readAsDataURL(file)

  // 2. Upload to server
  try {
    const formData = new FormData()
    formData.append('image', file)

    const res = await $api('/package-offers/upload-image', {
      method: 'POST',
      body: formData,
    })

    if (res.status && res.url) {
      image.value = res.url
    } else {
      errorMessage.value = res.message || 'حدث خطأ أثناء رفع الصورة.'
    }
  } catch (error: any) {
    console.error('Error uploading image:', error)
    errorMessage.value = error?.message || 'تعذر رفع الصورة إلى الخادم، تأكد من حجم وصيغة الملف.'
  } finally {
    imageUploading.value = false
  }
}

// Submit Form
const submitPackageOffer = async () => {
  loading.value = true
  errorMessage.value = ''
  validationErrors.value = {}

  try {
    const res = await $api('/package-offers', {
      method: 'POST',
      body: {
        name_ar: nameAr.value,
        description_ar: descriptionAr.value || null,
        original_price: Number(originalPrice.value) || 0,
        discount_price: discountPrice.value !== '' && discountPrice.value !== null ? Number(discountPrice.value) : null,
        image: image.value || imagePreview.value || null,
        sort_order: Number(sortOrder.value) || 1,
        is_active: isActive.value,
        tests: selectedTestIds.value,
      },
    })

    if (res.status) {
      router.push('/packages')
    } else {
      errorMessage.value = res.message || 'حدث خطأ أثناء حفظ العرض.'
    }
  } catch (error: any) {
    console.error('Error creating package offer:', error)
    if (error && error.errors) {
      validationErrors.value = error.errors
    } else if (error && error.message) {
      errorMessage.value = error.message
    } else {
      errorMessage.value = 'تعذر الاتصال بالخادم حفظ البيانات.'
    }
  } finally {
    loading.value = false
  }
}

const navigateBack = () => {
  router.push('/packages')
}

onMounted(() => {
  fetchAvailableTests()
})
</script>

<template>
  <div class="package-add-page">
    <!-- Top Bar -->
    <div class="d-flex flex-wrap justify-start justify-sm-space-between gap-y-4 gap-x-6 mb-6">
      <div class="d-flex flex-column justify-center">
        <div class="d-flex align-center gap-2 mb-1">
          <VBtn icon variant="text" size="small" color="primary" @click="navigateBack">
            <VIcon icon="tabler-arrow-right" />
          </VBtn>
          <h4 class="text-h4 font-weight-bold">
            إضافة باقة / عرض جديد
          </h4>
        </div>
        <div class="text-body-1 text-medium-emphasis">
          أدخل بيانات الباقة وحدد التحاليل المشمولة وسعر الخصم لكي تظهر في شاشة العروض في التطبيق
        </div>
      </div>

      <div class="d-flex gap-4 align-center flex-wrap">
        <VBtn
          variant="tonal"
          color="secondary"
          @click="navigateBack"
        >
          إلغاء
        </VBtn>
        <VBtn
          color="primary"
          prepend-icon="tabler-check"
          :loading="loading"
          @click="submitPackageOffer"
        >
          حفظ ونشر العرض
        </VBtn>
      </div>
    </div>

    <!-- Alert for general errors -->
    <VAlert
      v-if="errorMessage"
      type="error"
      variant="tonal"
      class="mb-6"
      closable
      @click:close="errorMessage = ''"
    >
      {{ errorMessage }}
    </VAlert>

    <VRow>
      <!-- Left Column: Main Info & Tests Selection -->
      <VCol cols="12" md="8">
        <!-- Main Information Card -->
        <VCard title="البيانات الأساسية للباقة أو العرض" class="mb-6">
          <VCardText>
            <VRow>
              <VCol cols="12">
                <AppTextField
                  v-model="nameAr"
                  label="اسم الباقة أو العرض (بالعربية) *"
                  placeholder="مثال: عرض الغدة الدرقية الشامل"
                  :error-messages="validationErrors.name_ar"
                />
              </VCol>

              <VCol cols="12">
                <label class="v-label mb-1 text-body-2 font-weight-medium">وصف الباقة ومميزاتها</label>
                <VTextarea
                  v-model="descriptionAr"
                  placeholder="اكتب وصفاً جذاباً يوضح أهمية هذه التحاليل ولمن يُنصح بها..."
                  rows="4"
                  variant="outlined"
                  :error-messages="validationErrors.description_ar"
                />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>

        <!-- Medical Tests Selection Card -->
        <VCard class="mb-6">
          <VCardItem>
            <template #title>
              <div class="d-flex align-center gap-2">
                <VIcon icon="tabler-flask" color="primary" />
                <span class="font-weight-bold">تحديد التحاليل المتوفرة في هذا العرض (ابحث واختَر)</span>
              </div>
            </template>
            <template #subtitle>
              ابحث باسم التحليل (عربي أو إنجليزي) أو بالرمز المخبري (مثل CBC أو TSH) واختَر جميع التحاليل المشمولة
            </template>
          </VCardItem>

          <VDivider />

          <VCardText class="pt-6">
            <!-- Method 1: Autocomplete Selector -->
            <VAutocomplete
              v-model="selectedTestIds"
              :items="availableTests"
              item-title="display_name"
              item-value="id"
              :custom-filter="customTestFilter"
              label="القائمة الشاملة للتحاليل (ابحث بالنص واختَر من القائمة)"
              placeholder="اكتب اسم التحليل أو الرمز المخبري لاختياره..."
              multiple
              chips
              closable-chips
              variant="outlined"
              :loading="testsLoading"
              class="mb-6"
            >
              <template #chip="{ props, item }">
                <VChip
                  v-bind="props"
                  color="primary"
                  variant="tonal"
                  class="font-weight-medium"
                >
                  {{ item.raw.name_ar }} [{{ item.raw.key }}]
                </VChip>
              </template>
            </VAutocomplete>

            <!-- Method 2: Quick Search Box & Interactive List -->
            <div class="border rounded pa-4 mb-6 bg-var-theme-background">
              <div class="d-flex align-center gap-2 mb-3">
                <VIcon icon="tabler-search" color="secondary" />
                <span class="text-subtitle-2 font-weight-bold">إضافة سريعة بالبحث المباشر:</span>
              </div>

              <AppTextField
                v-model="quickSearchQuery"
                placeholder="اكتب هنا اسم أي تحليل لتتمكن من إضافته فوراً بنقرة زر (مثال: سكر، غدة، CBC...)"
                clearable
                class="mb-3"
              />

              <!-- Quick Search Results -->
              <div v-if="quickFilteredTests.length > 0" class="border rounded bg-surface overflow-hidden">
                <VTable class="text-no-wrap" density="compact">
                  <thead>
                    <tr class="bg-light">
                      <th>الرمز (Key)</th>
                      <th>اسم التحليل</th>
                      <th>السعر الكلي للمريض</th>
                      <th class="text-end">الإجراء</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="test in quickFilteredTests" :key="test.id">
                      <td><VChip size="small" color="secondary" variant="tonal">{{ test.key }}</VChip></td>
                      <td class="font-weight-medium">
                        {{ test.name_ar }}
                        <span v-if="test.name_en" class="text-caption text-medium-emphasis">({{ test.name_en }})</span>
                      </td>
                      <td>{{ Number(test.total_price || test.price).toLocaleString() }} د.ع</td>
                      <td class="text-end">
                        <VBtn
                          :color="selectedTestIds.includes(test.id) ? 'success' : 'primary'"
                          :variant="selectedTestIds.includes(test.id) ? 'elevated' : 'tonal'"
                          size="small"
                          :prepend-icon="selectedTestIds.includes(test.id) ? 'tabler-check' : 'tabler-plus'"
                          @click="toggleTestSelection(test.id)"
                        >
                          {{ selectedTestIds.includes(test.id) ? 'تمت الإضافة (إزالة)' : 'إضافة للباقة' }}
                        </VBtn>
                      </td>
                    </tr>
                  </tbody>
                </VTable>
              </div>
              <div v-else-if="quickSearchQuery.trim()" class="text-center py-4 text-medium-emphasis text-caption">
                لا توجد نتائج مطابقة لبحثك في التحاليل المتاحة.
              </div>
            </div>

            <!-- Selected Tests Table / List -->
            <div v-if="selectedTestsList.length > 0">
              <div class="d-flex justify-space-between align-center mb-3">
                <span class="text-subtitle-1 font-weight-bold text-primary">
                  ✔ التحاليل المختارة داخل هذه الباقة حالياً ({{ selectedTestsList.length }} تحاليل):
                </span>
                <VBtn
                  variant="tonal"
                  size="small"
                  color="primary"
                  prepend-icon="tabler-calculator"
                  @click="applySumAsOriginalPrice"
                >
                  اعتماد المجموع كسعر أصلي للباقة
                </VBtn>
              </div>

              <VTable class="border rounded text-no-wrap mb-4">
                <thead>
                  <tr>
                    <th>الرمز</th>
                    <th>اسم التحليل</th>
                    <th>السعر الكلي للمريض</th>
                    <th class="text-end">حذف</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="test in selectedTestsList" :key="test.id">
                    <td><VChip size="small" variant="tonal" color="primary">{{ test.key }}</VChip></td>
                    <td class="font-weight-medium">{{ test.name_ar }} <span v-if="test.name_en" class="text-caption text-medium-emphasis">({{ test.name_en }})</span></td>
                    <td>{{ Number(test.total_price || test.price).toLocaleString() }} د.ع</td>
                    <td class="text-end">
                      <VBtn icon variant="text" size="small" color="error" title="إزالة من الباقة" @click="removeTestFromSelection(test.id)">
                        <VIcon icon="tabler-trash" />
                      </VBtn>
                    </td>
                  </tr>
                </tbody>
              </VTable>

              <!-- Sum bar -->
              <VAlert color="info" variant="tonal" class="d-flex align-center justify-space-between">
                <div class="d-flex align-center justify-space-between w-100 flex-wrap gap-2">
                  <span><strong>إجمالي السعر الكلي للمريض للتحاليل الفردية:</strong> يمكنك استخدامه كمرجع لتحديد سعر التخفيض الخاص بالباقة</span>
                  <strong class="text-h6 text-info">{{ sumOfSelectedTests.toLocaleString() }} د.ع</strong>
                </div>
              </VAlert>
            </div>

            <div v-else class="text-center py-6 border rounded border-dashed text-medium-emphasis">
              <VIcon icon="tabler-flask-off" size="36" class="mb-2" />
              <div>لم تقم باختيار أي تحليل بعد. استخدم قائمة البحث أو مربع البحث السريع أعلاه لإضافة التحاليل إلى الباقة.</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Right Column: Pricing, Sort & Status -->
      <VCol cols="12" md="4">
        <!-- Pricing Card -->
        <VCard title="التسعير والخصم" class="mb-6">
          <VCardText>
            <VRow>
              <VCol cols="12">
                <AppTextField
                  v-model="originalPrice"
                  type="number"
                  label="سعر العرض الأصلي (د.ع) *"
                  placeholder="مثال: 50000"
                  :error-messages="validationErrors.original_price"
                />
              </VCol>

              <VCol cols="12">
                <AppTextField
                  v-model="discountPrice"
                  type="number"
                  label="سعر التخفيض إن وجد (د.ع)"
                  placeholder="مثال: 35000"
                  :error-messages="validationErrors.discount_price"
                />
              </VCol>

              <VCol cols="12">
                <VAlert type="warning" variant="tonal" icon="tabler-discount-check" class="text-caption">
                  إذا تم تحديد <strong>سعر التخفيض</strong> وكان أقل من السعر الأصلي، فسوف يظهر في التطبيق أن الباقة عليها خصم حصري مع شطب السعر القديم!
                </VAlert>
              </VCol>
            </VRow>
          </VCardText>
        </VCard>

        <!-- Sort Order and Status Card -->
        <VCard title="الترتيب والتفعيل" class="mb-6">
          <VCardText>
            <VRow>
              <VCol cols="12">
                <AppTextField
                  v-model="sortOrder"
                  type="number"
                  label="ترتيب الظهور في شاشة العروض"
                  placeholder="1"
                  helper-text="الأرقام الأقل (1، 2، 3...) تظهر في أعلى شاشة التطبيق"
                />
              </VCol>

              <VCol cols="12">
                <VSwitch
                  v-model="isActive"
                  color="success"
                  label="تفعيل ونشر العرض في التطبيق"
                  hide-details
                />
                <div class="text-caption text-medium-emphasis mt-1">
                  {{ isActive ? 'سيظهر العرض للزوار في التطبيق مباشرة' : 'سيكون العرض مخفياً ولن يراه الزوار في التطبيق' }}
                </div>
              </VCol>
            </VRow>
          </VCardText>
        </VCard>

        <!-- Image Upload Card -->
        <VCard title="صورة الباقة أو العرض">
          <VCardText>
            <!-- 1. File Upload From Device -->
            <div class="mb-4">
              <label class="v-label mb-1 text-body-2 font-weight-medium">رفع صورة من جهازك (ملف)</label>
              <VFileInput
                v-model="fileInput"
                label="اختر صورة العرض من جهازك..."
                accept="image/*"
                prepend-icon=""
                prepend-inner-icon="tabler-upload"
                variant="outlined"
                :loading="imageUploading"
                @change="onImageFileChange"
              />
            </div>

            <!-- Loading Spinner / Bar during upload -->
            <div v-if="imageUploading" class="text-center py-3">
              <VProgressLinear indeterminate color="primary" class="mb-2" />
              <span class="text-caption text-primary font-weight-bold">جاري رفع ومعالجة الصورة للمعاينة والحفظ...</span>
            </div>



            <!-- 3. Live Preview Card -->
            <div v-if="imagePreview || image" class="text-center border rounded p-3 bg-var-theme-background">
              <div class="text-caption font-weight-bold text-success mb-2 d-flex align-center justify-center gap-1">
                <VIcon icon="tabler-check" size="16" />
                <span>معاينة الصورة الحالية (جاهزة للحفظ والتطبيق):</span>
              </div>
              <VImg :src="imagePreview || image" max-height="180" class="rounded mx-auto border" />
            </div>
            <div v-else class="text-center py-6 border rounded border-dashed text-medium-emphasis text-caption">
              <VIcon icon="tabler-photo-up" size="36" class="mb-1" />
              <div>اختر صورة من جهازك أو ضع رابطها لكي تظهر في بطاقة العرض في تطبيق الهاتف</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>
