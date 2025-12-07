<template>
  <div style="padding: 24px; background: white; border-radius: 8px;">
    <h2 style="font-size: 20px; font-weight: 600; margin-bottom: 24px;">🎤 Traducteur Audio</h2>

    <!-- PARTIE 1: ENREGISTREMENT -->
    <div style="padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px; background: #f9fafb; margin-bottom: 24px;">
      <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 16px;">📢 Enregistrer et Traduire</h3>

      <!-- Langue cible -->
      <div style="margin-bottom: 16px;">
        <label style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 8px;">Traduire en:</label>
        <select v-model="recordTargetLang" style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px;">
          <option value="fr">Français</option>
          <option value="en">Anglais</option>
          <option value="es">Espagnol</option>
          <option value="de">Allemand</option>
        </select>
      </div>

      <!-- Bouton Enregistrer -->
      <div style="margin-bottom: 16px;">
        <button
          @click="toggleRecording"
          style="width: 100%; padding: 12px 16px; border: none; border-radius: 6px; font-weight: 600; color: white; cursor: pointer; transition: all 0.2s;"
          :style="{ backgroundColor: isRecording ? '#dc2626' : '#3b82f6' }"
        >
          {{ isRecording ? '⏹️ Arrêter l\'enregistrement' : '⏺️ Enregistrer' }}
        </button>
      </div>

      <!-- Chrono -->
      <div v-if="isRecording" style="text-align: center; margin-bottom: 16px; font-size: 24px; font-weight: 700; color: #dc2626;">
        ⏱️ {{ recordingTime }}s
      </div>

      <!-- Zone transcription (toujours visible) -->
      <div style="margin-bottom: 16px; padding: 12px; background: #f0f9ff; border: 1px solid #bfdbfe; border-radius: 6px; min-height: 50px;">
        <p style="font-size: 13px; color: #1e40af; line-height: 1.6; margin: 0;">
          <strong>Texte enregistré:</strong>
          {{ recordingTranscript || '(appuyez sur Enregistrer pour commencer)' }}
        </p>
      </div>

      <!-- Bouton Traduire -->
      <div style="margin-bottom: 16px;">
        <button
          @click="doTranslateRecording"
          :disabled="isTranslating || !recordingTranscript"
          style="width: 100%; padding: 12px 16px; border: none; border-radius: 6px; font-weight: 600; color: white; transition: all 0.2s;"
          :style="{
            backgroundColor: (isTranslating || !recordingTranscript) ? '#d1d5db' : '#10b981',
            cursor: (isTranslating || !recordingTranscript) ? 'not-allowed' : 'pointer',
            opacity: (isTranslating || !recordingTranscript) ? 0.6 : 1
          }"
        >
          {{ isTranslating ? '⏳ Traduction en cours...' : '🌐 Traduire' }}
        </button>
      </div>

      <!-- Résultat traduction -->
      <div v-if="recordingTranslation" style="padding: 12px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; margin-bottom: 16px;">
        <p style="font-size: 13px; color: #1e40af; margin: 0;">
          <strong>Traduction:</strong> {{ recordingTranslation }}
        </p>
      </div>

      <!-- Erreur -->
      <div v-if="recordError" style="padding: 12px; background: #fee2e2; border: 1px solid #fecaca; border-radius: 6px; color: #991b1b; font-size: 13px;">
        {{ recordError }}
      </div>

      <!-- Debug: micro level + logs -->
      <div style="margin-top:12px; padding:8px; border-top:1px dashed #e5e7eb;">
        <div style="font-size:12px; color:#374151; margin-bottom:6px;"><strong>Micro level:</strong></div>
        <div style="height:10px; background:#e5e7eb; border-radius:4px; overflow:hidden; margin-bottom:8px;">
          <div :style="{width: (Math.min(1, audioLevel) * 100) + '%', height: '100%', background: '#10b981', transition: 'width 0.1s linear'}"></div>
        </div>
        <div style="font-size:12px; color:#374151; margin-bottom:6px;"><strong>Recognition logs (dernieres 10):</strong></div>
        <div style="max-height:120px; overflow:auto; background:#f8fafc; border:1px solid #eef2ff; padding:8px; border-radius:6px; font-size:12px; color:#111827;">
          <div v-for="(l, idx) in eventLogs.slice().reverse()" :key="idx" style="padding:4px 0; border-bottom:1px dashed #f1f5f9;">
            <div style="font-weight:600; font-size:11px; color:#0f172a">{{ l.time }}</div>
            <div style="font-size:12px; color:#374151">{{ l.msg }}</div>
          </div>
          <div v-if="eventLogs.length===0" style="color:#6b7280">(aucun événement enregistré)</div>
        </div>
      </div>
    </div>

    <!-- PARTIE 2: TEXTE VERS AUDIO -->
    <div style="padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px; background: #f9fafb;">
      <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 16px;">📝 Texte en Audio</h3>

      <!-- Langue du texte -->
      <div style="margin-bottom: 16px;">
        <label style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 8px;">Langue:</label>
        <select v-model="textLang" style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px;">
          <option value="fr">Français</option>
          <option value="en">Anglais</option>
          <option value="es">Espagnol</option>
          <option value="de">Allemand</option>
        </select>
      </div>

      <!-- Textarea -->
      <div style="margin-bottom: 16px;">
        <label style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 8px;">Texte:</label>
        <textarea
          v-model="textInput"
          placeholder="Entrez le texte..."
          style="width: 100%; height: 100px; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; resize: vertical;"
        ></textarea>
      </div>

      <!-- Boutons -->
      <div style="display: flex; gap: 12px;">
        <button
          @click="speakText"
          :disabled="!textInput || isTextSpeaking"
          style="flex: 1; padding: 12px 16px; border: none; border-radius: 6px; font-weight: 600; color: white; transition: all 0.2s;"
          :style="{
            backgroundColor: (!textInput || isTextSpeaking) ? '#d1d5db' : '#10b981',
            cursor: (!textInput || isTextSpeaking) ? 'not-allowed' : 'pointer',
            opacity: (!textInput || isTextSpeaking) ? 0.6 : 1
          }"
        >
          {{ isTextSpeaking ? '🔊 En cours...' : '▶️ Écouter' }}
        </button>
        <button
          @click="stopTextSpeaking"
          :disabled="!isTextSpeaking"
          style="flex: 1; padding: 12px 16px; border: none; border-radius: 6px; font-weight: 600; color: white; transition: all 0.2s;"
          :style="{
            backgroundColor: !isTextSpeaking ? '#d1d5db' : '#6b7280',
            cursor: !isTextSpeaking ? 'not-allowed' : 'pointer',
            opacity: !isTextSpeaking ? 0.6 : 1
          }"
        >
          ⏹️ Arrêter
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onUnmounted } from 'vue';
import axios from 'axios';
import { nextTick } from 'vue';

