<script setup lang="ts">
import { nextTick, ref, watch, computed } from 'vue'
import { VForm } from 'vuetify/components/VForm'

interface Props {
  isDrawerOpen: boolean
  couponData?: any
}

interface Emit {
  (e: 'update:isDrawerOpen', value: boolean): void
  (e: 'submit', value: any): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const refForm = ref<VForm>()
const isFormValid = ref(false)

const code = ref('')
const name_ar = ref('')
const name_en = ref('')
const discount_type = ref('percentage')
const discount_value = ref<number | null>(null)
const start_date = ref('')
const end_date = ref('')
const is_unlimited = ref(false)
const usage_limit = ref<number | null>(null)
const is_active = ref(true)
const notes = ref('')

watch(
  () => props.isDrawerOpen,
  (val) => {
    if (val && props.couponData && props.couponData.id) {
      code.value = props.couponData.code || ''
      name_ar.value = props.couponData.name_ar || ''
      name_en.value = props.couponData.name_en || ''
      discount_type.value = props.couponData.discount_type || 'percentage'
      discount_value.value = props.couponData.discount_value !== undefined ? props.couponData.discount_value : null
      start_date.value = props.couponData.start_date ? props.couponData.start_date.substring(0, 16).replace('T', ' ') : ''
      end_date.value = props.couponData.end_date ? props.couponData.end_date.substring(0, 16).replace('T', ' ') : ''
      is_unlimited.value = props.couponData.usage_limit === null || props.couponData.usage_limit === undefined
      usage_limit.value = props.couponData.usage_limit || null
      is_active.value = Boolean(props.couponData.is_active)
      notes.value = props.couponData.notes || ''
    } else if (val) {
      code.value = ''
      name_ar.value = ''
      name_en.value = ''
      discount_type.value = 'percentage'
      discount_value.value = null
      start_date.value = ''
      end_date.value = ''
      is_unlimited.value = false
      usage_limit.value = 50
      is_active.value = true
      notes.value = ''
    }
  },
  { immediate: true }
)

const closeNavigationDrawer = () => {
  emit('update:isDrawerOpen', false)
  nextTick(() => {
    refForm.value?.reset()
    refForm.value?.resetValidation()
  })
}

const onSubmit = () => {
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      emit('submit', {
        id: props.couponData?.id || null,
        code: code.value.trim().toUpperCase(),
        name_ar: name_ar.value,
        name_en: name_en.value,
        discount_type: discount_type.value,
        discount_value: Number(discount_value.value) || 0,
        start_date: start_date.value || null,
        end_date: end_date.value || null,
        usage_limit: is_unlimited.value ? null : (Number(usage_limit.value) || null),
        is_active: is_active.value,
        notes: notes.value,
      })
      emit('update:isDrawerOpen', false)
      nextTick(() => {
        refForm.value?.reset()
        refForm.value?.resetValidation()
      })
    }
  })
}

const handleDrawerModelValueUpdate = (val: boolean) => {
  emit('update:isDrawerOpen', val)
}
</script>

