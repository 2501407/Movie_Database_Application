<?php
// ─── Shared Helper Functions

/**
 * Generate a CSRF token and store it in the session.
 * Call once per page-load; the token is reused within the same request.
 */
function csrfToken(): string {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate the CSRF token submitted in a POST request.
 * Returns true if valid, false otherwise.
 */
function validateCsrf(): bool {
    return isset($_POST['csrf_token'])
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

/**
 * XSS-safe output helper.  Use h() before printing user data.
 */
function h(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Hidden CSRF input element — drop inside any <form> that uses POST.
 */
function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . h(csrfToken()) . '">';
}

/**
 * Simple pagination helper.
 * Returns an array with: total_pages, current_page, offset, limit.
 */
function paginate(int $totalRecords, int $perPage = 10): array {
    $currentPage = max(1, (int)($_GET['page'] ?? 1));
    $totalPages  = max(1, (int)ceil($totalRecords / $perPage));
    $currentPage = min($currentPage, $totalPages);   // clamp
    $offset      = ($currentPage - 1) * $perPage;

    return [
        'total_pages'  => $totalPages,
        'current_page' => $currentPage,
        'offset'       => $offset,
        'limit'        => $perPage,
        'total'        => $totalRecords,
    ];
}
?>
