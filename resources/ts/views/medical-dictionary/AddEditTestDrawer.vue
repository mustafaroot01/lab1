<script setup lang="ts">
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import type { VForm } from 'vuetify/components/VForm'

interface Emit {
  (e: 'update:isDrawerOpen', value: boolean): void
  (e: 'submit', value: any): void
}

interface Props {
  isDrawerOpen: boolean
  testData?: any
  groups: Array<{ id: number; name_ar: string }>
  sampleTypes?: Array<any>
  tubeTypes?: Array<any>
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const isFormValid = ref(false)
const refForm = ref<VForm>()

const test_group_id = ref<number | null>(null)
const name_ar = ref('')
const name_en = ref('')
const key = ref('')
const sample_type = ref('دم')
const tube_type = ref('sst_yellow')
const fasting_required = ref(false)
const result_time = ref('ساعة')
const price = ref<number | null>(null)
const platform_price = ref<number | null>(null)
const total_price = ref<number | null>(null)
const is_active = ref(false)
const description = ref('')
const sort_order = ref(1)

const sampleOptions = computed(() => {
  if (props.sampleTypes && props.sampleTypes.length) {
    return props.sampleTypes.map(s => typeof s === 'string' ? s : s.name_ar)
  }
  return ['دم', 'بول', 'ادرار', 'مسحة', 'براز', 'سائل شوكي']
})

const tubeOptions = computed(() => {
  if (props.tubeTypes && props.tubeTypes.length) {
    return props.tubeTypes.map(t => typeof t === 'string' ? t : t.name_ar)
  }
  return ['أنبوب EDTA البنفسجي', 'sst_yellow', 'أنبوب Citrate الأزرق', 'URINE_CUP', 'أنبوب أحمر عادى', 'سواب (Swab)']
})

watch([price, platform_price], ([p, pp]) => {
  if (p !== null || pp !== null) {
    total_price.value = (Number(p) || 0) + (Number(pp) || 0)
  }
})

watch(
  () => props.isDrawerOpen,
  (val) => {
    if (val && props.testData && props.testData.id) {
      test_group_id.value = props.testData.test_group_id || null
      name_ar.value = props.testData.name_ar || ''
      name_en.value = props.testData.name_en || ''
      key.value = props.testData.key || ''
      sample_type.value = props.testData.sample_type || 'دم'
      tube_type.value = props.testData.tube_type || 'sst_yellow'
      fasting_required.value = Boolean(props.testData.fasting_required)
      result_time.value = props.testData.result_time || 'ساعة'
      price.value = props.testData.price !== undefined ? props.testData.price : null
      platform_price.value = props.testData.platform_price !== undefined ? props.testData.platform_price : null
      total_price.value = props.testData.total_price !== undefined ? props.testData.total_price : ((Number(price.value) || 0) + (Number(platform_price.value) || 0))
      is_active.value = Boolean(props.testData.is_active)
      description.value = props.testData.description || ''
      sort_order.value = props.testData.sort_order || 1
    } else if (val) {
      test_group_id.value = props.groups[0]?.id || null
      name_ar.value = ''
      name_en.value = ''
      key.value = ''
      sample_type.value = 'دم'
      tube_type.value = 'sst_yellow'
      fasting_required.value = false
      result_time.value = 'ساعة'
      price.value = null
      platform_price.value = null
      total_price.value = null
      is_active.value = false
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
        id: props.testData?.id || null,
        test_group_id: test_group_id.value,
        name_ar: name_ar.value,
        name_en: name_en.value,
        key: key.value,
        sample_type: sample_type.value,
        tube_type: tube_type.value,
        fasting_required: fasting_required.value,
        result_time: result_time.value,
        price: (price.value !== null && price.value !== undefined && !isNaN(Number(price.value))) ? Number(price.value) : null,
        platform_price: (platform_price.value !== null && platform_price.value !== undefined && !isNaN(Number(platform_price.value))) ? Number(platform_price.value) : null,
        total_price: (total_price.value !== null && total_price.value !== undefined && !isNaN(Number(total_price.value))) ? Number(total_price.value) : null,
        is_active: is_active.value,
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
    :width="450"
    location="end"
    class="scrollable-content"
    :model-value="props.isDrawerOpen"
    @update:model-value="handleDrawerModelValueUpdate"
  >
    <AppDrawerHeaderSection
      :title="props.testData?.id ? 'تعديل بيانات التحليل' : 'إضافة تحليل مخبري جديد'"
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
                <AppSelect
                  v-model="test_group_id"
                  :items="props.groups"
                  item-title="name_ar"
                  item-value="id"
                  label="المجموعة التابع لها"
                  placeholder="اختر المجموعة المخبرية"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <VCol cols="12">
                <AppTextField
                  v-model="name_ar"
                  :rules="[requiredValidator]"
                  label="اسم التحليل (عربي)"
                  placeholder="مثال: صورة الدم الكاملة CBC"
                />
              </VCol>

              <VCol cols="12">
                <AppTextField
                  v-model="name_en"
                  label="اسم التحليل (إنجليزي)"
                  placeholder="complete blood count"
                />
              </VCol>

              <VCol cols="12">
                <AppTextField
                  v-model="key"
                  label="المفتاح البرمجي (Code / Key)"
                  placeholder="cbc"
                />
              </VCol>

              <VCol cols="12" md="6">
                <AppSelect
                  v-model="sample_type"
                  :items="sampleOptions"
                  label="نوع العينة"
                />
              </VCol>

              <VCol cols="12" md="6">
                <AppSelect
                  v-model="tube_type"
                  :items="tubeOptions"
                  label="نوع الأنبوب"
                />
              </VCol>

              <VCol cols="12" md="6">
                <AppSelect
                  v-model="result_time"
                  :items="['15 دقيقة', '30 دقيقة', 'ساعة', 'ساعتين', '24 ساعة', '48 ساعة', '7 ايام']"
                  label="وقت صدور النتيجة"
                />
              </VCol>

              <VCol cols="12" md="6" class="d-flex align-center">
                <VSwitch
                  v-model="fasting_required"
                  label="يتطلب صيام؟"
                  color="warning"
                />
              </VCol>

              <VCol cols="12" md="4">
                <AppTextField
                  v-model="price"
                  type="number"
                  label="سعر المختبر (د.ع)"
                  placeholder="مثال: 15000"
                />
              </VCol>

              <VCol cols="12" md="4">
                <AppTextField
                  v-model="platform_price"
                  type="number"
                  label="سعر المنصة (د.ع)"
                  placeholder="مثال: 2000"
                />
              </VCol>

              <VCol cols="12" md="4">
                <AppTextField
                  v-model="total_price"
                  type="number"
                  label="السعر الكلي للزبون (د.ع)"
                  placeholder="مثال: 17000"
                />
              </VCol>

              <VCol cols="12">
                <VSwitch
                  v-model="is_active"
                  color="success"
                  label="تفعيل وإظهار التحليل في تطبيق المرضى"
                />
              </VCol>

              <VCol cols="12">
                <AppTextarea
                  v-model="description"
                  label="وصف التحليل والغرض المنهجي"
                  placeholder="اكتب نبذة عن التحليل..."
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
                  {{ props.testData?.id ? 'حفظ التعديلات' : 'إضافة التحليل' }}
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
