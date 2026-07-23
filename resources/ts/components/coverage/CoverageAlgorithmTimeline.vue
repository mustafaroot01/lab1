<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  timeline: Array<{
    step: string
    status: 'Passed' | 'Failed' | 'Outside' | 'Skipped'
    message: string
    duration_ms: number
  }>
}>()

const getStatusColor = (status: string) => {
  if (status === 'Passed') return 'success'
  if (status === 'Skipped') return 'secondary'
  return 'error'
}

const getStatusIcon = (status: string) => {
  if (status === 'Passed') return 'tabler-check'
  if (status === 'Skipped') return 'tabler-player-skip-forward'
  if (status === 'Outside' || status === 'Failed') return 'tabler-x'
  return 'tabler-info-circle'
}
</script>

<template>
  <VTimeline density="compact" align="start">
    <VTimelineItem
      v-for="(item, index) in timeline"
      :key="index"
      :dot-color="getStatusColor(item.status)"
      size="small"
    >
      <template #icon>
        <VIcon :icon="getStatusIcon(item.status)" color="white" size="16" />
      </template>

      <div class="d-flex justify-space-between align-center mb-1">
        <h6 class="text-subtitle-1 font-weight-medium">
          {{ item.step }}
        </h6>
        <span class="text-caption text-disabled">{{ item.duration_ms }} ms</span>
      </div>
      
      <p class="text-body-2 mb-0">
        {{ item.message }}
      </p>
      <VChip
        class="mt-2"
        size="x-small"
        :color="getStatusColor(item.status)"
        variant="tonal"
      >
        {{ item.status }}
      </VChip>
    </VTimelineItem>
  </VTimeline>
</template>
