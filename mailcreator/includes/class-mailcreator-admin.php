<?php

defined('ABSPATH') || exit;

final class MailCreator_Admin
{
    public static function register(): void
    {
        add_action('admin_menu', [self::class, 'add_menu_page']);
        add_action('admin_init', [self::class, 'handle_debug_actions']);
        add_action('admin_init', [self::class, 'handle_draft_actions']);
    }
    
    public static function handle_debug_actions(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Download log
        if (!empty($_GET['mailcreator_download_log'])) {
            check_admin_referer('mailcreator_download');
            $debug_log_path = WP_CONTENT_DIR . '/debug.log';
            if (file_exists($debug_log_path)) {
                header('Content-Type: text/plain');
                header('Content-Disposition: attachment; filename="mailcreator-debug-' . date('Y-m-d-H-i-s') . '.log"');
                header('Content-Length: ' . filesize($debug_log_path));
                readfile($debug_log_path);
                exit;
            }
        }
        
        // Clear log
        if (!empty($_POST['mailcreator_clear_log'])) {
            check_admin_referer('mailcreator_clear_log');
            $debug_log_path = WP_CONTENT_DIR . '/debug.log';
            if (file_exists($debug_log_path)) {
                file_put_contents($debug_log_path, '');
            }
            wp_safe_redirect(add_query_arg(['page' => 'mailcreator-debug', 'mailcreator_cleared' => '1'], admin_url('admin.php')));
            exit;
        }
    }

    public static function handle_draft_actions(): void
    {
        if (empty($_GET['mailcreator_delete_draft'])) {
            return;
        }

        $post_id = absint($_GET['mailcreator_delete_draft']);
        check_admin_referer('mailcreator_delete_draft_' . $post_id);

        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'mailcreator_draft') {
            wp_die(esc_html__('Concept niet gevonden.', 'mailcreator'));
        }
        if (!current_user_can('delete_post', $post_id)) {
            wp_die(esc_html__('Je mag dit concept niet verwijderen.', 'mailcreator'));
        }

        wp_delete_post($post_id, true);

