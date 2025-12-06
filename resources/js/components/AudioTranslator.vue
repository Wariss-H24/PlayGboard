<template>
  <div style="padding: 24px; background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <h2 style="font-size: 20px; font-weight: 600; margin-bottom: 16px;">🎤 Traducteur Audio</h2>

    <!-- Mode sélection -->
    <div style="margin-bottom: 24px; display: flex; gap: 16px;">
      <button
        @click="mode = 'record'"
        :style="{
          padding: '8px 16px',
          borderRadius: '6px',
          fontWeight: '500',
          border: 'none',
          cursor: 'pointer',
          backgroundColor: mode === 'record' ? '#3b82f6' : '#d1d5db',
          color: mode === 'record' ? 'white' : '#374151',
          transition: 'all 0.2s'
        }"
      >
        🎙️ Enregistrer / Upload
      </button>
      <button
        @click="mode = 'text'"
        :style="{
          padding: '8px 16px',
          borderRadius: '6px',
          fontWeight: '500',
          border: 'none',
          cursor: 'pointer',
          backgroundColor: mode === 'text' ? '#3b82f6' : '#d1d5db',
          color: mode === 'text' ? 'white' : '#374151',
          transition: 'all 0.2s'
        }"
      >
        📝 Texte → Audio
      </button>
    </div>

    <!-- Mode 1: Enregistrement / Upload audio -->
    <div v-if="mode === 'record'" style="display: flex; flex-direction: column; gap: 16px;">
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
        <!-- Enregistrement microphone -->
        <div style="border: 1px solid #e5e7eb; border-radius: 6px; padding: 16px;">
          <h3 style="font-weight: 500; margin-bottom: 12px; color: #111827;">Enregistrement microphone</h3>
          <button
            @click="handleRecordClick"
            :disabled="!!audioFile"
            style="{
              width: '100%',
              padding: '8px 16px',
              marginBottom: '8px',
              backgroundColor: !!audioFile ? '#d1d5db' : (isRecording ? '#dc2626' : '#ef4444'),
              color: 'white',
              border: 'none',
              borderRadius: '6px',
              fontWeight: '500',
              cursor: !!audioFile ? 'not-allowed' : 'pointer',
              opacity: !!audioFile ? 0.6 : 1
            }"
          >
            {{ isRecording ? '⏹️ Arrêter' : '⏺️ Enregistrer' }}
          </button>
          <div v-if="recordingTime" style="font-size: 14px; color: #6b7280; margin-bottom: 8px;">
            Durée: {{ recordingTime }}s
          </div>
        </div>

        <!-- Upload fichier -->
        <div style="border: 1px solid #e5e7eb; border-radius: 6px; padding: 16px;">
          <h3 style="font-weight: 500; margin-bottom: 12px; color: #111827;">Upload fichier audio</h3>
          <label style="display: block;">
            <span style="display: none;">Choisir fichier</span>
            <input
              type="file"
              accept="audio/*"
              @change="handleFileSelect"
              style="display: block; width: 100%; font-size: 14px;"
            />
          </label>
        </div>
      </div>

      <!-- Fichier sélectionné -->
      <div v-if="audioFile || recordedBlob" style="background: #eff6ff; padding: 12px; border-radius: 6px; font-size: 14px; color: #1e40af; border: 1px solid #bfdbfe;">
        📁 Fichier sélectionné: {{ audioFile?.name || 'Enregistrement microphone' }}
      </div>

      <!-- Langue source et cible -->
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
        <div>
          <label style="font-size: 14px; font-weight: 500; color: #111827; display: block;">Langue source (auto-détectée)</label>
          <select
            v-model="sourceLangAudio"
            style="width: 100%; margin-top: 8px; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;"
          >
            <option value="auto">Détection automatique</option>
            <option value="fr">Français</option>
            <option value="en">Anglais</option>
            <option value="es">Espagnol</option>
            <option value="de">Allemand</option>
          </select>
        </div>
        <div>
          <label style="font-size: 14px; font-weight: 500; color: #111827; display: block;">Langue cible</label>
          <select
            v-model="targetLangAudio"
            style="width: 100%; margin-top: 8px; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;"
          >
            <option value="fr">Français</option>
            <option value="en">Anglais</option>
            <option value="es">Espagnol</option>
            <option value="de">Allemand</option>
            <option value="it">Italien</option>
          </select>
        </div>
      </div>

      <!-- Bouton traduire -->
      <button
        @click="transcribeAndTranslate"
        :disabled="(!audioFile && !recordedBlob) || isTranscribing"
        style="{
          width: '100%',
          padding: '10px 16px',
          backgroundColor: ((!audioFile && !recordedBlob) || isTranscribing) ? '#d1d5db' : '#10b981',
          color: 'white',
          border: 'none',
          borderRadius: '6px',
          fontWeight: '500',
          cursor: ((!audioFile && !recordedBlob) || isTranscribing) ? 'not-allowed' : 'pointer',
          opacity: ((!audioFile && !recordedBlob) || isTranscribing) ? 0.6 : 1
        }"
      >
        {{ isTranscribing ? '⏳ Traitement...' : '🚀 Transcrire & Traduire' }}
      </button>

      <!-- Erreur -->
      <div
        v-if="audioError"
        style="background: #fee2e2; border: 1px solid #fecaca; padding: 12px; border-radius: 6px; color: #991b1b; font-size: 14px;"
      >
        {{ audioError }}
      </div>

      <!-- Résultat -->
      <div v-if="transcribedText || translatedAudioText" style="display: flex; flex-direction: column; gap: 12px;">
        <div style="background: #f3f4f6; padding: 12px; border-radius: 6px;">
          <h4 style="font-weight: 500; margin-bottom: 8px; color: #111827;">Texte transcrit</h4>
          <p style="font-size: 14px; color: #374151;">{{ transcribedText }}</p>
        </div>

        <div style="background: #eff6ff; padding: 12px; border-radius: 6px;">
          <h4 style="font-weight: 500; margin-bottom: 8px; color: #1e40af;">Texte traduit</h4>
          <p style="font-size: 14px; color: #374151;">{{ translatedAudioText }}</p>
        </div>

        <!-- Playback audio traduit -->
        <div v-if="translatedAudioText" style="background: #f0fdf4; padding: 12px; border-radius: 6px;">
          <h4 style="font-weight: 500; margin-bottom: 8px; color: #166534;">🔊 Écouter la traduction</h4>
          <button
            @click="speakTranslation"
            :disabled="isSpeaking"
            style="{
              width: '100%',
              padding: '10px 16px',
              backgroundColor: isSpeaking ? '#d1d5db' : '#10b981',
              color: 'white',
              border: 'none',
              borderRadius: '6px',
              fontWeight: '500',
              cursor: isSpeaking ? 'not-allowed' : 'pointer',
              opacity: isSpeaking ? 0.6 : 1
            }"
          >
            {{ isSpeaking ? '🔊 En cours...' : '▶️ Jouer audio' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Mode 2: Texte vers Audio -->
    <div v-if="mode === 'text'" style="display: flex; flex-direction: column; gap: 16px;">
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
        <div>
          <label style="font-size: 14px; font-weight: 500; block mb-2; color: #111827; display: block; margin-bottom: 8px;">Texte à convertir</label>
          <textarea
            v-model="textToSpeak"
            placeholder="Entrez du texte..."
            style="width: 100%; height: 128px; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; font-family: sans-serif; resize: none;"
          ></textarea>
        </div>

        <div style="display: flex; flex-direction: column; gap: 12px;">
          <div>
            <label style="font-size: 14px; font-weight: 500; color: #111827; display: block; margin-bottom: 8px;">Langue</label>
            <select v-model="textSpeakLang" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
              <option value="fr">Français</option>
              <option value="en">Anglais</option>
              <option value="es">Espagnol</option>
              <option value="de">Allemand</option>
              <option value="it">Italien</option>
              <option value="ja">Japonais</option>
            </select>
          </div>

          <button
            @click="speakText"
            :disabled="!textToSpeak || isSpeaking"
            style="{
              width: '100%',
              padding: '10px 16px',
              backgroundColor: (!textToSpeak || isSpeaking) ? '#d1d5db' : '#3b82f6',
              color: 'white',
              border: 'none',
              borderRadius: '6px',
              fontWeight: '500',
              cursor: (!textToSpeak || isSpeaking) ? 'not-allowed' : 'pointer',
              opacity: (!textToSpeak || isSpeaking) ? 0.6 : 1
            }"
          >
            {{ isSpeaking ? '🔊 En cours...' : '▶️ Jouer' }}
          </button>

          <button
            @click="stopSpeaking"
            style="width: 100%; padding: 10px 16px; background: #6b7280; hover: #4b5563; color: white; border: none; border-radius: 6px; font-weight: 500; cursor: pointer;"
          >
            ⏹️ Arrêter
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onUnmounted } from 'vue';
import axios from 'axios';

