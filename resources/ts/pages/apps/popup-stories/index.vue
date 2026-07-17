<script setup lang="ts">
import { $api } from '@/utils/api'
import { ref, onMounted, computed } from 'vue'

// ── State ────────────────────────────────────────────
const loading = ref(false)
const stories = ref<any[]>([])
const summary = ref({
  total: 0,
  active: 0,
  inactive: 0,
  total_views: 0,
  total_clicks: 0,
})

const isDialogVisible = ref(false)
const isSubmitting = ref(false)
const editingStoryId = ref<number | null>(null)

// Form data
const form = ref({
  title: '',
  duration_seconds: 6,
  display_frequency: 'once_per_day',
  button_text: 'احجز الآن 🎁',
  button_link_type: 'link',
  button_link_target: '',
  is_active: true,
})

const imageFile = ref<File | null>(null)
const imagePreviewUrl = ref<string>('')

// ── Progress bar simulation for Live Phone Preview ───
const simulatedProgress = ref(0)
let progressInterval: any = null

const startProgressSimulation = () => {
  if (progressInterval) clearInterval(progressInterval)
  simulatedProgress.value = 0
  const stepMs = 100
  const totalMs = (form.value.duration_seconds || 6) * 1000
  const increment = (stepMs / totalMs) * 100

  progressInterval = setInterval(() => {
    simulatedProgress.value += increment
    if (simulatedProgress.value >= 100) {
      simulatedProgress.value = 0
    }
  }, stepMs)
}

watch(
  () => [form.value.duration_seconds, isDialogVisible.value],
  () => {
    if (isDialogVisible.value) {
      startProgressSimulation()
    } else if (progressInterval) {
      clearInterval(progressInterval)
    }
  },
  { immediate: true },
)

// ── Fetch Stories ────────────────────────────────────
const fetchStories = async () => {
  loading.value = true
  try {
    const res = await $api('/popup-stories')
    if (res?.status) {
      stories.value = res.stories || []
      if (res.summary) summary.value = res.summary
    }
  } catch (e) {
    console.error('[PopupStories] fetch error:', e)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchStories()
})

// ── Handlers ─────────────────────────────────────────
const openAddDialog = () => {
  editingStoryId.value = null
  form.value = {
    title: '',
    duration_seconds: 6,
    display_frequency: 'once_per_day',
    button_text: 'احجز الآن 🎁',
    button_link_type: 'link',
    button_link_target: '',
    is_active: true,
  }
  imageFile.value = null
  imagePreviewUrl.value = 'https://images.unsplash.com/photo-1579154204601-01588f351e67?auto=format&fit=crop&w=600&q=80'
  isDialogVisible.value = true
}

const openEditDialog = (story: any) => {
  editingStoryId.value = story.id
  form.value = {
    title: story.title || '',
    duration_seconds: story.duration_seconds || 6,
    display_frequency: story.display_frequency || 'once_per_day',
    button_text: story.button_text || '',
    button_link_type: story.button_link_type || 'none',
    button_link_target: story.button_link_target || '',
    is_active: story.is_active ?? true,
  }
  imageFile.value = null
  imagePreviewUrl.value = story.image_url || ''
  isDialogVisible.value = true
}

const handleFileChange = (e: Event) => {
  const input = e.target as HTMLInputElement
  if (input.files && input.files[0]) {
    imageFile.value = input.files[0]
    imagePreviewUrl.value = URL.createObjectURL(input.files[0])
  }
}

const saveStory = async () => {
  if (!form.value.title) {
    alert('يرجى إدخال عنوان الإعلان')
    return
  }
  if (!editingStoryId.value && !imageFile.value && !imagePreviewUrl.value) {
    alert('يرجى رفع صورة الستوري الطولية')
    return
  }

  isSubmitting.value = true
  try {
    const formData = new FormData()
    formData.append('title', form.value.title)
    formData.append('duration_seconds', String(form.value.duration_seconds))
    formData.append('display_frequency', form.value.display_frequency)
    formData.append('button_text', form.value.button_text || '')
    formData.append('button_link_type', form.value.button_link_type || 'none')
    formData.append('button_link_target', form.value.button_link_target || '')
    formData.append('is_active', form.value.is_active ? '1' : '0')

    if (imageFile.value) {
      formData.append('image', imageFile.value)
    }

    if (editingStoryId.value) {
      formData.append('_method', 'PUT')
      await $api(`/popup-stories/${editingStoryId.value}`, {
        method: 'POST',
        body: formData,
      })
    } else {
      await $api('/popup-stories', {
        method: 'POST',
        body: formData,
      })
    }

    isDialogVisible.value = false
    fetchStories()
  } catch (e: any) {
    console.error('[PopupStories] save error:', e)
    alert('حدث خطأ أثناء الحفظ، يرجى التأكد من البيانات والصورة المرفوعة')
  } finally {
    isSubmitting.value = false
  }
}

