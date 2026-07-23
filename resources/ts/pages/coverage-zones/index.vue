<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import 'leaflet-draw/dist/leaflet.draw.css'
import CoverageZoneDialog from '@/components/coverage/CoverageZoneDialog.vue'

// Types
interface CoverageZone {
  id: number
  name: string
  coverage_type: 'POLYGON' | 'RADIUS'
  radius_meters: number | null
  center_lat: number | null
  center_lng: number | null
  pricing_type: 'FIXED' | 'RULE_BASED'
  service_fee: number
  priority: number
  status: 'ACTIVE' | 'INACTIVE' | 'MAINTENANCE'
  free_visit_threshold: number | null
  geojson?: any
}

// State
const zones = ref<CoverageZone[]>([])
const loading = ref(false)
const dialog = ref(false)
const deleteDialog = ref(false)
const isEditing = ref(false)
const targetZone = ref<CoverageZone | null>(null)

const form = ref({
  name: '',
  coverage_type: 'POLYGON',
  geojson: null as any,
  center_lat: null as number | null,
  center_lng: null as number | null,
  radius_meters: null as number | null,
  pricing_type: 'FIXED',
  service_fee: 0,
  free_visit_threshold: null as number | null,
  priority: 0,
  grace_distance: null as number | null,
  status: 'ACTIVE',
  created_at: null,
  updated_at: null,
})

const formErrors = ref<any>({})
const snackbar = ref(false)
const snackbarText = ref('')
const snackbarColor = ref('success')

// Map variables
let map: L.Map
let drawnItems: L.FeatureGroup
let drawnLayersMap: Record<number, L.Layer> = {}

// Fetch Data
const fetchZones = async () => {
  loading.value = true
  try {
    const res = await $api('/coverage-zones')
    zones.value = res.data || []
    
    // Clear existing layers
    drawnItems.clearLayers()
    drawnLayersMap = {}
    
    // Add zones to map
    zones.value.forEach(zone => {
      let layer: L.Layer | null = null
      
      if (zone.coverage_type === 'POLYGON' && zone.geojson) {
        layer = L.geoJSON(zone.geojson).getLayers()[0]
      } else if (zone.coverage_type === 'RADIUS' && zone.center_lat && zone.center_lng && zone.radius_meters) {
        layer = L.circle([zone.center_lat, zone.center_lng], { radius: zone.radius_meters })
      }
      
      if (layer) {
        // Different colors based on status
        const color = zone.status === 'ACTIVE' ? '#28c76f' : (zone.status === 'MAINTENANCE' ? '#ff9f43' : '#ea5455')
        if (layer instanceof L.Path) {
          layer.setStyle({ color, fillColor: color, fillOpacity: 0.2 })
        }
        
        layer.bindPopup(`<b>${zone.name}</b><br>التكلفة: ${zone.service_fee} د.ع<br>الأولوية: ${zone.priority}`)
        drawnItems.addLayer(layer)
        drawnLayersMap[zone.id] = layer
      }
    })
  } catch (err) {
    showToast('فشل في جلب البيانات', 'error')
  } finally {
    loading.value = false
  }
}

const showToast = (text: string, color = 'success') => {
  snackbarText.value = text
  snackbarColor.value = color
  snackbar.value = true
}

const resetForm = () => {
  form.value = {
    name: '',
    coverage_type: 'POLYGON',
    geojson: null,
    center_lat: null,
    center_lng: null,
    radius_meters: null,
    pricing_type: 'FIXED',
    service_fee: 0,
    free_visit_threshold: null,
    priority: 0,
    grace_distance: null,
    status: 'ACTIVE',
    created_at: null,
    updated_at: null,
  }
  formErrors.value = {}
  isEditing.value = false
  targetZone.value = null
}

const openAddDialog = (type: 'POLYGON' | 'RADIUS', geometryData: any) => {
  resetForm()
  form.value.coverage_type = type
  if (type === 'POLYGON') {
    form.value.geojson = geometryData
  } else {
    form.value.center_lat = geometryData.lat
    form.value.center_lng = geometryData.lng
    form.value.radius_meters = Math.round(geometryData.radius)
  }
  dialog.value = true
}

