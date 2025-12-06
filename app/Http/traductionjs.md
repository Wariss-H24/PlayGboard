<script setup lang="ts">
import { ArrowLeft, Swap } from 'lucide-vue-next';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed, watch, onMounted } from 'vue';
import { type BreadcrumbItem } from '@/types';
import { traducteur } from '@/routes';

function goBack() {
    window.history.back();
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Traducteur',
        href: traducteur().url,
    },
];

// 20 most common languages (ISO codes) + 'auto' for detection
const languages = [
    { code: 'auto', name: 'Détection automatique' },
    { code: 'en', name: 'Anglais' },
    { code: 'fr', name: 'Français' },
    { code: 'es', name: 'Espagnol' },
    { code: 'de', name: 'Allemand' },
    { code: 'pt', name: 'Portugais' },
    { code: 'it', name: 'Italien' },
    { code: 'ru', name: 'Russe' },
    { code: 'zh', name: 'Chinois (simplifié)' },
    { code: 'ja', name: 'Japonais' },
    { code: 'ko', name: 'Coréen' },
    { code: 'ar', name: 'Arabe' },
    { code: 'hi', name: 'Hindi' },
    { code: 'tr', name: 'Turc' },
    { code: 'nl', name: 'Néerlandais' },
    { code: 'sv', name: 'Suédois' },
    { code: 'pl', name: 'Polonais' },
    { code: 'vi', name: 'Vietnamien' },
    { code: 'id', name: 'Indonésien' },
    { code: 'he', name: 'Hébreu' },
    { code: 'uk', name: 'Ukrainien' },
];

const maxChars = 2000;

const source = ref('auto');
const target = ref('fr');
const inputText = ref('');
const translatedText = ref('');
const detectedLang = ref('');
const loading = ref(false);
const error = ref('');
const charCount = computed(() => inputText.value.length);

let debounceTimer: number | undefined;

// Use LibreTranslate public instance; you can change this to your own server or API key.
const API_BASE = 'https://libretranslate.de';

async function detectLanguage(q: string) {
    try {
        const res = await fetch(`${API_BASE}/detect`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ q }),
        });
        if (!res.ok) throw new Error('Detection failed');
        const data = await res.json();
        // data is an array like [{language: 'en', confidence: 0.8}]
        if (Array.isArray(data) && data.length > 0) {
            return data[0].language as string;
        }
    } catch (e) {
        console.error(e);
    }
    return '';
}

async function translate(q: string, src: string, tgt: string) {
    try {
        loading.value = true;
        error.value = '';
        const res = await fetch(`${API_BASE}/translate`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ q, source: src === 'auto' ? 'auto' : src, target: tgt, format: 'text' }),
        });
        if (!res.ok) {
            const txt = await res.text();
            throw new Error(txt || 'Translation failed');
        }
        const data = await res.json();
        return (data.translatedText as string) || '';
    } catch (e: any) {
        console.error(e);
        error.value = e?.message || 'Erreur de traduction';
        return '';
    } finally {
        loading.value = false;
    }
}

async function doTranslate() {
    const q = inputText.value.trim();
    if (!q) {
        translatedText.value = '';
        detectedLang.value = '';
        return;
    }

    // If source is auto, call detect first
    let src = source.value;
    if (src === 'auto') {
        const d = await detectLanguage(q);
        detectedLang.value = d || '';
        src = d || 'auto';
    } else {
        detectedLang.value = '';
    }

    translatedText.value = await translate(q, src, target.value);
}

function onInputChange() {
    error.value = '';
    if (debounceTimer) window.clearTimeout(debounceTimer);
    debounceTimer = window.setTimeout(() => {
        // enforce max length
        if (inputText.value.length > maxChars) {
            inputText.value = inputText.value.slice(0, maxChars);
        }
        void doTranslate();
    }, 600);
}

function swapLanguages() {
    // allow swapping only real languages (not auto)
    if (source.value === 'auto') return;
    const s = source.value;
    source.value = target.value;
    target.value = s;
    void doTranslate();
}

