<?php
namespace FRU;

if ( ! defined('ABSPATH') ) { exit; }

final class Options {
    public const OPTION_KEY = 'fru_restrict_settings';

    public static function init(): void {
        $opt = get_option(self::OPTION_KEY);
        if ( ! is_array($opt) ) {
            $opt = self::defaults();
            add_option(self::OPTION_KEY, $opt, '', false);
        } else {
            $def = self::defaults();
            $opt = array_merge($def, $opt);
            update_option(self::OPTION_KEY, $opt, false);
        }
    }

    public static function defaults(): array {
        return ['allowed_roles' => ['administrator']];
    }

    public static function get_all(): array {
        $opt = get_option(self::OPTION_KEY);
        return is_array($opt) ? array_merge(self::defaults(), $opt) : self::defaults();
    }

    public static function get_allowed_roles(): array {
        $opt = self::get_all();
        $roles = $opt['allowed_roles'] ?? [];
        return is_array($roles) ? array_values(array_filter($roles, 'is_string')) : ['administrator'];
    }

    public static function save(array $data): void {
        $saved = self::get_all();
        $roles = $data['allowed_roles'] ?? [];
        $roles = is_array($roles) ? array_map('sanitize_text_field', $roles) : [];
        $roles = array_values(array_unique(array_filter($roles)));
        if (empty($roles)) {
            $roles = ['administrator'];
        }
        $saved['allowed_roles'] = $roles;
        update_option(self::OPTION_KEY, $saved, false);
    }
}
