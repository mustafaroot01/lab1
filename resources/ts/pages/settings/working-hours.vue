<script setup lang="ts">
import { ref, onMounted } from 'vue'

const isLoading = ref(false)
const isSaving = ref(false)
const workingHours = ref<any[]>([])
const snackbar = ref({ show: false, message: '', color: 'success' })

const fetchWorkingHours = async () => {
  isLoading.value = true
  try {
    const res = await $api('/settings/working-hours')
    if (res.status) {
      workingHours.value = res.data
    }
  } catch (error) {
    console.error('Error fetching working hours:', error)
    snackbar.value = { show: true, message: 'حدث خطأ أثناء جلب أوقات العمل', color: 'error' }
  } finally {
    isLoading.value = false
  }
}

const saveWorkingHours = async () => {
  isSaving.value = true
  try {
    const res = await $api('/settings/working-hours', {
      method: 'PUT',
      body: JSON.stringify({ working_hours: workingHours.value }),
    })
    
    if (res.status) {
      snackbar.value = { show: true, message: res.message, color: 'success' }
    }
  } catch (error: any) {
    snackbar.value = { show: true, message: error.response?.data?.message || 'حدث خطأ أثناء حفظ التعديلات', color: 'error' }
  } finally {
    isSaving.value = false
  }
}

const addTime = (period: any) => {
  if (!period.newTime) return
  if (!period.times) period.times = []
  if (!period.times.includes(period.newTime)) {
    period.times.push(period.newTime)
    period.times.sort()
  }
  period.newTime = ''
}

const removeTime = (period: any, index: number) => {
  period.times.splice(index, 1)
}

const getPeriodName = (key: string) => {
  const names: Record<string, string> = {
    morning: 'الفترة الصباحية',
    noon: 'فترة الظهيرة',
    evening: 'الفترة المسائية'
  }
  return names[key] || key
}

onMounted(() => {
  fetchWorkingHours()
})
</script>

<template>
  <div>
    <div class="d-flex flex-wrap justify-space-between align-center mb-6 gap-4">
      <div>
        <h4 class="text-h4 mb-1">أوقات وساعات العمل</h4>
        <p class="text-body-1 mb-0">قم بإدارة أيام وساعات الدوام المتاحة لحجوزات الزبائن</p>
      </div>
      <VBtn :loading="isSaving" @click="saveWorkingHours">
        <VIcon start icon="tabler-device-floppy" />
        حفظ التعديلات
      </VBtn>
    </div>

    <VCard :loading="isLoading">
      <VCardText v-if="!isLoading && workingHours.length === 0">
        لا توجد بيانات متاحة.
      </VCardText>
      
      <VExpansionPanels variant="accordion" class="custom-panels">
        <VExpansionPanel
          v-for="(day, index) in workingHours"
          :key="day.key"
        >
          <VExpansionPanelTitle>
            <div class="d-flex align-center gap-4 w-100">
              <VSwitch
                v-model="day.is_working"
                color="success"
                :label="day.is_working ? 'يوم عمل' : 'عطلة'"
                hide-details
                @click.stop
              />
              <span class="text-h6">{{ day.name }}</span>
            </div>
          </VExpansionPanelTitle>

          <VExpansionPanelText v-if="day.is_working">
            <VRow class="mt-2">
              <VCol v-for="(periodData, periodKey) in day.shifts" :key="periodKey" cols="12" md="4">
                <VCard variant="outlined" class="h-100">
                  <VCardItem>
                    <div class="d-flex align-center justify-space-between mb-4">
                      <span class="text-h6">{{ getPeriodName(periodKey) }}</span>
                      <VSwitch
                        v-model="periodData.is_active"
                        color="primary"
                        density="compact"
                        hide-details
                      />
                    </div>
                    
                    <div v-if="periodData.is_active">
                      <div class="d-flex gap-2 mb-4">
                        <AppTextField
                          v-model="periodData.newTime"
                          type="time"
                          placeholder="08:00"
                          density="compact"
                          hide-details
                          @keyup.enter="addTime(periodData)"
                        />
                        <VBtn color="primary" variant="tonal" @click="addTime(periodData)">
                          إضافة
                        </VBtn>
                      </div>

                      <div class="d-flex flex-wrap gap-2">
                        <VChip
                          v-for="(time, i) in periodData.times"
                          :key="i"
                          closable
                          @click:close="removeTime(periodData, i)"
                        >
                          {{ time }}
                        </VChip>
                        <span v-if="!periodData.times || periodData.times.length === 0" class="text-caption text-disabled">
                          لم يتم تحديد ساعات لهذه الفترة
                        </span>
                      </div>
                    </div>
                    <div v-else class="text-center text-disabled py-4">
                      هذه الفترة مغلقة
                    </div>
                  </VCardItem>
                </VCard>
              </VCol>
            </VRow>
          </VExpansionPanelText>
        </VExpansionPanel>
      </VExpansionPanels>
    </VCard>

    <VSnackbar
      v-model="snackbar.show"
      location="top"
      :color="snackbar.color"
      timeout="3000"
    >
      {{ snackbar.message }}
    </VSnackbar>
  </div>
</template>

<style lang="scss">
.custom-panels {
  .v-expansion-panel {
    border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
    margin-bottom: 1rem;
    border-radius: 8px !important;
    
    &::before {
      display: none;
    }
  }
}
</style>
