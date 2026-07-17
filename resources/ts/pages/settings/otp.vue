<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { $api } from '@/utils/api'

definePage({
  meta: {
    title: 'إعدادات تحقق الواتساب (OTP Settings)',
  },
})

const loading = ref(false)
const saving = ref(false)
const testing = ref(false)

const provider = ref('otpiq')
const otpiqKey = ref('')
const arqamKey = ref('')
const isConfigured = ref(false)
const showOtpiqKey = ref(false)
const showArqamKey = ref(false)

const testPhone = ref('07710030147')

const snackbar = ref({
  show: false,
  color: 'success',
  text: '',
})

const showToast = (message: string, color = 'success') => {
  snackbar.value = { show: true, color, text: message }
}

const fetchSettings = async () => {
  loading.value = true
  try {
    const res = await $api('/settings/otp')
    if (res?.status) {
      provider.value = res.otp_provider || 'otpiq'
      otpiqKey.value = res.otpiq_api_key || ''
      arqamKey.value = res.arqam_api_key || ''
      isConfigured.value = Boolean(res.is_configured)
    }
  } catch (e: any) {
    showToast('تعذر جلب إعدادات OTP', 'error')
  } finally {
    loading.value = false
  }
}

const saveSettings = async () => {
  saving.value = true
  try {
    const res = await $api('/settings/otp', {
      method: 'POST',
      body: {
        otp_provider: provider.value,
        otpiq_api_key: otpiqKey.value,
        arqam_api_key: arqamKey.value,
      },
    })

    if (res?.status) {
      isConfigured.value = Boolean(res.is_configured)
      showToast(res.message || 'تم حفظ الإعدادات بنجاح', 'success')
    } else {
      showToast(res?.message || 'تعذر حفظ الإعدادات', 'error')
    }
  } catch (e: any) {
    showToast(e?.data?.message || 'حدث خطأ أثناء حفظ الإعدادات', 'error')
  } finally {
    saving.value = false
  }
}

const testSending = async () => {
  if (!testPhone.value) {
    showToast('يرجى إدخال رقم الهاتف للاختبار', 'warning')
    return
  }

  testing.value = true
  try {
    const res = await $api('/settings/otp/test-send', {
      method: 'POST',
      body: { phone: testPhone.value },
    })

    if (res?.status) {
      showToast(res.message || 'تم إرسال رسالة الاختبار بنجاح عبر الواتساب!', 'success')
    } else {
      showToast(res?.message || 'فشل إرسال رسالة الاختبار', 'error')
    }
  } catch (e: any) {
    showToast(e?.data?.message || 'تعذر إرسال رسالة الاختبار', 'error')
  } finally {
    testing.value = false
  }
}

onMounted(() => {
  fetchSettings()
})
</script>

