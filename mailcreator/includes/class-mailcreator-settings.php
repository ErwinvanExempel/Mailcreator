<?php

defined('ABSPATH') || exit;

final class MailCreator_Settings
{
    const OPTION_KEY = 'mailcreator_settings';

    private static string $hook_suffix = '';

    public static function register(): void
    {
        add_action('admin_menu', [self::class, 'add_menu_page'], 20);
        add_action('admin_init', [self::class, 'handle_save']);
        add_action('admin_enqueue_scripts', [self::class, 'enqueue_assets']);
    }

    /** Hardcoded Exempel values become the seeded defaults, so nothing changes until edited. */
    public static function defaults(): array
    {
        return [
            'org_name' => 'Drumfanfare Exempel',
            'accent_color' => '#b21b17',
            'tool_logo_id' => 0,
            'tool_logo_url' => '',
            'mail_logo_id' => 0,
            'mail_logo_url' => 'https://www.exempel.net/wp-content/uploads/MailCreatorFiles/Logo%20Exempel.png',
            'sender_name' => 'THEBAND.',
            'sender_website' => 'https://exempel.net',
            'unsubscribe_url' => 'https://exempel.net/uitschrijven',
            'unsubscribe_email' => 'secretaris@exempel.net',
            'address_line' => 'Zandenweg 2 · 5398 KD Maren-Kessel',
            'preferences_url' => '',
        ];
    }

    public static function get(): array
    {
        $saved = get_option(self::OPTION_KEY, []);
        if (!is_array($saved)) {
            $saved = [];
        }
        return array_merge(self::defaults(), $saved);
    }

    /** Subset actually needed by app/index.html, passed as one query arg to the editor iframe. */
    public static function get_frontend_settings(): array
    {
        $settings = self::get();
        return [
            'org_name' => $settings['org_name'],
            'accent_color' => $settings['accent_color'],
            'tool_logo_url' => $settings['tool_logo_url'],
            'mail_logo_url' => $settings['mail_logo_url'],
            'sender_name' => $settings['sender_name'],
            'sender_website' => $settings['sender_website'],
            'unsubscribe_url' => $settings['unsubscribe_url'],
            'unsubscribe_email' => $settings['unsubscribe_email'],
            'address_line' => $settings['address_line'],
            'preferences_url' => $settings['preferences_url'],
        ];
    }

    public static function add_menu_page(): void
    {
        self::$hook_suffix = add_submenu_page(
            'mailcreator',
            'Beheer',
            'Beheer',
            'manage_options',
            'mailcreator-settings',
            [self::class, 'render_settings_page']
        );
    }

    public static function enqueue_assets(string $hook_suffix): void
    {
        if ($hook_suffix !== self::$hook_suffix) {
            return;
        }
        wp_enqueue_media();
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
    }

    private static function sanitize_color(string $color): string
    {
        $color = trim($color);
        return preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? $color : '';
    }

    public static function handle_save(): void
    {
        if (empty($_POST['mailcreator_save_settings'])) {
            return;
        }
        if (!current_user_can('manage_options')) {
            return;
        }
        check_admin_referer('mailcreator_save_settings');

        $defaults = self::defaults();
        $settings = [
            'org_name' => sanitize_text_field(wp_unslash($_POST['org_name'] ?? '')),
            'accent_color' => self::sanitize_color(wp_unslash($_POST['accent_color'] ?? '')),
            'tool_logo_id' => absint($_POST['tool_logo_id'] ?? 0),
            'tool_logo_url' => esc_url_raw(wp_unslash($_POST['tool_logo_url'] ?? '')),
            'mail_logo_id' => absint($_POST['mail_logo_id'] ?? 0),
            'mail_logo_url' => esc_url_raw(wp_unslash($_POST['mail_logo_url'] ?? '')),
            'sender_name' => sanitize_text_field(wp_unslash($_POST['sender_name'] ?? '')),
            'sender_website' => esc_url_raw(wp_unslash($_POST['sender_website'] ?? '')),
            'unsubscribe_url' => esc_url_raw(wp_unslash($_POST['unsubscribe_url'] ?? '')),
            'unsubscribe_email' => sanitize_email(wp_unslash($_POST['unsubscribe_email'] ?? '')),
            'address_line' => sanitize_text_field(wp_unslash($_POST['address_line'] ?? '')),
            'preferences_url' => esc_url_raw(wp_unslash($_POST['preferences_url'] ?? '')),
        ];

        // Fall back to the previous/default value for fields that must never be empty (avoids broken branding).
        foreach (['org_name', 'accent_color', 'sender_name', 'sender_website', 'unsubscribe_url', 'address_line'] as $key) {
            if ($settings[$key] === '') {
                $settings[$key] = $defaults[$key];
            }
        }

        update_option(self::OPTION_KEY, $settings);

        wp_safe_redirect(add_query_arg(['page' => 'mailcreator-settings', 'mailcreator_saved' => '1'], admin_url('admin.php')));
        exit;
    }

    private static function render_logo_picker(string $id_field, string $url_field, array $settings): void
    {
        $url = $settings[$url_field];
        ?>
        <img id="<?php echo esc_attr($id_field); ?>_preview" src="<?php echo esc_url($url); ?>" style="max-width:200px;max-height:80px;display:<?php echo $url ? 'block' : 'none'; ?>;margin-bottom:8px;">
        <input type="hidden" id="<?php echo esc_attr($id_field); ?>" name="<?php echo esc_attr($id_field); ?>" value="<?php echo esc_attr($settings[$id_field]); ?>">
        <input type="hidden" id="<?php echo esc_attr($id_field); ?>_url" name="<?php echo esc_attr($url_field); ?>" value="<?php echo esc_attr($url); ?>">
        <button type="button" class="button mailcreator-logo-button" id="<?php echo esc_attr($id_field); ?>_button"><?php esc_html_e('Logo kiezen', 'mailcreator'); ?></button>
        <?php
    }

