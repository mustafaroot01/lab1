<script setup lang="ts">
import { ref, onMounted } from 'vue'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import CoverageAlgorithmTimeline from '@/components/coverage/CoverageAlgorithmTimeline.vue'

// State
const loading = ref(false)
const result = ref<any>(null)
const debugTimeline = ref<any[]>([])
const executionTime = ref(0)
const snackbar = ref(false)
const snackbarText = ref('')
const snackbarColor = ref('success')

// Map variables
let map: L.Map
let marker: L.Marker | null = null

const initMap = () => {
  if (typeof window !== 'undefined') {
    (window as any).L = L
  }

  map = L.map('simulator-map').setView([33.3128, 44.3615], 11) // Baghdad coordinates
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
  }).addTo(map)

  map.on('click', (e: any) => {
    simulateCoverage(e.latlng.lat, e.latlng.lng)
  })
}

const simulateCoverage = async (lat: number, lng: number) => {
  loading.value = true
  result.value = null
  debugTimeline.value = []

  // Add or move marker
  if (marker) {
    marker.setLatLng([lat, lng])
  } else {
    marker = L.marker([lat, lng]).addTo(map)
  }

  try {
    const res = await $api('/coverage/check', {
      method: 'POST',
      body: { lat, lng, debug: true }
    })
    
    if (res.data) {
      result.value = res.data.result
      debugTimeline.value = res.data.debug_timeline
      executionTime.value = res.data.total_execution_time_ms
    } else {
      showToast(res.message, 'error')
    }
  } catch (err: any) {
    showToast(err.response?._data?.message || 'أداة المحاكاة معطلة أو حدث خطأ', 'error')
  } finally {
    loading.value = false
  }
}

const showToast = (text: string, color = 'success') => {
  snackbarText.value = text
  snackbarColor.value = color
  snackbar.value = true
}

const copyJson = () => {
  const dataToCopy = JSON.stringify({
    coordinates: marker?.getLatLng(),
    result: result.value,
    timeline: debugTimeline.value,
    total_time: executionTime.value
  }, null, 2)
  
  navigator.clipboard.writeText(dataToCopy)
  showToast('تم النسخ للحافظة')
}

onMounted(() => {
  initMap()
})
</script>

<template>
  <div>
    <VRow>
      <!-- Simulator Map -->
      <VCol cols="12" md="8">
        <VCard title="أداة محاكاة التغطية (Coverage Simulator)">
          <VCardText>
            <div class="text-body-1 mb-4">
              اضغط على أي مكان في الخريطة لإسقاط الدبوس واختبار التغطية الجغرافية للمكان، ومراقبة مسار استراتيجيات المحرك بالتفصيل.
            </div>
            
            <div style="position: relative;">
              <VOverlay
                v-model="loading"
                contained
                class="align-center justify-center"
              >
                <VProgressCircular indeterminate color="primary" />
              </VOverlay>
              <div id="simulator-map" style="height: 550px; width: 100%; z-index: 1;" class="rounded border"></div>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Simulation Results & Timeline -->
      <VCol cols="12" md="4">
        <VCard title="نتائج الفحص" class="h-100">
          <template #append>
            <VBtn v-if="result" icon="tabler-copy" variant="text" size="small" @click="copyJson" />
          </template>
          
          <VCardText v-if="!result && !loading" class="text-center pt-10">
            <VIcon icon="tabler-map-pin" size="48" color="secondary" class="mb-4 opacity-50" />
            <div class="text-body-1 text-disabled">
              قم باختيار موقع على الخريطة لبدء المحاكاة
            </div>
          </VCardText>

          <VCardText v-if="result">
            <!-- Summary Alert -->
            <VAlert
              :type="result.covered ? 'success' : 'error'"
              variant="tonal"
              class="mb-6"
            >
              <div class="text-subtitle-1 font-weight-bold mb-1">
                {{ result.covered ? 'المنطقة مغطاة' : 'المنطقة غير مغطاة' }}
              </div>
              <div v-if="result.covered" class="text-body-2">
                <strong>المنطقة:</strong> {{ result.zone?.name }} <br>
                <strong>الرسوم:</strong> {{ result.fee }} د.ع <br>
                <strong>نوع التطابق:</strong> {{ result.match_type === 'polygon_exact' ? 'تطابق مباشر' : 'مسافة سماح (Grace)' }} <br>
                <span v-if="result.distance"><strong>البعد عن الحدود:</strong> {{ result.distance }} م</span>
              </div>
              <div v-else class="text-body-2">
                الموقع المختار يقع خارج جميع مناطق التغطية الفعالة أو مسافات السماح الخاصة بها.
              </div>
            </VAlert>

            <VDivider class="mb-4" />

            <!-- Execution Timeline -->
            <h6 class="text-h6 mb-4">مسار الخوارزمية (Engine Trace)</h6>
            <CoverageAlgorithmTimeline :timeline="debugTimeline" />

            <VDivider class="my-4" />
            <div class="d-flex justify-space-between align-center text-caption text-disabled">
              <span>زمن التنفيذ الكلي:</span>
              <span class="font-weight-bold">{{ executionTime }} ms</span>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Snackbar -->
    <VSnackbar v-model="snackbar" :color="snackbarColor" :timeout="3000">
      {{ snackbarText }}
    </VSnackbar>
  </div>
</template>
