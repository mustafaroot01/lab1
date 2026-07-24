<script setup lang="ts">
import { ref, watch } from 'vue'

const props = defineProps<{
  modelValue: boolean
  isEditing: boolean
  formData: any
  formErrors: any
}>()

const emit = defineEmits(['update:modelValue', 'save', 'cancel'])

const drawer = ref(props.modelValue)

watch(() => props.modelValue, (newVal) => {
  drawer.value = newVal
})

watch(drawer, (newVal) => {
  emit('update:modelValue', newVal)
  if (!newVal) {
    emit('cancel')
  }
})

const handleSave = () => {
  emit('save')
}

const handleCancel = () => {
  drawer.value = false
}

// Helpers for history
const formatDate = (dateString: string) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleString('ar-IQ')
}
</script>

<template>
  <VNavigationDrawer
    v-model="drawer"
    location="end"
    temporary
    width="400"
    class="scrollable-content"
  >
    <!-- Header -->
    <div class="d-flex align-center pa-6 pb-4">
      <h6 class="text-h6 font-weight-medium mb-0">
        {{ isEditing ? 'تعديل منطقة التغطية' : 'إضافة منطقة جديدة' }}
      </h6>
      <VSpacer />
      <VBtn
        icon="tabler-x"
        variant="text"
        color="default"
        size="small"
        @click="handleCancel"
      />
    </div>

    <VDivider />

    <div class="pa-6">
      <!-- Revision History Alert -->
      <VAlert v-if="isEditing && formData.updated_at" type="info" variant="tonal" density="compact" class="mb-6">
        <div class="d-flex flex-column text-caption gap-1">
          <span><strong>تم الإنشاء:</strong> {{ formatDate(formData.created_at) }}</span>
          <span><strong>آخر تحديث:</strong> {{ formatDate(formData.updated_at) }}</span>
        </div>
      </VAlert>

      <VForm @submit.prevent="handleSave">
        <VRow>
          <VCol cols="12">
            <AppTextField
              v-model="formData.name"
              label="اسم المنطقة *"
              placeholder="مثال: بعقوبة المركز"
              :error-messages="formErrors.name"
            />
          </VCol>
          
          <VCol cols="12">
            <AppTextField
              v-model.number="formData.service_fee"
              label="رسوم الزيارة (د.ع) *"
              type="number"
              :error-messages="formErrors.service_fee"
            />
          </VCol>
          
          <VCol cols="12">
            <AppTextField
              v-model.number="formData.free_visit_threshold"
              label="حد الزيارة المجانية (د.ع)"
              type="number"
              hint="يترك فارغاً لعدم توفر زيارة مجانية"
              persistent-hint
              :error-messages="formErrors.free_visit_threshold"
            />
          </VCol>
          
          <VCol cols="12">
            <AppTextField
              v-model.number="formData.priority"
              label="الأولوية (رقم أعلى = أولوية أكبر)"
              type="number"
              hint="مفيد عند تداخل منطقتين"
              persistent-hint
              :error-messages="formErrors.priority"
            />
          </VCol>

          <VCol cols="12">
            <AppTextField
              v-model.number="formData.grace_distance"
              label="مسافة السماح (بالمتر)"
              type="number"
              hint="يترك فارغاً لاستخدام الافتراضي"
              persistent-hint
              :error-messages="formErrors.grace_distance"
            />
          </VCol>

          <VCol cols="12">
            <AppSelect
              v-model="formData.status"
              :items="[
                { title: 'فعال (يستقبل الطلبات)', value: 'ACTIVE' },
                { title: 'متوقف (خارج الخدمة)', value: 'INACTIVE' },
                { title: 'صيانة (لا يستقبل حاليا)', value: 'MAINTENANCE' }
              ]"
              label="حالة المنطقة *"
              :error-messages="formErrors.status"
            />
          </VCol>
        </VRow>

        <div class="d-flex justify-end gap-3 mt-8">
          <VBtn color="secondary" variant="tonal" @click="handleCancel">
            إلغاء
          </VBtn>
          <VBtn color="primary" type="submit">
            حفظ المنطقة
          </VBtn>
        </div>
      </VForm>
    </div>
  </VNavigationDrawer>
</template>
