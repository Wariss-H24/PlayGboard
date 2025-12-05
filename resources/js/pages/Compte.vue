<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ArrowLeft } from 'lucide-vue-next';
import { dashboard, traducteur, historique, parametres, compte } from '@/routes';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';

function goBack() {
   window.history.back();
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Compte',
        href: compte().url,
    },
]
</script>


<template>
    <Head title="Compte" />
    <AppLayout :breadcrumbs="breadcrumbs">

        <div class="p-4">
           <div class="mb-4 flex items-center gap-3">
              <button @click="goBack" class="inline-flex items-center gap-2 rounded px-2 py-1 text-sm hover:bg-neutral-100">
                 <ArrowLeft />
                 <span>Retour</span>
              </button>
              <h1 class="text-lg font-semibold">Mon Compte</h1>
           </div>
     
           <p>Bienvenue sur la page de gestion de votre compte.</p>
           <!-- Contenu supplémentaire pour la gestion du compte peut être ajouté ici -->
        </div>
    </AppLayout>

</template>


<style scoped>

</style>