// États généraux
const mode = ref<'record' | 'text'>('record');

// Enregistrement audio
const isRecording = ref(false);
const recordingTime = ref(0);
const audioFile = ref<File | null>(null);
const recordedBlob = ref<Blob | null>(null);
let mediaRecorder: MediaRecorder | null = null;
let recordingTimer: number | null = null;
let audioChunks: BlobPart[] = [];

// Traduction audio
const sourceLangAudio = ref('auto');
const targetLangAudio = ref('fr');
const isTranscribing = ref(false);
const audioError = ref('');
const transcribedText = ref('');
const translatedAudioText = ref('');

// Text-to-speech
const textToSpeak = ref('');
const textSpeakLang = ref('fr');
const isSpeaking = ref(false);
let speechSynthesis: SpeechSynthesisUtterance | null = null;

/**
 * Démarrer l'enregistrement microphone
 */
async function startRecording() {
  try {
    audioError.value = '';
    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
    mediaRecorder = new MediaRecorder(stream);
    audioChunks = [];

    mediaRecorder.ondataavailable = (event) => {
      audioChunks.push(event.data);
    };

    mediaRecorder.onstop = () => {
      recordedBlob.value = new Blob(audioChunks, { type: 'audio/webm' });
    };

    mediaRecorder.start();
    isRecording.value = true;
    recordingTime.value = 0;

    // Compteur de temps
    recordingTimer = window.setInterval(() => {
      recordingTime.value++;
    }, 1000);
  } catch (error) {
    audioError.value =
      'Accès microphone refusé. Vérifiez les permissions du navigateur.';
  }
}

