<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 128" fill="none" class="w-12 h-12">
  <!-- Fond bleu/violet -->
  <rect width="128" height="128" rx="32" fill="url(#gradient)" />
  <!-- Icône valise -->
  <rect x="36" y="48" width="56" height="38" rx="8" fill="white" />
  <rect x="52" y="38" width="24" height="10" rx="5" fill="white" />
  <rect x="36" y="64" width="56" height="6" fill="#DCE8FF" />
  <circle cx="90" cy="67" r="3" fill="#BFD2FF" />
  <defs>
    <linearGradient id="gradient" x1="0" y1="0" x2="128" y2="128" gradientUnits="userSpaceOnUse">
      <stop stop-color="#2D7BFF" />
      <stop offset="1" stop-color="#7A5CFF" />
    </linearGradient>
  </defs>
</svg>

  

</template>
