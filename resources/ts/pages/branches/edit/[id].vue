<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()
const branchId = route.params.id as string

// Form Fields
const nameAr = ref('')
const address = ref('')
const phone = ref('')
const notes = ref('')
const isActive = ref(true)
const lat = ref<number | null>(null)
const lng = ref<number | null>(null)

// Weekly Working Hours per day
interface ShiftSchedule {
  is_active: boolean
  times: string[]
}

interface DaySchedule {
  key: string
  label: string
  is_working: boolean
  shifts: Record<string, ShiftSchedule>
}

const getInitialShifts = () => ({
  morning: { is_active: false, times: [] },
  noon: { is_active: false, times: [] },
  evening: { is_active: false, times: [] },
})

const workingHours = ref<DaySchedule[]>([
  { key: 'saturday', label: 'السبت', is_working: true, shifts: getInitialShifts() },
  { key: 'sunday', label: 'الأحد', is_working: true, shifts: getInitialShifts() },
  { key: 'monday', label: 'الإثنين', is_working: true, shifts: getInitialShifts() },
  { key: 'tuesday', label: 'الثلاثاء', is_working: true, shifts: getInitialShifts() },
  { key: 'wednesday', label: 'الأربعاء', is_working: true, shifts: getInitialShifts() },
  { key: 'thursday', label: 'الخميس', is_working: true, shifts: getInitialShifts() },
  { key: 'friday', label: 'الجمعة', is_working: false, shifts: getInitialShifts() },
])

const applyFirstDayTimesToAll = () => {
  const firstActive = workingHours.value.find(d => d.is_working)
  if (!firstActive) return
  workingHours.value.forEach(d => {
    if (d.is_working && d.key !== firstActive.key) {
      d.shifts = JSON.parse(JSON.stringify(firstActive.shifts))
    }
  })
}

const getPeriodLabel = (period: string) => {
  if (period === 'morning') return 'صباحاً'
  if (period === 'noon') return 'ظهراً'
  if (period === 'evening') return 'مساءً'
  return ''
}

// Polygon points
const coveragePolygon = ref<[number, number][]>([])

// Map objects
let map: any = null
let marker: any = null
let polygonLayer: any = null
let vertexMarkers: any[] = []
const mapReady = ref(false)
const initialLoading = ref(true)

// Address search
const addressSearch = ref('')
const searchingAddress = ref(false)
const addressSuggestions = ref<any[]>([])

// UI states
const loading = ref(false)
const errorMessage = ref('')
const validationErrors = ref<Record<string, string[]>>({})

// Fetch existing branch data
const fetchBranch = async () => {
  initialLoading.value = true
  try {
    const res = await $api(`/branches/${branchId}`)
    if (res.status && res.branch) {
      const b = res.branch
      nameAr.value = b.name_ar || ''
      address.value = b.address || ''
      phone.value = b.phone || ''
      notes.value = b.notes || ''
      isActive.value = b.is_active ?? true
      coveragePolygon.value = Array.isArray(b.coverage_polygon) ? b.coverage_polygon : []

      if (b.working_hours && Array.isArray(b.working_hours) && b.working_hours.length === 7) {
        workingHours.value = b.working_hours.map((day: any) => {
          if (!day.shifts) {
            day.shifts = getInitialShifts()
          }
          return day
        })
      }

      if (b.lat && b.lng) {
        lat.value = b.lat
        lng.value = b.lng
      }
    } else {
      errorMessage.value = 'لم يتم العثور على بيانات الفرع'
    }
  } catch {
    errorMessage.value = 'تعذر جلب بيانات الفرع'
  } finally {
    initialLoading.value = false
    await nextTick()
    await initMap()

    if (mapReady.value) {
      const L = (await import('leaflet')).default
      const centerLat = lat.value || (coveragePolygon.value[0]?.[0] ?? 33.3128)
      const centerLng = lng.value || (coveragePolygon.value[0]?.[1] ?? 44.3615)
      map.setView([centerLat, centerLng], 13)

      if (coveragePolygon.value.length > 0) {
        redrawPolygon(L)
        if (lat.value && lng.value) placeCenterMarker(L, lat.value, lng.value)
      } else if (lat.value && lng.value) {
        placeCenterMarker(L, lat.value, lng.value)
      }
    }
  }
}