/**
 * Arrêter l'enregistrement
 */
function stopRecording() {
  if (mediaRecorder && isRecording.value) {
    mediaRecorder.stop();
    mediaRecorder.stream.getTracks().forEach((track) => track.stop());
    isRecording.value = false;
    if (recordingTimer) clearInterval(recordingTimer);
    recordingTime.value = 0;
  }
}

// Gérer le bouton enregistrement/arrêt
async function handleRecordClick() {
  if (isRecording.value) {
    stopRecording();
  } else {
    await startRecording();
  }
}

/**
 * Gérer upload fichier
 */
function handleFileSelect(event: Event) {
  const input = event.target as HTMLInputElement;
  if (input.files?.[0]) {
    audioFile.value = input.files[0];
    recordedBlob.value = null;
  }
}

/**
 * Transcrire audio via Web Speech API (côté client)
 * Note: Web Speech API fonctionne mieux en reconnaissance continue
 */
async function transcribeAndTranslate() {
  try {
    isTranscribing.value = true;
    audioError.value = '';
    transcribedText.value = '';
    translatedAudioText.value = '';

    // Prépa blob audio
    const blob = audioFile.value || recordedBlob.value;
    if (!blob) {
      throw new Error('Aucun fichier audio sélectionné');
    }

    // Créer FormData avec audio
    const fd = new FormData();
    fd.append('audio', blob);
    fd.append('source', sourceLangAudio.value);
    fd.append('target', targetLangAudio.value);

    // Appel backend (placeholder pour transcription côté serveur si nécessaire)
    // Pour maintenant, on utilise Web Speech API directement côté client
    const recognized = await clientTranscribe(blob);

    if (!recognized) {
      throw new Error('Transcription échouée');
    }

    transcribedText.value = recognized;

    // Traduction via endpoint existant
    const { data } = await axios.post('/api/translation/translate', {
      q: recognized,
      source: sourceLangAudio.value === 'auto' ? 'en' : sourceLangAudio.value,
      target: targetLangAudio.value,
    });

    translatedAudioText.value = data.translatedText || data.translated_text || '';
  } catch (error: any) {
    audioError.value = error.message || 'Erreur lors de la traduction audio';
  } finally {
    isTranscribing.value = false;
  }
}

