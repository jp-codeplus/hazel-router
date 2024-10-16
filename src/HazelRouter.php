<?php

/**
 * HazelRouter
 * 
 * Ein einfacher, anpassbarer Router für PHP-Anwendungen mit Middleware-Unterstützung und 
 * dynamischer Routenerstellung. Dieser Code wurde von Jan Behrens erstellt.
 * 
 * @author Jan Behrens
 */

namespace JayPiii;

class HazelRouter
{
    protected array $routes = [];                    // Routenarray zur Speicherung aller registrierten Routen
    protected array $middleware = [];                // Middleware-Array zur Speicherung von Middleware-Handlern
    protected array $middlewareErrors = [];          // Array zur Speicherung von Middleware-Fehlern
    protected string $sitemapDomain;                 // Domain für Sitemap
    protected string $sitemapRoute;                  // Pfad zur Sitemap
    protected array $errors = [];                    // Fehlerarray zur Speicherung von allgemeinen Fehlern
    protected array $currentRoute = [];              // Aktuelle Route


    /**
     * Fügt eine Route hinzu, standardmäßig mit der GET-Methode.
     * 
     * @param string $uri Die URI der Route.
     * @param callable|array $action Die Action als callable oder [Class::class, 'method'].
     * @param string $method Die HTTP-Methode (Standard: GET).
     * @param array $middleware Eine Liste von Middleware.
     * @return self Gibt die aktuelle Instanz für Fluent Interface zurück.
     */

    public function route(string $uri, callable|array $action, string $method = 'GET', array $middleware = [], array $attributes = []): self
    {
        // Überprüfen, ob die Aktion als [Class::class, 'method'] angegeben ist
        if (is_array($action) && is_string($action[0])) {
            $action = $this->resolveActionClassMethod($action);
            if ($action === null) {
                return $this; // Abbrechen, wenn die Methode nicht existiert, Fehler wurde gespeichert
            }
        }

        // Aktion muss aufrufbar sein, ansonsten Fehler speichern
        if (!is_callable($action)) {
            $this->errors[] = 'The action provided is not callable.';
            return $this;
        }

        // URI in ein reguläres Ausdrucksmuster umwandeln
        $uriPattern = $this->convertUriToPattern($uri);

        // Route registrieren und Attribute wie 'visibility' hinzufügen
        $this->routes[strtoupper($method)][$uriPattern] = array_merge([
            'action' => $action,
            'middleware' => $middleware,
            'sitemap' => false,
            'uri' => $uri
        ], $attributes);

        return $this;
    }


    /**
     * Auflösen von [Class::class, 'method'] zu einer Instanz oder statischen Methode.
     * 
     * @param array $action Das Action-Array.
     * @return callable|null Gibt das aufrufbare Action zurück oder null, wenn es nicht aufrufbar ist.
     */
    protected function resolveActionClassMethod(array $action): ?callable
    {
        [$class, $method] = $action;

        // Überprüfen, ob die Methode in der Klasse existiert
        if (!method_exists($class, $method)) {
            $this->errors[] = "Method $method does not exist in class $class.";
            return null;
        }

        // Überprüfen, ob die Methode statisch ist
        $reflectionMethod = new \ReflectionMethod($class, $method);
        if (!$reflectionMethod->isStatic()) {
            $action[0] = new $class(); // Instanziiere die Klasse, wenn die Methode nicht statisch ist
        }

        return $action;
    }

    /**
     * Konvertiert eine URI mit Platzhaltern (z.B. {id}) zu einem regulären Ausdruck.
     * 
     * @param string $uri Die URI der Route.
     * @return string Die URI als regulärer Ausdruck.
     */
    protected function convertUriToPattern(string $uri): string
    {
        return preg_replace('/{([^\/]+)}/', '([^\/]+)', $uri);
    }

    /**
     * Fügt Middleware hinzu.
     * 
     * @param string $name Der Name der Middleware.
     * @param callable|array $handler Der Middleware-Handler.
     * @return self Gibt die aktuelle Instanz für Fluent Interface zurück.
     */
    public function middleware(string $name, callable|array $handler): self
    {
        // Überprüfen, ob die Middleware als [Class::class, 'method'] angegeben ist
        if (is_array($handler) && is_string($handler[0])) {
            $handler = $this->resolveMiddlewareClassMethod($handler);
            if ($handler === null) {
                $this->middlewareErrors[] = "Middleware handler '$name' could not be resolved.";
                return $this; // Abbrechen, wenn die Methode nicht existiert
            }
        }

        $this->middleware[$name] = $handler;
        return $this;
    }

