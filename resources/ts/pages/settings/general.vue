<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { $api } from '@/utils/api'

definePage({
  meta: {
    title: 'الإعدادات العامّة (General Settings)',
  },
})

const loading = ref(false)
const saving = ref(false)

const settings = ref({
  lab_name: 'Healthy Lab - هيلثي لاب للتحاليل المخبرية',
  support_phone: '07700000000',
  support_email: 'support@healthylab.iq',
  package_offers_active: true,
  chat_active: true,
  maintenance_mode: false,
  welcome_message: 'مرحباً بكم في تطبيق Healthy Lab للخدمات المخبرية والتحاليل المنزلية المتكاملة',
  work_hours: 'يومياً من الساعة 8:00 صباحاً حتى 10:00 مساءً',
})

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
    const res = await $api('/settings/general')
    if (res?.status && res.data) {
      settings.value = { ...settings.value, ...res.data }
    }
  } catch (e) {
    console.error('Error fetching general settings:', e)
  } finally {
    loading.value = false
  }
}

const saveSettings = async () => {
  saving.value = true
  try {
    const res = await $api('/settings/general', {
      method: 'POST',
      body: { ...settings.value },
    })
    if (res?.status && res.data) {
      settings.value = { ...settings.value, ...res.data }
    }
    showToast('تم حفظ الإعدادات العامّة للمختبر بنجاح', 'success')
  } catch (e: any) {
    showToast('تعذر حفظ بعض الإعدادات، يرجى المحاولة لاحقاً', 'error')
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  fetchSettings()
})
</script>

