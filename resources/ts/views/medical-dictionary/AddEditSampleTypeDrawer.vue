<script setup lang="ts">
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import type { VForm } from 'vuetify/components/VForm'

interface Emit {
  (e: 'update:isDrawerOpen', value: boolean): void
  (e: 'submit', value: any): void
}

interface Props {
  isDrawerOpen: boolean
  sampleData?: any
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const isFormValid = ref(false)
const refForm = ref<VForm>()

const name_ar = ref('')
const name_en = ref('')
const code = ref('')
const icon = ref('tabler-test-pipe')
const color = ref('primary')
const description = ref('')
const sort_order = ref(1)

watch(
  () => props.isDrawerOpen,
  (val) => {
    if (val && props.sampleData && props.sampleData.id) {
      name_ar.value = props.sampleData.name_ar || ''
      name_en.value = props.sampleData.name_en || ''
      code.value = props.sampleData.code || ''
      icon.value = props.sampleData.icon || 'tabler-test-pipe'
      color.value = props.sampleData.color || 'primary'
      description.value = props.sampleData.description || ''
      sort_order.value = props.sampleData.sort_order || 1
    } else if (val) {
      name_ar.value = ''
      name_en.value = ''
      code.value = ''
      icon.value = 'tabler-test-pipe'
      color.value = 'primary'
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
        id: props.sampleData?.id || null,
        name_ar: name_ar.value,
        name_en: name_en.value,
        code: code.value,
        icon: icon.value,
        color: color.value,
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
    :width="400"
    location="end"
    class="scrollable-content"
    :model-value="props.isDrawerOpen"
    @update:model-value="handleDrawerModelValueUpdate"
  >
    <AppDrawerHeaderSection
      :title="props.sampleData?.id ? 'تعديل نوع عينة' : 'إضافة نوع عينة جديد'"
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
                  label="اسم العينة (عربي)"
                  placeholder="مثال: دم، بول، مسحة..."
                />
              </VCol>

              <VCol cols="12">
                <AppTextField
                  v-model="name_en"
                  label="اسم العينة (إنجليزي)"
                  placeholder="Blood / Serum / Urine..."
                />
              </VCol>

              <VCol cols="12">
                <AppTextField
                  v-model="code"
                  label="الرمز البرمجي (Code)"
                  placeholder="BLD / URN / SWB"
                />
              </VCol>

              <VCol cols="12">
                <AppSelect
                  v-model="color"
                  :items="['primary', 'secondary', 'success', 'info', 'warning', 'error']"
                  label="لون الشارة (Color)"
                />
              </VCol>

              <VCol cols="12">
                <AppTextField
                  v-model="icon"
                  label="الأيقونة (Tabler Icon)"
                  placeholder="tabler-test-pipe / tabler-droplet"
                />
              </VCol>

              <VCol cols="12">
                <AppTextarea
                  v-model="description"
                  label="وصف واستخدام العينة"
                  placeholder="اكتب نبذة عن شروط وطريقة سحب العينة..."
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
                  {{ props.sampleData?.id ? 'حفظ التعديلات' : 'إضافة نوع العينة' }}
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
