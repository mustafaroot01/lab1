<script setup lang="ts">
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import type { VForm } from 'vuetify/components/VForm'

interface Emit {
  (e: 'update:isDrawerOpen', value: boolean): void
  (e: 'submit', value: any): void
}

interface Props {
  isDrawerOpen: boolean
  groupData?: any
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const isFormValid = ref(false)
const refForm = ref<VForm>()

const name_ar = ref('')
const name_en = ref('')
const key = ref('')
const icon = ref('science')
const color = ref('#fa0400')
const is_active = ref(true)
const sort_order = ref(1)

// قائمة الأيقونات الطبية المتاحة من مكتبة Tabler
const iconOptions = [
  { value: 'flask',              label: 'أنبوب اختبار 1' },
  { value: 'flask-2',            label: 'أنبوب اختبار 2' },
  { value: 'test-pipe',          label: 'أنبوب تحليل' },
  { value: 'droplet',            label: 'قطرة دم' },
  { value: 'heart',              label: 'القلب' },
  { value: 'heart-rate-monitor', label: 'تخطيط القلب' },
  { value: 'brain',              label: 'المخ / الأعصاب' },
  { value: 'activity',           label: 'النشاط / الوظائف الحيوية' },
  { value: 'pills',              label: 'أدوية / فيتامينات' },
  { value: 'vaccine',            label: 'حقنة / لقاح / حساسية' },
  { value: 'dna',                label: 'الحمض النووي / هرمونات' },
  { value: 'virus',              label: 'فيروسات' },
  { value: 'bug',                label: 'بكتيريا / جراثيم' },
  { value: 'microscope',         label: 'مجهر / أورام' },
  { value: 'report-medical',     label: 'تقرير طبي / حمل' },
  { value: 'shield-check',       label: 'مستويات المناعة' },
  { value: 'flame',              label: 'التهابات' },
  { value: 'bolt',               label: 'الفصل الكهربي' },
  { value: 'first-aid-kit',      label: 'حقيبة طبية' },
  { value: 'stethoscope',        label: 'سماعة طبيب' },
  { value: 'pulse',              label: 'نبض' },
]

const getTablerIcon = (icon?: string) => {
  if (!icon) return 'tabler-flask'
  if (icon.startsWith('tabler-')) return icon
  const map: Record<string, string> = {
    kidney: 'flask',
    stomach: 'flask-2',
    bloodtype: 'droplet',
    water_drop: 'test-pipe',
    favorite: 'heart',
    science: 'flask',
    spa: 'activity',
    psychology: 'brain',
    wb_sunny: 'pills',
    auto_awesome: 'dna',
    coronavirus: 'virus',
    pregnant_woman: 'report-medical',
    biotech: 'microscope',
    pancreas: 'flask-2',
  }
  return 'tabler-' + (map[icon] || icon)
}

watch(
  () => props.isDrawerOpen,
  (val) => {
    if (val && props.groupData) {
      name_ar.value = props.groupData.name_ar || ''
      name_en.value = props.groupData.name_en || ''
      key.value = props.groupData.key || ''
      icon.value = props.groupData.icon || 'science'
      color.value = props.groupData.color || '#fa0400'
      is_active.value = props.groupData.is_active !== undefined ? props.groupData.is_active : true
      sort_order.value = props.groupData.sort_order || 1
    } else if (val) {
      name_ar.value = ''
      name_en.value = ''
      key.value = ''
      icon.value = 'science'
      color.value = '#fa0400'
      is_active.value = true
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
        id: props.groupData?.id || null,
        name_ar: name_ar.value,
        name_en: name_en.value,
        key: key.value,
        icon: icon.value,
        color: color.value,
        is_active: is_active.value,
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
    <!-- 👉 Title -->
    <AppDrawerHeaderSection
      :title="props.groupData?.id ? 'تعديل مجموعة تحاليل' : 'إضافة مجموعة جديدة'"
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
                  label="اسم المجموعة (عربي)"
                  placeholder="مثال: وظائف الكلى"
                />
              </VCol>

              <VCol cols="12">
                <AppTextField
                  v-model="name_en"
                  label="اسم المجموعة (إنجليزي)"
                  placeholder="Kidney Function Tests"
                />
              </VCol>

              <VCol cols="12">
                <AppTextField
                  v-model="key"
                  :rules="[requiredValidator]"
                  label="المفتاح البرمجي (Key)"
                  placeholder="kidney_panel"
                />
              </VCol>

              <!-- أيقونة المجموعة — اختيار مرئي -->
              <VCol cols="12">
                <div class="text-body-2 font-weight-medium mb-2">أيقونة المجموعة</div>

                <!-- معاينة الأيقونة المختارة -->
                <div class="d-flex align-center gap-3 mb-3">
                  <VAvatar :color="color || 'primary'" variant="tonal" size="48" rounded>
                    <VIcon :icon="getTablerIcon(icon)" size="26" />
                  </VAvatar>
                  <span class="text-caption text-medium-emphasis">الأيقونة المختارة: <code>{{ icon }}</code></span>
                </div>

                <!-- شبكة الأيقونات المتاحة -->
                <div class="d-flex flex-wrap gap-2">
                  <VTooltip
                    v-for="ic in iconOptions"
                    :key="ic.value"
                    :text="ic.label"
                    location="top"
                  >
                    <template #activator="{ props: tooltipProps }">
                      <VAvatar
                        v-bind="tooltipProps"
                        :color="icon === ic.value ? (color || 'primary') : 'default'"
                        :variant="icon === ic.value ? 'flat' : 'tonal'"
                        size="40"
                        rounded
                        style="cursor:pointer;"
                        @click="icon = ic.value"
                      >
                        <VIcon :icon="'tabler-' + ic.value" size="20" />
                      </VAvatar>
                    </template>
                  </VTooltip>
                </div>
              </VCol>

              <VCol cols="12">
                <AppTextField
                  v-model="color"
                  label="اللون (Hex Code)"
                  placeholder="#fa0400"
                />
              </VCol>

              <VCol cols="12">
                <AppTextField
                  v-model="sort_order"
                  type="number"
                  label="ترتيب العرض"
                  placeholder="1"
                />
              </VCol>

              <VCol cols="12">
                <VSwitch
                  v-model="is_active"
                  color="success"
                  label="تفعيل وإظهار المجموعة في تطبيق المريض"
                />
              </VCol>

              <VCol cols="12">
                <VBtn
                  type="submit"
                  class="me-3"
                >
                  {{ props.groupData?.id ? 'حفظ التعديلات' : 'إضافة المجموعة' }}
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
