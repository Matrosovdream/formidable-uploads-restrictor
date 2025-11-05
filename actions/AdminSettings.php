<?php
if ( ! defined('ABSPATH') ) { exit; }

use FRU\Options;

final class AdminSettings {
    private const NONCE = 'fru_restrict_nonce';
    private const SUBMENU_SLUG = 'fru-restrict-entry-files';

    public static function boot(): void {
        add_action('admin_menu', [__CLASS__, 'add_submenu'], 99);
        add_action('admin_post_fru_save_restrict_settings', [__CLASS__, 'handle_post']);
    }

    public static function add_submenu(): void {
        if ( current_user_can('manage_options') ) {
            add_submenu_page(
                'formidable',
                __('Restrict entry files', 'fru'),
                __('Restrict entry files', 'fru'),
                'manage_options',
                self::SUBMENU_SLUG,
                [__CLASS__, 'render_page']
            );
        }
    }

    public static function render_page(): void {
        if ( ! current_user_can('manage_options') ) {
            wp_die(__('Unauthorized', 'fru'));
        }
        $opts = Options::get_all();
        $roles = wp_roles()->roles;
        $allowed = $opts['allowed_roles'] ?? ['administrator'];
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Restrict entry files', 'fru'); ?></h1>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field(self::NONCE, self::NONCE); ?>
                <input type="hidden" name="action" value="fru_save_restrict_settings" />
                <table class="form-table">
                    <tr>
                        <th><label><?php esc_html_e('Choose allowed user groups', 'fru'); ?></label></th>
                        <td>
                            <select name="allowed_roles[]" multiple size="6" style="min-width:340px;">
                                <?php foreach ($roles as $key => $role): ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php selected(in_array($key, $allowed, true)); ?>>
                                        <?php echo esc_html($role['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php esc_html_e('Users with these roles can access Formidable uploaded files.', 'fru'); ?></p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(__('Save Changes', 'fru')); ?>
            </form>
        </div>
        <?php
    }

    public static function handle_post(): void {
        if ( ! current_user_can('manage_options') ) {
            wp_die(__('Unauthorized', 'fru'));
        }
        check_admin_referer(self::NONCE, self::NONCE);
        $allowed_roles = isset($_POST['allowed_roles']) ? (array) $_POST['allowed_roles'] : [];
        Options::save(['allowed_roles' => $allowed_roles]);
        wp_safe_redirect(add_query_arg(['page' => self::SUBMENU_SLUG, 'updated' => '1'], admin_url('admin.php')));
        exit;
    }
}