const toggleStatus = async (story: any) => {
  try {
    const res = await $api(`/popup-stories/${story.id}/toggle-active`, { method: 'PATCH' })
    if (res?.status) {
      story.is_active = res.story.is_active
    }
  } catch (e) {
    console.error('[PopupStories] toggle error:', e)
  }
}

const deleteStory = async (story: any) => {
  if (!confirm(`هل أنت متأكد من حذف إعلان الستوري (${story.title})؟`)) return
  try {
    const res = await $api(`/popup-stories/${story.id}`, { method: 'DELETE' })
    if (res?.status) {
      stories.value = stories.value.filter(s => s.id !== story.id)
      fetchStories()
    }
  } catch (e) {
    console.error('[PopupStories] delete error:', e)
  }
}
</script>

<template>
  <div class="popup-stories-management">
    <!-- ══ Hero Header ══ -->
    <div class="d-flex flex-wrap align-center justify-space-between gap-4 mb-6">
      <div>
        <div class="d-flex align-center gap-2 mb-1">
          <VIcon icon="tabler-device-mobile-message" size="28" color="primary" />
          <h4 class="text-h4 font-weight-bold text-high-emphasis">
            إعلانات الستوري الميدانية (Pop-up Story Ads) 📱
          </h4>
        </div>
        <p class="text-body-1 text-medium-emphasis mb-0">
          إدارة إعلانات البوب أب الطولية (9:16) التي تغطي شاشة تطبيق الموبايل مع عداد ثوانٍ تنازلي مثل ستوريات إنستغرام
        </p>
      </div>

      <div class="d-flex align-center gap-3">
        <VBtn
          variant="tonal"
          color="secondary"
          prepend-icon="tabler-refresh"
          :loading="loading"
          @click="fetchStories"
        >
          تحديث
        </VBtn>
        <VBtn
          color="primary"
          prepend-icon="tabler-plus"
          @click="openAddDialog"
        >
          إضافة ستوري بوب أب جديد
        </VBtn>
      </div>
    </div>

    <!-- ══ Summary Cards Row ══ -->
    <VRow class="match-height mb-6">
      <VCol cols="12" sm="6" md="3">
        <VCard class="border h-100">
          <VCardText class="d-flex align-center justify-space-between pa-4">
            <div>
              <span class="text-caption text-medium-emphasis font-weight-bold">إجمالي الستوريات</span>
              <h4 class="text-h4 font-weight-bold text-primary mt-1">{{ summary.total }}</h4>
            </div>
            <VAvatar size="44" color="primary" variant="tonal" rounded>
              <VIcon icon="tabler-stack-2" size="24" />
            </VAvatar>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" sm="6" md="3">
        <VCard class="border h-100">
          <VCardText class="d-flex align-center justify-space-between pa-4">
            <div>
              <span class="text-caption text-medium-emphasis font-weight-bold">الستوريات الفعالة الآن</span>
              <h4 class="text-h4 font-weight-bold text-success mt-1">{{ summary.active }}</h4>
            </div>
            <VAvatar size="44" color="success" variant="tonal" rounded>
              <VIcon icon="tabler-circle-check" size="24" />
            </VAvatar>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" sm="6" md="3">
        <VCard class="border h-100">
          <VCardText class="d-flex align-center justify-space-between pa-4">
            <div>
              <span class="text-caption text-medium-emphasis font-weight-bold">إجمالي مشاهدات الموبايل</span>
              <h4 class="text-h4 font-weight-bold text-info mt-1">{{ summary.total_views?.toLocaleString() || 0 }}</h4>
            </div>
            <VAvatar size="44" color="info" variant="tonal" rounded>
              <VIcon icon="tabler-eye" size="24" />
            </VAvatar>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" sm="6" md="3">
        <VCard class="border h-100">
          <VCardText class="d-flex align-center justify-space-between pa-4">
            <div>
              <span class="text-caption text-medium-emphasis font-weight-bold">إجمالي النقرات والحجوزات</span>
              <h4 class="text-h4 font-weight-bold text-warning mt-1">{{ summary.total_clicks?.toLocaleString() || 0 }}</h4>
            </div>
            <VAvatar size="44" color="warning" variant="tonal" rounded>
              <VIcon icon="tabler-pointer" size="24" />
            </VAvatar>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- ══ Stories Table ══ -->
    <VCard class="border">
      <VCardItem class="pb-3">
        <template #title>
          <span class="text-h6 font-weight-bold">قائمة الإعلانات الميدانية الطولية</span>
        </template>
        <template #subtitle>
          الإعلانات التي تنبثق فوق شاشة الهوم للمراجعين عند فتح تطبيق Healthy Lab
        </template>
      </VCardItem>

      <VDivider />

      <VTable class="text-no-wrap">
        <thead>
          <tr>
            <th>الصورة والترتيب</th>
            <th>عنوان الإعلان</th>
            <th>مدة الستوري</th>
            <th>تكرار الظهور</th>
            <th>زر الإجراء (CTA)</th>
            <th>المشاهدات والنقرات</th>
            <th>الحالة</th>
            <th>التحكم</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading && !stories.length">
            <td colspan="8" class="text-center py-8">
              <VProgressCircular indeterminate color="primary" />
            </td>
          </tr>
          <tr v-else-if="!stories.length">
            <td colspan="8" class="text-center py-12 text-disabled">
              <VIcon icon="tabler-device-mobile-off" size="42" class="mb-2 opacity-50" />
              <div class="text-body-1 font-weight-semibold">لا توجد إعلانات بوب أب ستوري مسجلة حالياً</div>
              <p class="text-caption mb-0">اضغط على "إضافة ستوري بوب أب جديد" للبدء</p>
            </td>
          </tr>
          <tr v-for="story in stories" :key="story.id" class="hover:bg-var-theme-surface">
            <!-- Image Thumbnail -->
            <td>
              <div class="d-flex align-center gap-3">
                <div class="story-thumb-wrapper">
                  <img :src="story.image_url" alt="Story" class="story-thumb-img" />
                </div>
                <VChip size="x-small" variant="tonal" color="primary">#{{ story.sort_order }}</VChip>
              </div>
            </td>

            <!-- Title -->
            <td>
              <div class="font-weight-bold text-body-1 text-high-emphasis">{{ story.title }}</div>
              <span class="text-xs text-disabled">{{ story.created_at }}</span>
            </td>

            <!-- Duration -->
            <td>
              <VChip color="info" size="small" variant="tonal" class="font-weight-bold">
                <VIcon icon="tabler-clock" size="14" class="me-1" />
                {{ story.duration_seconds }} ثوانٍ
              </VChip>
            </td>

            <!-- Frequency -->
            <td>
              <VChip
                :color="story.display_frequency === 'always' ? 'warning' : 'primary'"
                size="small"
                variant="flat"
              >
                {{ story.display_frequency_label }}
              </VChip>
            </td>

            <!-- CTA -->
            <td>
              <div v-if="story.button_text && story.button_link_type !== 'none'" class="d-flex align-center gap-1.5">
                <VChip color="success" size="small" variant="tonal" class="font-weight-bold">
                  {{ story.button_text }}
                </VChip>
                <span class="text-caption text-disabled">({{ story.button_link_type }})</span>
              </div>
              <span v-else class="text-caption text-disabled">— بدون رابط</span>
            </td>

            <!-- Analytics -->
            <td>
              <div class="d-flex flex-column text-caption">
                <span class="font-weight-semibold text-info">👁️ {{ story.views_count?.toLocaleString() }} مشاهدة</span>
                <span class="font-weight-semibold text-warning">👆 {{ story.clicks_count?.toLocaleString() }} نقرة ({{ story.ctr_percentage }}%)</span>
              </div>
            </td>

            <!-- Status Switch -->
            <td>
              <VSwitch
                :model-value="story.is_active"
                color="success"
                hide-details
                @change="toggleStatus(story)"
              />
            </td>

            <!-- Actions -->
            <td>
              <div class="d-flex align-center gap-1">
                <IconBtn size="small" color="primary" @click="openEditDialog(story)">
                  <VIcon icon="tabler-edit" size="18" />
                </IconBtn>
                <IconBtn size="small" color="error" @click="deleteStory(story)">
                  <VIcon icon="tabler-trash" size="18" />
                </IconBtn>
              </div>
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <!-- ══ ADD / EDIT DIALOG + LIVE INSTAGRAM STORY PHONE SIMULATOR ══ -->
    <VDialog v-model="isDialogVisible" max-width="980" persistent>
      <VCard class="pa-2 pa-sm-4">
        <!-- Dialog Header -->
        <VCardItem class="pb-3 border-b">
          <template #title>
            <div class="d-flex align-center gap-2">
              <VIcon :icon="editingStoryId ? 'tabler-edit' : 'tabler-plus'" size="24" color="primary" />
              <span class="text-h5 font-weight-bold">
                {{ editingStoryId ? 'تعديل إعلان الستوري' : 'إضافة إعلان ستوري بوب أب جديد' }}
              </span>
            </div>
          </template>
          <template #append>
            <IconBtn size="small" @click="isDialogVisible = false">
              <VIcon icon="tabler-x" size="20" />
            </IconBtn>
          </template>
        </VCardItem>

        <VCardText class="pt-5">
          <VRow>
            <!-- ── LEFT/TOP COLUMN: FORM SETTINGS ── -->
            <VCol cols="12" md="6">
              <div class="form-section d-flex flex-column gap-4 pr-md-2">
                <!-- Title -->
                <VTextField
                  v-model="form.title"
                  label="عنوان الستوري في الشريط العلوي (يظهر للمراجع وللإدارة)"
                  placeholder="مثال: إعلان Healthy Lab المميز ⭐ أو خصم الصيف 🎁"
                  variant="outlined"
                />

                <!-- Duration Slider -->
                <div>
                  <div class="d-flex justify-space-between align-center mb-1">
                    <span class="text-body-2 font-weight-bold">مدة الستوري بالثواني (Timer Countdown)</span>
                    <VChip size="small" color="primary" variant="flat" class="font-weight-bold">
                      {{ form.duration_seconds }} ثوانٍ
                    </VChip>
                  </div>
                  <VSlider
                    v-model="form.duration_seconds"
                    :min="3"
                    :max="20"
                    :step="1"
                    color="primary"
                    thumb-label
                    hide-details
                  />
                  <span class="text-xs text-disabled">الوقت الذي يستغرقه شريط الستوري قبل الإغلاق التلقائي فوق شاشة الهوم</span>
                </div>

                <!-- Display Frequency -->
                <VSelect
                  v-model="form.display_frequency"
                  :items="[
                    { title: 'مرة واحدة يومياً للمراجع (الموصى به ⭐ حتى لا يزعج الزبون)', value: 'once_per_day' },
                    { title: 'في كل مرة يفتح التطبيق / الجلسة', value: 'once_per_session' },
                    { title: 'دائماً في كل دخول لشاشة الهوم', value: 'always' },
                  ]"
                  label="تكرار ظهور الستوري للمراجع"
                  variant="outlined"
                />

                <!-- Image Upload -->
                <div>
                  <span class="text-body-2 font-weight-bold d-block mb-1">صورة الستوري الطولية (9:16 Aspect Ratio)</span>
                  <div class="upload-box d-flex align-center justify-center pa-4 rounded border border-dashed text-center">
                    <input
                      type="file"
                      accept="image/*"
                      class="upload-input"
                      @change="handleFileChange"
                    />
                    <div>
                      <VIcon icon="tabler-cloud-upload" size="36" color="primary" class="mb-1" />
                      <div class="text-body-2 font-weight-semibold">اضغط لاختيار صورة الإعلان الطولية</div>
                      <span class="text-xs text-disabled">يُفضل مقاس 1080x1920 بكسل لتغطي معظم الشاشة بوضوح فائق</span>
                    </div>
                  </div>
                </div>

                <VDivider />

                <!-- CTA Button Settings -->
                <div class="text-body-2 font-weight-bold text-primary">زر الإجراء والحجز (Call To Action)</div>
                <VRow>
                  <VCol cols="12" sm="6">
                    <VTextField
                      v-model="form.button_text"
                      label="نص الزر في أسفل الستوري"
                      placeholder="مثال: احجز الباقة الآن 🎁"
                      variant="outlined"
                    />
                  </VCol>
                  <VCol cols="12" sm="6">
                    <VSelect
                      v-model="form.button_link_type"
                      :items="[
                        { title: 'مع رابط (زر انتقال أسفل الستوري)', value: 'link' },
                        { title: 'بدون رابط (إعلان صورة فقط)', value: 'none' },
                      ]"
                      label="نوع الإجراء عند مشاهدة الستوري"
                      variant="outlined"
                    />
                  </VCol>
                  <VCol v-if="form.button_link_type !== 'none'" cols="12">
                    <VTextField
                      v-model="form.button_link_target"
                      label="الرابط المطلوب أو وجهة الانتقال (باقة / فحص / عرض / رابط واتساب)"
                      placeholder="مثال: https://... أو اسم/معرف الباقة المطلوب فتحها"
                      variant="outlined"
                    />
                  </VCol>
                </VRow>

                <!-- Active status -->
                <VSwitch
                  v-model="form.is_active"
                  label="تفعيل الإعلان وعرضه فوراً في تطبيق الموبايل"
                  color="success"
                  hide-details
                />
              </div>
            </VCol>

            <!-- ── RIGHT COLUMN: LIVE INSTAGRAM STORY PHONE SIMULATOR ── -->
            <VCol cols="12" md="6" class="d-flex flex-column align-center justify-center bg-var-theme-surface rounded-lg pa-4 border">
              <div class="text-body-2 font-weight-bold text-high-emphasis mb-1 text-center">
                معاينة شاشة الموبايل الحية (Live Phone Mockup) 📱
              </div>
              <span class="text-xs text-medium-emphasis mb-3 text-center">
                هكذا يظهر الإعلان تماماً للمراجع فوق الصفحة الرئيسية بنمط ستوري إنستغرام ويغطي معظم الشاشة
              </span>

              <!-- Phone Frame -->
              <div class="phone-frame">
                <!-- Top Notch / Speaker -->
                <div class="phone-notch" />

                <!-- Simulated App Home Background (dimmed) -->
                <div class="phone-app-bg">
                  <div class="phone-app-header">
                    <span class="font-weight-bold text-primary">Healthy Lab 🧪</span>
                    <div class="d-flex gap-1">
                      <span class="header-icon" />
                      <span class="header-icon" />
                    </div>
                  </div>
                  <div class="phone-app-dummy-cards">
                    <div class="dummy-card" />
                    <div class="dummy-card" />
                    <div class="dummy-card" />
                  </div>
                </div>

                <!-- Story Backdrop Overlay (70-80% dark overlay) -->
                <div class="story-backdrop">
                  <!-- Top Instagram Story Progress Bar -->
                  <div class="story-top-header">
                    <div class="story-progress-bar">
                      <div class="story-progress-fill" :style="{ width: `${simulatedProgress}%` }" />
                    </div>
                    <div class="d-flex align-center justify-space-between mt-2 px-1">
                      <span class="text-xs text-white font-weight-bold drop-shadow d-flex align-center gap-1.5">
                        <span class="story-avatar-dot" />
                        {{ form.title || 'إعلان Healthy Lab المميز ⭐' }}
                      </span>
                      <div class="story-close-btn" title="إغلاق">✕</div>
                    </div>
                  </div>

                  <!-- Full Portrait Story Image Container covering >80% of screen -->
                  <div class="story-image-container">
                    <img
                      v-if="imagePreviewUrl"
                      :src="imagePreviewUrl"
                      alt="Preview"
                      class="story-main-image"
                    />
                    <div v-else class="story-placeholder">
                      <VIcon icon="tabler-photo" size="48" color="white" class="opacity-40 mb-2" />
                      <span class="text-caption text-white opacity-80">صورة الستوري تظهر هنا وتغطي الشاشة بالكامل</span>
                    </div>

                    <!-- CTA Glowing Button floating at bottom of story -->
                    <div v-if="form.button_text && form.button_link_type !== 'none'" class="story-cta-container">
                      <div class="story-cta-button">
                        {{ form.button_text }}
                        <span class="cta-arrow">←</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </VCol>
          </VRow>
        </VCardText>

        <!-- Dialog Footer -->
        <VCardActions class="pt-4 border-t justify-end gap-3">
          <VBtn
            variant="tonal"
            color="secondary"
            @click="isDialogVisible = false"
          >
            إلغاء
          </VBtn>
          <VBtn
            color="primary"
            :loading="isSubmitting"
            @click="saveStory"
          >
            حفظ وتفعيل إعلان الستوري
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>