// Init Leaflet map
const initMap = async () => {
  const L = (await import('leaflet')).default
  await import('leaflet/dist/leaflet.css')

  delete (L.Icon.Default.prototype as any)._getIconUrl
  L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
    iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
  })

  map = L.map('branch-edit-map').setView([33.3128, 44.3615], 11)

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 19,
  }).addTo(map)

  map.on('click', (e: any) => {
    const clickedLat = parseFloat(e.latlng.lat.toFixed(7))
    const clickedLng = parseFloat(e.latlng.lng.toFixed(7))

    if (coveragePolygon.value.length === 0) {
      lat.value = clickedLat
      lng.value = clickedLng
      placeCenterMarker(L, clickedLat, clickedLng)
    }
    coveragePolygon.value.push([clickedLat, clickedLng])
    redrawPolygon(L)
  })

  mapReady.value = true
}

const placeCenterMarker = (L: any, cLat: number, cLng: number) => {
  lat.value = cLat
  lng.value = cLng

  if (marker) marker.remove()
  marker = L.marker([cLat, cLng], { draggable: true }).addTo(map)
  marker.on('dragend', (e: any) => {
    const pos = e.target.getLatLng()
    lat.value = parseFloat(pos.lat.toFixed(7))
    lng.value = parseFloat(pos.lng.toFixed(7))
  })
}

const redrawPolygon = async (L?: any) => {
  if (!L) L = (await import('leaflet')).default

  if (polygonLayer) polygonLayer.remove()
  vertexMarkers.forEach((m: any) => m.remove())
  vertexMarkers = []

  if (coveragePolygon.value.length > 0) {
    coveragePolygon.value.forEach((pt) => {
      const vMarker = L.circleMarker(pt, {
        radius: 6,
        color: '#7367f0',
        fillColor: '#fff',
        fillOpacity: 1,
        weight: 3,
      }).addTo(map)
      vertexMarkers.push(vMarker)
    })

    if (coveragePolygon.value.length >= 3) {
      polygonLayer = L.polygon(coveragePolygon.value, {
        color: '#7367f0',
        fillColor: '#7367f0',
        fillOpacity: 0.25,
        weight: 3,
      }).addTo(map)
    } else if (coveragePolygon.value.length === 2) {
      polygonLayer = L.polyline(coveragePolygon.value, {
        color: '#7367f0',
        weight: 3,
        dashArray: '5, 5',
      }).addTo(map)
    }
  }
}

const undoLastPoint = async () => {
  if (coveragePolygon.value.length === 0) return
  coveragePolygon.value.pop()
  if (coveragePolygon.value.length === 0 && marker) {
    marker.remove()
    marker = null
    lat.value = null
    lng.value = null
  }
  await redrawPolygon()
}

const clearPolygon = async () => {
  coveragePolygon.value = []
  if (marker) { marker.remove(); marker = null }
  if (polygonLayer) { polygonLayer.remove(); polygonLayer = null }
  vertexMarkers.forEach((m: any) => m.remove())
  vertexMarkers = []
  lat.value = null
  lng.value = null
}

// Nominatim search
let searchTimer: any
const searchAddress = () => {
  clearTimeout(searchTimer)
  if (!addressSearch.value.trim()) { addressSuggestions.value = []; return }
  searchTimer = setTimeout(async () => {
    searchingAddress.value = true
    try {
      const res = await fetch(
        `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(addressSearch.value)}&format=json&limit=5&accept-language=ar`,
        { headers: { 'Accept-Language': 'ar' } }
      )
      addressSuggestions.value = await res.json()
    } catch { addressSuggestions.value = [] }
    finally { searchingAddress.value = false }
  }, 600)
}

