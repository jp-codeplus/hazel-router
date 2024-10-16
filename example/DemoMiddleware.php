<?php

class DemoMiddleware{
    public function index($route)
    {
        echo '<h2>🥳 Hazel-Router Demo </h2>';
        echo '<p>Visibility: ' . htmlspecialchars($route['visibility']) . '</p>';
    }
}