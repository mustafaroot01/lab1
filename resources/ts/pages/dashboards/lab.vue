<script setup lang="ts">
import { $api } from '@/utils/api'
import { useTheme } from 'vuetify'

const theme = useTheme()
const loading = ref(true)
const dashboardData = ref<any>({
  kpi: {
    today_orders: { total: 0, pending: 0, active: 0, completed: 0, cancelled: 0, growth: 0 },
    technicians: { active: 0, busy: 0, total: 0 },
    tickets: { open: 0, closed: 0 },
    revenue: { today: 0, month: 0, last_month: 0, growth: 0 },
  },
  urgent_orders: [],
  charts: {
    flow: { dates: [], completed: [], active: [], cancelled: [], revenue: [] },
    top_tests: [],
    branch_distribution: [],
  },
  top_technicians: [],
  timeline: [],
})

const fetchStats = async () => {
  loading.value = true
  try {
    const res = await $api('/admin/dashboard/stats')
    if (res?.status && res.data) {
      dashboardData.value = res.data
    }
  } catch (err) {
    console.error('[Dashboard] Error fetching stats:', err)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchStats()
})

// ── Status Colors & Labels ──────────────────────────
const statusBadgeColor = (status: string) => {
  switch (status) {
    case 'awaiting_technician': return 'warning'
    case 'pending':
    case 'confirmed': return 'info'
    case 'sample_collected': return 'secondary'
    case 'in_progress':
    case 'technician_assigned':
    case 'on_the_way': return 'primary'
    case 'completed': return 'success'
    case 'cancelled': return 'error'
    default: return 'default'
  }
}

// ── ApexCharts Options: Orders 7-Day Flow ───────────
const flowChartOptions = computed(() => {
  const currentTheme = theme.current.value.colors
  return {
    chart: {
      type: 'area',
      stacked: false,
      parentHeightOffset: 0,
      toolbar: { show: false },
      fontFamily: 'Inter, sans-serif',
    },
    dataLabels: { enabled: false },
    stroke: {
      curve: 'smooth',
      width: [3, 2, 2],
    },
    colors: [currentTheme.success, currentTheme.primary, currentTheme.error],
    fill: {
      type: 'gradient',
      gradient: {
        shadeIntensity: 0.8,
        opacityFrom: 0.45,
        opacityTo: 0.05,
        stops: [0, 95, 100],
      },
    },
    grid: {
      borderColor: 'rgba(var(--v-border-color), 0.15)',
      strokeDashArray: 4,
      padding: { top: 10, right: 10, bottom: 0, left: 10 },
    },
    xaxis: {
      categories: dashboardData.value.charts?.flow?.dates || [],
      labels: {
        style: { colors: 'rgba(var(--v-theme-on-surface), 0.6)', fontSize: '12px' },
      },
      axisBorder: { show: false },
      axisTicks: { show: false },
    },
    yaxis: {
      labels: {
        style: { colors: 'rgba(var(--v-theme-on-surface), 0.6)', fontSize: '12px' },
      },
    },
    legend: {
      position: 'top',
      horizontalAlign: 'right',
      labels: { colors: 'rgba(var(--v-theme-on-surface), 0.8)' },
    },
  }
})

const flowChartSeries = computed(() => [
  { name: 'طلبات مكتملة', data: dashboardData.value.charts?.flow?.completed || [] },
  { name: 'قيد التنفيذ / الميدان', data: dashboardData.value.charts?.flow?.active || [] },
  { name: 'ملغاة', data: dashboardData.value.charts?.flow?.cancelled || [] },
])

// ── ApexCharts Options: Top Tests Bar Chart ──────────
const topTestsChartOptions = computed(() => {
  const currentTheme = theme.current.value.colors
  return {
    chart: {
      type: 'bar',
      parentHeightOffset: 0,
      toolbar: { show: false },
    },
    plotOptions: {
      bar: {
        horizontal: true,
        borderRadius: 6,
        barHeight: '60%',
        distributed: true,
      },
    },
    colors: [
      currentTheme.primary,
      currentTheme.success,
      currentTheme.info,
      currentTheme.warning,
      currentTheme.secondary,
      currentTheme.error,
    ],
    dataLabels: {
      enabled: true,
      style: { fontSize: '12px', fontWeight: 600 },
      formatter: (val: number) => `${val} طلب`,
    },
    grid: {
      borderColor: 'rgba(var(--v-border-color), 0.15)',
      strokeDashArray: 4,
    },
    xaxis: {
      categories: dashboardData.value.charts?.top_tests?.map((t: any) => t.name) || [],
      labels: {
        style: { colors: 'rgba(var(--v-theme-on-surface), 0.6)', fontSize: '11px' },
      },
    },
    yaxis: {
      labels: {
        style: { colors: 'rgba(var(--v-theme-on-surface), 0.85)', fontSize: '12px', fontWeight: 600 },
      },
    },
    legend: { show: false },
  }
})

