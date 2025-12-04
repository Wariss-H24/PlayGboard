<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import { useSidebar } from './utils'

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()

const { toggleSidebar, isMobile, openMobile, setOpenMobile } = useSidebar()
</script>

<template>
  <Button
    data-sidebar="trigger"
    data-slot="sidebar-trigger"
    variant="ghost"
    size="icon"
    :class="cn('h-9 w-9 flex flex-col justify-center items-center relative', props.class)"
    @click="toggleSidebar"
    aria-label="Ouvrir le menu"
  >
    <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
      <circle cx="14" cy="14" r="13" stroke="#7BAAF7" stroke-width="2" fill="none" />
      <rect x="8" y="10" width="12" height="2" rx="1" fill="#7BAAF7" />
      <rect x="8" y="14" width="12" height="2" rx="1" fill="#7BAAF7" />
      <rect x="8" y="18" width="8" height="2" rx="1" fill="#7BAAF7" />
    </svg>
    <span class="sr-only">Ouvrir le menu</span>
  </Button>
  <Button
    v-if="isMobile && openMobile"
    class="fixed top-4 right-4 z-50 h-9 w-9 flex flex-col justify-center items-center bg-white/80 backdrop-blur border border-gray-200 shadow-lg md:hidden"
    variant="ghost"
    size="icon"
    @click="setOpenMobile(false)"
    aria-label="Fermer le menu"
  >
    <span aria-hidden="true" class="block w-6 h-0.5 bg-gray-700 rounded rotate-45 absolute top-4 left-1"></span>
    <span aria-hidden="true" class="block w-6 h-0.5 bg-gray-700 rounded -rotate-45 absolute top-4 left-1"></span>
    <span class="sr-only">Fermer le menu</span>
  </Button>
</template>
