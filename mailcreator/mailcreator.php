<?php
/**
 * Plugin Name: MailCreator
 * Description: Nieuwsbriefeditor met instelbare huisstijl per vereniging.
 * Version: 0.3.7
 * Author: Drumfanfare Exempel
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

defined('ABSPATH') || exit;

define('MAILCREATOR_VERSION', '0.3.7');
define('MAILCREATOR_FILE', __FILE__);
define('MAILCREATOR_DIR', plugin_dir_path(__FILE__));
defined('MAILCREATOR_ROOT_FILE') || define('MAILCREATOR_ROOT_FILE', __FILE__);

require_once MAILCREATOR_DIR . 'includes/class-mailcreator-admin.php';
require_once MAILCREATOR_DIR . 'includes/class-mailcreator-rest.php';
require_once MAILCREATOR_DIR . 'includes/class-mailcreator-settings.php';

add_action('init', static function (): void {
    register_post_type('mailcreator_draft', [ // slug moet <=20 tekens zijn voor wp_posts.post_type
        'labels' => [
            'name' => 'MailCreator-concepten',
            'singular_name' => 'MailCreator-concept',
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => false,
        'supports' => ['title', 'author'],
        'capability_type' => 'post',
        'map_meta_cap' => true,
    ]);
});

add_action('plugins_loaded', static function (): void {
    MailCreator_Admin::register();
    MailCreator_REST::register();
    MailCreator_Settings::register();
});