const selectSuggestion = async (suggestion: any) => {
  const L = (await import('leaflet')).default
  const newLat = parseFloat(suggestion.lat)
  const newLng = parseFloat(suggestion.lon)
  map.setView([newLat, newLng], 14)
  placeCenterMarker(L, newLat, newLng)
  if (coveragePolygon.value.length === 0) {
    coveragePolygon.value.push([newLat, newLng])
    redrawPolygon(L)
  }
  addressSearch.value = suggestion.display_name
  addressSuggestions.value = []
}

// Update Branch
const updateBranch = async () => {
  if (coveragePolygon.value.length < 3) {
    errorMessage.value = 'يرجى رسم 3 نقاط على الأقل لإغلاق منطقة التغطية'
    return
  }

  loading.value = true
  errorMessage.value = ''
  validationErrors.value = {}

  try {
    const res = await $api(`/branches/${branchId}`, {
      method: 'PUT',
      body: {
        name_ar: nameAr.value,
        address: address.value || null,
        phone: phone.value || null,
        lat: lat.value,
        lng: lng.value,
        radius_km: 5,
        coverage_type: 'polygon',
        coverage_polygon: coveragePolygon.value,
        working_hours: workingHours.value,
        is_active: isActive.value,
        notes: notes.value || null,
      },
    })

    if (res.status) {
      router.push('/branches')
    } else {
      errorMessage.value = res.message || 'حدث خطأ أثناء تحديث الفرع'
    }
  } catch (e: any) {
    if (e?.errors) validationErrors.value = e.errors
    else errorMessage.value = e?.message || 'تعذر الاتصال بالخادم'
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await fetchBranch()
})

onBeforeUnmount(() => {
  if (map) { map.remove(); map = null }
})
</script>