    public static function render_settings_page(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Je hebt geen toestemming om deze pagina te bekijken.', 'mailcreator'));
        }
        $settings = self::get();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('MailCreator ‒ Beheer', 'mailcreator'); ?></h1>
            <?php if (!empty($_GET['mailcreator_saved'])) : ?>
                <div class="notice notice-success is-dismissible"><p><?php echo esc_html__('Instellingen opgeslagen.', 'mailcreator'); ?></p></div>
            <?php endif; ?>
            <form method="post">
                <?php wp_nonce_field('mailcreator_save_settings'); ?>
                <input type="hidden" name="mailcreator_save_settings" value="1">
                <h2><?php esc_html_e('De tool', 'mailcreator'); ?></h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="org_name"><?php esc_html_e('Naam vereniging', 'mailcreator'); ?></label></th>
                        <td><input type="text" id="org_name" name="org_name" value="<?php echo esc_attr($settings['org_name']); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="accent_color"><?php esc_html_e('Kleurenschema (accentkleur)', 'mailcreator'); ?></label></th>
                        <td><input type="text" id="accent_color" name="accent_color" value="<?php echo esc_attr($settings['accent_color']); ?>" class="mailcreator-color-field" data-default-color="<?php echo esc_attr(self::defaults()['accent_color']); ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Logo in de tool (editor-header)', 'mailcreator'); ?></th>
                        <td><?php self::render_logo_picker('tool_logo_id', 'tool_logo_url', $settings); ?></td>
                    </tr>
                </table>
                <h2><?php esc_html_e('De mail', 'mailcreator'); ?></h2>
                <p class="description"><?php esc_html_e('Dit wordt de nieuwe standaard voor nieuwe nieuwsbrieven. Per nieuwsbrief kan de afzendernaam, website en uitschrijflink nog worden aangepast.', 'mailcreator'); ?></p>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php esc_html_e('Logo in de mail', 'mailcreator'); ?></th>
                        <td><?php self::render_logo_picker('mail_logo_id', 'mail_logo_url', $settings); ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="sender_name"><?php esc_html_e('Standaard afzendernaam', 'mailcreator'); ?></label></th>
                        <td><input type="text" id="sender_name" name="sender_name" value="<?php echo esc_attr($settings['sender_name']); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="sender_website"><?php esc_html_e('Standaard website', 'mailcreator'); ?></label></th>
                        <td><input type="url" id="sender_website" name="sender_website" value="<?php echo esc_attr($settings['sender_website']); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="unsubscribe_url"><?php esc_html_e('Standaard uitschrijflink', 'mailcreator'); ?></label></th>
                        <td><input type="url" id="unsubscribe_url" name="unsubscribe_url" value="<?php echo esc_attr($settings['unsubscribe_url']); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="unsubscribe_email"><?php esc_html_e('E-mailadres voor uitschrijven (optioneel)', 'mailcreator'); ?></label></th>
                        <td><input type="email" id="unsubscribe_email" name="unsubscribe_email" value="<?php echo esc_attr($settings['unsubscribe_email']); ?>" class="regular-text" placeholder="naam@vereniging.nl"><p class="description"><?php esc_html_e('Toont een extra mailto-link onder de uitschrijflink, met onderwerp/tekst "Uitschrijven nieuwsbrief" al ingevuld.', 'mailcreator'); ?></p></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="address_line"><?php esc_html_e('Adres (straat, postcode en plaats)', 'mailcreator'); ?></label></th>
                        <td><input type="text" id="address_line" name="address_line" value="<?php echo esc_attr($settings['address_line']); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="preferences_url"><?php esc_html_e('Link "Voorkeuren wijzigen" (optioneel)', 'mailcreator'); ?></label></th>
                        <td><input type="url" id="preferences_url" name="preferences_url" value="<?php echo esc_attr($settings['preferences_url']); ?>" class="regular-text" placeholder="https://"></td>
                    </tr>
                </table>
                <?php submit_button(__('Instellingen opslaan', 'mailcreator')); ?>
            </form>
        </div>
        <script>
        jQuery(function($){
            $('.mailcreator-color-field').wpColorPicker();

            function mailcreatorMediaPicker(idField) {
                var frame;
                var button = document.getElementById(idField + '_button');
                if (!button) { return; }
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    if (frame) { frame.open(); return; }
                    frame = wp.media({ title: '<?php echo esc_js(__('Kies een afbeelding', 'mailcreator')); ?>', button: { text: '<?php echo esc_js(__('Gebruiken', 'mailcreator')); ?>' }, multiple: false });
                    frame.on('select', function () {
                        var attachment = frame.state().get('selection').first().toJSON();
                        document.getElementById(idField).value = attachment.id;
                        var urlField = document.getElementById(idField + '_url');
                        if (urlField) { urlField.value = attachment.url; }
                        var preview = document.getElementById(idField + '_preview');
                        if (preview) { preview.src = attachment.url; preview.style.display = 'block'; }
                    });
                    frame.open();
                });
            }
            mailcreatorMediaPicker('tool_logo_id');
            mailcreatorMediaPicker('mail_logo_id');
        });
        </script>
        <?php
    }
}
