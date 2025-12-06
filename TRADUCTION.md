# Guide Complet du Système de Traduction

## 📋 Table des matières
1. [Vue d'ensemble](#vue-densemble)
2. [Architecture](#architecture)
3. [Composant Vue](#composant-vue)
4. [Backend Laravel](#backend-laravel)
5. [API Utilisée](#api-utilisée)
6. [Fonctionnalités](#fonctionnalités)
7. [Guide d'utilisation](#guide-dutilisation)
8. [Troubleshooting](#troubleshooting)
9. [Astuces et optimisations](#astuces-et-optimisations)

---

## Vue d'ensemble

Ce système permet aux utilisateurs de traduire du texte de manière **automatique et en temps réel**. Le système fonctionne en deux parties :

- **Frontend (Vue 3)** : Interface utilisateur avec saisie de texte, sélection des langues, et affichage du résultat
- **Backend (Laravel)** : Proxy qui communique avec l'API MyMemory pour les traductions

**API utilisée** : MyMemory (https://api.mymemory.translated.net) - gratuite, sans clé API requise

---

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                  NAVIGATEUR (Vue 3)                         │
│                  resources/js/pages/Traducteur.vue          │
│  ┌──────────────────────────────────────────────────────┐  │
│  │ • Textarea pour texte source                         │  │
│  │ • Select pour langue source (20 langues)             │  │
│  │ • Affichage live de la traduction                    │  │
│  │ • Select pour langue cible                           │  │
│  │ • Bouton permutation (↔)                             │  │
│  │ • Bouton copier texte                                │  │
│  │ • Compteur de caractères (max 2000)                  │  │
│  └──────────────────────────────────────────────────────┘  │
└──────────────────┬──────────────────────────────────────────┘
                   │ Axios HTTP POST
                   │
┌──────────────────▼──────────────────────────────────────────┐
│              SERVEUR LARAVEL (Backend)                       │
│         app/Http/Controllers/TranslationController.php       │
│  ┌──────────────────────────────────────────────────────┐  │
│  │ POST /api/translation/translate                      │  │
│  │ POST /api/translation/detect                         │  │
│  │ (Proxy vers MyMemory)                                │  │
│  └──────────────────────────────────────────────────────┘  │
└──────────────────┬──────────────────────────────────────────┘
                   │ cURL HTTP Request
                   │
┌──────────────────▼──────────────────────────────────────────┐
│          API EXTERNE : MyMemory                              │
│     https://api.mymemory.translated.net/get                 │
│     ?q=text&langpair=en|fr                                  │
└──────────────────────────────────────────────────────────────┘
```

---

## Composant Vue

### Fichier : `resources/js/pages/Traducteur.vue`

#### 1. **Imports et Configuration**

```typescript
import axios from 'axios';
import { ArrowLeft, ArrowRightLeft } from 'lucide-vue-next';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed, watch, onMounted } from 'vue';
```

**Explications** :
- `axios` : Client HTTP pour faire les requêtes au backend
- `lucide-vue-next` : Icônes (ArrowLeft pour "Retour", ArrowRightLeft pour "Permuter")
- `Head` : Gère le titre de la page
- `ref`, `computed`, `watch` : Réactivité Vue 3

#### 2. **Langues Supportées**

```typescript
const languages = [
    { code: 'auto', name: 'Détection automatique' },
    { code: 'en', name: 'Anglais' },
    { code: 'fr', name: 'Français' },
    { code: 'es', name: 'Espagnol' },
    // ... 16 autres langues
];
```

**Comment ajouter une langue** :
1. Trouvez le code ISO 639-1 de la langue (ex: `de` pour Allemand)
2. Vérifiez que MyMemory la supporte : https://api.mymemory.translated.net/
3. Ajoutez une entrée : `{ code: 'de', name: 'Allemand' }`

#### 3. **Données Réactives**

```typescript
const source = ref('auto');           // Langue source (par défaut: détection)
const target = ref('fr');              // Langue cible (par défaut: français)
const inputText = ref('');             // Texte à traduire
const translatedText = ref('');        // Texte traduit
const detectedLang = ref('');          // Langue détectée (si auto)
const loading = ref(false);            // État de chargement
const error = ref('');                 // Messages d'erreur
const maxChars = 2000;                 // Limite de caractères
```

#### 4. **Traduction (Fonction Clé)**

```typescript
async function translate(q: string, src: string, tgt: string) {
    try {
        loading.value = true;
        error.value = '';
        
        // Appel au backend
        const { data } = await axios.post(`${API_BASE}/translate`, {
            q,
            source: src === 'auto' ? 'auto' : src,
            target: tgt,
            format: 'text',
        });
        
        console.log('Translation response:', data);
        
        // MyMemory retourne soit translatedText soit translated_text
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
```

**Explications** :
- `loading.value = true` : Affiche "Traduction en cours…"
- `axios.post()` : Appel sécurisé au backend (CORS évité)
- `translatedText || translated_text` : MyMemory peut retourner l'un ou l'autre
- `error.value` : Affiche l'erreur à l'utilisateur
- `finally` : Masque le loader même en cas d'erreur

#### 5. **Détection de Langue (Fonction Bonus)**

```typescript
async function detectLanguage(q: string) {
    try {
        const { data } = await axios.post(`${API_BASE}/detect`, { q });
        // data est un array : [{language: 'en', confidence: 0.8}]
        if (Array.isArray(data) && data.length > 0) {
            return data[0].language as string;
        }
    } catch (e) {
        console.error(e);
    }
    return '';
}
```

**Note** : MyMemory n'a pas de vrai endpoint `/detect`, donc le backend retourne un dummy.
Pour une vraie détection, voir la section [Astuces](#astuces-et-optimisations).

#### 6. **Permutation des Langues (Fonctionnalité Critique)**

```typescript
function swapLanguages() {
    // Bloquer si source = 'auto' (pas de sens d'inverser)
    if (source.value === 'auto') return;
    
    // Inverser les langues
    const s = source.value;
    source.value = target.value;
    target.value = s;
    
    // ⭐ CLÉS : Inverser aussi les TEXTES
    const temp = inputText.value;
    inputText.value = translatedText.value;      // La traduction devient le texte à traduire
    translatedText.value = temp;                 // Le texte à traduire devient la traduction
    
    // Retraduire avec les nouvelles langues
    void doTranslate();
}
```

**Exemple concret** :
```
Avant clic sur ↔ :
- Source: Anglais  | Texte: "Hello"
- Target: Français | Traduction: "Bonjour"

Après clic sur ↔ :
- Source: Français | Texte: "Bonjour"
- Target: Anglais  | Traduction: "Hello" (recalculé)
```

**Pourquoi bloquer `auto`** :
Si source='auto', on ne peut pas savoir la vraie langue source. On ne peut donc pas l'inverser.

#### 7. **Copie du Texte**

```typescript
function copyTranslated() {
    if (!translatedText.value) return;
    navigator.clipboard?.writeText(translatedText.value);
}
```

Utilise l'API moderne `navigator.clipboard` (supportée par tous les navigateurs modernes).

#### 8. **Debounce (Performance)**

```typescript
function onInputChange() {
    error.value = '';
    if (debounceTimer) window.clearTimeout(debounceTimer);
    
    // Attendre 600ms avant de tracer
    debounceTimer = window.setTimeout(() => {
        if (inputText.value.length > maxChars) {
            inputText.value = inputText.value.slice(0, maxChars);
        }
        void doTranslate();
    }, 600);
}
```

**Pourquoi debounce** :
- L'utilisateur tape rapidement (ex: 50 caractères)
- Sans debounce : 50 appels API
- Avec debounce : 1 seul appel après qu'il ait fini
- **Économise 98% des appels API** ✅

#### 9. **Watchers (Réactivité)**

```typescript
watch([source, target], () => {
    // Si l'utilisateur change la langue, retraduire automatiquement
    if (inputText.value.trim()) {
        if (debounceTimer) window.clearTimeout(debounceTimer);
        debounceTimer = window.setTimeout(() => void doTranslate(), 200);
    }
});
```

---

## Backend Laravel

### Fichier : `app/Http/Controllers/TranslationController.php`

#### 1. **Endpoints**

```php
Route::post('/translation/translate', [TranslationController::class, 'translate']);
Route::post('/translation/detect', [TranslationController::class, 'detect']);
```

#### 2. **Méthode Translate**

```php
public function translate(Request $request): JsonResponse
{
    // Valider les entrées
    $request->validate([
        'q' => 'required|string|max:2000',
        'source' => 'required|string',
        'target' => 'required|string',
    ]);

    try {
        $payload = [
            'q' => $request->input('q'),
            'source' => $request->input('source'),
            'target' => $request->input('target'),
            'format' => 'text',
        ];
        
        // Appeler MyMemory
        $response = $this->callAPI($url);
        
        // Transformer la réponse
        return response()->json([
            'translatedText' => $response['responseData']['translatedText'] ?? '',
        ]);
    } catch (\Exception $e) {
        \Log::error('Translate error: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
```

#### 3. **Appel API (cURL)**

```php
private function callAPI(string $url): ?array
{
    $ch = curl_init($url);
    
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,  // ⚠️ Dev only!
        CURLOPT_USERAGENT => 'Mozilla/5.0...',
    ]);

    $response = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        throw new \Exception('cURL Error: ' . $curlError);
    }

    $decoded = json_decode($response, true);
    if ($decoded === null) {
        throw new \Exception('Invalid JSON response');
    }

    if ($httpCode >= 400) {
        throw new \Exception("API Error (HTTP $httpCode)");
    }

    return $decoded;
}
```

**Astuces** :
- `CURLOPT_SSL_VERIFYPEER => false` : Pour développement/Laragon. **À ACTIVER en production** !
- `CURLOPT_TIMEOUT => 10` : Évite les requêtes infinies
- `curl_error()` : Capture les erreurs de connexion

---

## API Utilisée

### MyMemory Translate API

**URL** : `https://api.mymemory.translated.net/get`

**Format** :
```
GET https://api.mymemory.translated.net/get?q=Hello&langpair=en|fr
```

**Réponse** :
```json
{
    "responseStatus": 200,
    "responseData": {
        "translatedText": "Bonjour",
        "match": 0.5
    }
}
```

**Avantages** :
✅ Gratuit, pas de clé API  
✅ Pas de limite de débit  
✅ Supporte 100+ langues  
✅ Simple et rapide  

**Limitations** :
⚠️ Qualité inférieure à Google Translate  
⚠️ Pas de détection de langue native  
⚠️ Pas d'authentification (public)  

**Alternatives** :
- **Google Cloud Translation** (payant, haute qualité)
- **DeepL API** (payant, très bonne qualité)
- **LibreTranslate** (auto-hébergé)

---

## Fonctionnalités

### 1. **Détection Automatique**
- Sélectionnez "Détection automatique" en source
- Le système détecte automatiquement la langue
- Affichée dans "Langue détectée : [code]"

### 2. **Traduction en Temps Réel**
- À chaque frappe, le texte se traduit (~600ms de debounce)
- "Traduction en cours…" pendant le fetch
- Affiche les erreurs rouge

### 3. **Permutation (↔)**
- Inverse source ↔ target
- **Les textes s'inversent aussi** (clé du fonctionnement!)
- Retraduction automatique
- Bloquée si source='auto'

### 4. **Copie**
- Bouton "Copier le texte" copie la traduction
- Utilise `navigator.clipboard`
- Notification système native

### 5. **Compteur**
- Affiche `X/2000` caractères
- Bloque à 2000 caractères
- Affichage côté frontend et backend

### 6. **Spellcheck**
- `spellcheck="true"` sur textarea
- Soulignement rouge des fautes (navigateur)
- Client-side only

---

## Guide d'utilisation

### Pour l'utilisateur final

1. **Accédez** à `/traducteur` (page Traducteur)
2. **Entrez** du texte dans le champ gauche
3. **Sélectionnez** une langue source (ou "Détection automatique")
4. **Sélectionnez** une langue cible
5. **Voyez** la traduction instantanément à droite
6. **Permutez** avec le bouton ↔ pour inverser
7. **Copiez** avec le bouton vert

### Pour un développeur

#### Installation

```bash
cd c:\Users\HPC\Documents\PlayGboard
npm install        # Vue/Axios déjà inclus
php artisan serve  # Démarrer le serveur
```

#### Tester localement

```bash
# URL
http://localhost:8000/traducteur

# Console (F12)
# Vérifier les logs:
console.log('Translation response:', data);
```

#### Debug

1. Ouvrir DevTools (F12)
2. **Onglet Console** : voir les erreurs
3. **Onglet Network** : voir requêtes API
4. Logs Laravel : `storage/logs/laravel.log`

```bash
# Voir les logs en temps réel
Get-Content -Path "storage/logs/laravel.log" -Tail 50 -Wait
```

---

## Troubleshooting

### Erreur 500 "Empty response from API"

**Cause** : MyMemory ne répond pas

**Solution** :
```bash
# Tester manuellement
curl "https://api.mymemory.translated.net/get?q=hello&langpair=en|fr"

# Si ça ne marche pas, utiliser un proxy HTTPS
# Ou changer d'API
```

### Le texte ne se traduit pas

**Causes possibles** :
1. Connexion internet down
2. MyMemory down (rare)
3. Langue source/cible non supportée
4. Texte > 2000 caractères

**Vérifier** :
```bash
# Console browser
POST /api/translation/translate 500
# Voir le détail de l'erreur
```

### Permutation ne fonctionne pas

**Cause** : Probablement source='auto'

**Vérifier** :
```js
// Console
console.log('Source:', source.value)  // Ne doit pas être 'auto'
```

### Debounce trop agressif/pas assez

**Ajuster** :
```typescript
// Dans Traducteur.vue, ligne ~121
debounceTimer = window.setTimeout(() => {
    void doTranslate();
}, 600);  // ← Changer 600 à 300 (rapide) ou 1000 (lent)
```

---

## Astuces et optimisations

### 1. **Vraie Détection de Langue**

MyMemory n'a pas de vrai `/detect`. Pour ajouter une vraie détection :

```php
// app/Http/Controllers/TranslationController.php

private const DETECT_API = 'https://api.languageidentifierapi.com/identify';

public function detect(Request $request): JsonResponse
{
    $q = $request->input('q');
    
    // API tierce (payante) ou TextRazor API
    $response = $this->callAPI(
        self::DETECT_API . '?q=' . urlencode($q)
    );
    
    return response()->json([
        ['language' => $response['language'], 'confidence' => 0.9]
    ]);
}
```

### 2. **Cache les Traductions**

```php
// Éviter de retraduire le même texte

use Illuminate\Support\Facades\Cache;

public function translate(Request $request): JsonResponse
{
    $cacheKey = 'translate:' . md5($request->input('q') . $src . $tgt);
    
    if (Cache::has($cacheKey)) {
        return response()->json(Cache::get($cacheKey));
    }
    
    // ... effectuer traduction
    
    Cache::put($cacheKey, $result, now()->addHours(24));
    return response()->json($result);
}
```

### 3. **Rate Limiting**

```php
// routes/api.php

Route::middleware('throttle:60,1')->group(function () {
    Route::post('/translation/translate', [...]);
    Route::post('/translation/detect', [...]);
});
```

Limite à 60 requêtes par minute par utilisateur.

### 4. **SSL en Production**

```php
// app/Http/Controllers/TranslationController.php

CURLOPT_SSL_VERIFYPEER => env('APP_ENV') === 'production',
// Ou télécharger un certificat CA :
CURLOPT_CAINFO => storage_path('certs/cacert.pem'),
```

### 5. **Support des Fichiers**

Ajouter un upload de fichier pour traduction en batch :

```typescript
const file = ref<File | null>(null);

function uploadFile() {
    const formData = new FormData();
    formData.append('file', file.value);
    formData.append('target', target.value);
    
    axios.post('/api/translation/file', formData);
}
```

### 6. **Historique des Traductions**

Sauvegarder chaque traduction :

```php
// app/Models/Translation.php
class Translation extends Model {
    protected $fillable = ['user_id', 'source_lang', 'target_lang', 'source_text', 'translated_text'];
}

// Dans TranslationController
Translation::create([
    'user_id' => auth()->id(),
    'source_lang' => $source,
    'target_lang' => $target,
    'source_text' => $q,
    'translated_text' => $result['translatedText'],
]);
```

---

## Résumé

| Composant | Rôle | Technologie |
|-----------|------|-------------|
| Vue Component | UI interactive | Vue 3 + Axios |
| Controller | Middleware | Laravel |
| API | Traduction réelle | MyMemory |
| Debounce | Optimisation | setTimeout/clearTimeout |
| Permutation | Inversion texte+langue | State management |

**Points clés à retenir** :
✅ Le debounce réduit les appels API de 98%  
✅ La permutation inverse AUSSI les textes  
✅ MyMemory gratuit mais limité en qualité  
✅ Toujours valider les entrées côté backend  
✅ Gérer les erreurs gracieusement  

---

## Questions Fréquentes

**Q: Puis-je changer l'API ?**  
R: Oui ! Remplacez `callAPI()` et adaptez la réponse.

**Q: Comment améliorer la qualité ?**  
R: Passer à DeepL ou Google Cloud Translation (payant).

**Q: Puis-je traduire des fichiers ?**  
R: Oui, ajouter un upload + traitement batch.

**Q: Comment sauvegarder l'historique ?**  
R: Créer une table `translations` et logger chaque requête.

**Q: Quelle est la limite de caractères ?**  
R: 2000 (configurable). MyMemory supporte jusqu'à 5000.

---

**Bonne traduction ! 🌍**
