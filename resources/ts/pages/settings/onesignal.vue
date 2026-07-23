<script setup lang="ts">
import { ref, onMounted } from 'vue'

const isLoading = ref(false)
const isTesting = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const appData = ref({
  onesignal_app_id: '',
  onesignal_rest_api_key: '',
  onesignal_enabled: true,
})

const testPayload = ref({
  title: 'إشعار تجريبي',
  body: 'هذا إشعار تجريبي للتأكد من نجاح ربط OneSignal',
})

const fetchSettings = async () => {
  isLoading.value = true
  try {
    const res = await $api('/settings/onesignal')
    if (res.status) {
      appData.value = res.data
    }
  } catch (error) {
    console.error('Failed to load settings', error)
  }
  isLoading.value = false
}

const saveSettings = async () => {
  isLoading.value = true
  successMessage.value = ''
  errorMessage.value = ''
  try {
    const res = await $api('/settings/onesignal', {
      method: 'POST',
      body: JSON.stringify(appData.value),
    })
    
    if (res.status) {
      successMessage.value = res.message
    }
  } catch (error) {
    console.error('Failed to save settings', error)
    errorMessage.value = 'حدث خطأ أثناء الحفظ'
  }
  isLoading.value = false
}

const testNotification = async () => {
  isTesting.value = true
  successMessage.value = ''
  errorMessage.value = ''
  try {
    const res = await $api('/settings/onesignal/test', {
      method: 'POST',
      body: JSON.stringify(testPayload.value),
    })

    if (res.status) {
      successMessage.value = res.message
    }
  } catch (error: any) {
    console.error('Test failed', error)
    errorMessage.value = error?.response?.data?.message || 'فشل إرسال الإشعار. تأكد من صحة المفاتيح (App ID و API Key).'
  }
  isTesting.value = false
}

onMounted(() => {
  fetchSettings()
})
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VAlert v-if="successMessage" type="success" variant="tonal" class="mb-4" closable @click:close="successMessage = ''">
        {{ successMessage }}
      </VAlert>
      <VAlert v-if="errorMessage" type="error" variant="tonal" class="mb-4" closable @click:close="errorMessage = ''">
        {{ errorMessage }}
      </VAlert>
    </VCol>

    <VCol cols="12" md="8">
      <VCard title="إعدادات إشعارات OneSignal">
        <VCardText>
          <VForm @submit.prevent="saveSettings">
            <VRow>
              <VCol cols="12">
                <AppTextField
                  v-model="appData.onesignal_app_id"
                  label="App ID"
                  placeholder="e.g. 8d3..."
                  hint="معرف التطبيق الخاص بك في OneSignal"
                  persistent-hint
                />
              </VCol>

              <VCol cols="12">
                <AppTextField
                  v-model="appData.onesignal_rest_api_key"
                  label="REST API Key"
                  placeholder="e.g. MWY..."
                  hint="مفتاح الـ API الخاص بـ OneSignal"
                  persistent-hint
                  type="password"
                />
              </VCol>

              <VCol cols="12">
                <VSwitch
                  v-model="appData.onesignal_enabled"
                  label="تفعيل الإشعارات"
                />
              </VCol>

              <VCol cols="12" class="d-flex gap-4">
                <VBtn type="submit" :loading="isLoading">
                  حفظ الإعدادات
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="4">
      <VCard title="اختبار الإرسال">
        <VCardText>
          <p class="text-body-2 mb-4">
            تأكد من حفظ المفاتيح أولاً قبل تجربة إرسال الإشعار لتجنب ظهور أي أخطاء.
          </p>

          <VForm @submit.prevent="testNotification">
            <VRow>
              <VCol cols="12">
                <AppTextField
                  v-model="testPayload.title"
                  label="عنوان الإشعار"
                />
              </VCol>
              <VCol cols="12">
                <AppTextarea
                  v-model="testPayload.body"
                  label="محتوى الإشعار"
                  rows="3"
                />
              </VCol>
              <VCol cols="12">
                <VBtn
                  color="success"
                  type="submit"
                  :loading="isTesting"
                  variant="tonal"
                  block
                >
                  <VIcon start icon="tabler-send" />
                  إرسال إشعار تجريبي
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
