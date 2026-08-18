<?php
/**
 * Register custom post type and taxonomies for Wedding Portfolio Manager
 */

if (!defined('ABSPATH')) exit;

function wpm_register_post_types() {
    register_post_type('portfolio', [
        'label'               => 'Portfolios',
        'public'              => true,
        'show_in_rest'        => true,
        'show_in_menu'        => true,   // Make it visible in admin
        'menu_icon'           => 'dashicons-format-image',
        'supports'            => ['title', 'editor', 'thumbnail', 'custom-fields'],
        'has_archive'         => true,
        'rewrite'             => ['slug' => 'portfolios'],
        'rest_base'           => 'portfolios',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
    ]);
}
add_action('init', 'wpm_register_post_types');

function wpm_register_taxonomies() {
    $taxonomies = [
        'portfolio_type' => 'Portfolio Type',
        'wedding_type'   => 'Wedding Type',
        'style'          => 'Style',
        'location'       => 'Location',
    ];
    foreach ($taxonomies as $slug => $label) {
        register_taxonomy($slug, 'portfolio', [
            'label'             => $label,
            'public'            => true,
            'show_in_rest'      => true,
            'rest_base'         => $slug,  
            'hierarchical'      => true,
            'rewrite'           => ['slug' => $slug],
            'show_admin_column' => true,
        ]);
    }
}
add_action('init', 'wpm_register_taxonomies');