function copyTranslated() {
    if (!translatedText.value) return;
    navigator.clipboard?.writeText(translatedText.value);
}

onMounted(() => {
    // initial empty state
});

watch([source, target], () => {
    // retranslate when languages change
    if (inputText.value.trim()) {
        if (debounceTimer) window.clearTimeout(debounceTimer);
        debounceTimer = window.setTimeout(() => void doTranslate(), 200);
    }
});
</script>


<template>
    <Head title="Traducteur" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div class="mb-4 flex items-center gap-3">
                <button @click="goBack" class="inline-flex items-center gap-2 rounded px-2 py-1 text-sm hover:bg-neutral-100">
                    <ArrowLeft />
                    <span>Retour</span>
                </button>
                <h1 class="text-lg font-semibold">Traducteur</h1>
            </div>

            <div class="bg-white rounded shadow p-4">
                <div class="flex gap-4">
                    <!-- Left: input -->
                    <div class="w-1/2">
                        <div class="flex items-center justify-between mb-2">
                            <div class="text-sm font-medium">Traduire depuis</div>
                            <div class="text-xs text-neutral-500">{{ charCount }}/{{ maxChars }}</div>
                        </div>

                        <textarea
                            v-model="inputText"
                            @input="onInputChange"
                            :maxlength="maxChars"
                            spellcheck="true"
                            placeholder="Entrez le texte à traduire..."
                            class="w-full h-48 p-3 border rounded resize-none"
                        ></textarea>

                        <div class="mt-3 text-sm text-neutral-700">Choisir la langue source (ou laissez « Détection automatique »):</div>
                        <div class="flex flex-wrap gap-2 mt-2 max-h-36 overflow-auto p-2 border rounded">
                            <label v-for="lang in languages" :key="lang.code" class="inline-flex items-center gap-2 text-sm mr-2">
                                <input type="radio" name="sourceLang" :value="lang.code" v-model="source" />
                                <span>{{ lang.name }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Right: output -->
                    <div class="w-1/2">
                        <div class="flex items-center justify-between mb-2">
                            <div class="text-sm font-medium">Traduire en</div>
                            <div class="flex items-center gap-2">
                                <button @click="swapLanguages" title="Inverser" class="inline-flex items-center p-1 border rounded hover:bg-neutral-100">
                                    <Swap />
                                </button>
                            </div>
                        </div>

                        <div class="h-48 p-3 border rounded bg-neutral-50 overflow-auto">
                            <div v-if="loading" class="text-sm text-neutral-500">Traduction en cours…</div>
                            <div v-else-if="error" class="text-sm text-red-600">{{ error }}</div>
                            <div v-else class="whitespace-pre-wrap text-neutral-800">{{ translatedText || (inputText ? '—' : '') }}</div>
                        </div>

                        <div class="mt-3 text-sm text-neutral-700">Choisir la langue cible:</div>
                        <div class="flex flex-wrap gap-2 mt-2 max-h-36 overflow-auto p-2 border rounded">
                            <label v-for="lang in languages" v-if="lang.code !== 'auto'" :key="'t-'+lang.code" class="inline-flex items-center gap-2 text-sm mr-2">
                                <input type="radio" name="targetLang" :value="lang.code" v-model="target" />
                                <span>{{ lang.name }}</span>
                            </label>
                        </div>

                        <div class="mt-3 flex items-center gap-3">
                            <div class="text-xs text-neutral-500">Langue détectée: <strong>{{ detectedLang || '—' }}</strong></div>
                            <button @click="copyTranslated" class="ml-auto inline-flex items-center gap-2 px-2 py-1 text-sm border rounded hover:bg-neutral-100">Copier le texte</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>


<style scoped>
.border { border: 1px solid #e5e7eb; }
.rounded { border-radius: 6px; }
.shadow { box-shadow: 0 1px 2px rgba(0,0,0,0.04); }
.h-48 { height: 12rem; }
.max-h-36 { max-height: 9rem; }
.bg-neutral-50 { background-color: #fafafa; }
.hover\:bg-neutral-100:hover { background-color: #f3f4f6; }
</style>