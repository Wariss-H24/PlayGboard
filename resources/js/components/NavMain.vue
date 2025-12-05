<script setup lang="ts">
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { urlIsActive } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';

defineProps<{
    items: NavItem[];
}>();

const page = usePage();
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel></SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton
                    as-child
                    :is-active="urlIsActive(item.href, page.url)"
                    :tooltip="item.title"
                >
                    <Link :href="item.href"
                    :class="['nav-link', urlIsActive(item.href, page.url) ? 'nav-link--active' : '']">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
<style>
.nav-link--active {
  color: #4C70FF;
  font-weight: 600;
  border-left: 4px solid #4C70FF;
  background: #20203D;
}
.nav-link {
  color: #bdc6cf;
  transition: background 0.2s, color 0.2s;
}
.nav-link:hover {
  background: #3b3b5a;
  color: #fdfdfd;
}
</style>
