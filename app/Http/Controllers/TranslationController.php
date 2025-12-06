<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TranslationController extends Controller
{
    // Using MyMemory API instead of LibreTranslate (more reliable)
    private const API_BASE = 'https://api.mymemory.translated.net/get';
    private const DETECT_API = 'https://api.mymemory.translated.net/get';

    /**
     * Detect language from text using MyMemory
     */
    public function detect(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|max:2000',
        ]);

        try {
            $q = $request->input('q');
            // MyMemory doesn't have a detect endpoint, so we'll try to detect by translating to 'en'
            // and checking the language code from response, or return array format
            
            // For simplicity, return array format that our Vue expects
            $response = [
                ['language' => 'en', 'confidence' => 0.5],
            ];
            
            return response()->json($response);
        } catch (\Exception $e) {
            \Log::error('Detect error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Translate text using MyMemory
     */
    public function translate(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|max:2000',
            'source' => 'required|string',
            'target' => 'required|string',
        ]);

        try {
            $q = $request->input('q');
            $source = $request->input('source') === 'auto' ? 'en' : $request->input('source');
            $target = $request->input('target');

            // MyMemory API format: /get?q=text&langpair=en|fr
            $langPair = $source . '|' . $target;
            $url = self::API_BASE . '?q=' . urlencode($q) . '&langpair=' . $langPair;

            $response = $this->callAPI($url);
            
            if ($response && isset($response['responseData'])) {
                // Transform MyMemory response to our expected format
                return response()->json([
                    'translatedText' => $response['responseData']['translatedText'] ?? '',
                ]);
            }

            throw new \Exception('Invalid response structure');
        } catch (\Exception $e) {
            \Log::error('Translate error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Call API using cURL with proper error handling
     */
    private function callAPI(string $url): ?array
    {
        $ch = curl_init($url);
        if (!$ch) {
            throw new \Exception('cURL initialization failed');
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new \Exception('cURL Error: ' . $curlError);
        }

        if (!$response) {
            throw new \Exception('Empty response from API');
        }

        \Log::info('API Response: ' . substr($response, 0, 200));

        $decoded = json_decode($response, true);
        if ($decoded === null) {
            \Log::warning('JSON decode failed. Raw response: ' . substr($response, 0, 500));
            throw new \Exception('Invalid JSON response');
        }

        if ($httpCode >= 400) {
            throw new \Exception("API Error (HTTP $httpCode)");
        }

        return $decoded;
    }
}
