<?php
if (!defined('ABSPATH')) exit;

add_action('init', function() {
    $meta_keys = [
    'client_name',
    'event_date',
    'venue',
    'cover_image',
    'hero_image',
    'gallery',
    'videos',
    'video_url',
    'video_url_vertical',
    'video_thumbnail_vertical',
    'featured',
    'story_content'
];
    foreach ($meta_keys as $key) {
        register_meta('post', '_wpm_' . $key, [
            'object_subtype' => 'portfolio',
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string',
            'auth_callback' => function() {
                return current_user_can('edit_posts');
            }
        ]);
    }
});

// Add slug and permalink for frontend (read-only)
add_action('rest_api_init', function() {
    $post_type = 'portfolio';
    register_rest_field($post_type, 'slug', [
        'get_callback' => function($post) {
            return $post['slug'];
        },
    ]);
    register_rest_field($post_type, 'permalink', [
        'get_callback' => function($post) {
            return get_permalink($post['id']);
        },
    ]);
});