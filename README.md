
# HazelRouter

**Version 0.1.0**  
Ein leichter und flexibler Router für PHP-Anwendungen, der Middleware-Unterstützung und Sitemap-Generierung bietet. Entwickelt und programmiert von **Jan Behrens**.

## Übersicht

Der **HazelRouter** ermöglicht es, HTTP-Routen in einer PHP-Anwendung einfach zu registrieren, Middleware hinzuzufügen und dynamische URIs zu verwenden. Darüber hinaus kann eine XML-Sitemap basierend auf den registrierten GET-Routen generiert werden.

## Features

- **Routenregistrierung**: Unterstützt GET, POST und andere HTTP-Methoden mit dynamischen Platzhaltern (z.B. `/users/{id}`).
- **Middleware**: Füge Middleware für bestimmte Routen hinzu, um Funktionen wie Authentifizierung oder andere Vorverarbeitungen zu ermöglichen.
- **Sitemap-Generierung**: Generiert eine XML-Sitemap basierend auf den registrierten Routen.
- **Fehlerbehandlung**: Zeichnet Fehler in Routen- und Middleware-Prozessen auf und ermöglicht eine einfache Ausgabe.
- **Aufrufbare Aktionen**: Unterstützt sowohl Callables als auch Methoden innerhalb von Klassen (mit automatischer Klasseninstanziierung).

## Installation

1. **Download oder Clone** dieses Repositories:

```bash
git clone https://github.com/dein-repo/hazel-router.git
```

2. **Einbinden in dein Projekt**: Lade die `HazelRouter`-Klasse in dein Projektverzeichnis und inkludierte sie in deiner `index.php`:

```php
require_once 'path/to/HazelRouter.php';
```

3. **Route hinzufügen**:

```php
$router = new HazelRouter();

$router->route('/users/{id}', [UserController::class, 'show'], 'GET');
$router->run();
```

4. **Middleware hinzufügen**:

```php
$router->middleware('auth', function() {
    if (!isset($_SESSION['user'])) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
});
```

5. **Sitemap generieren**:

```php
echo $router->sitemap();
```

6. **Fehler anzeigen** (optional):

```php
echo $router->displayErrors();
```

## Anforderungen

- PHP 7.4 oder höher

## Lizenz

Dieses Projekt ist unter der MIT-Lizenz lizenziert. Siehe die `LICENSE`-Datei für weitere Informationen.