        wp_safe_redirect(add_query_arg(['page' => 'mailcreator-drafts', 'mailcreator_deleted' => '1'], admin_url('admin.php')));
        exit;
    }

    public static function add_menu_page(): void
    {
        add_menu_page(
            'MailCreator',
            'MailCreator',
            'upload_files',
            'mailcreator',
            [self::class, 'render_editor_page'],
            'dashicons-email-alt2',
            30
        );
        add_submenu_page(
            'mailcreator',
            'Nieuwe nieuwsbrief',
            'Nieuwe nieuwsbrief',
            'upload_files',
            'mailcreator',
            [self::class, 'render_editor_page']
        );
        add_submenu_page(
            'mailcreator',
            'Concepten',
            'Concepten',
            'upload_files',
            'mailcreator-drafts',
            [self::class, 'render_drafts_page']
        );
        add_submenu_page(
            'mailcreator',
            'Debug Log',
            'Debug Log',
            'manage_options',
            'mailcreator-debug',
            [self::class, 'render_debug_page']
        );
    }

    private static function editor_url(int $draft_id = 0): string
    {
        return add_query_arg(
            array_filter([
                'mc_rest' => rest_url('mailcreator/v1/media'),
                'mc_drafts' => rest_url('mailcreator/v1/drafts'),
                'mc_draft_load' => rest_url('mailcreator/v1/drafts/' . $draft_id),
                'mc_nonce' => wp_create_nonce('wp_rest'),
                'mc_draft' => $draft_id ?: null,
                // add_query_arg() does not urlencode values; without this, a raw "#" in accent_color truncates the URL at the fragment.
                'mc_settings' => rawurlencode(wp_json_encode(MailCreator_Settings::get_frontend_settings())),
                'mc_v' => MAILCREATOR_VERSION,
            ]),
            plugins_url('app/index.html', MAILCREATOR_ROOT_FILE)
        );
    }

    public static function render_editor_page(): void
    {
        if (!current_user_can('upload_files')) {
            wp_die(esc_html__('Je hebt geen toestemming om MailCreator te gebruiken.', 'mailcreator'));
        }
        $draft_id = absint($_GET['draft_id'] ?? 0);
        $editor_url = self::editor_url($draft_id);
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('MailCreator', 'mailcreator'); ?></h1>
            <?php // Debug-blok (Editor URL / REST API / Nonce) verwijderd uit de UI; zie class-mailcreator-admin.php git-historie om het terug te zetten. ?>
            <iframe
                src="<?php echo esc_url($editor_url); ?>"
                title="MailCreator nieuwsbriefeditor"
                style="display:block;width:100%;min-height:1200px;border:0;background:#fff;"
            ></iframe>
        </div>
        <?php
    }

    public static function render_drafts_page(): void
    {
        if (!current_user_can('upload_files')) {
            wp_die(esc_html__('Je hebt geen toestemming om MailCreator-concepten te bekijken.', 'mailcreator'));
        }
        $drafts = get_posts([
            'post_type' => 'mailcreator_draft',
            'post_status' => 'draft',
            'numberposts' => -1,
            'orderby' => 'modified',
            'order' => 'DESC',
        ]);
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('MailCreator-concepten', 'mailcreator'); ?></h1>
            <?php if (!empty($_GET['mailcreator_deleted'])) : ?>
                <div class="notice notice-success is-dismissible"><p><?php echo esc_html__('Concept verwijderd.', 'mailcreator'); ?></p></div>
            <?php endif; ?>
            <?php if (!$drafts) : ?>
                <p><?php echo esc_html__('Er zijn nog geen opgeslagen concepten.', 'mailcreator'); ?></p>
            <?php else : ?>
                <table class="widefat striped">
                    <thead><tr><th>Titel</th><th>Laatst gewijzigd</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($drafts as $draft) : ?>
                        <?php
                        $delete_url = wp_nonce_url(
                            add_query_arg(['page' => 'mailcreator-drafts', 'mailcreator_delete_draft' => (int) $draft->ID], admin_url('admin.php')),
                            'mailcreator_delete_draft_' . $draft->ID
                        );
                        ?>
                        <tr>
                            <td><?php echo esc_html($draft->post_title); ?></td>
                            <td><?php echo esc_html(get_the_modified_date('d-m-Y H:i', $draft)); ?></td>
                            <td>
                                <a class="button" href="<?php echo esc_url(add_query_arg(['page' => 'mailcreator', 'draft_id' => (int) $draft->ID], admin_url('admin.php'))); ?>">Openen</a>
                                <?php if (current_user_can('delete_post', $draft->ID)) : ?>
                                    <a class="button" style="color:#b32d2e;" href="<?php echo esc_url($delete_url); ?>" onclick="return confirm('<?php echo esc_js(__('Concept definitief verwijderen?', 'mailcreator')); ?>');">Verwijderen</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }

    public static function render_debug_page(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Je hebt geen toestemming om debug logs te bekijken.', 'mailcreator'));
        }

        $debug_log_path = WP_CONTENT_DIR . '/debug.log';
        $has_log = file_exists($debug_log_path);
        $log_content = '';
        
        if ($has_log) {
            $log_content = file_get_contents($debug_log_path);
            // Toon alleen MailCreator logs
            $lines = explode("\n", $log_content);
            $mailcreator_lines = array_filter($lines, function($line) {
                return strpos($line, 'MailCreator') !== false;
            });
            $log_content = implode("\n", $mailcreator_lines);
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('MailCreator Debug Log', 'mailcreator'); ?></h1>

            <?php if (!empty($_GET['mailcreator_cleared'])) : ?>
                <div class="notice notice-success is-dismissible"><p><?php echo esc_html__('Debug log geleegd.', 'mailcreator'); ?></p></div>
            <?php endif; ?>
            
            <?php if (!$has_log) : ?>
                <p style="color:#d63638;">
                    <?php echo esc_html__('Debug log niet gevonden. Zet WP_DEBUG in wp-config.php in:', 'mailcreator'); ?><br>
                    <code>define('WP_DEBUG', true);<br>define('WP_DEBUG_LOG', true);<br>define('WP_DEBUG_DISPLAY', false);</code>
                </p>
            <?php else : ?>
                <div style="background:#f5f5f5;padding:15px;border:1px solid #ddd;border-radius:4px;margin:15px 0;">
                    <div style="margin-bottom:15px;">
                        <button type="button" class="button button-primary" onclick="document.querySelector('textarea[readonly]').select(); document.execCommand('copy'); alert('Log gekopieerd naar klembord!');">
                            📋 Kopiëren naar klembord
                        </button>
                        <a href="<?php echo esc_url(wp_nonce_url(add_query_arg('mailcreator_download_log', '1'), 'mailcreator_download')); ?>" class="button">
                            ⬇️ Downloaden
                        </a>
                        <form method="post" style="display:inline-block;margin:0;">
                            <?php wp_nonce_field('mailcreator_clear_log'); ?>
                            <button type="submit" name="mailcreator_clear_log" value="1" class="button" onclick="return confirm('<?php echo esc_js(__('Weet je zeker dat je het volledige debug log wilt leegmaken?', 'mailcreator')); ?>');">
                                🗑️ Log leegmaken
                            </button>
                        </form>
                    </div>
                    
                    <h3><?php echo esc_html__('MailCreator logs:', 'mailcreator'); ?></h3>
                    <textarea readonly style="width:100%;height:400px;font-family:monospace;padding:10px;background:#fff;border:1px solid #ddd;"><?php echo esc_textarea($log_content ?: __('Geen MailCreator logs gevonden.', 'mailcreator')); ?></textarea>
                </div>
                
                <p style="font-size:12px;color:#666;">
                    <?php echo sprintf(esc_html__('Log bestand: %s', 'mailcreator'), esc_html($debug_log_path)); ?>
                </p>
            <?php endif; ?>
        </div>
        <?php
    }
}