const editZone = (zone: CoverageZone) => {
  resetForm()
  isEditing.value = true
  targetZone.value = zone
  form.value = {
    name: zone.name,
    coverage_type: zone.coverage_type,
    geojson: zone.geojson,
    center_lat: zone.center_lat,
    center_lng: zone.center_lng,
    radius_meters: zone.radius_meters,
    pricing_type: zone.pricing_type,
    service_fee: zone.service_fee,
    free_visit_threshold: zone.free_visit_threshold,
    priority: zone.priority,
    grace_distance: zone.grace_distance || null,
    status: zone.status,
    created_at: zone.created_at,
    updated_at: zone.updated_at,
  }
  dialog.value = true
  
  // Center map on zone
  if (drawnLayersMap[zone.id]) {
    const layer = drawnLayersMap[zone.id]
    if (layer instanceof L.Polygon || layer instanceof L.Circle) {
      map.fitBounds(layer.getBounds())
      layer.openPopup()
    }
  }
}

const confirmDelete = (zone: CoverageZone) => {
  targetZone.value = zone
  deleteDialog.value = true
}

const saveZone = async () => {
  formErrors.value = {}
  
  if (form.value.coverage_type === 'POLYGON' && !form.value.geojson) {
    showToast('الرجاء رسم المنطقة على الخريطة أولاً باستخدام أدوات الرسم', 'error')
    return
  }
  
  if (form.value.coverage_type === 'RADIUS' && !form.value.center_lat) {
    showToast('الرجاء رسم الدائرة على الخريطة أولاً', 'error')
    return
  }

  try {
    const method = isEditing.value ? 'PUT' : 'POST'
    const url = isEditing.value ? `/coverage-zones/${targetZone.value?.id}` : `/coverage-zones`
    
    await $api(url, { method, body: form.value })
    showToast(isEditing.value ? 'تم التعديل بنجاح' : 'تمت الإضافة بنجاح')
    dialog.value = false
    fetchZones()
  } catch (err: any) {
    const errorData = err.response?._data || err.response?.data || err.data;
    if (errorData?.errors) {
      formErrors.value = errorData.errors
      showToast('يرجى التحقق من الحقول المطلوبة', 'error')
    } else {
      showToast(errorData?.message || 'حدث خطأ أثناء الحفظ', 'error')
    }
  }
}

const executeDelete = async () => {
  if (!targetZone.value) return
  try {
    await $api(`/coverage-zones/${targetZone.value.id}`, { method: 'DELETE' })
    showToast('تم الحذف بنجاح')
    deleteDialog.value = false
    fetchZones()
  } catch (err) {
    showToast('فشل في الحذف', 'error')
  }
}

onMounted(async () => {
  // Fix Leaflet-Draw compatibility with Vite by providing global L
  if (typeof window !== 'undefined') {
    (window as any).L = L
  }
  // Dynamically import leaflet-draw so it runs AFTER window.L is set
  await import('leaflet-draw')

  map = L.map('coverage-map').setView([33.3128, 44.3615], 9) // Baghdad coordinates

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
  }).addTo(map)

  drawnItems = new L.FeatureGroup()
  map.addLayer(drawnItems)

  const drawControl = new L.Control.Draw({
    edit: {
      featureGroup: drawnItems,
      remove: false // We use the Vue table for deletion
    },
    draw: {
      polygon: {
        allowIntersection: false,
        drawError: {
          color: '#e1e100',
          message: '<strong>خطأ:</strong> لا يمكن تداخل خطوط المضلع!'
        },
        shapeOptions: { color: '#28c76f' }
      },
      circle: {
        shapeOptions: { color: '#28c76f' }
      },
      marker: false,
      polyline: false,
      rectangle: false,
      circlemarker: false
    }
  })
  map.addControl(drawControl)

  map.on(L.Draw.Event.CREATED, function (e: any) {
    const type = e.layerType
    const layer = e.layer
    
    // We don't add it to drawnItems yet until they save the form
    
    if (type === 'polygon') {
      openAddDialog('POLYGON', layer.toGeoJSON().geometry)
    } else if (type === 'circle') {
      const latlng = layer.getLatLng()
      const radius = layer.getRadius()
      openAddDialog('RADIUS', { lat: latlng.lat, lng: latlng.lng, radius })
    }
  })
  
  map.on(L.Draw.Event.EDITED, function (e: any) {
     const layers = e.layers;
     layers.eachLayer(function (layer: any) {
         // Find which zone ID this layer belongs to
         let zoneId = null;
         for (const [id, l] of Object.entries(drawnLayersMap)) {
             if (l === layer) {
                 zoneId = id;
                 break;
             }
         }
         
         if (zoneId) {
             const zone = zones.value.find(z => z.id === Number(zoneId));
             if (zone) {
                targetZone.value = zone;
                isEditing.value = true;
                form.value = { ...zone } as any;
                
                if (layer instanceof L.Polygon) {
                    form.value.coverage_type = 'POLYGON';
                    form.value.geojson = layer.toGeoJSON().geometry;
                } else if (layer instanceof L.Circle) {
                    form.value.coverage_type = 'RADIUS';
                    const latlng = layer.getLatLng();
                    form.value.center_lat = latlng.lat;
                    form.value.center_lng = latlng.lng;
                    form.value.radius_meters = Math.round(layer.getRadius());
                }
                
                // Show dialog to confirm edit
                dialog.value = true;
             }
         }
     });
  });

  fetchZones()
})
</script>