<style lang="scss" scoped>
/* ── Stories Table Thumbnail ── */
.story-thumb-wrapper {
  width: 42px;
  height: 64px;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid rgba(var(--v-border-color), 0.3);
  background: #1e1e2d;
  flex-shrink: 0;
}

.story-thumb-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* ── Upload Box ── */
.upload-box {
  position: relative;
  border-color: rgba(var(--v-theme-primary), 0.4) !important;
  background: rgba(var(--v-theme-primary), 0.03);
  cursor: pointer;
  transition: all 0.2s;
  &:hover {
    background: rgba(var(--v-theme-primary), 0.07);
    border-color: rgb(var(--v-theme-primary)) !important;
  }
}

.upload-input {
  position: absolute;
  inset: 0;
  opacity: 0;
  cursor: pointer;
  width: 100%;
  height: 100%;
}

/* ══ LIVE PHONE SIMULATOR (IG STORY MOCKUP) ══ */
.phone-frame {
  width: 290px;
  height: 580px;
  background: #0f172a;
  border-radius: 40px;
  border: 8px solid #334155;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4), inset 0 0 0 2px #475569;
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.phone-notch {
  width: 110px;
  height: 22px;
  background: #334155;
  border-radius: 0 0 16px 16px;
  position: absolute;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  z-index: 20;
}

/* Simulated underlying App Home */
.phone-app-bg {
  position: absolute;
  inset: 0;
  background: #f8fafc;
  padding: 36px 16px 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  opacity: 0.35;
  filter: blur(2px);
}

.phone-app-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-icon {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: #cbd5e1;
}

.phone-app-dummy-cards {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.dummy-card {
  height: 100px;
  border-radius: 12px;
  background: #e2e8f0;
}

/* Story Dark Backdrop Overlay */
.story-backdrop {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.82);
  z-index: 10;
  display: flex;
  flex-direction: column;
  padding: 30px 14px 18px;
}

/* Top Instagram Story Progress Bar */
.story-top-header {
  width: 100%;
  z-index: 15;
  margin-bottom: 12px;
}

.story-progress-bar {
  width: 100%;
  height: 3.5px;
  background: rgba(255, 255, 255, 0.28);
  border-radius: 4px;
  overflow: hidden;
}

.story-progress-fill {
  height: 100%;
  background: #ffffff;
  box-shadow: 0 0 6px rgba(255, 255, 255, 0.8);
  transition: width 0.1s linear;
}

.story-avatar-dot {
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
  display: inline-block;
  box-shadow: 0 0 4px rgba(220, 39, 67, 0.8);
  flex-shrink: 0;
}

.story-close-btn {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.18);
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: bold;
  cursor: pointer;
  backdrop-filter: blur(4px);
}

/* Story Main Portrait Container (covering >82% of phone screen height) */
.story-image-container {
  flex: 1;
  width: 100%;
  border-radius: 20px;
  overflow: hidden;
  position: relative;
  background: #1e293b;
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
}

.story-main-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.story-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 20px;
  text-align: center;
}

/* CTA Floating Button at bottom of story image */
.story-cta-container {
  position: absolute;
  bottom: 20px;
  left: 0;
  right: 0;
  display: flex;
  justify-content: center;
  padding: 0 16px;
  z-index: 18;
}

.story-cta-button {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: #ffffff;
  font-weight: 700;
  font-size: 14px;
  padding: 12px 24px;
  border-radius: 30px;
  box-shadow: 0 8px 20px rgba(16, 185, 129, 0.45), 0 0 0 3px rgba(255, 255, 255, 0.25);
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  transition: transform 0.2s;
  animation: ctaPulse 2s infinite;
}

@keyframes ctaPulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.05); }
  100% { transform: scale(1); }
}

.drop-shadow {
  text-shadow: 0 1px 3px rgba(0, 0, 0, 0.8);
}
</style>
