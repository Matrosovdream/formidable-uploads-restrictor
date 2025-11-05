<?php
if ( ! defined('ABSPATH') ) { exit; }

use FRU\Options;

final class ProtectedFormidableUploads {
    public static function init(): void {
        add_action('template_redirect', [__CLASS__, 'maybe_intercept_formidable_file']);
    }

    public static function maybe_intercept_formidable_file(): void {
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($request_uri, '/wp-content/uploads/formidable/') === false) return;

        $abs_path = ABSPATH . ltrim($request_uri, '/');
        $abs_path = strtok($abs_path, '?');
        if (!file_exists($abs_path)) {
            status_header(404);
            exit('File not found.');
        }

        if (!self::current_user_allowed()) {
            status_header(403);
            exit('Access denied.');
        }

        //self::serve_file($abs_path);
    }

    private static function serve_file(string $path): void {
        $mime = wp_check_filetype($path);
        $type = $mime['type'] ?: 'application/octet-stream';
        header('Content-Type: ' . $type);
        header('Content-Length: ' . filesize($path));
        header('Content-Disposition: inline; filename="' . basename($path) . '"');
        readfile($path);
        exit;
    }

    private static function current_user_allowed(): bool {
        if (!is_user_logged_in()) return false;
        $user = wp_get_current_user();
        $roles = $user->roles ?? [];
        $allowed = Options::get_allowed_roles();
        return (bool) array_intersect($roles, $allowed);
    }
}