<template>
  <div>
    <VCard title="مناطق التغطية الجغرافية" class="mb-6">
      <VCardText>
        <div class="text-body-1 mb-4">
          قم برسم مضلع (Polygon) أو دائرة (Radius) على الخريطة لتحديد منطقة تغطية جديدة. 
          يمكنك لاحقاً تعديلها أو ربطها بتكاليف معينة.
        </div>
        <div id="coverage-map" style="height: 500px; width: 100%; z-index: 1;" class="rounded border"></div>
      </VCardText>
    </VCard>

    <VCard title="قائمة المناطق المضافة">
      <VDataTable
        :headers="[
          { title: 'اسم المنطقة', key: 'name' },
          { title: 'النوع', key: 'coverage_type' },
          { title: 'الأولوية', key: 'priority' },
          { title: 'مسافة السماح', key: 'grace_distance' },
          { title: 'رسوم الزيارة', key: 'service_fee' },
          { title: 'حد الزيارة المجانية', key: 'free_visit_threshold' },
          { title: 'الحالة', key: 'status' },
          { title: 'الإجراءات', key: 'actions', sortable: false, align: 'end' }
        ]"
        :items="zones"
        :loading="loading"
        hover
      >
        <template #item.coverage_type="{ item }">
          <VChip size="small" :color="item.coverage_type === 'POLYGON' ? 'primary' : 'info'">
            {{ item.coverage_type === 'POLYGON' ? 'مضلع جغرافي' : 'نطاق دائري' }}
          </VChip>
        </template>
        
        <template #item.grace_distance="{ item }">
          {{ item.grace_distance ? `${item.grace_distance} م` : 'افتراضي' }}
        </template>

        <template #item.free_visit_threshold="{ item }">
          <span v-if="item.free_visit_threshold">{{ item.free_visit_threshold }} د.ع</span>
          <span v-else class="text-medium-emphasis text-caption">لا يوجد</span>
        </template>

        <template #item.status="{ item }">
          <VChip size="small" :color="item.status === 'ACTIVE' ? 'success' : (item.status === 'MAINTENANCE' ? 'warning' : 'error')">
            {{ item.status === 'ACTIVE' ? 'فعال' : (item.status === 'MAINTENANCE' ? 'صيانة' : 'متوقف') }}
          </VChip>
        </template>
        
        <template #item.actions="{ item }">
          <VBtn icon="tabler-edit" variant="text" size="small" color="primary" @click="editZone(item)" />
          <VBtn icon="tabler-trash" variant="text" size="small" color="error" @click="confirmDelete(item)" />
        </template>
      </VDataTable>
    </VCard>

    <!-- Add/Edit Dialog -->
    <CoverageZoneDialog
      v-model="dialog"
      :is-editing="isEditing"
      :form-data="form"
      :form-errors="formErrors"
      @save="saveZone"
      @cancel="fetchZones"
    />

    <!-- Delete Dialog -->
    <VDialog v-model="deleteDialog" max-width="400">
      <VCard>
        <VCardText class="text-center pt-6 pb-2">
          <VIcon icon="tabler-alert-circle" color="error" size="64" class="mb-4" />
          <h6 class="text-h6 font-weight-medium">تأكيد الحذف</h6>
          <p class="mt-2">هل أنت متأكد من حذف منطقة ({{ targetZone?.name }})؟</p>
        </VCardText>
        <VCardActions class="justify-center gap-4 pb-6">
          <VBtn color="secondary" variant="tonal" @click="deleteDialog = false">إلغاء</VBtn>
          <VBtn color="error" @click="executeDelete">نعم، احذف</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Snackbar -->
    <VSnackbar v-model="snackbar" :color="snackbarColor" :timeout="3000">
      {{ snackbarText }}
    </VSnackbar>
  </div>
</template>
