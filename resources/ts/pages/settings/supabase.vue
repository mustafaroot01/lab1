<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { VForm } from 'vuetify/components/VForm'

const refForm = ref<VForm>()
const isLoading = ref(false)
const isTesting = ref(false)
const testResult = ref<{ status: boolean; message: string } | null>(null)
const snackbar = ref({ show: false, message: '', color: 'success' })

const settings = ref({
  supabase_anon_key: '',
  supabase_url: '',
  is_configured: false,
})

const fetchSettings = async () => {
  isLoading.value = true
  try {
    const res = await $api('/settings/supabase')
    if (res.status) {
      settings.value = res.data
    }
  } catch (error) {
    console.error('Error fetching settings:', error)
  } finally {
    isLoading.value = false
  }
}

const saveSettings = async () => {
  const { valid } = await refForm.value?.validate() || { valid: false }
  
  if (!valid) return
  
  isLoading.value = true
  try {
    const res = await $api('/settings/supabase', {
      method: 'POST',
      body: JSON.stringify({ supabase_anon_key: settings.value.supabase_anon_key }),
    })
    
    if (res.status) {
      snackbar.value = { show: true, message: res.message, color: 'success' }
      await fetchSettings()
    }
  } catch (error: any) {
    snackbar.value = { show: true, message: error.response?.data?.message || 'حدث خطأ أثناء حفظ الإعدادات', color: 'error' }
  } finally {
    isLoading.value = false
  }
}

const testConnection = async () => {
  isTesting.value = true
  testResult.value = null
  try {
    const res = await $api('/settings/supabase/test', {
      method: 'POST',
    })
    testResult.value = {
      status: true,
      message: res.message,
    }
    snackbar.value = { show: true, message: res.message, color: 'success' }
  } catch (error: any) {
    testResult.value = {
      status: false,
      message: error.response?.data?.message || 'حدث خطأ في الاتصال',
    }
    snackbar.value = { show: true, message: testResult.value.message, color: 'error' }
  } finally {
    isTesting.value = false
  }
}

onMounted(() => {
  fetchSettings()
})
</script>

<template>
  <div>
    <h4 class="text-h4 mb-4">إعدادات ربط Supabase (الدردشة)</h4>
    <p class="text-body-1 mb-6">
      تُستخدم هذه الإعدادات لتفعيل نظام الدردشة المباشرة (Real-time). المفاتيح السرية (Service Role Key) يجب أن توضع في ملف <code>.env</code> على السيرفر حصراً لحمايتها.
    </p>

    <VRow>
      <VCol cols="12" md="8">
        <VCard :loading="isLoading">
          <VCardItem>
            <VCardTitle>إعدادات الاتصال الآمنة</VCardTitle>
          </VCardItem>

          <VCardText>
            <VAlert
              v-if="settings.is_configured"
              type="success"
              variant="tonal"
              class="mb-6"
            >
              تم العثور على <strong>SUPABASE_SERVICE_ROLE_KEY</strong> و <strong>SUPABASE_URL</strong> في إعدادات السيرفر بنجاح. الاتصال مؤمن.
            </VAlert>
            <VAlert
              v-else
              type="error"
              variant="tonal"
              class="mb-6"
            >
              بيانات الاتصال مفقودة! الرجاء إضافة <strong>SUPABASE_SERVICE_ROLE_KEY</strong> و <strong>SUPABASE_URL</strong> إلى ملف <code>.env</code>.
            </VAlert>

            <VForm
              ref="refForm"
              @submit.prevent="saveSettings"
            >
              <VRow>
                <VCol cols="12">
                  <AppTextField
                    v-model="settings.supabase_url"
                    label="Project URL (من الـ .env)"
                    placeholder="https://xxxx.supabase.co"
                    readonly
                    disabled
                    hint="هذا الرابط يتم قراءته من السيرفر ولا يمكن تعديله من هنا."
                    persistent-hint
                  />
                </VCol>

                <VCol cols="12">
                  <AppTextField
                    v-model="settings.supabase_anon_key"
                    label="Supabase Anon Key (Public)"
                    placeholder="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
                    :rules="[requiredValidator]"
                    hint="هذا المفتاح سيتم إرساله للواجهات وتطبيقات الموبايل للاشتراك بقنوات الـ Real-time."
                    persistent-hint
                  />
                </VCol>

                <VCol cols="12" class="d-flex gap-4">
                  <VBtn
                    type="submit"
                    :loading="isLoading"
                  >
                    حفظ التغييرات
                  </VBtn>

                  <VBtn
                    color="secondary"
                    variant="tonal"
                    :loading="isTesting"
                    @click="testConnection"
                    :disabled="!settings.is_configured"
                  >
                    فحص الاتصال مع السيرفر
                  </VBtn>
                </VCol>
              </VRow>
            </VForm>

            <VAlert
              v-if="testResult"
              :type="testResult.status ? 'success' : 'error'"
              variant="outlined"
              class="mt-6"
            >
              {{ testResult.message }}
            </VAlert>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" md="4">
        <VCard title="معلومات الربط">
          <VCardText>
            <p>
              <strong>لماذا لا يوجد Service Role Key هنا؟</strong><br>
              المفتاح السري خطير جداً ويمنح صلاحية كاملة على قاعدة البيانات. كإجراء أمني احترافي، يمنع إدخاله في الواجهات. يجب أن يُضاف مباشرة داخل ملف البيئة الخاص بالسيرفر.
            </p>
            <VDivider class="my-4" />
            <p>
              <strong>الـ Anon Key:</strong><br>
              هو مفتاح عام (Public) يُستخدم للسماح للمستخدمين بالاتصال بخدمة الـ WebSocket، ولكن تحميهم قواعد الـ Row Level Security (RLS) الخاصة بك.
            </p>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <VSnackbar
      v-model="snackbar.show"
      location="top"
      :color="snackbar.color"
      timeout="3000"
    >
      {{ snackbar.message }}
    </VSnackbar>
  </div>
</template>
