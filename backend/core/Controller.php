<?php
// core/Controller.php
// Logic Layer — base controller providing session init, View injection, and shared helpers
// OOP Concepts:
//   - Encapsulation: session logic is private
//   - Inheritance: all page controllers extend this
//   - Composition: View injected via constructor
//   - NO static properties or methods

require_once __DIR__ . '/View.php';
require_once __DIR__ . '/../config/app.php';

abstract class Controller
{
    protected View $view;

    public function __construct()
    {
        $this->startSession();
        $this->view = new View();
    }

    // Private: only this class manages session startup
    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Strict',
            ]);
            session_start();
        }
    }

    // Redirect using the url() helper so paths work on localhost subfolders
    protected function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }

    protected function h(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    protected function getSession(string $key): mixed
    {
        return $_SESSION[$key] ?? null;
    }

    protected function setSession(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Render a view template with the provided data.
     * This is the ONLY way controllers produce HTML — no bare includes or echo.
     */
    protected function render(string $template, array $data = []): void
    {
        $this->view->render($template, $data);
    }
}
