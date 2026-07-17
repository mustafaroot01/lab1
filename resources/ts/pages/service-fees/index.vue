<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { $api } from '@/utils/api'

definePage({
  meta: {
    title: 'تحديد كلفة ورسوم الزيارة المنزلية للفروع',
  },
})

const loading = ref(false)
const branches = ref<any[]>([])

// الإشعارات العلوية
const snackbar = ref({
  show: false,
  color: 'success',
  text: '',
})

const showToast = (message: string, color = 'success') => {
  snackbar.value = {
    show: true,
    color,
    text: message,
  }
}

// نافذة تعديل أجور الزيارة الأساسية للفرع وحد الإعفاء المجاني
const branchFeeDialog = ref({
  show: false,
  loading: false,
  branchId: null as number | null,
  form: {
    name_ar: '',
    service_fee: 5000,
    free_threshold: 50000,
    fee_notes: '',
  },
})

// جلب الفروع وأجورها
const fetchData = async () => {
  loading.value = true
  try {
    const res = await $api('/branch-service-fees')
    if (res?.status) {
      branches.value = res.branches || res.data?.branches || []
    } else {
      showToast('تعذر جلب بيانات الفروع والأجور', 'error')
    }
  } catch (e: any) {
    showToast('حدث خطأ أثناء الاتصال بالخادم', 'error')
  } finally {
    loading.value = false
  }
}

// فتح نافذة تعديل أجور الفرع
const openEditBranchFeeDialog = (branch: any) => {
  branchFeeDialog.value = {
    show: true,
    loading: false,
    branchId: branch.id,
    form: {
      name_ar: branch.name_ar || '',
      service_fee: branch.service_fee ?? 5000,
      free_threshold: branch.free_threshold ?? 50000,
      fee_notes: branch.fee_notes || '',
    },
  }
}

// حفظ تعديل أجور الفرع الأساسية
const saveBranchFee = async () => {
  const { branchId, form } = branchFeeDialog.value
  if (!branchId) return

  branchFeeDialog.value.loading = true
  try {
    const res = await $api(`/branch-service-fees/${branchId}`, {
      method: 'PUT',
      body: {
        service_fee: Number(form.service_fee),
        free_threshold: Number(form.free_threshold),
        fee_notes: form.fee_notes,
      },
    })
    if (res?.status) {
      showToast(res.message || 'تم تحديث أجور الزيارة للفرع بنجاح', 'success')
      branchFeeDialog.value.show = false
      fetchData()
    } else {
      showToast(res?.message || 'فشل التحديث', 'error')
    }
  } catch {
    showToast('حدث خطأ أثناء تحديث أجور الفرع', 'error')
  } finally {
    branchFeeDialog.value.loading = false
  }
}

const formatCurrency = (val: number | string | null | undefined) => {
  if (val === null || val === undefined || val === '') return '0 د.ع'
  const num = typeof val === 'string' ? parseFloat(val) : val
  return new Intl.NumberFormat('ar-IQ').format(num) + ' د.ع'
}

onMounted(() => {
  fetchData()
})
</script>

