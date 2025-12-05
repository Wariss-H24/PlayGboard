<script setup lang="ts">
import { ArrowLeft } from 'lucide-vue-next';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard, traducteur, historique, parametres, compte } from '@/routes';
import { type BreadcrumbItem } from '@/types';

function goBack() {
    window.history.back();
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Paramètres',
        href: parametres().url,
    },
]
</script>


<template>
    <Head title="Paramètres" />
    <AppLayout :breadcrumbs="breadcrumbs">

    <div class="p-4">
        <div class="mb-4 flex items-center gap-3">
            <button @click="goBack" class="inline-flex items-center gap-2 rounded px-2 py-1 text-sm hover:bg-neutral-100">
                <ArrowLeft />
                <span>Retour</span>
            </button>
            <h1 class="text-lg font-semibold">Paramètres</h1>
        </div>

        <p>Gérez vos paramètres ici.</p>
    </div>
    </AppLayout>
</template>


<style scoped>

</style>