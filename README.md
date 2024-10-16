
# HazelRouter - Anleitung zur Verwendung

Der **HazelRouter** ist ein einfacher, anpassbarer PHP-Router mit Unterstützung für Middleware, dynamische Routenerstellung und automatischer XML-Sitemap-Generierung.

## Installation

Du kannst den HazelRouter ganz einfach über Composer installieren:

```bash
composer require jp-codeplus/hazel-router
```

## Voraussetzungen

Stelle sicher, dass du folgende Dateien in deinem Projekt hast:
- `HazelRouter.php` (die Router-Klasse)
- `DemoController.php` (zum Testen der Controller)
- `DemoMiddleware.php` (zum Testen der Middleware)
- `routes.php` (eine Datei mit den definierten Routen)

## Schritt-für-Schritt-Anleitung

### 1. Router initialisieren

Zuerst müssen wir den Router initialisieren und die notwendigen Dateien einbinden:

```php
require __DIR__ . '/../src/HazelRouter.php';
require __DIR__ . '/DemoController.php';
require __DIR__ . '/DemoMiddleware.php';

// Basic Router-Initialisierung
$router = new JayPiii\HazelRouter();
$routerPath = __DIR__ . '/routes.php'; // ==> DEMO ROUTES
```

### 2. XML-Sitemap automatisch erstellen

Du kannst eine Sitemap einfach durch Aufruf der `createSitemap`-Methode erstellen. Gib die gewünschte URI und die Domain an:

```php
$router->createSitemap('/sitemap.xml', 'http://hazel-router.test');
```

Dies erstellt eine Route, unter der die XML-Sitemap abgerufen werden kann.

### 3. Routen laden

Lade die Routen aus einer externen PHP-Datei. Die Datei sollte ein Array mit Routen zurückgeben:

```php
$router->loadRoutes($routerPath);
```

### 4. Middleware hinzufügen

Um Middleware hinzuzufügen, kannst du die `middleware`-Methode verwenden. Diese Methode unterstützt sowohl Closures als auch Klassenmethoden:

```php
$router->middleware('myMiddleware', [DemoMiddleware::class, 'index']);
```

### 5. Router ausführen

Rufe die Methode `run()` auf, um den Router auszuführen. Diese Methode überprüft die aktuelle Anfrage und führt die entsprechende Route aus:

```php
$router->run();
```

### 6. Fehler anzeigen

Falls während der Verarbeitung Fehler auftreten, kannst du diese mit `displayErrors()` ausgeben:

```php
if ($router->displayErrors() !== null) {
    echo $router->displayErrors();
}
```

## Beispiel-Code

Hier ist der vollständige Beispiel-Code für die Verwendung des HazelRouters:

```php
require __DIR__ . '/../src/HazelRouter.php';
require __DIR__ . '/DemoController.php';
require __DIR__ . '/DemoMiddleware.php';

// Basic Router
$router = new JayPiii\HazelRouter();
$routerPath = __DIR__ . '/routes.php'; // ==> DEMO ROUTES
$router->createSitemap('/sitemap.xml', 'http://hazel-router.test');
$router->loadRoutes($routerPath);
$router->middleware('myMiddleware', [DemoMiddleware::class, 'index']);
$router->run();

if ($router->displayErrors() !== null) {
    echo $router->displayErrors();
}
```

## Beispielhafte `routes.php`

Die Datei `routes.php` sollte ein Array mit Routen zurückgeben, die wie folgt definiert sind:

```php
return [
    [
        'uri' => '/',
        'action' => [DemoController::class, 'index'],
        'method' => 'GET',
        'middleware' => ['myMiddleware'],
        'sitemap' => true,
        'visibility' => 'live'
    ],
    [
        'uri' => '/hello',
        'action' => [DemoController::class, 'hello'],
        'method' => 'GET',
        'middleware' => [],
        'sitemap' => false,
        'visibility' => 'staging'
    ],
    [
        'uri' => '/mellow',
        'action' => [DemoController::class, 'mellow'],
        'method' => 'GET',
        'middleware' => [],
        'sitemap' => true,
        'visibility' => 'live'
    ],
];
```

### Viel Spaß beim Programmieren mit dem **HazelRouter**!
