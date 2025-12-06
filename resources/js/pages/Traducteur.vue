<script setup lang="ts">
import axios from 'axios';
import { ArrowLeft, ArrowRightLeft } from 'lucide-vue-next';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import AudioTranslator from '@/components/AudioTranslator.vue';
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

const currentTab = ref<'text' | 'pdf' | 'audio'>('text');

const source = ref('auto');
const target = ref('fr');
const inputText = ref('');
const translatedText = ref('');
const detectedLang = ref('');
const loading = ref(false);
const error = ref('');
const charCount = computed(() => inputText.value.length);

let debounceTimer: number | undefined;

// Use backend proxy routes instead of direct API
const API_BASE = '/api/translation';

async function detectLanguage(q: string) {
    try {
        const { data } = await axios.post(`${API_BASE}/detect`, { q });
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
        const { data } = await axios.post(`${API_BASE}/translate`, {
            q,
            source: src === 'auto' ? 'auto' : src,
            target: tgt,
            format: 'text',
        });
        console.log('Translation response:', data);
        const result = data?.translatedText || data?.translated_text || '';
        return result as string;
    } catch (e: any) {
        console.error('Translation error:', e);
        error.value = e?.response?.data?.error || e?.message || 'Erreur de traduction';
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
    
    // Swap languages
    const s = source.value;
    source.value = target.value;
    target.value = s;
    
    // Swap texts: the translated text becomes the input, input becomes translated
    const temp = inputText.value;
    inputText.value = translatedText.value;
    translatedText.value = temp;
    
    // Retranslate with new language pair
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
                <!-- Onglets -->
                <div class="flex gap-2 mb-4 border-b">
                    <button
                        @click="currentTab = 'text'"
                        :class="[
                            'px-4 py-2 font-medium border-b-2 transition',
                            currentTab === 'text'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-neutral-600 hover:text-neutral-900',
                        ]"
                    >
                        📝 Texte
                    </button>
                    <button
                        @click="currentTab = 'audio'"
                        :class="[
                            'px-4 py-2 font-medium border-b-2 transition',
                            currentTab === 'audio'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-neutral-600 hover:text-neutral-900',
                        ]"
                    >
                        🎤 Audio
                    </button>
                </div>

                <!-- Contenu onglet Texte -->
                <div v-show="currentTab === 'text'">
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

                        <div class="mt-3 text-sm text-neutral-700">Langue source:</div>
                        <select v-model="source" class="w-full mt-2 p-2 border rounded">
                            <option v-for="lang in languages" :key="lang.code" :value="lang.code">
                                {{ lang.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Right: output -->
                    <div class="w-1/2">
                        <div class="flex items-center justify-between mb-2">
                            <div class="text-sm font-medium">Traduire en</div>
                            <div class="flex items-center gap-2">
                                <button @click="swapLanguages" title="Inverser les langues" class="swap-btn">
                                    <ArrowRightLeft :size="20" />
                                </button>
                            </div>
                        </div>

                        <div class="h-48 p-3 border rounded bg-neutral-50 overflow-auto">
                            <div v-if="loading" class="text-sm text-neutral-500">Traduction en cours…</div>
                            <div v-else-if="error" class="text-sm text-red-600">{{ error }}</div>
                            <div v-else class="whitespace-pre-wrap text-neutral-800">{{ translatedText || (inputText ? '—' : '') }}</div>
                        </div>

                        <div class="mt-3 text-sm text-neutral-700">Langue cible:</div>
                        <select v-model="target" class="w-full mt-2 p-2 border rounded">
                            <option v-for="lang in languages.filter(l => l.code !== 'auto')" :key="'t-'+lang.code" :value="lang.code">
                                {{ lang.name }}
                            </option>
                        </select>

                        <div class="mt-3 flex items-center gap-3">
                            <div class="text-xs text-neutral-500">Langue détectée: <strong>{{ detectedLang || '—' }}</strong></div>
                            <button @click="copyTranslated" class="copy-btn">Copier le texte</button>
                        </div>
                    </div>
                </div>

                <!-- Contenu onglet Audio -->
                <div v-show="currentTab === 'audio'">
                    <AudioTranslator :languages="languages" />
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
.bg-neutral-50 { background-color: #ffffff !important; }
.hover\:bg-neutral-100:hover { background-color: #f3f4f6; }

textarea {
  background-color: #ffffff !important;
  color: #000000 !important;
  border: 1px solid #d1d5db;
}

textarea::placeholder {
  color: #9ca3af;
}

select {
  background-color: #ffffff !important;
  color: #000000 !important;
  border: 1px solid #d1d5db;
}

.bg-neutral-50 {
  background-color: #ffffff !important;
  color: #000000;
}

/* Swap button styling */
.swap-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 8px 12px;
  border: 2px solid #3b82f6;
  background-color: #3b82f6;
  color: white;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s ease;
}

.swap-btn:hover {
  background-color: #2563eb;
  border-color: #2563eb;
  transform: scale(1.05);
}

.swap-btn:active {
  transform: scale(0.95);
}

/* Copy button styling */
.copy-btn {
  margin-left: auto;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  border: 2px solid #10b981;
  background-color: #10b981;
  color: white;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  font-size: 0.875rem;
  transition: all 0.2s ease;
}

.copy-btn:hover {
  background-color: #059669;
  border-color: #059669;
  transform: scale(1.05);
}

.copy-btn:active {
  transform: scale(0.95);
}
</style>