<template>
  <div class="settings-general-page">
    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardItem>
            <template #prepend>
              <VAvatar color="primary" variant="tonal" rounded size="44">
                <VIcon icon="tabler-settings" size="26" />
              </VAvatar>
            </template>
            <VCardTitle class="text-h5 font-weight-bold">
              الإعدادات العامّة للنظام والمختبر
            </VCardTitle>
            <VCardSubtitle>
              التحكم بإعدادات تطبيق الموبايل، معلومات التواصل الرسمي، وحالة التشغيل العامّة
            </VCardSubtitle>
          </VCardItem>

          <VDivider />

          <VCardText v-if="loading" class="text-center py-8">
            <VProgressCircular indeterminate color="primary" size="48" />
            <div class="mt-3 text-body-1 text-medium-emphasis">جاري تحميل بيانات الإعدادات...</div>
          </VCardText>

          <VCardText v-else class="pt-6">
            <VForm @submit.prevent="saveSettings">
              <VRow>
                <!-- معلومات المختبر الأساسية -->
                <VCol cols="12">
                  <div class="d-flex align-center gap-2 mb-2">
                    <VIcon icon="tabler-building-hospital" color="primary" size="22" />
                    <span class="text-h6 font-weight-bold">معلومات المختبر الرسمية</span>
                  </div>
                </VCol>

                <VCol cols="12" md="6">
                  <AppTextField
                    v-model="settings.lab_name"
                    label="اسم النظام / المختبر الرسمى"
                    placeholder="Healthy Lab"
                  />
                </VCol>

                <VCol cols="12" md="6">
                  <AppTextField
                    v-model="settings.support_phone"
                    label="رقم الهاتف المعتمد للخدمات والدعم"
                    placeholder="077XXXXXXXX"
                    dir="ltr"
                  />
                </VCol>

                <VCol cols="12" md="6">
                  <AppTextField
                    v-model="settings.support_email"
                    label="البريد الإلكتروني للدعم والمراسلات"
                    placeholder="support@healthylab.iq"
                    dir="ltr"
                  />
                </VCol>

                <VCol cols="12" md="6">
                  <AppTextField
                    v-model="settings.work_hours"
                    label="أوقات العمل المعتمدة للزيارات المنزلية"
                    placeholder="يومياً من 8 صباحاً حتى 10 مساءً"
                  />
                </VCol>

                <VCol cols="12">
                  <AppTextarea
                    v-model="settings.welcome_message"
                    label="رسالة الترحيب الرئيسية في تطبيق الموبايل"
                    rows="2"
                  />
                </VCol>

                <VCol cols="12"><VDivider class="my-3" /></VCol>

                <!-- أزرار التحكم وحالة التشغيل -->
                <VCol cols="12">
                  <div class="d-flex align-center gap-2 mb-3">
                    <VIcon icon="tabler-switches" color="warning" size="24" />
                    <span class="text-h6 font-weight-bold">التحكم الفوري بأقسام وخدمات التطبيق (الموبايل)</span>
                  </div>
                </VCol>

                <!-- 1. الباقات والعروض -->
                <VCol cols="12">
                  <VCard
                    variant="outlined"
                    :color="settings.package_offers_active ? 'primary' : 'secondary'"
                    :class="['pa-5 rounded-xl transition-all', settings.package_offers_active ? 'border-primary border-2 bg-var-theme-background' : 'opacity-75']"
                  >
                    <div class="d-flex flex-column flex-md-row align-md-center justify-space-between gap-4">
                      <div class="d-flex align-start gap-3">
                        <VAvatar :color="settings.package_offers_active ? 'primary' : 'secondary'" variant="tonal" rounded size="48">
                          <VIcon icon="tabler-packages" size="28" />
                        </VAvatar>
                        <div>
                          <div class="d-flex align-center gap-2 flex-wrap mb-1">
                            <span class="text-h6 font-weight-bold">قسم الباقات والعروض المخبرية</span>
                            <VChip
                              :color="settings.package_offers_active ? 'success' : 'secondary'"
                              size="small"
                              class="font-weight-bold px-3"
                              :prepend-icon="settings.package_offers_active ? 'tabler-circle-check' : 'tabler-circle-x'"
                            >
                              {{ settings.package_offers_active ? 'مفعّل ويظهر للمرضى في الموبايل' : 'معطّل ومخفي من التطبيق' }}
                            </VChip>
                          </div>
                          <div class="text-body-2 text-medium-emphasis">
                            إظهار أو إخفاء تبويب العروض والباقات الشاملة بالكامل من الصفحة الرئيسية لتطبيق المرضى.
                          </div>
                        </div>
                      </div>
                      <div class="d-flex align-center justify-end">
                        <VSwitch
                          v-model="settings.package_offers_active"
                          color="primary"
                          inset
                          :label="settings.package_offers_active ? 'تشغيل (ON)' : 'إيقاف (OFF)'"
                          class="font-weight-bold text-h6"
                          hide-details
                        />
                      </div>
                    </div>
                  </VCard>
                </VCol>

                <!-- 2. الدردشة والدعم المباشر -->
                <VCol cols="12">
                  <VCard
                    variant="outlined"
                    :color="settings.chat_active ? 'info' : 'secondary'"
                    :class="['pa-5 rounded-xl transition-all', settings.chat_active ? 'border-info border-2 bg-var-theme-background' : 'opacity-75']"
                  >
                    <div class="d-flex flex-column flex-md-row align-md-center justify-space-between gap-4">
                      <div class="d-flex align-start gap-3">
                        <VAvatar :color="settings.chat_active ? 'info' : 'secondary'" variant="tonal" rounded size="48">
                          <VIcon icon="tabler-message-dots" size="28" />
                        </VAvatar>
                        <div>
                          <div class="d-flex align-center gap-2 flex-wrap mb-1">
                            <span class="text-h6 font-weight-bold">قسم الدردشة والدعم المباشر (Live Chat)</span>
                            <VChip
                              :color="settings.chat_active ? 'info' : 'secondary'"
                              size="small"
                              class="font-weight-bold px-3"
                              :prepend-icon="settings.chat_active ? 'tabler-circle-check' : 'tabler-circle-x'"
                            >
                              {{ settings.chat_active ? 'مفعّل ومتاح للمحادثة الفورية' : 'معطّل ومخفي عن الزبون' }}
                            </VChip>
                          </div>
                          <div class="text-body-2 text-medium-emphasis">
                            إظهار أو إخفاء زر وشاشة الدردشة والمحادثة الفورية بالكامل في تطبيق المرضى بحيث لا يراها الزبون نهائياً عند الإيقاف.
                          </div>
                        </div>
                      </div>
                      <div class="d-flex align-center justify-end">
                        <VSwitch
                          v-model="settings.chat_active"
                          color="info"
                          inset
                          :label="settings.chat_active ? 'تشغيل (ON)' : 'إيقاف (OFF)'"
                          class="font-weight-bold text-h6"
                          hide-details
                        />
                      </div>
                    </div>
                  </VCard>
                </VCol>

                <!-- 3. وضع الصيانة للتطبيق -->
                <VCol cols="12">
                  <VCard
                    variant="outlined"
                    :color="settings.maintenance_mode ? 'error' : 'secondary'"
                    :class="['pa-5 rounded-xl transition-all', settings.maintenance_mode ? 'border-error border-2 bg-var-theme-background' : '']"
                  >
                    <div class="d-flex flex-column flex-md-row align-md-center justify-space-between gap-4">
                      <div class="d-flex align-start gap-3">
                        <VAvatar :color="settings.maintenance_mode ? 'error' : 'secondary'" variant="tonal" rounded size="48">
                          <VIcon icon="tabler-alert-triangle" size="28" />
                        </VAvatar>
                        <div>
                          <div class="d-flex align-center gap-2 flex-wrap mb-1">
                            <span class="text-h6 font-weight-bold">وضع الصيانة للتطبيق (Maintenance Mode)</span>
                            <VChip
                              :color="settings.maintenance_mode ? 'error' : 'success'"
                              size="small"
                              class="font-weight-bold px-3"
                              :prepend-icon="settings.maintenance_mode ? 'tabler-shield-lock' : 'tabler-circle-check'"
                            >
                              {{ settings.maintenance_mode ? '🚨 الصيانة مفعلة (التطبيق متوقف مؤقتاً)' : '🟢 التطبيق يعمل بشكل طبيعي' }}
                            </VChip>
                          </div>
                          <div class="text-body-2 text-medium-emphasis">
                            إيقاف استقبال طلبات التحاليل المنزلية مؤقتاً وإظهار شاشة الصيانة والاعتذار للمرضى عند قيامك بتحديث السيرفر أو الأسعار.
                          </div>
                        </div>
                      </div>
                      <div class="d-flex align-center justify-end">
                        <VSwitch
                          v-model="settings.maintenance_mode"
                          color="error"
                          inset
                          :label="settings.maintenance_mode ? 'تفعيل الصيانة (ON)' : 'إيقاف الصيانة (OFF)'"
                          class="font-weight-bold text-h6"
                          hide-details
                        />
                      </div>
                    </div>
                  </VCard>
                </VCol>

                <VCol cols="12" class="d-flex justify-end gap-3 mt-4">
                  <VBtn
                    color="primary"
                    type="submit"
                    :loading="saving"
                    prepend-icon="tabler-device-floppy"
                  >
                    حفظ التغييرات
                  </VBtn>
                </VCol>
              </VRow>
            </VForm>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <VSnackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      location="top end"
      timeout="3000"
    >
      {{ snackbar.text }}
    </VSnackbar>
  </div>
</template>
