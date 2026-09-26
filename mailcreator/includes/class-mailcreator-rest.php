<?php

defined('ABSPATH') || exit;

final class MailCreator_REST
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        // Test endpoint (debug only - remove in production)
        register_rest_route('mailcreator/v1', '/test', [
            'methods' => WP_REST_Server::READABLE,
            'permission_callback' => '__return_true',
            'callback' => [self::class, 'test_endpoint'],
        ]);
        
        register_rest_route('mailcreator/v1', '/media', [
            'methods' => WP_REST_Server::CREATABLE,
            'permission_callback' => [self::class, 'can_upload'],
            'callback' => [self::class, 'upload_media'],
        ]);
        register_rest_route('mailcreator/v1', '/drafts', [
            'methods' => WP_REST_Server::CREATABLE,
            'permission_callback' => [self::class, 'can_upload'],
            'callback' => [self::class, 'save_draft'],
        ]);
        register_rest_route('mailcreator/v1', '/drafts/(?P<id>\d+)', [
            'methods' => WP_REST_Server::READABLE,
            'permission_callback' => [self::class, 'can_upload'],
            'callback' => [self::class, 'get_draft'],
        ]);
    }
    
    public static function test_endpoint()
    {
        return new WP_REST_Response([
            'status' => 'ok',
            'user_id' => get_current_user_id(),
            'can_upload' => current_user_can('upload_files'),
            'nonce_valid' => wp_verify_nonce($_SERVER['HTTP_X_WP_NONCE'] ?? '', 'wp_rest') > 0,
            'rest_url' => rest_url('mailcreator/v1/drafts'),
            'plugin_dir' => MAILCREATOR_DIR,
        ]);
    }

    public static function can_upload(WP_REST_Request $request): bool
    {
        $user_id = get_current_user_id();
        $can_upload = current_user_can('upload_files');
        error_log("MailCreator permission check: user_id=$user_id, can_upload=$can_upload, method=" . $request->get_method());
        
        if (!$can_upload) {
            error_log('User cannot upload files');
        }
        
        return $can_upload;
    }

    public static function upload_media(WP_REST_Request $request)
    {
        if (empty($_FILES['image'])) {
            return new WP_Error(
                'mailcreator_missing_image',
                __('Geen afbeelding ontvangen.', 'mailcreator'),
                ['status' => 400]
            );
        }

        $file = $_FILES['image'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = wp_check_filetype_and_ext($file['tmp_name'], $file['name']);

        if (empty($file_type['type']) || !in_array($file_type['type'], $allowed_types, true)) {
            return new WP_Error(
                'mailcreator_invalid_image',
                __('Dit bestandstype wordt niet ondersteund.', 'mailcreator'),
                ['status' => 415]
            );
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $attachment_id = media_handle_upload('image', 0);

        if (is_wp_error($attachment_id)) {
            return $attachment_id;
        }

        $url = wp_get_attachment_url($attachment_id);

        return new WP_REST_Response([
            'id' => $attachment_id,
            'url' => $url,
            'alt' => get_post_meta($attachment_id, '_wp_attachment_image_alt', true),
        ], 201);
    }

    public static function save_draft(WP_REST_Request $request)
    {
        // Cache buster: 20260911-001
        error_log('MailCreator: save_draft called');
        error_log('Current user: ' . get_current_user_id());
        error_log('Can upload? ' . (current_user_can('upload_files') ? 'yes' : 'no'));
        
        error_log('About to call get_json_params()');
        try {
            $payload = $request->get_json_params();
            error_log('get_json_params succeeded, payload type: ' . gettype($payload));
        } catch (Exception $e) {
            error_log('get_json_params failed: ' . $e->getMessage());
            return new WP_Error('mailcreator_json_error', 'JSON parsing failed: ' . $e->getMessage(), ['status' => 400]);
        }
        
        if (!is_array($payload)) {
            error_log('Payload is not array: ' . gettype($payload) . ', value: ' . json_encode($payload));
            return new WP_Error('mailcreator_no_json', 'Payload is not an array', ['status' => 400]);
        }
        
        error_log('Payload received: ' . json_encode(array_keys($payload)));
        $payload_json = json_encode($payload);
        error_log('Payload size: ' . strlen($payload_json) . ' bytes (' . round(strlen($payload_json)/1024, 2) . ' KB)');
        
        $title = sanitize_text_field($payload['title'] ?? 'Nieuwe nieuwsbrief');
        $newsletter = $payload['newsletter'] ?? null;

        if (!is_array($newsletter)) {
            error_log('Newsletter is not array: ' . gettype($newsletter));
            return new WP_Error(
                'mailcreator_invalid_draft',
                __('Ongeldige nieuwsbriefgegevens ontvangen.', 'mailcreator'),
                ['status' => 400]
            );
        }

        $post_id = absint($payload['id'] ?? 0);
        $newsletter_json = json_encode($newsletter);
        
        error_log('Post content size: ' . strlen($newsletter_json) . ' bytes (' . round(strlen($newsletter_json)/1024, 2) . ' KB)');
        error_log('Post title length: ' . strlen($title) . ' chars');
        
        $post_data = [
            'post_type' => 'mailcreator_draft',
            'post_status' => 'draft',
            'post_title' => $title ?: 'Nieuwe nieuwsbrief',
            'post_name' => 'draft-' . uniqid(),
            'post_content' => $newsletter_json,
            'post_author' => get_current_user_id(),
        ];

        if ($post_id) {
            $existing = get_post($post_id);
            if (!$existing || $existing->post_type !== 'mailcreator_draft') {
                return new WP_Error('mailcreator_invalid_draft_id', __('Concept niet gevonden.', 'mailcreator'), ['status' => 404]);
            }
            if (!current_user_can('edit_post', $post_id)) {
                return new WP_Error('mailcreator_forbidden_draft', __('Je mag dit concept niet wijzigen.', 'mailcreator'), ['status' => 403]);
            }
            $post_data['ID'] = $post_id;
        }

        error_log('About to call wp_insert_post with post_data keys: ' . json_encode(array_keys($post_data)));
        
        $saved_id = wp_insert_post($post_data, true);
        
        if (is_wp_error($saved_id)) {
            error_log('wp_insert_post failed: ' . $saved_id->get_error_code() . ' - ' . $saved_id->get_error_message());
            $error_data = $saved_id->get_error_data();
            if ($error_data) {
                error_log('Error data: ' . json_encode($error_data));
            }
            return $saved_id;
        }

        error_log('Post saved successfully with ID: ' . $saved_id);

        return new WP_REST_Response([
            'id' => $saved_id,
            'title' => get_the_title($saved_id),
            'status' => 'draft',
        ], $post_id ? 200 : 201);
    }

    public static function get_draft(WP_REST_Request $request)
    {
        $post_id = absint($request['id']);
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'mailcreator_draft' || $post->post_status !== 'draft') {
            return new WP_Error('mailcreator_draft_not_found', __('Concept niet gevonden.', 'mailcreator'), ['status' => 404]);
        }
        if (!current_user_can('edit_post', $post_id)) {
            return new WP_Error('mailcreator_forbidden_draft', __('Je mag dit concept niet bekijken.', 'mailcreator'), ['status' => 403]);
        }
        $newsletter = json_decode($post->post_content, true);
        if (!is_array($newsletter)) {
            return new WP_Error('mailcreator_invalid_saved_draft', __('De opgeslagen nieuwsbrief is ongeldig.', 'mailcreator'), ['status' => 500]);
        }
        return new WP_REST_Response(['id' => $post_id, 'title' => $post->post_title, 'newsletter' => $newsletter]);
    }
}
