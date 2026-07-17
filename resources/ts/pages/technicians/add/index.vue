<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// Form Fields
const name       = ref('')
const phone      = ref('')
const password   = ref('')
const showPass   = ref(false)
const address    = ref('')
const specialty  = ref('')
const hasTransport  = ref(false)
const hasEquipment  = ref(false)
const notes      = ref('')
const status     = ref<'active' | 'suspended' | 'on_leave'>('active')

// Images
const idFrontImage    = ref<string | null>(null)
const idBackImage     = ref<string | null>(null)
const districtIdImage = ref<string | null>(null)

// UI
const loading         = ref(false)
const errorMessage    = ref('')
const validationErrors = ref<Record<string, string[]>>({})

const statusOptions = [
  { title: 'نشط', value: 'active' },
  { title: 'موقوف', value: 'suspended' },
  { title: 'إجازة', value: 'on_leave' },
]

const specialtyOptions = [
  'سحب عينات',
  'تحليل مخبري',
  'رعاية منزلية',
  'تمريض',
  'أشعة',
  'أخرى',
]

// Image helpers
const imageToBase64 = (file: File): Promise<string> =>
  new Promise((resolve, reject) => {
    const reader = new FileReader()
    reader.onload = () => resolve(reader.result as string)
    reader.onerror = reject
    reader.readAsDataURL(file)
  })

const handleImagePick = async (
  field: 'idFrontImage' | 'idBackImage' | 'districtIdImage',
  event: Event
) => {
  const file = (event.target as HTMLInputElement).files?.[0]
  if (!file) return
  const base64 = await imageToBase64(file)
  if (field === 'idFrontImage') idFrontImage.value = base64
  else if (field === 'idBackImage') idBackImage.value = base64
  else districtIdImage.value = base64
}

const clearImage = (field: 'idFrontImage' | 'idBackImage' | 'districtIdImage') => {
  if (field === 'idFrontImage') idFrontImage.value = null
  else if (field === 'idBackImage') idBackImage.value = null
  else districtIdImage.value = null
}

