<script setup lang="ts">
import { ref, onMounted } from 'vue'

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

const errorMessage = ref('')
const successMessage = ref('')

const fetchSettings = async () => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const res = await $api('/settings/firebase')
    if (res.status) {
      settings.value = res.data
    } else {
      errorMessage.value = res.message || 'حدث خطأ أثناء جلب الإعدادات'
    }
  } catch (error: any) {
    errorMessage.value = error?.message || 'تعذر الاتصال بالخادم'
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
  errorMessage.value = ''
  successMessage.value = ''
  
  const formData = new FormData()
  formData.append('firebase_enabled', settings.value.firebase_enabled ? '1' : '0')
  
  if (fileInput.value) {
    formData.append('credentials_file', fileInput.value)
  }

  try {
    const res = await $api('/settings/firebase', {
      method: 'POST',
      body: formData
    })
    
    if (res.status) {
      successMessage.value = 'تم حفظ إعدادات Firebase بنجاح'
      fileInput.value = null
      await fetchSettings()
    } else {
      errorMessage.value = res.message || 'حدث خطأ أثناء الحفظ'
    }
  } catch (error: any) {
    errorMessage.value = error?.message || 'تعذر الاتصال بالخادم أثناء الحفظ'
  } finally {
    isLoading.value = false
  }
}

const testNotification = async () => {
  if (!testNotificationForm.value.fcm_token) {
    errorMessage.value = 'يرجى إدخال Token الجهاز'
    return
  }

  isLoading.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const res = await $api('/settings/firebase/test', {
      method: 'POST',
      body: testNotificationForm.value
    })
    
    if (res.status) {
      successMessage.value = res.message || 'تم إرسال الإشعار التجريبي بنجاح'
    } else {
      errorMessage.value = res.message || 'فشل إرسال الإشعار'
    }
  } catch (error: any) {
    errorMessage.value = error?.message || 'حدث خطأ أثناء إرسال الإشعار'
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchSettings()
})
</script>

<template>
  <div>
    <VAlert v-if="successMessage" type="success" variant="tonal" class="mb-6" closable @click:close="successMessage = ''">
      {{ successMessage }}
    </VAlert>

    <VAlert v-if="errorMessage" type="error" variant="tonal" class="mb-6" closable @click:close="errorMessage = ''">
      {{ errorMessage }}
    </VAlert>

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
                  <VBtn type="submit" color="primary" :loading="isLoading" prepend-icon="tabler-device-floppy">
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
  </div>
</template>
