<script setup lang="ts">
import AddEditGroupDrawer from '@/views/medical-dictionary/AddEditGroupDrawer.vue'

// States
const searchQuery = ref('')
const groups = ref<any[]>([])
const totalGroups = ref(0)
const loading = ref(false)

const isDrawerOpen = ref(false)
const selectedGroup = ref<any>(null)

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

// Fetch groups from backend
const fetchGroups = async () => {
  loading.value = true
  try {
    const response = await $api('/medical-dictionary/groups', {
      query: {
        q: searchQuery.value,
      },
    })
    groups.value = response.groups || []
    totalGroups.value = response.totalGroups || 0
  } catch (error) {
    console.error('Error fetching groups:', error)
  } finally {
    loading.value = false
  }
}

watch(searchQuery, () => {
  fetchGroups()
})

onMounted(() => {
  fetchGroups()
})

const headers = [
  { title: '# الترتيب', key: 'sort_order', width: '90px' },
  { title: 'اسم المجموعة (عربي)', key: 'name_ar' },
  { title: 'اسم المجموعة (إنجليزي)', key: 'name_en' },
  { title: 'المفتاح (Key)', key: 'key' },
  { title: 'عدد التحاليل', key: 'tests_count' },
  { title: 'حالة التفعيل للمرضى', key: 'is_active', width: '160px' },
  { title: 'الإجراءات', key: 'actions', sortable: false, width: '130px' },
]

const openAddDrawer = () => {
  selectedGroup.value = null
  isDrawerOpen.value = true
}

const openEditDrawer = (group: any) => {
  selectedGroup.value = { ...group }
  isDrawerOpen.value = true
}

const toggleGroupStatus = async (group: any) => {
  try {
    await $api(`/medical-dictionary/groups/${group.id}/toggle-status`, {
      method: 'PUT',
      body: { is_active: group.is_active },
    })
  } catch (error) {
    console.error('Error toggling group status:', error)
    group.is_active = !group.is_active
  }
}

const handleGroupSubmit = async (groupData: any) => {
  try {
    if (groupData.id) {
      await $api(`/medical-dictionary/groups/${groupData.id}`, {
        method: 'PUT',
        body: groupData,
      })
    } else {
      await $api('/medical-dictionary/groups', {
        method: 'POST',
        body: groupData,
      })
    }
    fetchGroups()
  } catch (error) {
    console.error('Error saving group:', error)
  }
}

const deleteGroup = async (id: number) => {
  if (confirm('هل أنت متأكد من حذف هذه المجموعة المخبرية؟ سيتم حذف جميع التحاليل التابعة لها.')) {
    try {
      await $api(`/medical-dictionary/groups/${id}`, {
        method: 'DELETE',
      })
      fetchGroups()
    } catch (error) {
      console.error('Error deleting group:', error)
    }
  }
}

const widgetData = computed(() => [
  { title: 'إجمالي المجموعات المخبرية', value: totalGroups.value, icon: 'tabler-category', iconColor: 'primary' },
  { title: 'إجمالي التحاليل المندرجة', value: groups.value.reduce((acc, g) => acc + (g.tests_count || 0), 0), icon: 'tabler-flask', iconColor: 'success' },
])
</script>

<template>
  <section>
    <!-- 👉 Widgets -->
    <VRow class="mb-6">
      <VCol
        v-for="(data, id) in widgetData"
        :key="id"
        cols="12"
        md="6"
      >
        <VCard>
          <VCardText class="d-flex justify-space-between">
            <div class="d-flex flex-column gap-y-1">
              <span class="text-base text-medium-emphasis">{{ data.title }}</span>
              <h4 class="text-h4 d-flex align-center gap-2">
                {{ data.value }}
              </h4>
            </div>
            <VAvatar
              :color="data.iconColor"
              variant="tonal"
              size="44"
              rounded
            >
              <VIcon :icon="data.icon" size="28" />
            </VAvatar>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- 👉 Groups Table Card -->
    <VCard title="مجموعات التحاليل المخبرية">
      <VCardText class="d-flex flex-wrap py-4 gap-4 align-center justify-space-between">
        <div class="d-flex align-center gap-3">
          <AppTextField
            v-model="searchQuery"
            placeholder="بحث باسم المجموعة أو المفتاح..."
            density="compact"
            style="inline-size: 320px;"
            prepend-inner-icon="tabler-search"
            clearable
          />
        </div>

        <div class="d-flex align-center gap-4">
          <VBtn
            color="primary"
            prepend-icon="tabler-plus"
            @click="openAddDrawer"
          >
            إضافة مجموعة جديدة
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <!-- 👉 Data Table -->
      <VDataTable
        :headers="headers"
        :items="groups"
        :loading="loading"
        item-value="id"
        class="text-no-wrap"
      >
        <!-- sort_order -->
        <template #item.sort_order="{ item }">
          <VChip size="small" variant="tonal" color="info">
            #{{ item.sort_order }}
          </VChip>
        </template>

        <!-- name_ar with icon & color -->
        <template #item.name_ar="{ item }">
          <div class="d-flex align-center gap-x-3">
            <VAvatar
              :color="item.color || 'primary'"
              variant="tonal"
              size="38"
            >
              <VIcon :icon="getTablerIcon(item.icon)" size="22" />
            </VAvatar>
            <div class="d-flex flex-column">
              <span class="font-weight-medium text-base">{{ item.name_ar }}</span>
            </div>
          </div>
        </template>

        <!-- tests_count -->
        <template #item.tests_count="{ item }">
          <VChip
            color="success"
            variant="flat"
            size="small"
          >
            {{ item.tests_count || 0 }} تحليل
          </VChip>
        </template>

        <!-- is_active -->
        <template #item.is_active="{ item }">
          <VSwitch
            v-model="item.is_active"
            color="success"
            hide-details
            @change="toggleGroupStatus(item)"
          />
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <VBtn
            icon
            variant="text"
            color="medium-emphasis"
          >
            <VIcon icon="tabler-dots-vertical" />
            <VMenu activator="parent">
              <VList>
                <VListItem @click="openEditDrawer(item)">
                  <template #prepend>
                    <VIcon icon="tabler-pencil" class="me-2" />
                  </template>
                  <VListItemTitle>تعديل المجموعة</VListItemTitle>
                </VListItem>

                <VListItem @click="deleteGroup(item.id)">
                  <template #prepend>
                    <VIcon icon="tabler-trash" color="error" class="me-2" />
                  </template>
                  <VListItemTitle class="text-error">حذف المجموعة</VListItemTitle>
                </VListItem>
              </VList>
            </VMenu>
          </VBtn>
        </template>
      </VDataTable>
    </VCard>

    <!-- 👉 Add / Edit Group Drawer -->
    <AddEditGroupDrawer
      v-model:isDrawerOpen="isDrawerOpen"
      :group-data="selectedGroup"
      @submit="handleGroupSubmit"
    />
  </section>
</template>