    /**
     * Auflösen von [Class::class, 'method'] zu einer Instanz oder statischen Methode.
     * 
     * @param array $middleware Das Middleware-Array.
     * @return callable|null Gibt das aufrufbare Middleware zurück oder null, wenn es nicht aufrufbar ist.
     */
    protected function resolveMiddlewareClassMethod(array $middleware): ?callable
    {
        [$class, $method] = $middleware;

        // Überprüfen, ob die Methode in der Klasse existiert
        if (!method_exists($class, $method)) {
            $this->errors[] = "Method $method does not exist in class $class.";
            return null;
        }

        // Überprüfen, ob die Methode statisch ist
        $reflectionMethod = new \ReflectionMethod($class, $method);
        if (!$reflectionMethod->isStatic()) {
            $middleware[0] = new $class(); // Instanziiere die Klasse, wenn die Methode nicht statisch ist
        }

        return $middleware;
    }


    /**
     * Lädt Routen aus einer PHP-Datei und fügt sie zum Router hinzu.
     * 
     * @param string $phpFile Der Pfad zur PHP-Datei, die die Routen zurückgibt.
     * @throws InvalidArgumentException Wenn eine Route keine "uri" oder "action" enthält.
     * @return void
     */
    public function loadRoutes(string $phpFile): void
    {
        $routes = require $phpFile; // Routen aus Datei laden

        foreach ($routes as $route) {
            $route = $this->validateAndNormalizeRoute($route); // Route validieren

            // Route hinzufügen, und die zusätzlichen Attribute wie 'visibility' übergeben
            $this->route(
                uri: $route['uri'],
                action: $route['action'],
                method: $route['method'],
                middleware: $route['middleware'],
                attributes: [
                    'sitemap' => $route['sitemap'],
                    'visibility' => $route['visibility'] // Hier wird 'visibility' hinzugefügt
                ]
            );
        }
    }


    /**
     * Validiert und normalisiert eine Route.
     * 
     * @param array $route Die zu validierende Route.
     * @return array Die validierte und normalisierte Route.
     * @throws InvalidArgumentException Wenn eine Route keine "uri" oder "action" enthält.
     */
    protected function validateAndNormalizeRoute(array $route): array
    {
        if (!isset($route['uri'])) {
            $this->errors[] = 'The route does not contain a "uri" key.';
            return [];
        }

        if (!isset($route['action'])) {
            $this->errors[] = 'The route does not contain an "action" key.';
            return [];
        }

        // Standardwerte setzen
        return array_merge([
            'method' => 'GET',
            'middleware' => [],
            'sitemap' => false
        ], $route);
    }

    /**
     * Fügt eine Route zur Sitemap hinzu.
     * 
     * @param string $route Die URI der Route.
     * @param bool $sitemap Ob die Route zur Sitemap hinzugefügt werden soll.
     * @return void
     */
    public function setSitemap(string $route, bool $sitemap): void
    {
        foreach ($this->routes as $method => &$routes) {
            foreach ($routes as &$routeData) {
                if ($routeData['uri'] === $route) {
                    $routeData['sitemap'] = $sitemap; // Sitemap-Einstellung setzen
                    return;
                }
            }
        }

        $this->errors[] = "Route '$route' not found."; // Route nicht gefunden
    }

    /**
     * Führt den Router aus, vergleicht die URI und die Methode mit den registrierten Routen.
     * 
     * @return void
     */
    public function run(): void
    {
        $requestedUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        // Überprüfen, ob die Methode registriert ist
        if (!$this->isMethodRegistered($requestMethod)) {
            $this->sendNotFoundResponse();
            return;
        }

        // Route abgleichen und ausführen
        if (!$this->matchRoute($requestedUri, $requestMethod)) {
            $this->sendNotFoundResponse();
        }
    }

    /**
     * Überprüft, ob eine Methode registriert ist.
     * 
     * @param string $method Die HTTP-Methode.
     * @return bool True, wenn die Methode registriert ist, sonst false.
     */
    protected function isMethodRegistered(string $method): bool
    {
        return isset($this->routes[$method]);
    }

