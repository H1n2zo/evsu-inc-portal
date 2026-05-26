<?php
// core/View.php
// Presentation Layer bridge — renders HTML templates with injected data
// OOP Concepts:
//   - Encapsulation: rendering and escaping logic are self-contained
//   - NO static properties or methods

require_once __DIR__ . '/../config/app.php';

class View
{
    private string $basePath;

    public function __construct()
    {
        $this->basePath = __DIR__ . '/../../frontend/views';
    }

    /**
     * Render a view template with the given data.
     * Variables in $data are extracted into the template scope.
     * Templates must contain NO business logic, DB queries, or auth checks.
     */
    public function render(string $template, array $data = []): void
    {
        $templatePath = $this->basePath . '/' . ltrim($template, '/') . '.php';

        if (!file_exists($templatePath)) {
            throw new RuntimeException("View template not found: {$templatePath}");
        }

        extract($data, EXTR_SKIP);
        $view = $this;   // make $view available inside every template
        require $templatePath;
    }

    /**
     * Include a layout partial (head, sidebars, etc.)
     */
    public function partial(string $partial, array $data = []): void
    {
        $partialPath = $this->basePath . '/' . ltrim($partial, '/') . '.php';

        if (!file_exists($partialPath)) {
            throw new RuntimeException("View partial not found: {$partialPath}");
        }

        extract($data, EXTR_SKIP);
        $view = $this;
        require $partialPath;
    }

    /** HTML-escape a value */
    public function e(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /** Build a full URL using the app base */
    public function url(string $path = ''): string
    {
        return url($path);
    }

    /** Build a URL to a frontend asset (css, uploads, images) */
    public function asset(string $path = ''): string
    {
        return asset($path);
    }

    /** Format a date string */
    public function date(string $dateStr, string $format = 'M d, Y'): string
    {
        return date($format, strtotime($dateStr));
    }

    /** Status badge CSS class */
    public function statusBadge(string $status): string
    {
        return match($status) {
            'in_progress'     => 'badge-info',
            'pending_payment' => 'badge-gold',
            'verification'    => 'badge-gold',
            'resolved'        => 'badge-success',
            'rejected'        => 'badge-danger',
            'active'          => 'badge-success',
            'pending'         => 'badge-gold',
            'disabled'        => 'badge-gray',
            default           => 'badge-gray',
        };
    }

    /** Human-readable status label */
    public function statusLabel(string $status): string
    {
        return match($status) {
            'in_progress'     => 'In Progress',
            'pending_payment' => 'Pending Payment',
            'verification'    => 'Verification',
            'resolved'        => 'Resolved',
            'rejected'        => 'Rejected',
            default           => ucfirst(str_replace('_', ' ', $status)),
        };
    }
}
