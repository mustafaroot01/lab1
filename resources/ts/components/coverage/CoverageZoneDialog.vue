<script setup lang="ts">
import { ref, watch } from 'vue'

const props = defineProps<{
  modelValue: boolean
  isEditing: boolean
  formData: any
  formErrors: any
}>()

const emit = defineEmits(['update:modelValue', 'save', 'cancel'])

const dialog = ref(props.modelValue)

watch(() => props.modelValue, (newVal) => {
  dialog.value = newVal
})

watch(dialog, (newVal) => {
  emit('update:modelValue', newVal)
})

const handleSave = () => {
  emit('save')
}

const handleCancel = () => {
  emit('cancel')
  dialog.value = false
}

// Helpers for history
const formatDate = (dateString: string) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleString('ar-IQ')
}
</script>

<template>
  <VDialog v-model="dialog" max-width="700" persistent>
    <VCard :title="isEditing ? 'تعديل منطقة التغطية' : 'حفظ منطقة تغطية جديدة'">
      
      <!-- Revision History Alert -->
      <VCardText v-if="isEditing && formData.updated_at" class="pt-0 pb-2">
        <VAlert type="info" variant="tonal" density="compact">
          <div class="d-flex justify-space-between align-center text-caption">
            <span><strong>آخر تحديث:</strong> {{ formatDate(formData.updated_at) }}</span>
            <span><strong>تم الإنشاء:</strong> {{ formatDate(formData.created_at) }}</span>
            <VChip size="x-small" color="primary">النسخة الحالية</VChip>
          </div>
        </VAlert>
      </VCardText>

      <VCardText class="pt-2">
        <VRow>
          <VCol cols="12">
            <AppTextField
              v-model="formData.name"
              label="اسم المنطقة *"
              placeholder="مثال: بعقوبة المركز"
              :error-messages="formErrors.name"
            />
          </VCol>
          
          <VCol cols="12" md="4">
            <AppTextField
              v-model.number="formData.service_fee"
              label="رسوم الزيارة (د.ع) *"
              type="number"
              :error-messages="formErrors.service_fee"
            />
          </VCol>
          
          <VCol cols="12" md="4">
            <AppTextField
              v-model.number="formData.free_visit_threshold"
              label="حد الزيارة المجانية (د.ع)"
              type="number"
              hint="يترك فارغاً لعدم توفر زيارة مجانية"
              persistent-hint
              :error-messages="formErrors.free_visit_threshold"
            />
          </VCol>
          
          <VCol cols="12" md="4">
            <AppTextField
              v-model.number="formData.priority"
              label="الأولوية (رقم أعلى = أولوية أكبر)"
              type="number"
              hint="مفيد عند تداخل منطقتين"
              persistent-hint
              :error-messages="formErrors.priority"
            />
          </VCol>

          <VCol cols="12" md="6">
            <AppTextField
              v-model.number="formData.grace_distance"
              label="مسافة السماح (بالمتر)"
              type="number"
              hint="يترك فارغاً لاستخدام الافتراضي"
              persistent-hint
              :error-messages="formErrors.grace_distance"
            />
          </VCol>

          <VCol cols="12" md="6">
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
      </VCardText>
      
      <VCardActions class="pa-4 justify-end">
        <VBtn color="secondary" variant="tonal" @click="handleCancel">إلغاء</VBtn>
        <VBtn color="primary" @click="handleSave">حفظ المنطقة</VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