// ==================== PARTIE 1: ENREGISTREMENT ====================
const recordTargetLang = ref('fr');
const isRecording = ref(false);
const recordingTime = ref(0);
const recordingTranscript = ref('');
const recordingTranslation = ref('');
const recordError = ref('');
const isTranslating = ref(false);

// Debug helpers
const audioLevel = ref(0); // 0..1
const eventLogs = ref<Array<{time:string,msg:string}>>([]);

function pushLog(msg: string) {
  try {
    const t = new Date().toLocaleTimeString();
    eventLogs.value.push({ time: t, msg });
    if (eventLogs.value.length > 50) eventLogs.value.shift();
  } catch (e) {}
}

let mediaRecorder: MediaRecorder | null = null;
let recordingTimer: number | null = null;
let recognition: any = null;
let audioCtx: AudioContext | null = null;
let analyser: AnalyserNode | null = null;
let sourceNode: MediaStreamAudioSourceNode | null = null;
let meterInterval: number | null = null;

async function toggleRecording() {
  if (isRecording.value) {
    stopRecording();
  } else {
    await startRecording();
  }
}

async function startRecording() {
  try {
    recordError.value = '';
    recordingTranscript.value = '';
    recordingTranslation.value = '';

    // Accès microphone
    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
    pushLog('Micro: permission accordée');
    mediaRecorder = new MediaRecorder(stream);
    // setup audio analyser to show level
    try {
      audioCtx = new (window.AudioContext || (window as any).webkitAudioContext)();
      analyser = audioCtx.createAnalyser();
      analyser.fftSize = 256;
      sourceNode = audioCtx.createMediaStreamSource(stream);
      sourceNode.connect(analyser);
      // poll level
      meterInterval = window.setInterval(() => {
        if (!analyser) return;
        const data = new Uint8Array(analyser.frequencyBinCount);
        analyser.getByteTimeDomainData(data);
        // compute RMS
        let sum = 0;
        for (let i = 0; i < data.length; i++) {
          const v = (data[i] - 128) / 128;
          sum += v * v;
        }
        const rms = Math.sqrt(sum / data.length);
        audioLevel.value = rms; // 0..~0.7
      }, 100);
    } catch (e) {
      pushLog('Audio analyser impossible: ' + (e as any).message);
    }
    mediaRecorder.start();
    isRecording.value = true;
    recordingTime.value = 0;

    // Chronomètre
    recordingTimer = window.setInterval(() => {
      recordingTime.value++;
    }, 1000);

    // Web Speech API
    const SpeechRecognition = (window as any).webkitSpeechRecognition || (window as any).SpeechRecognition;
    if (!SpeechRecognition) {
      throw new Error('Web Speech API non supportée dans ce navigateur');
    }

    recognition = new SpeechRecognition();
    recognition.continuous = false;
    recognition.interimResults = true;
    recognition.lang = 'fr-FR';

    let fullTranscript = '';

    recognition.onresult = (event: any) => {
      let interim = '';
      for (let i = event.resultIndex; i < event.results.length; i++) {
        const res = event.results[i][0].transcript;
        if (event.results[i].isFinal) {
          fullTranscript += (fullTranscript ? ' ' : '') + res;
        } else {
          interim += res;
        }
      }
      recordingTranscript.value = (fullTranscript + (interim ? ' ' + interim : '')).trim();
      console.log('[AudioTranslator] Transcript updated:', recordingTranscript.value);
      pushLog('onresult — interim length ' + recordingTranscript.value.length);
    };

    recognition.onerror = (event: any) => {
      console.warn('[AudioTranslator] Recognition error:', event.error);
      pushLog('onerror: ' + event.error);
      if (event.error !== 'no-speech' && event.error !== 'aborted') {
        let msg = event.error;
        if (event.error === 'network') msg = 'Problème réseau — vérifiez Internet';
        if (event.error === 'not-allowed') msg = 'Permission micro refusée';
        recordError.value = msg;
      }
    };

    recognition.onend = () => {
      console.log('[AudioTranslator] Recognition ended. Final transcript:', fullTranscript);
      pushLog('onend — final length ' + (fullTranscript.trim().length));
      recordingTranscript.value = fullTranscript.trim() || recordingTranscript.value;
      isRecording.value = false;
      if (recordingTimer) clearInterval(recordingTimer);
      // stop meter
      if (meterInterval) { clearInterval(meterInterval); meterInterval = null; }
      try { if (sourceNode) sourceNode.disconnect(); } catch(e) {}
      try { if (analyser) analyser.disconnect(); } catch(e) {}
      try { if (audioCtx) audioCtx.close(); audioCtx = null; } catch(e) {}
    };

    recognition.start();
  } catch (error: any) {
    recordError.value = error.message || 'Erreur microphone';
    isRecording.value = false;
    if (recordingTimer) clearInterval(recordingTimer);
    console.error('[AudioTranslator] Start recording error:', error);
  }
}