<template>
  <div>
    <!-- Top Header Bar -->
    <div class="d-flex flex-wrap justify-start justify-sm-space-between gap-y-4 gap-x-6 mb-6">
      <div class="d-flex flex-column justify-center">
        <div class="d-flex align-center gap-2 mb-1">
          <VBtn icon variant="tonal" size="small" color="primary" @click="router.push('/branches')">
            <VIcon icon="tabler-arrow-right" />
          </VBtn>
          <h4 class="text-h4 font-weight-medium">
            تعديل بيانات وتغطية الفرع رقم (#{{ branchId }})
          </h4>
        </div>
        <div class="text-body-1 text-medium-emphasis">
          تحديث معلومات الفرع ورسم نطاق الخدمة الجغرافية وأوقات العمل الأسبوعية
        </div>
      </div>

      <div class="d-flex gap-3 align-center flex-wrap">
        <VBtn variant="tonal" color="secondary" @click="router.push('/branches')">
          إلغاء
        </VBtn>
        <VBtn color="primary" prepend-icon="tabler-device-floppy" :loading="loading || initialLoading" @click="updateBranch">
          حفظ التعديلات
        </VBtn>
      </div>
    </div>

    <!-- Loading State -->
    <VCard v-if="initialLoading" class="py-12 text-center">
      <VProgressCircular indeterminate color="primary" size="48" class="mb-4" />
      <div class="text-h6 font-weight-medium">جاري تحميل بيانات الفرع والخريطة...</div>
    </VCard>

    <template v-else>
      <VAlert v-if="errorMessage" type="error" variant="tonal" class="mb-6" closable @click:close="errorMessage = ''">
        {{ errorMessage }}
      </VAlert>

      <VRow>
        <!-- Right Column (In RTL): Map & Coverage Area (7 columns) -->
        <VCol cols="12" md="7">
          <VCard class="mb-6">
            <VCardItem class="pb-3">
              <template #title>
                <div class="d-flex align-center justify-space-between flex-wrap gap-4">
                  <div class="d-flex align-center gap-2">
                    <VIcon icon="tabler-map-pin" color="primary" size="24" />
                    <span class="text-h6 font-weight-medium">الخريطة ورسم نطاق التغطية الجغرافية</span>
                  </div>
                  <VChip :color="coveragePolygon.length >= 3 ? 'success' : 'warning'" variant="tonal" size="small">
                    {{ coveragePolygon.length >= 3 ? `المنطقة مكتملة (${coveragePolygon.length} نقاط)` : `مطلوب 3 نقاط على الأقل (${coveragePolygon.length}/3)` }}
                  </VChip>
                </div>
              </template>
            </VCardItem>

            <VDivider />

            <VCardText class="pt-4">
              <!-- Search Bar & Tools -->
              <div class="d-flex gap-3 flex-wrap align-center justify-space-between mb-4">
                <div class="position-relative flex-grow-1" style="min-inline-size: 280px;">
                  <AppTextField
                    v-model="addressSearch"
                    placeholder="ابحث عن اسم الحي أو الشارع للتوجه إليه على الخريطة..."
                    prepend-inner-icon="tabler-search"
                    :loading="searchingAddress"
                    clearable
                    @input="searchAddress"
                  />
                  <VCard
                    v-if="addressSuggestions.length > 0"
                    class="position-absolute w-100 elevation-8 mt-1"
                    style="z-index: 1050; max-height: 220px; overflow-y: auto;"
                  >
                    <VList density="compact">
                      <VListItem
                        v-for="s in addressSuggestions"
                        :key="s.place_id"
                        :title="s.display_name"
                        class="cursor-pointer"
                        @click="selectSuggestion(s)"
                      />
                    </VList>
                  </VCard>
                </div>

                <!-- Polygon Undo/Clear Controls -->
                <div class="d-flex gap-2">
                  <VBtn
                    variant="tonal"
                    color="secondary"
                    size="small"
                    prepend-icon="tabler-arrow-back-up"
                    :disabled="coveragePolygon.length === 0"
                    @click="undoLastPoint"
                  >
                    تراجع
                  </VBtn>
                  <VBtn
                    variant="tonal"
                    color="error"
                    size="small"
                    prepend-icon="tabler-trash"
                    :disabled="coveragePolygon.length === 0"
                    @click="clearPolygon"
                  >
                    مسح الرسم
                  </VBtn>
                </div>
              </div>

              <!-- Leaflet Map Container -->
              <div
                id="branch-edit-map"
                class="rounded border mb-3"
                style="height: 480px; cursor: crosshair; overflow: hidden;"
              />

              <!-- Instructions Footer -->
              <div class="d-flex align-center gap-2 text-caption text-medium-emphasis">
                <VIcon icon="tabler-info-circle" size="18" color="primary" />
                <span>انقر على الخريطة نقطة تلو الأخرى لوضع زوايا الحي أو المنطقة. سيتم رسم وتظليل نطاق الخدمة تلقائياً.</span>
              </div>
            </VCardText>
          </VCard>
        </VCol>

        <!-- Left Column (In RTL): Branch Info + Weekly Working Hours (5 columns) -->
        <VCol cols="12" md="5">
          <!-- Basic Branch Info Card -->
          <VCard title="البيانات الأساسية للفرع" class="mb-6">
            <VCardText>
              <VRow>
                <VCol cols="12">
                  <AppTextField
                    v-model="nameAr"
                    label="اسم الفرع *"
                    placeholder="مثال: فرع الكرادة"
                    :error-messages="validationErrors.name_ar"
                  />
                </VCol>
                <VCol cols="12">
                  <AppTextField
                    v-model="address"
                    label="العنوان التفصيلي"
                    placeholder="مثال: شارع 14 رمضان..."
                  />
                </VCol>
                <VCol cols="12">
                  <AppTextField
                    v-model="phone"
                    label="رقم الهاتف"
                    placeholder="07700000000"
                  />
                </VCol>
                <VCol cols="12">
                  <label class="v-label mb-1 text-body-2 font-weight-medium">ملاحظات إضافية</label>
                  <VTextarea
                    v-model="notes"
                    placeholder="أي تعليمات أو ملاحظات تخص الفرع..."
                    rows="2"
                  />
                </VCol>
                <VCol cols="12">
                  <VDivider class="my-1" />
                  <div class="d-flex align-center justify-space-between pt-1">
                    <div>
                      <div class="text-subtitle-2 font-weight-medium">حالة الفرع</div>
                      <div class="text-caption text-medium-emphasis">تفعيل استقبال الطلبات من هذا الفرع</div>
                    </div>
                    <VSwitch v-model="isActive" color="success" hide-details />
                  </div>
                </VCol>
              </VRow>
            </VCardText>
          </VCard>

          <!-- Weekly Working Hours Card -->
          <VCard class="mb-6">
            <VCardItem class="pb-2">
              <template #title>
                <div class="d-flex align-center justify-space-between flex-wrap gap-2">
                  <div class="d-flex align-center gap-2">
                    <VIcon icon="tabler-clock" color="primary" size="22" />
                    <span class="text-h6 font-weight-medium">أوقات العمل الأسبوعية</span>
                  </div>
                  <VBtn
                    variant="text"
                    color="primary"
                    size="small"
                    prepend-icon="tabler-copy"
                    @click="applyFirstDayTimesToAll"
                  >
                    تعميم على كل أيام العمل
                  </VBtn>
                </div>
              </template>
            </VCardItem>

            <VDivider />

            <VCardText class="pt-3">
              <div
                v-for="(day, index) in workingHours"
                :key="day.key"
                class="py-2"
                :class="{ 'border-b': index < workingHours.length - 1 }"
              >
                <div class="d-flex align-start flex-column gap-2">
                  <div class="d-flex align-center justify-space-between w-100">
                    <div class="d-flex align-center gap-2" style="min-inline-size: 110px;">
                      <VCheckbox
                        v-model="day.is_working"
                        color="primary"
                        hide-details
                        density="compact"
                      />
                      <span class="font-weight-medium text-body-2">{{ day.label }}</span>
                    </div>
                    <div v-if="!day.is_working">
                      <VChip color="error" variant="tonal" size="small">عطلة (مغلق)</VChip>
                    </div>
                  </div>

                  <!-- Shifts Config -->
                  <div v-if="day.is_working" class="w-100 mt-2 px-6">
                    <div v-for="period in ['morning', 'noon', 'evening']" :key="period" class="d-flex align-start gap-4 mb-2">
                      <div style="min-inline-size: 80px;" class="mt-1">
                        <VCheckbox 
                          v-model="day.shifts[period].is_active" 
                          :label="getPeriodLabel(period)" 
                          color="success"
                          hide-details 
                          density="compact" 
                        />
                      </div>
                      <div class="flex-grow-1" v-if="day.shifts[period].is_active">
                        <VCombobox
                          v-model="day.shifts[period].times"
                          chips
                          multiple
                          closable-chips
                          density="compact"
                          placeholder="اكتب الوقت واضغط Enter (مثال: 08:30)"
                          hide-details
                        />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </VCardText>
          </VCard>

          <!-- Coverage Summary Card -->
          <VCard title="ملخص التغطية">
            <VCardText>
              <div class="d-flex justify-space-between align-center mb-2">
                <span class="text-body-2 text-medium-emphasis">عدد إحداثيات الرؤوس:</span>
                <span class="font-weight-bold">{{ coveragePolygon.length }} نقاط</span>
              </div>

              <div v-if="lat && lng" class="d-flex justify-space-between align-center">
                <span class="text-body-2 text-medium-emphasis">المركز المعتمد:</span>
                <span class="text-caption font-monospace">{{ lat }}, {{ lng }}</span>
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
    </template>
  </div>
</template>