const topTestsChartSeries = computed(() => [
  {
    name: 'عدد الطلبات',
    data: dashboardData.value.charts?.top_tests?.map((t: any) => t.count) || [],
  },
])
</script>

<template>
  <div class="healthy-lab-dashboard">
    <!-- ══ Welcome Banner + Refresh Action ══ -->
    <div class="d-flex flex-wrap align-center justify-space-between gap-4 mb-6">
      <div>
        <div class="d-flex align-center gap-2 mb-1">
          <VIcon icon="tabler-heart-rate-monitor" size="28" color="primary" />
          <h4 class="text-h4 font-weight-bold text-high-emphasis">
            مركز عمليات مختبر Healthy Lab 🧪
          </h4>
        </div>
        <p class="text-body-1 text-medium-emphasis mb-0">
          متابعة حية وشاملة لطلبات سحب العينات المنزلية والميدانية وتذاكر المراجعين الفورية
        </p>
      </div>

      <div class="d-flex align-center gap-3">
        <VBtn
          variant="tonal"
          color="secondary"
          prepend-icon="tabler-refresh"
          :loading="loading"
          @click="fetchStats"
        >
          تحديث البيانات
        </VBtn>
        <VBtn
          color="primary"
          prepend-icon="tabler-plus"
          to="/orders"
        >
          إدارة الطلبات
        </VBtn>
      </div>
    </div>

    <!-- Loading Skeleton for entire dashboard -->
    <div v-if="loading && !dashboardData.kpi.today_orders.total" class="py-12">
      <VRow>
        <VCol v-for="i in 4" :key="i" cols="12" sm="6" lg="3">
          <VSkeleton height="120" rounded="lg" />
        </VCol>
        <VCol cols="12" lg="8">
          <VSkeleton height="360" rounded="lg" />
        </VCol>
        <VCol cols="12" lg="4">
          <VSkeleton height="360" rounded="lg" />
        </VCol>
      </VRow>
    </div>

    <!-- ══ Dashboard Content ══ -->
    <template v-else>
      <!-- ── 1. KPI Cards Row ── -->
      <VRow class="match-height mb-6">
        <!-- Today's Orders -->
        <VCol cols="12" sm="6" lg="3">
          <VCard class="kpi-card border h-100">
            <VCardText class="d-flex flex-column justify-space-between h-100 pa-5">
              <div class="d-flex align-center justify-space-between mb-3">
                <VAvatar size="48" color="primary" variant="tonal" class="rounded-lg">
                  <VIcon icon="tabler-flask-2" size="26" />
                </VAvatar>
                <VChip
                  :color="dashboardData.kpi.today_orders.growth >= 0 ? 'success' : 'error'"
                  size="small"
                  variant="tonal"
                  class="font-weight-bold"
                >
                  <VIcon :icon="dashboardData.kpi.today_orders.growth >= 0 ? 'tabler-trending-up' : 'tabler-trending-down'" size="14" class="me-1" />
                  {{ dashboardData.kpi.today_orders.growth >= 0 ? '+' : '' }}{{ dashboardData.kpi.today_orders.growth }}%
                </VChip>
              </div>
              <div>
                <span class="text-caption text-medium-emphasis font-weight-semibold d-block mb-1">طلبات اليوم</span>
                <div class="d-flex align-baseline gap-2">
                  <h3 class="text-h3 font-weight-bold text-primary">{{ dashboardData.kpi.today_orders.total }}</h3>
                  <span class="text-xs text-medium-emphasis">طلب جديد</span>
                </div>
              </div>
              <!-- Micro breakdown -->
              <div class="d-flex align-center justify-space-between border-t pt-3 mt-3 text-caption">
                <span class="text-success font-weight-semibold">✅ {{ dashboardData.kpi.today_orders.completed }} مكتمل</span>
                <span class="text-info font-weight-semibold">⏳ {{ dashboardData.kpi.today_orders.active }} ميداني</span>
                <span class="text-warning font-weight-semibold">⚠️ {{ dashboardData.kpi.today_orders.pending }} معلق</span>
              </div>
            </VCardText>
          </VCard>
        </VCol>

        <!-- Active Technicians -->
        <VCol cols="12" sm="6" lg="3">
          <VCard class="kpi-card border h-100">
            <VCardText class="d-flex flex-column justify-space-between h-100 pa-5">
              <div class="d-flex align-center justify-space-between mb-3">
                <VAvatar size="48" color="success" variant="tonal" class="rounded-lg">
                  <VIcon icon="tabler-car" size="26" />
                </VAvatar>
                <span class="text-xs font-weight-bold text-success bg-var-theme-success-08 px-2 py-1 rounded-pill">
                  نشط ميدانياً
                </span>
              </div>
              <div>
                <span class="text-caption text-medium-emphasis font-weight-semibold d-block mb-1">الفنيون والميدان</span>
                <div class="d-flex align-baseline gap-2">
                  <h3 class="text-h3 font-weight-bold text-success">{{ dashboardData.kpi.technicians.active }}</h3>
                  <span class="text-xs text-medium-emphasis">من أصل {{ dashboardData.kpi.technicians.total }} فني مسجل</span>
                </div>
              </div>
              <div class="d-flex align-center justify-space-between border-t pt-3 mt-3 text-caption">
                <span class="text-primary font-weight-bold">🚗 {{ dashboardData.kpi.technicians.busy }} في مهمة سحب الآن</span>
                <RouterLink to="/technicians" class="text-primary text-decoration-none font-weight-semibold">
                  متابعة الميدان ←
                </RouterLink>
              </div>
            </VCardText>
          </VCard>
        </VCol>

        <!-- Live Support Tickets -->
        <VCol cols="12" sm="6" lg="3">
          <VCard class="kpi-card border h-100">
            <VCardText class="d-flex flex-column justify-space-between h-100 pa-5">
              <div class="d-flex align-center justify-space-between mb-3">
                <VAvatar size="48" color="info" variant="tonal" class="rounded-lg">
                  <VIcon icon="tabler-headset" size="26" />
                </VAvatar>
                <VChip
                  v-if="dashboardData.kpi.tickets.open > 0"
                  color="warning"
                  size="small"
                  variant="flat"
                  class="font-weight-bold animate-pulse"
                >
                  فوري
                </VChip>
              </div>
              <div>
                <span class="text-caption text-medium-emphasis font-weight-semibold d-block mb-1">تذاكر الدعم المباشر</span>
                <div class="d-flex align-baseline gap-2">
                  <h3 class="text-h3 font-weight-bold text-info">{{ dashboardData.kpi.tickets.open }}</h3>
                  <span class="text-xs text-medium-emphasis">محادثة حية مفتوحة</span>
                </div>
              </div>
              <div class="d-flex align-center justify-space-between border-t pt-3 mt-3 text-caption">
                <span class="text-medium-emphasis">🔒 {{ dashboardData.kpi.tickets.closed }} تذكرة مغلقة</span>
                <RouterLink to="/apps/chat" class="text-info text-decoration-none font-weight-bold">
                  دخول المحادثات ←
                </RouterLink>
              </div>
            </VCardText>
          </VCard>
        </VCol>

        <!-- Revenue Overview -->
        <VCol cols="12" sm="6" lg="3">
          <VCard class="kpi-card border h-100">
            <VCardText class="d-flex flex-column justify-space-between h-100 pa-5">
              <div class="d-flex align-center justify-space-between mb-3">
                <VAvatar size="48" color="warning" variant="tonal" class="rounded-lg">
                  <VIcon icon="tabler-cash" size="26" />
                </VAvatar>
                <VChip
                  :color="dashboardData.kpi.revenue.growth >= 0 ? 'success' : 'error'"
                  size="small"
                  variant="tonal"
                  class="font-weight-bold"
                >
                  {{ dashboardData.kpi.revenue.growth >= 0 ? '+' : '' }}{{ dashboardData.kpi.revenue.growth }}% شهرياً
                </VChip>
              </div>
              <div>
                <span class="text-caption text-medium-emphasis font-weight-semibold d-block mb-1">إيرادات شهر {{ new Date().toLocaleDateString('ar', { month: 'long' }) }}</span>
                <div class="d-flex align-baseline gap-1">
                  <h3 class="text-h3 font-weight-bold text-high-emphasis">{{ dashboardData.kpi.revenue.month?.toLocaleString() }}</h3>
                  <span class="text-xs font-weight-semibold text-warning">د.ع</span>
                </div>
              </div>
              <div class="d-flex align-center justify-space-between border-t pt-3 mt-3 text-caption">
                <span class="font-weight-medium text-high-emphasis">إيرادات اليوم: {{ dashboardData.kpi.revenue.today?.toLocaleString() }} د.ع</span>
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>

      <!-- ── 2. Urgent Orders Table + Top Tests Bar Chart ── -->
      <VRow class="match-height mb-6">
        <!-- Urgent Orders Table -->
        <VCol cols="12" lg="8">
          <VCard class="border h-100">
            <VCardItem class="pb-3">
              <template #title>
                <div class="d-flex align-center gap-2">
                  <VIcon icon="tabler-alert-triangle" size="22" color="warning" />
                  <span class="text-h6 font-weight-bold">الطلبات المستعجلة والحرجة قيد المتابعة</span>
                </div>
              </template>
              <template #subtitle>
                الطلبات التي بانتظار تعيين فني ميداني أو العينات المسحوبة بانتظار رفع تقرير النتيجة
              </template>
              <template #append>
                <VBtn size="small" variant="text" color="primary" to="/orders">
                  كل الطلبات
                  <VIcon icon="tabler-chevron-left" class="ms-1" />
                </VBtn>
              </template>
            </VCardItem>

            <VDivider />

            <VTable class="text-no-wrap">
              <thead>
                <tr>
                  <th>رقم الطلب</th>
                  <th>المريض</th>
                  <th>الفرع</th>
                  <th>الفني الميداني</th>
                  <th>الموعد</th>
                  <th>الحالة</th>
                  <th>إجراء سريع</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="!dashboardData.urgent_orders.length">
                  <td colspan="7" class="text-center py-6 text-disabled">
                    <VIcon icon="tabler-circle-check" size="32" class="mb-1 text-success opacity-60" />
                    <div>ممتاز! لا توجد طلبات معلقة أو حرجة تتطلب تدخلاً حالياً</div>
                  </td>
                </tr>
                <tr v-for="order in dashboardData.urgent_orders" :key="order.id" class="hover:bg-var-theme-surface">
                  <td>
                    <RouterLink :to="`/orders`" class="font-weight-bold text-primary text-decoration-none">
                      #{{ order.id }}
                    </RouterLink>
                  </td>
                  <td>
                    <div class="font-weight-semibold text-body-2">{{ order.patient_name }}</div>
                    <div class="text-caption text-medium-emphasis" dir="ltr">{{ order.patient_phone }}</div>
                  </td>
                  <td>
                    <VChip size="small" variant="tonal" color="secondary">{{ order.branch_name }}</VChip>
                  </td>
                  <td>
                    <div v-if="order.technician" class="d-flex align-center gap-1.5">
                      <VAvatar size="26" color="primary" variant="tonal">
                        <span class="text-xs font-weight-bold">{{ avatarText(order.technician.name) }}</span>
                      </VAvatar>
                      <div>
                        <div class="text-body-2 font-weight-medium">{{ order.technician.name }}</div>
                        <span class="text-caption text-disabled" dir="ltr">{{ order.technician.phone }}</span>
                      </div>
                    </div>
                    <VChip v-else size="small" color="warning" variant="flat">
                      ⚠️ غير معين
                    </VChip>
                  </td>
                  <td>
                    <div class="text-body-2">{{ order.visit_date || '—' }}</div>
                    <span class="text-caption text-medium-emphasis">{{ order.visit_time || '' }}</span>
                  </td>
                  <td>
                    <VChip :color="statusBadgeColor(order.status)" size="small" variant="tonal" class="font-weight-bold">
                      {{ order.status_label }}
                    </VChip>
                  </td>
                  <td>
                    <VBtn
                      size="x-small"
                      :color="order.status === 'awaiting_technician' ? 'warning' : 'primary'"
                      variant="tonal"
                      to="/orders"
                    >
                      {{ order.status === 'awaiting_technician' ? 'تعيين فني' : 'متابعة وتحديث' }}
                    </VBtn>
                  </td>
                </tr>
              </tbody>
            </VTable>
          </VCard>
        </VCol>

        <!-- Top Requested Lab Tests -->
        <VCol cols="12" lg="4">
          <VCard class="border h-100 d-flex flex-column">
            <VCardItem class="pb-2">
              <template #title>
                <div class="d-flex align-center gap-2">
                  <VIcon icon="tabler-microscope" size="22" color="primary" />
                  <span class="text-h6 font-weight-bold">أكثر الفحوصات الطبية طلباً</span>
                </div>
              </template>
              <template #subtitle>توزيع الفحوصات في عينات المراجعين</template>
            </VCardItem>
            <VCardText class="flex-grow-1 pt-2">
              <VueApexCharts
                v-if="dashboardData.charts?.top_tests?.length"
                type="bar"
                height="320"
                :options="topTestsChartOptions"
                :series="topTestsChartSeries"
              />
              <div v-else class="d-flex h-100 align-center justify-center text-disabled py-10">
                لا توجد بيانات كافية للتحاليل بعد
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>

      <!-- ── 3. Orders Flow 7 Days Chart + Branch Distribution ── -->
      <VRow class="match-height mb-6">
        <!-- 7 Days Area Chart -->
        <VCol cols="12" lg="8">
          <VCard class="border h-100">
            <VCardItem class="pb-2">
              <template #title>
                <div class="d-flex align-center justify-space-between">
                  <div class="d-flex align-center gap-2">
                    <VIcon icon="tabler-chart-dots" size="22" color="success" />
                    <span class="text-h6 font-weight-bold">حركة وتدفق الطلبات الميدانية (آخر 7 أيام)</span>
                  </div>
                  <div class="d-flex align-center gap-3 text-caption">
                    <span class="d-flex align-center gap-1 font-weight-bold text-success">
                      <span class="chart-dot bg-success" /> مكتملة
                    </span>
                    <span class="d-flex align-center gap-1 font-weight-bold text-primary">
                      <span class="chart-dot bg-primary" /> قيد التنفيذ
                    </span>
                    <span class="d-flex align-center gap-1 font-weight-bold text-error">
                      <span class="chart-dot bg-error" /> ملغاة
                    </span>
                  </div>
                </div>
              </template>
            </VCardItem>
            <VCardText>
              <VueApexCharts
                type="area"
                height="340"
                :options="flowChartOptions"
                :series="flowChartSeries"
              />
            </VCardText>
          </VCard>
        </VCol>

        <!-- Branch Distribution Progress -->
        <VCol cols="12" lg="4">
          <VCard class="border h-100 d-flex flex-column">
            <VCardItem class="pb-3">
              <template #title>
                <div class="d-flex align-center gap-2">
                  <VIcon icon="tabler-building-hospital" size="22" color="info" />
                  <span class="text-h6 font-weight-bold">أداء الفروع وإيراداتها</span>
                </div>
              </template>
              <template #subtitle>توزيع إنجاز الطلبات حسب الفرع المختص</template>
            </VCardItem>
            <VCardText class="flex-grow-1">
              <div v-if="dashboardData.charts?.branch_distribution?.length" class="d-flex flex-column gap-5 pt-2">
                <div v-for="branch in dashboardData.charts.branch_distribution" :key="branch.id">
                  <div class="d-flex justify-space-between align-center mb-1">
                    <span class="font-weight-bold text-body-1 text-high-emphasis">{{ branch.name }}</span>
                    <span class="text-body-2 font-weight-bold text-primary">{{ branch.completed_orders }} / {{ branch.total_orders }} طلب</span>
                  </div>
                  <VProgressLinear
                    :model-value="branch.total_orders > 0 ? (branch.completed_orders / branch.total_orders) * 100 : 0"
                    color="primary"
                    height="8"
                    rounded
                  />
                  <div class="d-flex justify-space-between align-center mt-1 text-caption text-medium-emphasis">
                    <span>نسبة الإنجاز: {{ branch.total_orders > 0 ? Math.round((branch.completed_orders / branch.total_orders) * 100) : 0 }}%</span>
                    <span class="font-weight-semibold text-warning">{{ branch.revenue?.toLocaleString() }} د.ع إيراد</span>
                  </div>
                </div>
              </div>
              <div v-else class="d-flex h-100 align-center justify-center text-disabled py-10">
                لا توجد فروع مسجلة
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>

      <!-- ── 4. Top Technicians + Live Activity Feed ── -->
      <VRow class="match-height">
        <!-- Top Technicians -->
        <VCol cols="12" lg="5">
          <VCard class="border h-100">
            <VCardItem class="pb-3">
              <template #title>
                <div class="d-flex align-center gap-2">
                  <VIcon icon="tabler-award" size="22" color="warning" />
                  <span class="text-h6 font-weight-bold">أكفأ الفنيين الميدانيين (هذا الشهر)</span>
                </div>
              </template>
              <template #subtitle>ترتيب الفنيين حسب عدد العينات المكتملة</template>
            </VCardItem>
            <VCardText>
              <div v-if="dashboardData.top_technicians?.length" class="d-flex flex-column gap-4 pt-1">
                <div
                  v-for="(tech, idx) in dashboardData.top_technicians"
                  :key="tech.id"
                  class="d-flex align-center justify-space-between pa-3 rounded-lg border hover:bg-var-theme-surface"
                >
                  <div class="d-flex align-center gap-3">
                    <VAvatar
                      size="42"
                      :color="idx === 0 ? 'warning' : (idx === 1 ? 'primary' : 'secondary')"
                      variant="tonal"
                      class="font-weight-bold"
                    >
                      {{ idx + 1 }}
                    </VAvatar>
                    <div>
                      <div class="d-flex align-center gap-1.5">
                        <span class="text-body-1 font-weight-bold text-high-emphasis">{{ tech.name }}</span>
                        <span
                          class="status-dot"
                          :class="tech.is_busy ? 'status-dot--busy' : 'status-dot--active'"
                          :title="tech.is_busy ? 'في مهمة سحب الآن' : 'متاح ميدانياً'"
                        />
                      </div>
                      <span class="text-caption text-medium-emphasis">{{ tech.specialty }}</span>
                    </div>
                  </div>

                  <div class="text-end">
                    <VChip color="success" size="small" variant="tonal" class="font-weight-bold mb-1">
                      {{ tech.completed_month }} عينة مكتملة
                    </VChip>
                    <div class="text-xs text-disabled" dir="ltr">{{ tech.phone }}</div>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-10 text-disabled">
                لا يوجد نشاط للفنيين بعد هذا الشهر
              </div>
            </VCardText>
          </VCard>
        </VCol>

        <!-- Live System Activity Timeline -->
        <VCol cols="12" lg="7">
          <VCard class="border h-100">
            <VCardItem class="pb-3">
              <template #title>
                <div class="d-flex align-center gap-2">
                  <VIcon icon="tabler-pulse" size="22" color="error" />
                  <span class="text-h6 font-weight-bold">شريط الأحداث المباشر (Timeline)</span>
                </div>
              </template>
              <template #subtitle>آخر تحركات وسجلات النظام الميدانية وتذاكر المراجعين</template>
            </VCardItem>
            <VCardText class="pt-3">
              <VTimeline
                v-if="dashboardData.timeline?.length"
                side="end"
                align="start"
                line-inset="8"
                truncate-line="both"
                density="compact"
              >
                <VTimelineItem
                  v-for="item in dashboardData.timeline"
                  :key="item.id"
                  :dot-color="item.color"
                  size="small"
                >
                  <template #icon>
                    <VIcon :icon="item.icon" size="14" color="white" />
                  </template>
                  <div class="d-flex justify-space-between align-center mb-1">
                    <span class="text-body-2 font-weight-bold text-high-emphasis">{{ item.title }}</span>
                    <span class="text-xs text-disabled">{{ item.time }}</span>
                  </div>
                  <p class="text-caption text-medium-emphasis mb-0">
                    {{ item.description }}
                  </p>
                </VTimelineItem>
              </VTimeline>
              <div v-else class="text-center py-10 text-disabled">
                لا توجد أحداث مسجلة في النظام بعد
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
    </template>
  </div>
</template>

<style lang="scss" scoped>
.kpi-card {
  transition: transform 0.2s, box-shadow 0.2s;
  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(var(--v-theme-primary), 0.1) !important;
  }
}

.chart-dot {
  display: inline-block;
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.status-dot {
  display: inline-block;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  &--active { background: rgb(var(--v-theme-success)); }
  &--busy {
    background: rgb(var(--v-theme-warning));
    animation: pulse 1.5s infinite;
  }
}

@keyframes pulse {
  0% { transform: scale(0.95); opacity: 0.8; }
  50% { transform: scale(1.2); opacity: 1; }
  100% { transform: scale(0.95); opacity: 0.8; }
}
</style>