    /**
     * Überprüft, ob eine Route mit der angegebenen URI übereinstimmt.
     * 
     * @param string $requestedUri Die angeforderte URI.
     * @param string $method Die HTTP-Methode.
     * @return bool True, wenn eine Route übereinstimmt, sonst false.
     */
    protected function matchRoute(string $requestedUri, string $method): bool
    {
        foreach ($this->routes[$method] as $routePattern => $routeData) {
            $pattern = '#^' . $routePattern . '$#';

            if (preg_match($pattern, $requestedUri, $matches)) {
                array_shift($matches); // Entferne den vollständigen URI-Treffer

                $this->currentRoute = $routeData; // Speichere die aktuelle Route

                // Middleware ausführen und Route-Daten übergeben
                $this->handleMiddleware(array_merge($routeData['middleware'], $this->getMiddlewareForRoute($routePattern)));

                // Überprüfen, ob die Aktion aufrufbar ist
                if (!isset($routeData['action']) || !is_callable($routeData['action'])) {
                    $this->errors[] = 'The action is not callable for route: ' . $routePattern;
                    return true; // Route gefunden, aber Aktion ist nicht aufrufbar
                }

                // Route ausführen und Parameter übergeben
                $routeData['action'](...$matches);
                return true;
            }
        }

        return false;
    }


    /**
     * Sendet eine 404-Fehlerantwort, wenn keine Route gefunden wurde.
     * 
     * @return void
     */
    protected function sendNotFoundResponse(): void
    {
        http_response_code(404);
        echo '404 Not Found';
    }

    /**
     * Führt Middleware aus.
     * 
     * @param array $middlewares Die Middleware-Liste.
     * @return void
     */
    protected function handleMiddleware(array $middlewares): void
    {
        foreach ($middlewares as $middleware) {
            if (array_key_exists($middleware, $this->middleware)) {
                // Middleware mit aktuellem Routendaten ausführen
                call_user_func($this->middleware[$middleware], $this->currentRoute);
            } else {
                $this->middlewareErrors[] = "Middleware '$middleware' not found.";
            }
        }
    }

    /**
     * Bestimmt, welche Middleware für die gegebene Route verwendet werden soll.
     * 
     * @param string $routePattern Das URI-Muster der Route.
     * @return array Die Liste der zu verwendenden Middleware.
     */
    protected function getMiddlewareForRoute(string $routePattern): array
    {
        return [];
    }


    /**
     * Erstellt die Sitemap und fügt automatisch eine Route hinzu, die die Sitemap bereitstellt.
     * 
     * @param string $sitemapUri Die URI, unter der die Sitemap angezeigt werden soll.
     * @param string $domain Die Domain, unter der die Sitemap erstellt wird.
     * @return void
     */
    public function createSitemap(string $sitemapUri, string $domain): void
    {
        $this->sitemapRoute = $sitemapUri;     // Setzt die Sitemap-URI
        $this->sitemapDomain = rtrim($domain, '/'); // Setzt die Domain (ohne abschließenden Slash)

        // Füge eine Route hinzu, um die Sitemap anzuzeigen
        $this->route($sitemapUri, function () {
            header('Content-Type: application/xml; charset=utf-8');
            echo $this->generateSitemapXml();
            exit(); // Skript nach der Sitemap-Ausgabe beenden
        }, 'GET');
    }

    /**
     * Gibt die Sitemap im XML-Format zurück.
     * 
     * @return string Die generierte Sitemap im XML-Format.
     */
    public function generateSitemapXml(): string
    {
        $xml = "<?xml version='1.0' encoding='UTF-8'?>\n";
        $xml .= "<urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'>\n";
    
        foreach ($this->routes as $method => $routes) {
            if (strtoupper($method) === 'GET') {
                foreach ($routes as $uri => $routeData) {
                    if ($routeData['sitemap']) {
                        // Sicherstellen, dass kein doppeltes Präfix entsteht
                        $url = $this->sitemapDomain . (strpos($routeData['uri'], '/') === 0 ? '' : '/') . $routeData['uri'];
    
                        $xml .= "    <url>\n";
                        $xml .= "        <loc>" . htmlspecialchars($url) . "</loc>\n";
                        $xml .= "    </url>\n";
                    }
                }
            }
        }
    
        $xml .= "</urlset>";
        return $xml;
    }
    


    /**
     * Gibt die gesammelten Fehler aus.
     * 
     * @return string|null Die Fehlerliste im HTML-Format oder null, wenn keine Fehler vorhanden sind.
     */
    public function displayErrors(): ?string
    {
        $allErrors = array_merge($this->errors, $this->middlewareErrors); // Alle Fehler kombinieren

        if (empty($allErrors)) return null;

        $errorItems = array_map(fn($error) => '<li>' . htmlspecialchars($error) . '</li>', $allErrors);
        return '<h2>Errors:</h2><ul>' . implode('', $errorItems) . '</ul>';
    }
}
