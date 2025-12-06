# PDF Translation — Guide et bonnes pratiques

Ce document décrit le fonctionnement du module de traduction de PDF présent dans le projet, les limitations actuelles, et les méthodes pour accélérer ou améliorer la qualité des traductions.

## Vue d'ensemble

Le flux principal pour traduire un PDF est :

1. L'utilisateur upload un fichier PDF via l'interface.
2. Le backend (Laravel) valide le fichier (type `pdf`, taille max 5MB).
3. Le backend extrait le texte du PDF avec `smalot/pdfparser`.
4. Le texte est découpé en "chunks" (morceaux) pour limiter la taille des requêtes vers l'API de traduction.
5. Chaque chunk est traduit via MyMemory API.
6. Les chunks traduits sont concaténés et un nouveau PDF est généré (FPDF) et rendu téléchargeable.

## Endpoints

- `POST /api/pdf/upload` : upload + extrait + traduit le PDF. Retourne `translated_text` et métadonnées.
- `GET /api/pdf/download` : télécharge le PDF généré à partir du texte traduit (la traduction est stockée en session).

## Format du texte et limites

- Le pipeline supporte **uniquement** les PDFs textuels (avec texte sélectionnable). Les PDFs scannés (images) nécessitent OCR et ne sont pas traités par défaut.
- Limite de taille côté serveur : **5 MB**. Le validateur Laravel (`max:5120`) et une vérification additionnelle en bytes sont appliqués.
- Le pipeline extrait le texte page par page et le concatène en une seule chaîne.

## Découpage (chunking)

- Le texte est divisé en chunks pour deux raisons :
  - Respecter les limites de taille des requêtes HTTP/API.
  - Garder un contexte sémantique raisonnable (on découpe de préférence sur des limites de phrases).
- Par défaut, la taille d'un chunk est **500 caractères**. On peut augmenter cette taille (ex : 1000) pour réduire le nombre d'appels API au prix d'appels individuels légèrement plus lourds.

## Accélérer la traduction (techniques)

1. Parallélisation
   - Au lieu d'appels séquentiels, utiliser des requêtes asynchrones (Guzzle `getAsync`) pour traduire plusieurs chunks en parallèle.
   - Dans le code actuel, on lance les requêtes en parallèle et on attend les résultats (gestion des promesses). Attention aux quotas/rate limits de l'API.

2. Ajuster la taille des chunks
   - Passer de 500 à 1000 caractères réduit le nombre d'appels par ~2x.
   - Recommandation : tester 800–1200 pour trouver le meilleur compromis.

3. Limiter la concurrence
   - Pour éviter de surcharger l'API ou d'atteindre les quotas, limiter le nombre de requêtes parallèles (ex : 5 concurrentes).

4. Caching
   - Mettre en cache les traductions de chunks identiques (ex : clé = hash(chunk + target)).
   - Utile pour gros documents récurrents ou pour parties répétitives.

5. Utiliser une API payante (optionnel)
   - Google Translate, DeepL, Microsoft Translator donnent généralement de meilleures performances et une meilleure qualité.
   - Elles permettent aussi une détection de langue plus fiable.

## Gestion de la langue source

- MyMemory n'offre pas d'endpoint fiable de détection en tant que service séparé. Le code actuel passe la source en `auto` pour que l'API identifie la langue.
- Pour un meilleur contrôle, on peut exécuter une détection locale (ex : heuristiques ou service tiers) avant d'appeler la traduction.

## Qualité du PDF généré (Unicode / polices)

- Le code actuel utilise `FPDF` (via `setasign/fpdi`) pour générer le PDF final.
- Limitation importante : `FPDF` n'est pas Unicode-ready par défaut. Les caractères non-latins (chinois, arabe, hébreu, etc.) peuvent s'afficher incorrectement dans le PDF de sortie.

Solutions :
- **Option A (recommandée)** : utiliser `TCPDF` ou `mPDF` (ex : `composer require tecnickcom/tcpdf` ou `mpdf/mpdf`) — ces bibliothèques supportent l'UTF-8 et l'incorporation de polices Unicode.
- **Option B** : ajouter et intégrer des polices TrueType Unicode dans FPDF/FPDI (plus de travail manuel).

## OCR pour PDFs scannés

- Pour traduire des PDFs scannés (image-only), ajouter une étape OCR (Tesseract via un binding PHP ou un service externe). Après OCR, le texte peut passer par le pipeline normal.
- Impact : augmente le coût et le temps de traitement.

## Limites et risques

- Quotas de l'API MyMemory : possible throttling / limites horaires.
- Erreurs réseau / JSON invalides : le code doit prévoir un fallback (retourner le chunk original en cas d'échec).
- Perte de mise en forme : le flux ne préserve pas la mise en page, images, tableaux.

## Conseils pratiques pour les développeurs

- Pour tester rapidement : utiliser un PDF court (1–5 pages) et observer le `char_count` et `page_count` retournés par `POST /api/pdf/upload`.
- Pour accélération : combiner augmentation du chunk size (ex: 1000) + parallélisation (5–10 concurrents) + cache.
- Pour production : activer la vérification SSL (`verify` true) et envisager une API payante pour fiabilité.

## Réactivation de l'UI PDF

- Le code de l'UI PDF est présent mais peut être commenté dans `resources/js/pages/Traducteur.vue`.
- Pour réactiver : décommenter l'import `PDFTranslationPanel` et la balise `<PDFTranslationPanel :languages="languages" />`.

---

Si tu veux, je peux :
- Ajouter un exemple de configuration `tcpdf` et modifier `PDFTranslationController` pour générer un PDF UTF-8 correct.
- Implémenter le cache de chunks (Redis ou filesystem).
- Ajouter une option OCR (via Tesseract) dans l'UI.

Dis-moi quelle amélioration prioriser et je l'implémente.