function stopRecording() {
  isRecording.value = false;
  if (recognition) {
    try {
      recognition.stop();
    } catch (e) {
      //
    }
  }
  if (mediaRecorder) {
    mediaRecorder.stop();
    mediaRecorder.stream.getTracks().forEach((track) => track.stop());
  }
  if (recordingTimer) clearInterval(recordingTimer);
  console.log('[AudioTranslator] Recording stopped. Transcript:', recordingTranscript.value);
}

async function doTranslateRecording() {
  if (!recordingTranscript.value.trim()) {
    recordError.value = 'Aucun texte à traduire';
    return;
  }

  try {
    isTranslating.value = true;
    recordError.value = '';
    recordingTranslation.value = '';

    console.log('[AudioTranslator] Starting translation for:', recordingTranscript.value);

    // Crée une promesse qui reject après 8 secondes (timeout)
    const timeoutPromise = new Promise((_, reject) =>
      setTimeout(() => reject(new Error('TIMEOUT')), 8000)
    );

    // Lance la requête axios
    const translationPromise = axios.post('/api/translation/translate', {
      q: recordingTranscript.value,
      source: 'auto',
      target: recordTargetLang.value,
    });

    // Attend la première à terminer (requête ou timeout)
    const { data } = await Promise.race([translationPromise, timeoutPromise]);

    recordingTranslation.value = data.translatedText || data.translated_text || '';
    console.log('[AudioTranslator] Translation completed:', recordingTranslation.value);
  } catch (error: any) {
    console.error('[AudioTranslator] Translation error:', error.message, error);

    // Messages d'erreur clairs et spécifiques
    if (error.message === 'TIMEOUT') {
      recordError.value = '⏳ La traduction prend trop de temps (plus de 8 secondes). Vérifiez votre connexion Internet.';
    } else if (error.response?.status === 429) {
      recordError.value = '❌ Quota API atteint — réessayez dans quelques minutes.';
    } else if (error.response?.data?.error) {
      recordError.value = '❌ ' + error.response.data.error;
    } else if (error.code === 'ECONNABORTED') {
      recordError.value = '❌ Connexion interrompue — vérifiez Internet.';
    } else {
      recordError.value = '❌ Erreur réseau — vérifiez votre connexion.';
    }
  } finally {
    isTranslating.value = false;
  }
}

