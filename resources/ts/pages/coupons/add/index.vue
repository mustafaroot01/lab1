<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import type { VForm } from 'vuetify/components/VForm'

const router = useRouter()
const refForm = ref<VForm>()
const isFormValid = ref(false)
const loading = ref(false)

const code = ref('')
const name_ar = ref('')
const name_en = ref('')
const discount_type = ref('percentage')
const discount_value = ref<number | null>(null)
const start_date = ref('')
const end_date = ref('')
const is_unlimited = ref(false)
const usage_limit = ref<number | null>(50)
const is_active = ref(true)
const notes = ref('')

const onSubmit = () => {
  refForm.value?.validate().then(async ({ valid }) => {
    if (valid) {
      loading.value = true
      try {
        await $api('/coupons', {
          method: 'POST',
          body: {
            code: code.value.trim().toUpperCase(),
            name_ar: name_ar.value,
            name_en: name_en.value || null,
            discount_type: discount_type.value,
            discount_value: Number(discount_value.value) || 0,
            start_date: start_date.value || null,
            end_date: end_date.value || null,
            usage_limit: is_unlimited.value ? null : (Number(usage_limit.value) || null),
            is_active: is_active.value,
            notes: notes.value || null,
          },
        })
        router.push('/coupons')
      } catch (error: any) {
        console.error('Error creating coupon:', error)
        alert(error?.data?.message || 'حدث خطأ أثناء إضافة الكوبون')
      } finally {
        loading.value = false
      }
    }
  })
}
</script>

<template>
  <VForm
    ref="refForm"
    v-model="isFormValid"
    @submit.prevent="onSubmit"
  >
    <div class="d-flex flex-wrap justify-start justify-sm-space-between gap-y-4 gap-x-6 mb-6">
      <div class="d-flex flex-column justify-center">
        <h4 class="text-h4 font-weight-medium">
          إضافة كوبون خصم جديد
        </h4>
        <div class="text-body-1 text-medium-emphasis">
          تجهيز وإنشاء رمز خصم أو عرض ترويجي ليتم استخدامه في قائمة التحاليل المخبرية
        </div>
      </div>

      <div class="d-flex gap-4 align-center flex-wrap">
        <VBtn
          variant="tonal"
          color="secondary"
          to="/coupons"
          prepend-icon="tabler-arrow-right"
        >
          إلغاء ورجوع
        </VBtn>
        <VBtn
          type="submit"
          color="primary"
          :loading="loading"
          prepend-icon="tabler-check"
        >
          حفظ ونشر الكوبون
        </VBtn>
      </div>
    </div>

    <VRow>
      <VCol cols="12" md="8">
        <!-- 👉 Coupon Information -->
        <VCard
          class="mb-6"
          title="بيانات الكوبون الأساسية والتعريفية"
        >
          <VCardText>
            <VRow>
              <VCol cols="12">
                <AppTextField
                  v-model="code"
                  :rules="[requiredValidator]"
                  label="رمز / كود الكوبون (Code)"
                  placeholder="مثال: WELCOME20 أو SUMMER15"
                  dir="ltr"
                  class="font-weight-bold"
                />
              </VCol>

              <VCol cols="12">
                <AppTextField
                  v-model="name_ar"
                  :rules="[requiredValidator]"
                  label="اسم أو عنوان الخصم (عربي)"
                  placeholder="مثال: خصم ترحيبي للمرضى الجدد"
                />
              </VCol>

              <VCol cols="12">
                <AppTextField
                  v-model="name_en"
                  label="اسم أو عنوان الخصم (إنجليزي - اختياري)"
                  placeholder="Welcome New Patients Discount"
                />
              </VCol>

              <VCol cols="12">
                <AppTextarea
                  v-model="notes"
                  label="ملاحظات وشروط الاستخدام (اختياري)"
                  placeholder="اكتب نبذة أو شروط خاصة بهذا الكوبون تظهر للإدارة أو المرضى..."
                  rows="4"
                />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>

        <!-- 👉 Validity & Timing -->
        <VCard
          class="mb-6"
          title="صلاحيات وتواريخ العمل"
        >
          <VCardText>
            <VRow>
              <VCol cols="12" md="6">
                <AppDateTimePicker
                  v-model="start_date"
                  label="تاريخ ووقت بدء الكوبون (البدء)"
                  placeholder="اختر تاريخ ووقت البدء"
                  :config="{ enableTime: true, dateFormat: 'Y-m-d H:i' }"
                />
              </VCol>

              <VCol cols="12" md="6">
                <AppDateTimePicker
                  v-model="end_date"
                  label="تاريخ ووقت انتهاء الكوبون (الانتهاء)"
                  placeholder="اختر تاريخ ووقت الانتهاء (أو اتركه دائماً)"
                  :config="{ enableTime: true, dateFormat: 'Y-m-d H:i' }"
                />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" md="4">
        <!-- 👉 Pricing & Discount Type -->
        <VCard
          title="نوع وقيمة الخصم"
          class="mb-6"
        >
          <VCardText>
            <AppSelect
              v-model="discount_type"
              :items="[
                { title: 'نسبة مئوية (%)', value: 'percentage' },
                { title: 'مبلغ ثابت (د.ع)', value: 'fixed' },
              ]"
              label="نوع الخصم"
              class="mb-6"
            />

            <AppTextField
              v-model="discount_value"
              type="number"
              :rules="[requiredValidator]"
              :label="discount_type === 'percentage' ? 'نسبة الخصم (%)' : 'مبلغ الخصم (د.ع)'"
              :placeholder="discount_type === 'percentage' ? 'مثال: 20' : 'مثال: 15000'"
            />
          </VCardText>
        </VCard>

        <!-- 👉 Usage & Limits -->
        <VCard
          title="حدود وقيود الاستخدام"
          class="mb-6"
        >
          <VCardText>
            <VSwitch
              v-model="is_unlimited"
              color="primary"
              label="حد الاستخدام مفتوح (عدد غير محدد)"
              class="mb-4"
            />

            <AppTextField
              v-if="!is_unlimited"
              v-model="usage_limit"
              type="number"
              label="الحد الأقصى لعدد المستفيدين / المرات"
              placeholder="مثال: 50 شخص"
            />
            <div v-else class="text-caption text-medium-emphasis">
              يمكن استخدام هذا الرمز لعدد غير محدود من المرضى طالما أنه في فترة الصلاحية ومفعّل.
            </div>
          </VCardText>
        </VCard>

        <!-- 👉 Status -->
        <VCard title="حالة التفعيل">
          <VCardText>
            <div class="d-flex align-center justify-space-between">
              <span class="font-weight-medium">تفعيل وإتاحة الكوبون للاستخدام</span>
              <VSwitch
                v-model="is_active"
                color="success"
              />
            </div>
            <div class="text-caption text-medium-emphasis mt-2">
              في حال إيقافه، لن يتمكن أي مريض من تطبيق الخصم حتى وإن كان في فترة الصلاحية ولم يصل للحد الأقصى.
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </VForm>
</template>
