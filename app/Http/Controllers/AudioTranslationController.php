<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AudioTranslationController extends Controller
{
    /**
     * Contrôleur pour la traduction audio
     * 
     * Note: La plupart du traitement se fait côté client via Web Speech API:
     * - Enregistrement microphone: MediaRecorder API
     * - Transcription: Web Speech API (SpeechRecognition)
     * - Text-to-Speech: Web Speech API (SpeechSynthesis)
     * 
     * Ce contrôleur est réservé pour:
     * - Coordination des requêtes de traduction
     * - Future: transcription côté serveur si besoin (nécessiterait ffmpeg + speech-to-text)
     * - Future: gestion fichiers audio (stockage, compression)
     */

    /**
     * Transcrire audio via endpoint serveur (optionnel, implémentation future)
     * 
     * POST /api/audio/transcribe
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function transcribe(Request $request)
    {
        // Placeholder pour future implémentation serveur
        // Nécessiterait:
        // - ffmpeg pour conversion audio
        // - Speech-to-Text API (Google Cloud, Azure, etc.)
        // 
        // Pour maintenant, transcription faite côté client via Web Speech API
        
        return response()->json([
            'message' => 'Transcription côté serveur non encore implémentée',
            'note' => 'Utiliser Web Speech API côté client (déjà intégré)',
        ]);
    }

    /**
     * Traduire texte transcrit
     * 
     * POST /api/audio/translate
     * Proxy vers TranslationController@translate
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function translate(Request $request)
    {
        // Utiliser le contrôleur de traduction existant
        $translationController = new TranslationController();
        return $translationController->translate($request);
    }
}