<template>
  <VNavigationDrawer
    data-allow-mismatch
    temporary
    :width="460"
    location="end"
    class="scrollable-content"
    :model-value="props.isDrawerOpen"
    @update:model-value="handleDrawerModelValueUpdate"
  >
    <AppDrawerHeaderSection
      :title="props.couponData?.id ? 'تعديل بيانات الكوبون' : 'إضافة كوبون خصم جديد'"
      @cancel="closeNavigationDrawer"
    />

    <VDivider />

    <PerfectScrollbar :options="{ wheelPropagation: false }">
      <VCard flat>
        <VCardText>
          <VForm
            ref="refForm"
            v-model="isFormValid"
            @submit.prevent="onSubmit"
          >
            <VRow>
              <!-- Code -->
              <VCol cols="12">
                <AppTextField
                  v-model="code"
                  :rules="[requiredValidator]"
                  label="رمز / كود الكوبون (Code)"
                  placeholder="مثال: WELCOME20 أو SUMMER15"
                  dir="ltr"
                />
              </VCol>

              <!-- Name AR -->
              <VCol cols="12">
                <AppTextField
                  v-model="name_ar"
                  :rules="[requiredValidator]"
                  label="اسم أو عنوان الخصم (عربي)"
                  placeholder="مثال: خصم ترحيبي للمرضى الجدد"
                />
              </VCol>

              <!-- Name EN -->
              <VCol cols="12">
                <AppTextField
                  v-model="name_en"
                  label="اسم أو عنوان الخصم (إنجليزي)"
                  placeholder="Welcome New Patients Discount"
                />
              </VCol>

              <!-- Discount Type -->
              <VCol cols="12" md="6">
                <AppSelect
                  v-model="discount_type"
                  :items="[
                    { title: 'نسبة مئوية (%)', value: 'percentage' },
                    { title: 'مبلغ ثابت (د.ع)', value: 'fixed' },
                  ]"
                  label="نوع الخصم"
                />
              </VCol>

              <!-- Discount Value -->
              <VCol cols="12" md="6">
                <AppTextField
                  v-model="discount_value"
                  type="number"
                  :rules="[requiredValidator]"
                  :label="discount_type === 'percentage' ? 'نسبة الخصم (%)' : 'مبلغ الخصم (د.ع)'"
                  :placeholder="discount_type === 'percentage' ? 'مثال: 20' : 'مثال: 15000'"
                />
              </VCol>

              <!-- Start Date -->
              <VCol cols="12">
                <AppDateTimePicker
                  v-model="start_date"
                  label="تاريخ ووقت بدء الكوبون"
                  placeholder="اختر تاريخ ووقت البدء"
                  :config="{ enableTime: true, dateFormat: 'Y-m-d H:i' }"
                />
              </VCol>

              <!-- End Date -->
              <VCol cols="12">
                <AppDateTimePicker
                  v-model="end_date"
                  label="تاريخ ووقت انتهاء الكوبون (اختياري)"
                  placeholder="اختر تاريخ ووقت الانتهاء (أو اتركه دائماً)"
                  :config="{ enableTime: true, dateFormat: 'Y-m-d H:i' }"
                />
              </VCol>

              <!-- Unlimited switch -->
              <VCol cols="12">
                <VSwitch
                  v-model="is_unlimited"
                  color="primary"
                  label="حد الاستخدام مفتوح (عدد غير محدد من الأشخاص)"
                />
              </VCol>

              <!-- Usage Limit -->
              <VCol v-if="!is_unlimited" cols="12">
                <AppTextField
                  v-model="usage_limit"
                  type="number"
                  label="حد الاستخدام الأقصى (عدد الأشخاص/المرات)"
                  placeholder="مثال: 50 شخص"
                />
              </VCol>

              <!-- Active switch -->
              <VCol cols="12">
                <VSwitch
                  v-model="is_active"
                  color="success"
                  label="تفعيل الكوبون وإتاحته للاستخدام"
                />
              </VCol>

              <!-- Notes -->
              <VCol cols="12">
                <AppTextarea
                  v-model="notes"
                  label="ملاحظات وشروط الاستخدام"
                  placeholder="اكتب نبذة أو شروط خاصة بهذا الكوبون..."
                  rows="3"
                />
              </VCol>

              <!-- Submit / Cancel Buttons -->
              <VCol cols="12">
                <div class="d-flex justify-end gap-3">
                  <VBtn
                    variant="tonal"
                    color="secondary"
                    @click="closeNavigationDrawer"
                  >
                    إلغاء
                  </VBtn>
                  <VBtn type="submit">
                    حفظ الكوبون
                  </VBtn>
                </div>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </PerfectScrollbar>
  </VNavigationDrawer>
</template>
