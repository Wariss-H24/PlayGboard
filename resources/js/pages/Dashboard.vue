<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard, traducteur, historique, parametres, compte } from '@/routes';
import { BookOpen, Clock, Settings, User, ArrowLeft } from 'lucide-vue-next';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

// function goBack() {
//     window.history.back();
// }
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout  class="min-h-screen bg-[#0D0D19] text-white"  :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="mb-4 flex items-center gap-3">
                <!-- <button @click="goBack" class="inline-flex items-center gap-2 rounded px-2 py-1 text-sm hover:bg-neutral-100">
                    <ArrowLeft />
                    <span>Retour</span>
                </button> -->
                <h1 class="sr-only">Dashboard</h1>
            </div>
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <PlaceholderPattern />
                </div>
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <PlaceholderPattern />
                </div>
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <PlaceholderPattern />
                </div>
            </div>
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
            >
                <PlaceholderPattern />
            </div>
            <!-- Quick links section placed at the bottom of the dashboard -->
            <!-- <div class="mt-6 border-t pt-4">
                <h3 class="mb-2 text-sm font-medium text-neutral-600">Aller vers</h3>
                <div class="flex flex-wrap gap-3">
                    <a :href="traducteur().url" class="flex items-center gap-2 rounded px-3 py-2 bg-sidebar-accent text-sidebar-accent-foreground">
                        <BookOpen class="size-4" />
                        Traducteur
                    </a>
                    <a :href="historique().url" class="flex items-center gap-2 rounded px-3 py-2 bg-sidebar-accent text-sidebar-accent-foreground">
                        <Clock class="size-4" />
                        Historique
                    </a>
                    <a :href="parametres().url" class="flex items-center gap-2 rounded px-3 py-2 bg-sidebar-accent text-sidebar-accent-foreground">
                        <Settings class="size-4" />
                        Parametres
                    </a>
                    <a :href="compte().url" class="flex items-center gap-2 rounded px-3 py-2 bg-sidebar-accent text-sidebar-accent-foreground">
                        <User class="size-4" />
                        Mon Compte
                    </a>
                </div>
            </div> -->
        </div>
    </AppLayout>
</template>
