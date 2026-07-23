<script setup lang="ts">
import { ref, onMounted } from 'vue'

const loading = ref(false)
const saving = ref(false)
const snackbar = ref(false)
const snackbarText = ref('')
const snackbarColor = ref('success')

const form = ref({
  default_grace_distance: 50,
  cache_ttl: 3600,
  log_enabled: true,
  slow_request_ms: 30,
  log_grace_matches: true,
  log_no_matches: true,
  log_all: false,
  simulator_enabled: true,
})

const fetchSettings = async () => {
  loading.value = true
  try {
    const res = await $api('/coverage-settings')
    form.value = { ...form.value, ...res }
  } catch (err) {
    showToast('فشل في جلب الإعدادات', 'error')
  } finally {
    loading.value = false
  }
}

const saveSettings = async () => {
  saving.value = true
  try {
    const res = await $api('/coverage-settings', { method: 'PUT', body: form.value })
    showToast(res.message || 'تم حفظ الإعدادات بنجاح')
  } catch (err) {
    showToast('فشل في حفظ الإعدادات', 'error')
  } finally {
    saving.value = false
  }
}

const clearCache = async () => {
  try {
    const res = await $api('/coverage-settings/clear-cache', { method: 'POST' })
    showToast(res.message || 'تم تفريغ الكاش بنجاح')
  } catch (err) {
    showToast('فشل في تفريغ الكاش', 'error')
  }
}

const showToast = (text: string, color = 'success') => {
  snackbarText.value = text
  snackbarColor.value = color
  snackbar.value = true
}

onMounted(() => {
  fetchSettings()
})
</script>

<template>
  <div>
    <VRow>
      <VCol cols="12" md="8">
        <VCard title="إعدادات محرك التغطية الجغرافية" :loading="loading">
          <VCardText>
            <VForm @submit.prevent="saveSettings">
              <VRow>
                <!-- General Settings -->
                <VCol cols="12">
                  <h6 class="text-h6 mb-3">الإعدادات العامة</h6>
                </VCol>
                
                <VCol cols="12" md="6">
                  <AppTextField
                    v-model.number="form.default_grace_distance"
                    label="مسافة السماح الافتراضية (بالمتر)"
                    type="number"
                    hint="المسافة التي يتم احتسابها كـ Grace Match"
                    persistent-hint
                  />
                </VCol>
                
                <VCol cols="12" md="6">
                  <AppTextField
                    v-model.number="form.cache_ttl"
                    label="عمر الكاش للمناطق (بالثانية)"
                    type="number"
                    hint="كم ثانية يتم الاحتفاظ بالمناطق في الذاكرة"
                    persistent-hint
                  />
                </VCol>
                
                <VCol cols="12" md="6">
                  <VSwitch
                    v-model="form.simulator_enabled"
                    label="تفعيل أداة المحاكاة (Simulator)"
                    color="primary"
                  />
                </VCol>
                
                <VCol cols="12">
                  <VDivider class="my-4" />
                </VCol>

                <!-- Smart Logging Settings -->
                <VCol cols="12">
                  <div class="d-flex align-center justify-space-between mb-3">
                    <h6 class="text-h6">إعدادات التسجيل الذكي (Smart Logging)</h6>
                    <VSwitch
                      v-model="form.log_enabled"
                      label="تفعيل نظام السجلات"
                      color="primary"
                    />
                  </div>
                </VCol>
                
                <VCol cols="12" md="6">
                  <AppTextField
                    v-model.number="form.slow_request_ms"
                    label="حد الطلبات البطيئة (ms)"
                    type="number"
                    hint="سيتم تسجيل أي طلب يستغرق أطول من هذا الوقت"
                    persistent-hint
                    :disabled="!form.log_enabled"
                  />
                </VCol>
                
                <VCol cols="12" md="6">
                  <VSwitch
                    v-model="form.log_grace_matches"
                    label="تسجيل تطابقات السماح (Grace Matches)"
                    color="primary"
                    :disabled="!form.log_enabled"
                  />
                </VCol>
                
                <VCol cols="12" md="6">
                  <VSwitch
                    v-model="form.log_no_matches"
                    label="تسجيل الطلبات المرفوضة (No Matches)"
                    color="primary"
                    :disabled="!form.log_enabled"
                  />
                </VCol>
                
                <VCol cols="12" md="6">
                  <VSwitch
                    v-model="form.log_all"
                    label="تسجيل كل شيء (Log All)"
                    color="error"
                    hint="تحذير: لا ينصح بتفعيله في بيئة الإنتاج"
                    persistent-hint
                    :disabled="!form.log_enabled"
                  />
                </VCol>
              </VRow>

              <VRow class="mt-6">
                <VCol cols="12" class="d-flex gap-4">
                  <VBtn type="submit" color="primary" :loading="saving">
                    حفظ الإعدادات
                  </VBtn>
                  <VBtn color="error" variant="tonal" @click="clearCache">
                    تفريغ كاش المناطق
                  </VBtn>
                </VCol>
              </VRow>
            </VForm>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" md="4">
        <VCard title="معلومات النظام">
          <VCardText>
            <VAlert type="info" variant="tonal" class="mb-4">
              محرك التغطية (Coverage Engine) يعتمد على استراتيجيات ذكية لتحديد الموقع ويستخدم التخزين المؤقت (Cache-Aside) لتسريع الاستجابة.
            </VAlert>
            <ul class="text-body-2" style="padding-right: 20px;">
              <li class="mb-2"><strong>مسافة السماح (Grace Distance):</strong> إذا قام المراجع بطلب زيارة خارج المضلع الجغرافي قليلاً، سيعتبر الطلب مقبولاً إذا كانت المسافة ضمن حد السماح.</li>
              <li class="mb-2"><strong>Smart Logging:</strong> تسجيل العمليات الاستثنائية فقط يخفف الضغط على قاعدة البيانات.</li>
              <li><strong>تفريغ الكاش:</strong> عند أي خطأ في ظهور المناطق، يمكنك تفريغ الكاش يدوياً، مع أن النظام يقوم بذلك تلقائياً عند أي تحديث.</li>
            </ul>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <VSnackbar v-model="snackbar" :color="snackbarColor" :timeout="3000">
      {{ snackbarText }}
    </VSnackbar>
  </div>
</template>
