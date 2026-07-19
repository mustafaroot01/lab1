<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useApi } from '@/composables/useApi'
import { useSnackbar } from '@/composables/useSnackbar'

const api = useApi()
const snackbar = useSnackbar()

const settings = ref({
  firebase_enabled: false,
  has_credentials: false
})

const fileInput = ref<File | null>(null)
const isLoading = ref(false)

const testNotificationForm = ref({
  fcm_token: '',
  title: 'رسالة تجريبية',
  body: 'هذا إشعار تجريبي من نظام الإدارة للتأكد من عمل Firebase بنجاح.'
})

const fetchSettings = async () => {
  isLoading.value = true
  try {
    const response = await api.get('/settings/firebase')
    settings.value = response.data.data
  } catch (error) {
    snackbar.show('حدث خطأ أثناء جلب الإعدادات', 'error')
  } finally {
    isLoading.value = false
  }
}

const handleFileUpload = (event: any) => {
  const file = event.target.files[0]
  if (file) {
    fileInput.value = file
  }
}

const saveSettings = async () => {
  isLoading.value = true
  const formData = new FormData()
  formData.append('firebase_enabled', settings.value.firebase_enabled ? '1' : '0')
  
  if (fileInput.value) {
    formData.append('credentials_file', fileInput.value)
  }

  try {
    await api.post('/settings/firebase', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    snackbar.show('تم حفظ إعدادات Firebase بنجاح', 'success')
    fileInput.value = null
    await fetchSettings()
  } catch (error) {
    snackbar.show('حدث خطأ أثناء الحفظ', 'error')
  } finally {
    isLoading.value = false
  }
}

const testNotification = async () => {
  if (!testNotificationForm.value.fcm_token) {
    snackbar.show('يرجى إدخال Token الجهاز', 'error')
    return
  }

  isLoading.value = true
  try {
    const res = await api.post('/settings/firebase/test', testNotificationForm.value)
    if (res.data.status) {
      snackbar.show(res.data.message, 'success')
    } else {
      snackbar.show(res.data.message, 'error')
    }
  } catch (error: any) {
    snackbar.show(error.response?.data?.message || 'حدث خطأ أثناء إرسال الإشعار', 'error')
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchSettings()
})
</script>

<template>
  <VRow>
    <VCol cols="12" md="6">
      <VCard title="إعدادات Firebase" :loading="isLoading">
        <VCardText>
          <VForm @submit.prevent="saveSettings">
            <VRow>
              <VCol cols="12">
                <VSwitch
                  v-model="settings.firebase_enabled"
                  label="تفعيل إشعارات Push Notifications"
                  color="primary"
                />
              </VCol>

              <VCol cols="12">
                <VAlert
                  :type="settings.has_credentials ? 'success' : 'warning'"
                  variant="tonal"
                  class="mb-4"
                >
                  حالة ملف الاعتماد (Credentials):
                  <strong>{{ settings.has_credentials ? 'متوفر' : 'غير متوفر' }}</strong>
                </VAlert>

                <VFileInput
                  label="تحديث ملف firebase-credentials.json"
                  accept=".json"
                  prepend-icon="tabler-file-upload"
                  @change="handleFileUpload"
                  hint="يرجى رفع ملف JSON الخاص بحساب الخدمة (Service Account)"
                  persistent-hint
                />
              </VCol>

              <VCol cols="12" class="d-flex gap-4">
                <VBtn type="submit" color="primary" :loading="isLoading">
                  حفظ الإعدادات
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="6">
      <VCard title="اختبار الإشعارات (Test Notification)">
        <VCardText>
          <p class="mb-4 text-body-2 text-medium-emphasis">
            يمكنك إرسال إشعار تجريبي إلى جهاز معين للتأكد من نجاح عملية الربط قبل تفعيل النظام بالكامل.
          </p>
          <VForm @submit.prevent="testNotification">
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="testNotificationForm.fcm_token"
                  label="Device FCM Token"
                  placeholder="ادخل التوكن الخاص بالجهاز هنا"
                  required
                />
              </VCol>

              <VCol cols="12" md="6">
                <VTextField
                  v-model="testNotificationForm.title"
                  label="عنوان الإشعار"
                  required
                />
              </VCol>

              <VCol cols="12" md="6">
                <VTextField
                  v-model="testNotificationForm.body"
                  label="نص الإشعار"
                  required
                />
              </VCol>

              <VCol cols="12">
                <VBtn
                  type="submit"
                  color="success"
                  variant="tonal"
                  prepend-icon="tabler-send"
                  :loading="isLoading"
                  :disabled="!settings.has_credentials"
                >
                  إرسال الإشعار
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
