<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarTrigger,
} from '@/components/ui/sidebar';
import { dashboard, traducteur, historique, parametres, compte } from '@/routes';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { Home, BookOpen, Clock, Settings, User, Folder } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import Traducteur from '@/pages/Traducteur.vue';

const mainNavItems: NavItem[] = [
    { title: 'Dashboard', href: dashboard(), icon: Home },
    { title: 'Traducteur', href: traducteur(), icon: BookOpen },
    { title: 'Historique', href: historique(), icon: Clock },
    { title: 'Parametres', href: parametres(), icon: Settings },
    { title: 'Mon Compte', href: compte(), icon: User },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <div>
        <!-- Mobile hamburger trigger -->
        <div class="md:hidden fixed top-4 left-4 z-50">
            <SidebarTrigger />
        </div>
            <Sidebar collapsible="icon" variant="inset" class="sidebar data-[sidebar='sidebar']">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" as-child>
                            <Link :href="dashboard()">
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain :items="mainNavItems" />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter :items="footerNavItems" />
                <NavUser />
                <slot name="sidebar-extra" />
            </SidebarFooter>
        </Sidebar>
        <slot />
    </div>
</template>
<style >
.sidebar,
[data-sidebar="sidebar"] {
  background: rgba(18, 28, 58, 0.95);
  border-right: 2px solid rgba(255,255,255,0.08);
  box-shadow: 0 0 32px 0 rgba(0,0,0,0.18);
  backdrop-filter: blur(8px);
  border-radius: 1.5rem 0 0 1.5rem;
}
</style>
