<script setup lang="ts">
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import type { VForm } from 'vuetify/components/VForm'

interface Emit {
  (e: 'update:isDrawerOpen', value: boolean): void
  (e: 'submit', value: any): void
}

interface Props {
  isDrawerOpen: boolean
  tubeData?: any
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const isFormValid = ref(false)
const refForm = ref<VForm>()

const name_ar = ref('')
const name_en = ref('')
const code = ref('')
const cap_color = ref('بنفسجي (Lavender)')
const color_hex = ref('#8e24aa')
const additive = ref('EDTA')
const icon = ref('tabler-color-swatch')
const description = ref('')
const sort_order = ref(1)

watch(
  () => props.isDrawerOpen,
  (val) => {
    if (val && props.tubeData && props.tubeData.id) {
      name_ar.value = props.tubeData.name_ar || ''
      name_en.value = props.tubeData.name_en || ''
      code.value = props.tubeData.code || ''
      cap_color.value = props.tubeData.cap_color || 'بنفسجي (Lavender)'
      color_hex.value = props.tubeData.color_hex || '#8e24aa'
      additive.value = props.tubeData.additive || 'EDTA'
      icon.value = props.tubeData.icon || 'tabler-color-swatch'
      description.value = props.tubeData.description || ''
      sort_order.value = props.tubeData.sort_order || 1
    } else if (val) {
      name_ar.value = ''
      name_en.value = ''
      code.value = ''
      cap_color.value = 'بنفسجي (Lavender)'
      color_hex.value = '#8e24aa'
      additive.value = 'EDTA'
      icon.value = 'tabler-color-swatch'
      description.value = ''
      sort_order.value = 1
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
        id: props.tubeData?.id || null,
        name_ar: name_ar.value,
        name_en: name_en.value,
        code: code.value,
        cap_color: cap_color.value,
        color_hex: color_hex.value,
        additive: additive.value,
        icon: icon.value,
        description: description.value,
        sort_order: Number(sort_order.value) || 1,
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
    :width="420"
    location="end"
    class="scrollable-content"
    :model-value="props.isDrawerOpen"
    @update:model-value="handleDrawerModelValueUpdate"
  >
    <AppDrawerHeaderSection
      :title="props.tubeData?.id ? 'تعديل بيانات الأنبوب' : 'إضافة أنبوب سحب جديد'"
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
              <VCol cols="12">
                <AppTextField
                  v-model="name_ar"
                  :rules="[requiredValidator]"
                  label="اسم الأنبوب (عربي)"
                  placeholder="مثال: أنبوب EDTA البنفسجي"
                />
              </VCol>

              <VCol cols="12">
                <AppTextField
                  v-model="name_en"
                  label="اسم الأنبوب (إنجليزي)"
                  placeholder="EDTA Lavender Top Tube"
                />
              </VCol>

              <VCol cols="12" md="6">
                <AppTextField
                  v-model="code"
                  label="الكود المخبري"
                  placeholder="EDTA-PURPLE"
                />
              </VCol>

              <VCol cols="12" md="6">
                <AppTextField
                  v-model="cap_color"
                  label="لون الغطاء"
                  placeholder="بنفسجي / أصفر / أزرق"
                />
              </VCol>

              <VCol cols="12" md="6">
                <AppTextField
                  v-model="color_hex"
                  label="اللون (Hex Code)"
                  placeholder="#8e24aa"
                />
              </VCol>

              <VCol cols="12" md="6">
                <AppTextField
                  v-model="additive"
                  label="المادة المضافة (Additive)"
                  placeholder="EDTA / Gel Separator / Citrate"
                />
              </VCol>

              <VCol cols="12">
                <AppTextarea
                  v-model="description"
                  label="وصف الأنبوب والاستخدام الموصى به"
                  placeholder="اكتب التحاليل التي تُسحب عادة في هذا الأنبوب..."
                  rows="3"
                />
              </VCol>

              <VCol cols="12">
                <AppTextField
                  v-model="sort_order"
                  type="number"
                  label="ترتيب العرض"
                />
              </VCol>

              <VCol cols="12">
                <VBtn
                  type="submit"
                  class="me-3"
                >
                  {{ props.tubeData?.id ? 'حفظ التعديلات' : 'إضافة الأنبوب' }}
                </VBtn>
                <VBtn
                  type="reset"
                  variant="tonal"
                  color="secondary"
                  @click="closeNavigationDrawer"
                >
                  إلغاء
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </PerfectScrollbar>
  </VNavigationDrawer>
</template>
