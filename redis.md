# Redis — Mise en place du cache pour les traductions

Ce document explique comment installer et configurer Redis pour être utilisé comme cache dans ce projet Laravel, ainsi que les commandes de test et bonnes pratiques. Le contrôleur de traduction (`TranslationController`) a été modifié pour utiliser la façade `Cache` de Laravel (clé basée sur `md5(text|source|target)` et TTL par défaut 7 jours).

Fichiers modifiés / impacts
- `app/Http/Controllers/TranslationController.php` : ajout de l'utilisation de `Cache::remember(...)` pour stocker les traductions.
- Aucune autre dépendance PHP requise si vous utilisez l'extension `phpredis`. Si vous préférez `predis`, installez-le.

1) Installer Redis (Windows / WSL / Linux)

- Windows (recommandé : WSL2 + apt)
  - Installer WSL2 et Ubuntu depuis le Microsoft Store.
  - Dans WSL : `sudo apt update; sudo apt install redis-server`
  - Lancer Redis : `sudo service redis-server start`

- Linux (Debian/Ubuntu)
  - `sudo apt update; sudo apt install redis-server`
  - `sudo systemctl enable --now redis-server`

- macOS (Homebrew)
  - `brew install redis`
  - `brew services start redis`

2) PHP Redis client

Option A (recommandée) : extension `phpredis` (C) — performante
- Sous Windows/Unix, installez l'extension `phpredis` (procédure dépend de PHP/OS). Vérifiez `php -m | grep redis`.

Option B (predis, pure PHP) : plus simple à installer mais légèrement plus lent
- Installer via composer :

```bash
composer require predis/predis
```

3) Configuration Laravel

- Dans `.env` :

```
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

- Dans `config/database.php` assurez-vous que la section `redis` est correcte (par défaut Laravel est prêt).

4) Tester le cache

- Ouvrir Tinker :

```bash
php artisan tinker
>>> Cache::put('test-key','hello', 60);
>>> Cache::get('test-key');
=> "hello"
```

- Tester l'endpoint de traduction (exemple) :

```powershell
curl -X POST "http://localhost/api/translation/translate" -H "Content-Type: application/json" -d '{"q":"Bonjour","source":"fr","target":"en"}'
```

La première requête doit appeler l'API externe et créer une entrée cache. La seconde requête identique devrait retourner rapidement et inclure le champ `cached: true` dans la réponse JSON.

5) Bonnes pratiques

- TTL : 7 jours par défaut; ajuster selon besoins.
- Nettoyage : `php artisan cache:clear` pour purger le cache lors des tests.
- Sécurité : n'enregistrez pas de contenus sensibles en clair dans le cache. Si nécessaire, chiffrez avant stockage.
- Monitoring : surveillez la mémoire si vous utilisez Redis en RAM (out-of-memory possible si taille grande et TTL long).

6) Rollback

- Si vous retirez Redis, mettez `CACHE_DRIVER=file` dans `.env` et purgez la config cache (`php artisan config:clear`).

Si tu veux, je peux :
- Ajouter une route de test pour vérifier hits/misses depuis le navigateur
- Implémenter l'extension du cache aux chunks PDF si tu veux réactiver la fonctionnalité PDF

