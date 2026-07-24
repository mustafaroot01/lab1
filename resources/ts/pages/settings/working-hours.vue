<script setup lang="ts">
import { ref, onMounted } from 'vue'

const isLoading = ref(false)
const isSaving = ref(false)
const workingHours = ref<any[]>([])
const snackbar = ref({ show: false, message: '', color: 'success' })

// Delete Confirmation State
const deleteDialog = ref(false)
const timeToDelete = ref<{ period: any, time: string } | null>(null)

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

const confirmDeleteTime = (period: any, time: string) => {
  timeToDelete.value = { period, time }
  deleteDialog.value = true
}

const executeDeleteTime = () => {
  if (!timeToDelete.value) return
  const { period, time } = timeToDelete.value
  
  // Filter by value instead of index to prevent duplicate deletions
  period.times = period.times.filter((t: string) => t !== time)
  
  deleteDialog.value = false
  timeToDelete.value = null
}

const copyToAllDays = (sourceDay: any) => {
  // Deep clone to avoid reactive reference issues
  const sourceShifts = JSON.parse(JSON.stringify(sourceDay.shifts))
  
  workingHours.value.forEach(day => {
    if (day.key !== sourceDay.key) {
      day.shifts = JSON.parse(JSON.stringify(sourceShifts))
      day.is_working = sourceDay.is_working
    }
  })
  
  snackbar.value = { 
    show: true, 
    message: `تم تعميم إعدادات (${sourceDay.name}) على جميع الأيام بنجاح`, 
    color: 'success' 
  }
}

const getPeriodName = (key: string) => {
  const names: Record<string, string> = {
    morning: 'الفترة الصباحية',
    noon: 'فترة الظهيرة',
    evening: 'الفترة المسائية'
  }
  return names[key] || key
}

const getPeriodIcon = (key: string) => {
  const icons: Record<string, string> = {
    morning: 'tabler-sunrise',
    noon: 'tabler-sun',
    evening: 'tabler-moon-stars'
  }
  return icons[key] || 'tabler-clock'
}

const getPeriodColor = (key: string) => {
  const colors: Record<string, string> = {
    morning: 'info',
    noon: 'warning',
    evening: 'primary'
  }
  return colors[key] || 'primary'
}

const getPeriodSubtitle = (key: string) => {
  const subs: Record<string, string> = {
    morning: 'حجوزات الصباح الباكر',
    noon: 'حجوزات منتصف النهار',
    evening: 'حجوزات المساء والليل'
  }
  return subs[key] || ''
}

const formatTime12Hour = (timeStr: string) => {
  if (!timeStr) return ''
  const parts = timeStr.split(':')
  let hour = parseInt(parts[0], 10)
  const minute = parts[1]
  const ampm = hour >= 12 ? 'مساءً' : 'صباحاً'
  hour = hour % 12
  hour = hour ? hour : 12 // 0 becomes 12
  const hourFormatted = hour < 10 ? '0' + hour : hour
  return `${hourFormatted}:${minute} ${ampm}`
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
            <div class="d-flex align-center justify-space-between w-100 pe-4">
              <div class="d-flex align-center gap-4">
                <VSwitch
                  v-model="day.is_working"
                  color="success"
                  :label="day.is_working ? 'يوم عمل' : 'عطلة'"
                  hide-details
                  @click.stop
                />
                <span class="text-h6">{{ day.name }}</span>
              </div>
              <VBtn
                variant="tonal"
                color="primary"
                size="small"
                @click.stop="copyToAllDays(day)"
              >
                <VIcon start icon="tabler-copy" size="18" />
                <span class="d-none d-sm-inline">تعميم على الكل</span>
              </VBtn>
            </div>
          </VExpansionPanelTitle>

          <VExpansionPanelText v-if="day.is_working">
            <VRow class="mt-2">
              <VCol v-for="(periodData, periodKey) in day.shifts" :key="periodKey" cols="12" md="4">
                <VCard elevation="0" border class="h-100 overflow-hidden transition-swing" :class="periodData.is_active ? `border-${getPeriodColor(periodKey)}` : ''">
                  <VCardItem class="pb-3 pt-4 px-4 bg-var-theme-background">
                    <template #prepend>
                      <VAvatar
                        rounded
                        variant="tonal"
                        :color="getPeriodColor(periodKey)"
                        class="me-3"
                      >
                        <VIcon :icon="getPeriodIcon(periodKey)" size="24" />
                      </VAvatar>
                    </template>
                    
                    <div class="d-flex align-center justify-space-between w-100">
                      <div>
                        <div class="text-h6 font-weight-bold mb-0">{{ getPeriodName(periodKey) }}</div>
                        <div class="text-caption text-medium-emphasis">{{ getPeriodSubtitle(periodKey) }}</div>
                      </div>
                      <VSwitch
                        v-model="periodData.is_active"
                        :color="getPeriodColor(periodKey)"
                        density="compact"
                        hide-details
                      />
                    </div>
                  </VCardItem>
                  
                  <VDivider />

                  <VCardText v-if="periodData.is_active" class="pt-5 px-4 pb-4">
                    <div class="d-flex align-center gap-3 mb-5">
                      <AppTextField
                        v-model="periodData.newTime"
                        type="time"
                        density="compact"
                        hide-details
                        prepend-inner-icon="tabler-clock"
                        @keyup.enter="addTime(periodData)"
                        style="flex: 1;"
                      />
                      <VBtn :color="getPeriodColor(periodKey)" variant="elevated" @click="addTime(periodData)">
                        <VIcon start icon="tabler-plus" />
                        إضافة
                      </VBtn>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                      <VChip
                        v-for="time in periodData.times"
                        :key="time"
                        closable
                        :color="getPeriodColor(periodKey)"
                        variant="tonal"
                        size="default"
                        class="font-weight-medium"
                        prepend-icon="tabler-clock-hour-4"
                        @click:close="confirmDeleteTime(periodData, time)"
                      >
                        {{ formatTime12Hour(time) }}
                      </VChip>
                      
                      <div v-if="!periodData.times || periodData.times.length === 0" class="d-flex flex-column align-center justify-center w-100 py-6 text-disabled">
                        <VIcon icon="tabler-calendar-time" size="32" class="mb-2 opacity-50" />
                        <span class="text-body-2">لم يتم إضافة ساعات عمل</span>
                      </div>
                    </div>
                  </VCardText>
                  
                  <VCardText v-else class="d-flex flex-column align-center justify-center py-8 text-disabled bg-grey-50">
                    <VIcon icon="tabler-power" size="40" class="mb-2 opacity-50" />
                    <span class="text-body-2">هذه الفترة مغلقة ولا تستقبل حجوزات</span>
                  </VCardText>
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

    <!-- Delete Time Confirmation Dialog -->
    <VDialog v-model="deleteDialog" max-width="400">
      <VCard>
        <VCardText class="text-center pt-6 pb-4">
          <VIcon icon="tabler-alert-circle" color="error" size="64" class="mb-4" />
          <h6 class="text-h6 font-weight-medium mb-2">تأكيد حذف الوقت</h6>
          <p class="text-body-1 mb-0">هل أنت متأكد من حذف الوقت ({{ formatTime12Hour(timeToDelete?.time || '') }}) من هذه الفترة؟</p>
        </VCardText>
        <VCardActions class="justify-center gap-4 pb-6">
          <VBtn color="secondary" variant="tonal" @click="deleteDialog = false">إلغاء</VBtn>
          <VBtn color="error" variant="elevated" @click="executeDeleteTime">نعم، احذف الوقت</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
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