<template>
  <div>
    <VSnackbar
      v-model="snackbar.show"
      location="top"
      :color="snackbar.color"
      timeout="3500"
    >
      {{ snackbar.text }}
    </VSnackbar>

    <!-- عنوان الصفحة -->
    <VRow class="mb-4">
      <VCol cols="12" class="d-flex align-center justify-space-between flex-wrap gap-4">
        <div>
          <h4 class="text-h4 font-weight-bold mb-1 d-flex align-center gap-2">
            <VIcon icon="tabler-brand-whatsapp" color="success" size="32" />
            إعدادات بوابة التحقق عبر الواتساب (OTP)
          </h4>
          <p class="text-body-1 text-medium-emphasis mb-0">
            إدارة مفتاح الربط وتخزينه في النظام لإرسال رموز التحقق لعملاء الموبايل عبر الواتساب
          </p>
        </div>

        <VChip
          :color="isConfigured ? 'success' : 'warning'"
          variant="tonal"
          size="large"
          class="font-weight-bold"
        >
          <VIcon
            :icon="isConfigured ? 'tabler-circle-check' : 'tabler-alert-triangle'"
            start
          />
          {{ isConfigured ? 'خدمة الرسائل مفعّلة ومربوطة' : 'بانتظار إعداد مفتاح API' }}
        </VChip>
      </VCol>
    </VRow>

    <VRow>
      <!-- بطاقة إعداد وحفظ المفتاح -->
      <VCol cols="12" md="7">
        <VCard :loading="loading">
          <VCardItem>
            <VCardTitle class="d-flex align-center gap-2">
              <VIcon icon="tabler-key" size="24" color="primary" />
              مفتاح بوابة الرسائل (OTP API KEY)
            </VCardTitle>
            <VCardSubtitle>
              يتم حفظ المفتاح مباشرة في قاعدة البيانات
            </VCardSubtitle>
          </VCardItem>

          <VDivider />

          <VCardText class="pt-6">
            <VRow>
              <VCol cols="12">
                <div class="text-subtitle-1 font-weight-bold mb-3">
                  اختر الخدمة المشغلة حالياً (Active Provider):
                </div>
                <VRadioGroup
                  v-model="provider"
                  inline
                  class="mb-4"
                >
                  <VRadio
                    label="OTPIQ (سيرفر otpiq.com مباشر + تحقق محلي)"
                    value="otpiq"
                    color="primary"
                  />
                  <VRadio
                    label="أرقام Tech (سيرفر arqam.tech مباشر + تحقق سيرفر)"
                    value="arqam"
                    color="primary"
                  />
                </VRadioGroup>
              </VCol>

              <!-- حقل مفتاح OTPIQ -->
              <VCol
                v-if="provider === 'otpiq'"
                cols="12"
              >
                <AppTextField
                  v-model="otpiqKey"
                  label="مفتاح OTPIQ API Key (Authorization Bearer)"
                  placeholder="ضع مفتاح OTPIQ هنا..."
                  :type="showOtpiqKey ? 'text' : 'password'"
                  :append-inner-icon="showOtpiqKey ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="showOtpiqKey = !showOtpiqKey"
                />
                <div class="text-caption text-medium-emphasis mt-2">
                  يتم حفظ المفتاح والاتصال مباشرة بـ api.otpiq.com وتوليد الرقم من لوحة التحكم محلياً.
                </div>
              </VCol>

              <!-- حقل مفتاح أرقام -->
              <VCol
                v-if="provider === 'arqam'"
                cols="12"
              >
                <AppTextField
                  v-model="arqamKey"
                  label="مفتاح أرقام Arqam API Key (X-API-Key)"
                  placeholder="ضع مفتاح أرقام هنا (مثال: otplive_...)"
                  :type="showArqamKey ? 'text' : 'password'"
                  :append-inner-icon="showArqamKey ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="showArqamKey = !showArqamKey"
                />
                <div class="text-caption text-medium-emphasis mt-2">
                  يتم حفظ المفتاح والاتصال مباشرة بسيرفرات أرقام (otp.arqam.tech) للإرسال والتحقق الفوري.
                </div>
              </VCol>
            </VRow>
          </VCardText>

          <VDivider />

          <VCardActions class="pa-4 justify-end">
            <VBtn
              color="primary"
              variant="elevated"
              prepend-icon="tabler-device-floppy"
              :loading="saving"
              @click="saveSettings"
            >
              حفظ المفتاح
            </VBtn>
          </VCardActions>
        </VCard>
      </VCol>

      <!-- بطاقة فحص واختبار الإرسال -->
      <VCol cols="12" md="5">
        <VCard>
          <VCardItem>
            <VCardTitle class="d-flex align-center gap-2">
              <VIcon icon="tabler-send" size="24" color="success" />
              فحص الربط وإرسال رسالة اختبار
            </VCardTitle>
            <VCardSubtitle>
              تأكد من وصول كود التحقق لرقم هاتف تجريبي عبر الواتساب
            </VCardSubtitle>
          </VCardItem>

          <VDivider />

          <VCardText class="pt-6">
            <VRow>
              <VCol cols="12">
                <AppTextField
                  v-model="testPhone"
                  label="رقم الهاتف للتجربة"
                  placeholder="075xxxxxxxxx أو +96475xxxxxxxxx"
                  prepend-inner-icon="tabler-phone"
                />
              </VCol>

              <VCol cols="12">
                <VAlert
                  icon="tabler-info-circle"
                  color="info"
                  variant="tonal"
                  density="compact"
                >
                  سيتولى النظام تحويل الرقم تلقائياً لصيغة العراق (+964) وإرسال كود تحقق مكون من 6 أرقام عبر الواتساب.
                </VAlert>
              </VCol>
            </VRow>
          </VCardText>

          <VDivider />

          <VCardActions class="pa-4 justify-end">
            <VBtn
              color="success"
              variant="elevated"
              prepend-icon="tabler-brand-whatsapp"
              :loading="testing"
              @click="testSending"
            >
              إرسال رسالة تجريبية
            </VBtn>
          </VCardActions>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>
