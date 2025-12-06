# Guide d'implémentation — Traduction de PDFs

Ce guide explique étape par étape comment mettre en place la fonctionnalité de traduction de fichiers PDF dans ce projet Laravel + Vue. Il indique aussi les optimisations (parallélisation, taille des chunks, cache), les problèmes courants (Unicode, OCR) et des snippets pour réimplémenter le contrôleur et le composant côté frontend.

---

## 1. Dépendances (backend)

Installer les paquets nécessaires :

- `smalot/pdfparser` pour extraire le texte depuis un PDF textuel
- `setasign/fpdi` (ou `tecnickcom/tcpdf` / `mpdf` pour Unicode)
- `guzzlehttp/guzzle` (déjà inclus généralement via Laravel)

Commandes :

```bash
composer require smalot/pdfparser setasign/fpdi guzzlehttp/guzzle
# Option pour Unicode dans le PDF final (recommandé)
composer require tecnickcom/tcpdf
```

Remarque : si vous utilisez `TCPDF` ou `mPDF` vous n'aurez pas à gérer manuellement les polices TrueType pour l'UTF-8.

---

## 2. Contrôleur backend (squelette)

Le contrôleur principal (anciennement `PDFTranslationController.php`) doit :

1. Valider le fichier uploadé (`mimes:pdf`, taille max)
2. Extraire le texte via `smalot/pdfparser`
3. Découper le texte en "chunks" (ex: 500–1000 caractères) sur limites de phrase
4. Traduire chaque chunk (via MyMemory ou autre API). Pour la performance, lancer plusieurs requêtes en parallèle avec Guzzle (`getAsync`) et limiter la concurrence (ex: Pool de 5)
5. Concaténer les chunks traduits
6. Générer un nouveau PDF (recommandation : `TCPDF` pour l'UTF-8)
7. Retourner le résultat (JSON + stocker en session pour `download` si besoin)

Extraits à adapter depuis l'ancien code :

- `splitText($text, $maxChunkSize = 500)` — découpe en phrases
- `translateChunksParallel($chunks, $source, $target)` — utiliser Guzzle async + Promise handling

---

## 3. Routes

Ajouter dans `routes/api.php` :

```php
Route::post('/pdf/upload', [PDFTranslationController::class, 'upload']);
Route::get('/pdf/download', [PDFTranslationController::class, 'download']);
```

---

## 4. Frontend (Vue)

- Créer un composant `PDFTranslationPanel.vue` qui :
  - Gère drag&drop ou sélection de fichier
  - Affiche progression d'upload et de traduction
  - Envoie `FormData` vers `/api/pdf/upload` avec `file` et `target`
  - Affiche le texte traduit et propose un bouton `Télécharger` (GET `/api/pdf/download`)

- Exemple minimal d'envoi :

```ts
const fd = new FormData();
fd.append('file', file);
fd.append('target', targetCode);
const res = await axios.post('/api/pdf/upload', fd, {
  headers: { 'Content-Type': 'multipart/form-data' },
  onUploadProgress: (e) => { /* progress */ }
});
```

---

## 5. Optimisations (performances)

- **Parallélisation** : traduire N chunks en parallèle. Limiter à 5–10 requêtes simultanées pour éviter throttling.
- **Taille des chunks** : augmenter de 500 → 800–1000 pour réduire le nombre d'appels.
- **Caching** : utiliser Redis ou filesystem pour stocker la traduction de chunks (clé = hash(chunk + target)).
- **API de traduction** : MyMemory est gratuite mais limitée. Pour production, envisager Google/DeepL.

---

## 6. Qualité d'affichage (Unicode)

- `FPDF` ne gère pas l'UTF-8 nativement ; pour les langues non-latines, utilisez `TCPDF` ou `mPDF`.
- Exemple rapide avec `TCPDF` :

```php
$pdf = new \TCPDF();
$pdf->AddPage();
$pdf->SetFont('dejavusans', '', 12); // police Unicode
$pdf->Write(0, $translatedText);
$pdf->Output('translated.pdf', 'D');
```

---

## 7. OCR (optionnel pour PDFs scannés)

- Intégrer Tesseract (via une dépendance PHP ou exécutable système) pour extraire le texte des images avant traduction.
- Workflow : détecter si `smalot/pdfparser` retourne peu/no text → lancer OCR → continuer le pipeline.

---

## 8. Sécurité & production

- Activer la vérification SSL pour les requêtes externes (Guzzle `verify: true`).
- Ajouter validation supplémentaire côté serveur (MIME sniffing si nécessaire).
- Mettre en place des limites utilisateur (taille, fréquence) pour éviter l'abus.

---

## 9. Tests & débogage

- Tester localement avec un petit PDF (1–3 pages). Mesurer `char_count` et `page_count` renvoyés.
- Vérifier les logs Laravel pour erreurs réseau ou réponses non JSON.

---

## 10. Réactiver l'UI

Si tu as commenté/retiré l'import et l'affichage dans `resources/js/pages/Traducteur.vue`, réactive en :

- Décommenter `import PDFTranslationPanel from '@/components/PDFTranslationPanel.vue';`
- Décommenter la balise `<PDFTranslationPanel :languages="languages" />`

---

## 11. Restauration / suppression

- Si tu veux **supprimer** définitivement le contrôleur et le composant, supprime :
  - `app/Http/Controllers/PDFTranslationController.php`
  - `resources/js/components/PDFTranslationPanel.vue`
  - Les routes `/api/pdf/*` dans `routes/api.php`

- Si tu veux **restaurer** le code supprimé, récupère les fichiers depuis Git (`git checkout -- path/to/file`) si le code était sous contrôle de version.

---

Si tu veux, j'implémente maintenant une des améliorations suivantes :
- Remplacer FPDF par TCPDF pour support Unicode (générer PDF UTF-8)
- Ajouter caching pour éviter retraductions de chunks identiques
- Recréer le contrôleur et le composant à partir d'un squelette testé

Dis laquelle tu veux prioriser et je la fais.