// Submit
const submitTechnician = async () => {
  loading.value = true
  errorMessage.value = ''
  validationErrors.value = {}

  try {
    const res = await $api('/technicians', {
      method: 'POST',
      body: {
        name: name.value,
        phone: phone.value,
        password: password.value,
        address: address.value || null,
        specialty: specialty.value || null,
        has_transport: hasTransport.value,
        has_equipment: hasEquipment.value,
        id_front_image: idFrontImage.value || null,
        id_back_image: idBackImage.value || null,
        district_id_image: districtIdImage.value || null,
        notes: notes.value || null,
        status: status.value,
      },
    })

    if (res.status) {
      router.push('/technicians')
    } else {
      errorMessage.value = res.message || 'حدث خطأ أثناء حفظ الفني'
    }
  } catch (e: any) {
    if (e?.errors) validationErrors.value = e.errors
    else errorMessage.value = e?.message || 'تعذر الاتصال بالخادم'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div>
    <!-- Top Header -->
    <div class="d-flex flex-wrap justify-start justify-sm-space-between gap-y-4 gap-x-6 mb-6">
      <div class="d-flex flex-column justify-center">
        <div class="d-flex align-center gap-2 mb-1">
          <VBtn icon variant="tonal" size="small" color="primary" @click="router.push('/technicians')">
            <VIcon icon="tabler-arrow-right" />
          </VBtn>
          <h4 class="text-h4 font-weight-medium">إضافة فني ميداني جديد</h4>
        </div>
        <div class="text-body-1 text-medium-emphasis">تسجيل بيانات الفني الميداني ومؤهلاته</div>
      </div>
      <div class="d-flex gap-3 align-center">
        <VBtn variant="tonal" color="secondary" @click="router.push('/technicians')">إلغاء</VBtn>
        <VBtn color="primary" prepend-icon="tabler-check" :loading="loading" @click="submitTechnician">
          حفظ الفني
        </VBtn>
      </div>
    </div>

    <VAlert v-if="errorMessage" type="error" variant="tonal" class="mb-6" closable @click:close="errorMessage = ''">
      {{ errorMessage }}
    </VAlert>

    <VRow>
      <!-- Right Column: Personal Info + Status -->
      <VCol cols="12" md="8">

        <!-- Personal Info Card -->
        <VCard title="البيانات الشخصية والوظيفية" class="mb-6">
          <template #prepend>
            <VAvatar color="primary" variant="tonal" rounded size="42">
              <VIcon icon="tabler-user-circle" size="24" />
            </VAvatar>
          </template>
          <VCardText>
            <VRow>
              <VCol cols="12" md="6">
                <AppTextField
                  v-model="name"
                  label="اسم الفني *"
                  placeholder="الاسم الكامل"
                  prepend-inner-icon="tabler-user"
                  :error-messages="validationErrors.name"
                />
              </VCol>
              <VCol cols="12" md="6">
                <AppTextField
                  v-model="phone"
                  label="رقم الهاتف *"
                  placeholder="07700000000"
                  prepend-inner-icon="tabler-phone"
                  :error-messages="validationErrors.phone"
                />
              </VCol>
              <VCol cols="12" md="6">
                <AppTextField
                  v-model="password"
                  label="كلمة السر *"
                  placeholder="6 أحرف على الأقل"
                  prepend-inner-icon="tabler-lock"
                  :type="showPass ? 'text' : 'password'"
                  :append-inner-icon="showPass ? 'tabler-eye-off' : 'tabler-eye'"
                  :error-messages="validationErrors.password"
                  @click:append-inner="showPass = !showPass"
                />
              </VCol>
              <VCol cols="12" md="6">
                <AppSelect
                  v-model="specialty"
                  :items="specialtyOptions"
                  label="التخصص"
                  placeholder="اختر التخصص أو اتركه فارغاً"
                  clearable
                />
              </VCol>
              <VCol cols="12">
                <AppTextField
                  v-model="address"
                  label="عنوان السكن"
                  placeholder="المحافظة — الحي — الشارع"
                  prepend-inner-icon="tabler-map-pin"
                />
              </VCol>
              <VCol cols="12">
                <label class="v-label text-body-2 font-weight-medium mb-1">ملاحظات الفني</label>
                <VTextarea
                  v-model="notes"
                  placeholder="أي ملاحظات تخص الفني (مواعيد متاحة، مناطق التغطية، إلخ...)"
                  rows="3"
                />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>

        <!-- Capabilities Card -->
        <VCard title="المؤهلات والإمكانيات" class="mb-6">
          <template #prepend>
            <VAvatar color="info" variant="tonal" rounded size="42">
              <VIcon icon="tabler-tools" size="24" />
            </VAvatar>
          </template>
          <VCardText>
            <VRow>
              <VCol cols="12" md="6">
                <VCard variant="outlined" class="pa-4 h-100">
                  <div class="d-flex align-center justify-space-between">
                    <div class="d-flex align-center gap-3">
                      <VAvatar :color="hasTransport ? 'success' : 'secondary'" variant="tonal" size="44" rounded>
                        <VIcon icon="tabler-car" size="24" />
                      </VAvatar>
                      <div>
                        <div class="font-weight-medium text-body-1">وسيلة نقل</div>
                        <div class="text-caption text-medium-emphasis">يمتلك سيارة أو دراجة للتنقل</div>
                      </div>
                    </div>
                    <VSwitch v-model="hasTransport" color="success" hide-details />
                  </div>
                </VCard>
              </VCol>
              <VCol cols="12" md="6">
                <VCard variant="outlined" class="pa-4 h-100">
                  <div class="d-flex align-center justify-space-between">
                    <div class="d-flex align-center gap-3">
                      <VAvatar :color="hasEquipment ? 'success' : 'secondary'" variant="tonal" size="44" rounded>
                        <VIcon icon="tabler-briefcase-medical" size="24" />
                      </VAvatar>
                      <div>
                        <div class="font-weight-medium text-body-1">حقيبة ومعدات</div>
                        <div class="text-caption text-medium-emphasis">يمتلك أدوات السحب والفحص الميداني</div>
                      </div>
                    </div>
                    <VSwitch v-model="hasEquipment" color="success" hide-details />
                  </div>
                </VCard>
              </VCol>
            </VRow>
          </VCardText>
        </VCard>

        <!-- ID Documents Card -->
        <VCard title="صور وثائق الهوية">
          <template #prepend>
            <VAvatar color="warning" variant="tonal" rounded size="42">
              <VIcon icon="tabler-id" size="24" />
            </VAvatar>
          </template>
          <VCardText>
            <VRow>
              <!-- ID Front -->
              <VCol cols="12" md="4">
                <div class="text-subtitle-2 font-weight-medium mb-2">صورة الهوية (الوجه) *</div>
                <div
                  class="rounded border d-flex align-center justify-center position-relative overflow-hidden"
                  style="height: 180px; border-style: dashed !important; cursor: pointer;"
                  @click="($refs.idFrontInput as HTMLInputElement)?.click()"
                >
                  <img v-if="idFrontImage" :src="idFrontImage" class="w-100 h-100" style="object-fit: cover;" />
                  <div v-else class="text-center text-medium-emphasis">
                    <VIcon icon="tabler-upload" size="36" class="mb-2" />
                    <div class="text-caption">انقر لرفع الصورة</div>
                  </div>
                  <VBtn
                    v-if="idFrontImage"
                    icon="tabler-x"
                    color="error"
                    size="x-small"
                    class="position-absolute"
                    style="top: 6px; right: 6px;"
                    @click.stop="clearImage('idFrontImage')"
                  />
                </div>
                <input ref="idFrontInput" type="file" accept="image/*" class="d-none" @change="handleImagePick('idFrontImage', $event)" />
              </VCol>

              <!-- ID Back -->
              <VCol cols="12" md="4">
                <div class="text-subtitle-2 font-weight-medium mb-2">صورة الهوية (الظهر) *</div>
                <div
                  class="rounded border d-flex align-center justify-center position-relative overflow-hidden"
                  style="height: 180px; border-style: dashed !important; cursor: pointer;"
                  @click="($refs.idBackInput as HTMLInputElement)?.click()"
                >
                  <img v-if="idBackImage" :src="idBackImage" class="w-100 h-100" style="object-fit: cover;" />
                  <div v-else class="text-center text-medium-emphasis">
                    <VIcon icon="tabler-upload" size="36" class="mb-2" />
                    <div class="text-caption">انقر لرفع الصورة</div>
                  </div>
                  <VBtn
                    v-if="idBackImage"
                    icon="tabler-x"
                    color="error"
                    size="x-small"
                    class="position-absolute"
                    style="top: 6px; right: 6px;"
                    @click.stop="clearImage('idBackImage')"
                  />
                </div>
                <input ref="idBackInput" type="file" accept="image/*" class="d-none" @change="handleImagePick('idBackImage', $event)" />
              </VCol>

              <!-- District ID -->
              <VCol cols="12" md="4">
                <div class="text-subtitle-2 font-weight-medium mb-2">هوية الدائرة (اختياري)</div>
                <div
                  class="rounded border d-flex align-center justify-center position-relative overflow-hidden"
                  style="height: 180px; border-style: dashed !important; cursor: pointer;"
                  @click="($refs.districtIdInput as HTMLInputElement)?.click()"
                >
                  <img v-if="districtIdImage" :src="districtIdImage" class="w-100 h-100" style="object-fit: cover;" />
                  <div v-else class="text-center text-medium-emphasis">
                    <VIcon icon="tabler-upload" size="36" class="mb-2" />
                    <div class="text-caption">انقر لرفع الصورة</div>
                  </div>
                  <VBtn
                    v-if="districtIdImage"
                    icon="tabler-x"
                    color="error"
                    size="x-small"
                    class="position-absolute"
                    style="top: 6px; right: 6px;"
                    @click.stop="clearImage('districtIdImage')"
                  />
                </div>
                <input ref="districtIdInput" type="file" accept="image/*" class="d-none" @change="handleImagePick('districtIdImage', $event)" />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Left Column: Status + Summary -->
      <VCol cols="12" md="4">
        <!-- Status Card -->
        <VCard title="الحالة الوظيفية" class="mb-6">
          <template #prepend>
            <VAvatar color="secondary" variant="tonal" rounded size="42">
              <VIcon icon="tabler-activity" size="24" />
            </VAvatar>
          </template>
          <VCardText>
            <AppSelect
              v-model="status"
              :items="statusOptions"
              item-title="title"
              item-value="value"
              label="الحالة الحالية للفني"
            />
            <VDivider class="my-4" />
            <div class="d-flex flex-column gap-2">
              <div class="d-flex align-center gap-2 pa-3 rounded" :style="{ background: status === 'active' ? 'rgba(var(--v-theme-success), 0.12)' : 'transparent' }">
                <VIcon icon="tabler-circle-check" color="success" size="20" />
                <div>
                  <div class="text-body-2 font-weight-medium">نشط</div>
                  <div class="text-caption text-medium-emphasis">يستقبل مهام ميدانية</div>
                </div>
              </div>
              <div class="d-flex align-center gap-2 pa-3 rounded" :style="{ background: status === 'on_leave' ? 'rgba(var(--v-theme-warning), 0.12)' : 'transparent' }">
                <VIcon icon="tabler-calendar-off" color="warning" size="20" />
                <div>
                  <div class="text-body-2 font-weight-medium">إجازة</div>
                  <div class="text-caption text-medium-emphasis">مؤقتاً غير متاح</div>
                </div>
              </div>
              <div class="d-flex align-center gap-2 pa-3 rounded" :style="{ background: status === 'suspended' ? 'rgba(var(--v-theme-error), 0.12)' : 'transparent' }">
                <VIcon icon="tabler-ban" color="error" size="20" />
                <div>
                  <div class="text-body-2 font-weight-medium">موقوف</div>
                  <div class="text-caption text-medium-emphasis">تم إيقاف التعاون</div>
                </div>
              </div>
            </div>
          </VCardText>
        </VCard>

        <!-- Capabilities Summary -->
        <VCard title="ملخص المؤهلات">
          <template #prepend>
            <VAvatar color="info" variant="tonal" rounded size="42">
              <VIcon icon="tabler-list-check" size="24" />
            </VAvatar>
          </template>
          <VCardText>
            <div class="d-flex justify-space-between align-center mb-3">
              <span class="text-body-2 text-medium-emphasis">التخصص:</span>
              <span class="font-weight-medium">{{ specialty || '—' }}</span>
            </div>
            <div class="d-flex justify-space-between align-center mb-3">
              <span class="text-body-2 text-medium-emphasis">وسيلة نقل:</span>
              <VChip :color="hasTransport ? 'success' : 'default'" variant="tonal" size="small">
                {{ hasTransport ? 'نعم' : 'لا' }}
              </VChip>
            </div>
            <div class="d-flex justify-space-between align-center mb-3">
              <span class="text-body-2 text-medium-emphasis">حقيبة ومعدات:</span>
              <VChip :color="hasEquipment ? 'success' : 'default'" variant="tonal" size="small">
                {{ hasEquipment ? 'نعم' : 'لا' }}
              </VChip>
            </div>
            <VDivider class="my-3" />
            <div class="d-flex justify-space-between align-center">
              <span class="text-body-2 text-medium-emphasis">صور الهوية:</span>
              <span class="text-body-2 font-weight-medium">
                {{ [idFrontImage, idBackImage, districtIdImage].filter(Boolean).length }}/3 مرفوعة
              </span>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>