// ==================== PARTIE 2: TEXTE VERS AUDIO ====================
const textInput = ref('');
const textLang = ref('fr');
const isTextSpeaking = ref(false);

function speakText() {
  if (!textInput.value || !('speechSynthesis' in window)) return;

  isTextSpeaking.value = true;
  const utterance = new SpeechSynthesisUtterance(textInput.value);
  utterance.lang = getLangCode(textLang.value);
  utterance.rate = 1;
  utterance.pitch = 1;

  utterance.onend = () => (isTextSpeaking.value = false);
  utterance.onerror = () => (isTextSpeaking.value = false);

  window.speechSynthesis.cancel();
  window.speechSynthesis.speak(utterance);
  console.log('[AudioTranslator] Speaking:', textInput.value);
}

function stopTextSpeaking() {
  if ('speechSynthesis' in window) {
    window.speechSynthesis.cancel();
    isTextSpeaking.value = false;
    console.log('[AudioTranslator] Speech stopped');
  }
}

function getLangCode(lang: string): string {
  const map: Record<string, string> = {
    fr: 'fr-FR',
    en: 'en-US',
    es: 'es-ES',
    de: 'de-DE',
    it: 'it-IT',
    pt: 'pt-BR',
    ja: 'ja-JP',
    zh: 'zh-CN',
  };
  return map[lang] || lang;
}

onUnmounted(() => {
  stopRecording();
  stopTextSpeaking();
});
</script>