/**
 * Transcrire audio côté client via Web Speech API
 * (Limitation: fonctionne mieux dans certains navigateurs, anglais/français supportés)
 */
function clientTranscribe(blob: Blob): Promise<string> {
  return new Promise((resolve, reject) => {
    const recognition = new (window as any).webkitSpeechRecognition();
    recognition.continuous = false;
    recognition.interimResults = false;
    recognition.language = sourceLangAudio.value === 'auto' ? 'en-US' : sourceLangAudio.value;

    recognition.onresult = (event: any) => {
      let transcript = '';
      for (let i = event.resultIndex; i < event.results.length; i++) {
        transcript += event.results[i][0].transcript;
      }
      resolve(transcript);
    };

    recognition.onerror = (event: any) => {
      reject(new Error(`Erreur reconnaissance vocale: ${event.error}`));
    };

    // Convertir blob en audio et jouer
    const url = URL.createObjectURL(blob);
    const audio = new Audio(url);
    audio.onended = () => {
      recognition.stop();
    };
    recognition.start();
    audio.play();
  });
}

/**
 * Parler la traduction (TTS via Web Speech API)
 */
function speakTranslation() {
  if (!translatedAudioText.value) return;
  speakText();
}

/**
 * Convertir texte en parole (Text-to-Speech)
 */
function speakText() {
  if (!textToSpeak.value && !translatedAudioText.value) return;

  const text = translatedAudioText.value || textToSpeak.value;
  const lang = translatedAudioText.value
    ? targetLangAudio.value
    : textSpeakLang.value;

  if ('speechSynthesis' in window) {
    isSpeaking.value = true;
    speechSynthesis = new SpeechSynthesisUtterance(text);
    speechSynthesis.lang = getLangCode(lang);
    speechSynthesis.onend = () => {
      isSpeaking.value = false;
    };
    window.speechSynthesis.speak(speechSynthesis);
  } else {
    audioError.value = 'Web Speech API non supportée dans ce navigateur';
  }
}

/**
 * Arrêter la parole
 */
function stopSpeaking() {
  if ('speechSynthesis' in window) {
    window.speechSynthesis.cancel();
    isSpeaking.value = false;
  }
}

/**
 * Convertir code langue court en locale complète
 */
function getLangCode(lang: string): string {
  const map: Record<string, string> = {
    fr: 'fr-FR',
    en: 'en-US',
    es: 'es-ES',
    de: 'de-DE',
    it: 'it-IT',
    ja: 'ja-JP',
  };
  return map[lang] || lang;
}

/**
 * Cleanup
 */
onUnmounted(() => {
  if (recordingTimer) clearInterval(recordingTimer);
  if (mediaRecorder && isRecording.value) stopRecording();
  stopSpeaking();
});
</script>