<template>
  <div class="service-fees-page">
    <!-- إشعار علوي (Snackbar) -->
    <VSnackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      location="top end"
      timeout="3500"
      class="font-weight-bold"
    >
      {{ snackbar.text }}
    </VSnackbar>

    <!-- الهيدر الرئيسي والإرشادات -->
    <VRow class="mb-6">
      <VCol cols="12">
        <VCard class="border-primary border-s-4 shadow-sm">
          <VCardItem class="py-4">
            <template #prepend>
              <VAvatar color="primary" variant="tonal" rounded size="48">
                <VIcon icon="tabler-home-heart" size="28" />
              </VAvatar>
            </template>
            <VCardTitle class="text-h4 font-weight-bold">
              تحديد أجور خدمة الزيارة المنزلية الميدانية لكل فرع
            </VCardTitle>
            <VCardSubtitle class="text-body-1 mt-1">
              إدارة كلفة وصول الفنيين وسحب العينات الميدانية من منازل المراجعين وشروط الإعفاء المجاني للطلبات
            </VCardSubtitle>
          </VCardItem>
        </VCard>
      </VCol>
    </VRow>

    <VRow class="mb-6">
      <VCol cols="12">
        <VAlert
          color="info"
          variant="tonal"
          icon="tabler-info-circle"
          class="border border-info rounded-lg"
        >
          <div class="text-h6 font-weight-bold mb-1">آلية احتساب الرسوم في سلة وتطبيق المريض:</div>
          <ul class="ms-4 text-body-2 d-flex flex-column gap-1">
            <li><strong>أجور الزيارة المنزلية الثابتة (`service_fee`)</strong>: يتم إضافة هذا المبلغ تلقائياً على فاتورة المراجع عند اختيار هذا الفرع لإجراء الزيارة وسحب العينة.</li>
            <li><strong>شرط الإعفاء المجاني (`free_threshold`)</strong>: إذا بلغت قيمة التحاليل في سلة المريض هذا المبلغ أو تجاوزته، يتم إعفاء المراجع تماماً وتتحول أجور الزيارة المنزلية في الفاتورة إلى <strong>(مجاني 🎁)</strong> تلقائياً دون تدخل يدوي.</li>
            <li><strong>نظافة فاتورة الدفع</strong>: إذا لم يقم المراجع بإدخال كوبون خصم، يختفي سطر الخصم نهائياً من الفاتورة لضمان وضوح وشفافية السلة.</li>
          </ul>
        </VAlert>
      </VCol>
    </VRow>

    <!-- جدول أجور وشروط الفروع -->
    <VCard :loading="loading" class="shadow-sm">
      <VCardItem class="py-4 border-b">
        <template #title>
          <h5 class="text-h5 font-weight-bold d-flex align-center gap-2">
            <VIcon icon="tabler-building-hospital" color="primary" />
            <span>قائمة الفروع ورسوم الزيارات المنزلية المعتمدة</span>
          </h5>
        </template>
        <template #append>
          <VBtn
            color="secondary"
            variant="tonal"
            prepend-icon="tabler-refresh"
            size="small"
            @click="fetchData"
          >
            تحديث البيانات
          </VBtn>
        </template>
      </VCardItem>

      <VTable class="text-no-wrap table-header-bg">
        <thead>
          <tr>
            <th style="width: 70px;"># ID</th>
            <th>الفرع والعنوان</th>
            <th class="text-center">أجور الزيارة المنزلية الثابتة</th>
            <th class="text-center">حد فاتورة الإعفاء المجاني</th>
            <th>ملاحظات أو تعليمات للزائر</th>
            <th class="text-center">تعديل الأجور</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="branches.length === 0">
            <td colspan="6" class="text-center py-8 text-medium-emphasis">
              <VIcon icon="tabler-building-off" size="48" class="mb-2 d-block mx-auto" />
              لا توجد فروع مسجلة حالياً
            </td>
          </tr>
          <tr v-for="branch in branches" :key="branch.id" class="align-middle">
            <td>
              <VChip size="small" color="primary" variant="tonal" class="font-weight-bold">
                #{{ branch.id }}
              </VChip>
            </td>
            <td>
              <div class="font-weight-bold text-high-emphasis text-body-1">
                {{ branch.name_ar }}
              </div>
              <div class="text-caption text-medium-emphasis d-flex align-center gap-1 mt-1">
                <VIcon icon="tabler-map-pin" size="14" />
                <span>{{ branch.address || 'العنوان غير محدد' }}</span>
              </div>
            </td>
            <td class="text-center">
              <VChip
                :color="(!branch.service_fee || branch.service_fee == 0) ? 'success' : 'primary'"
                variant="elevated"
                size="medium"
                class="font-weight-bold px-4"
              >
                <VIcon :icon="(!branch.service_fee || branch.service_fee == 0) ? 'tabler-gift' : 'tabler-cash'" start size="18" />
                {{ (!branch.service_fee || branch.service_fee == 0) ? 'مجاني بالكامل 🎁' : formatCurrency(branch.service_fee) }}
              </VChip>
            </td>
            <td class="text-center">
              <VChip
                v-if="branch.free_threshold > 0"
                color="info"
                variant="tonal"
                size="small"
                class="font-weight-bold"
              >
                مجاني عند بلوغ {{ formatCurrency(branch.free_threshold) }}
              </VChip>
              <span v-else class="text-caption text-medium-emphasis">— لا يوجد إعفاء مجاني —</span>
            </td>
            <td>
              <span v-if="branch.fee_notes" class="text-body-2 text-medium-emphasis">
                {{ branch.fee_notes }}
              </span>
              <span v-else class="text-caption text-disabled">—</span>
            </td>
            <td class="text-center">
              <VBtn
                color="primary"
                variant="tonal"
                size="small"
                prepend-icon="tabler-edit"
                @click="openEditBranchFeeDialog(branch)"
              >
                تعديل الأجور والشروط
              </VBtn>
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <!-- نافذة تعديل أجور الزيارة وشروط الإعفاء للفرع -->
    <VDialog v-model="branchFeeDialog.show" max-width="520" persistent>
      <VCard :title="`تعديل أجور خدمة الزيارة: ${branchFeeDialog.form.name_ar}`">
        <VCardText class="pt-4">
          <VRow>
            <VCol cols="12">
              <AppTextField
                v-model="branchFeeDialog.form.service_fee"
                label="أجور الزيارة المنزلية الثابتة للفرع (د.ع)"
                type="number"
                placeholder="مثال: 5000"
                hint="أدخل 0 إذا كانت أجور الزيارة لهذا الفرع مجانية دائماً"
                persistent-hint
              />
            </VCol>

            <VCol cols="12">
              <AppTextField
                v-model="branchFeeDialog.form.free_threshold"
                label="حد الإعفاء المجاني لأجور الزيارة (د.ع)"
                type="number"
                placeholder="مثال: 50000"
                hint="إذا بلغت قيمة تحاليل المريض في السلة هذا الحد، تصبح أجور الزيارة مجانية (أدخل 0 لإلغاء هذا الشرط)"
                persistent-hint
              />
            </VCol>

            <VCol cols="12">
              <AppTextField
                v-model="branchFeeDialog.form.fee_notes"
                label="ملاحظات أو شروط وصول الزيارة الميدانية للفرع"
                placeholder="مثال: تغطية مجانية ضمن النطاق أو أجور رمزية للمناطق المجاورة"
              />
            </VCol>
          </VRow>
        </VCardText>

        <VCardActions class="pa-4 justify-end gap-2 border-t">
          <VBtn
            color="secondary"
            variant="tonal"
            @click="branchFeeDialog.show = false"
          >
            إلغاء
          </VBtn>
          <VBtn
            color="primary"
            variant="elevated"
            :loading="branchFeeDialog.loading"
            @click="saveBranchFee"
          >
            حفظ واعتماد الأجور
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>

<style scoped>
.table-header-bg th {
  background-color: rgba(var(--v-theme-on-surface), 0.04) !important;
  font-weight: 700 !important;
}
</style>
