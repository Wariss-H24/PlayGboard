<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';
import type { User } from '@/types';
import { computed } from 'vue';

interface Props {
    user: User;
    showEmail?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    showEmail: false,
});

const { getInitials } = useInitials();

// Compute whether we should show the avatar image
const showAvatar = computed(
    () => props.user.avatar && props.user.avatar !== '',
);
</script>

<template>
    <div class="flex items-center gap-3 p-0 rounded-2xl border-amber-50">
        <Avatar class="h-9 w-9 overflow-hidden rounded-full bg-[#4C70FF] text-white font-bold flex items-center justify-center">
            <AvatarImage v-if="showAvatar" :src="user.avatar!" :alt="user.name" />
            <AvatarFallback class="rounded-full text-white font-bold">
                {{ getInitials(user.name) }}
            </AvatarFallback>
        </Avatar>
        <div class="flex flex-col text-left">
            <span class="font-semibold text-white text-base leading-tight">{{ user.name }}</span>
            <span v-if="showEmail" class="text-xs text-gray-200 leading-tight">{{ user.email }}</span>
        </div>
    </div>
</template>
