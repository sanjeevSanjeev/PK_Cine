<?php
if (!defined('ABSPATH')) exit;

function wpm_get_taxonomy_terms_shortcode() {
    $taxonomies = [
        'portfolioType' => 'portfolio_type',
        'weddingType'   => 'wedding_type',
        'style'         => 'style',
        'location'      => 'location'
    ];
    $data = [];
    foreach ($taxonomies as $camel => $snake) {
        $terms = get_terms(['taxonomy' => $snake, 'hide_empty' => false]);
        if (!is_wp_error($terms)) {
            $data[$camel] = array_map(function($term) {
                return [
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                    'description' => $term->description,
                ];
            }, $terms);
        } else {
            $data[$camel] = [];
        }
    }
    return $data;
}

add_shortcode('wedding_portfolio', 'wpm_shortcode');
function wpm_shortcode($atts) {
    // If this is a REST API request (e.g., block editor saving), return only the container
    if (wp_is_json_request()) {
        return '<div id="wpm-frontend-root-placeholder"></div>';
    }

    $atts = shortcode_atts([
        'columns'    => 3,
        'layout'     => 'grid',
        'per_page'   => 12,
        'id'         => 0,
        'type'       => '',
        'media_type' => '',
        'show_filters' => 'yes',
        'thumbnail_aspect_ratio' => '3:4',
    ], $atts);

    wp_enqueue_style('wpm-frontend-css', WPM_PLUGIN_URL . 'includes/assets/App.bundle.css', [], '1.0');
    wp_enqueue_script('wpm-frontend-js', WPM_PLUGIN_URL . 'includes/assets/frontend.bundle.js', [], '1.0', true);

    $unique = uniqid('wpm-frontend-');
    $container_id = 'wpm-frontend-root-' . $unique;

    $data = [
        'apiRoot'    => esc_url_raw(rest_url('wp/v2/portfolios')),
        'nonce'      => wp_create_nonce('wp_rest'),
        'atts'       => $atts,
        'taxonomies' => wpm_get_taxonomy_terms_shortcode(),
    ];

    // Use wp_add_inline_script to pass data safely (no direct echo)
    wp_add_inline_script('wpm-frontend-js', 
        'window.wpmFrontendInstances = window.wpmFrontendInstances || {}; window.wpmFrontendInstances["' . esc_js($container_id) . '"] = ' . wp_json_encode($data) . ';',
        'before'
    );

    return '<div id="' . esc_attr($container_id) . '" data-wpm-settings="' . esc_attr(wp_json_encode($data)) . '"></div>';
}

add_shortcode('wedding_portfolio_single', function() {
    if (!is_singular('portfolio')) {
        return '<!-- This shortcode only works on single portfolio pages -->';
    }
    if (wp_is_json_request()) {
        return '<div id="wpm-frontend-root"></div>';
    }

    global $post;
    
    wp_enqueue_style('wpm-frontend-css', WPM_PLUGIN_URL . 'includes/assets/App.bundle.css', [], '1.0');
    wp_enqueue_script('wpm-frontend-js', WPM_PLUGIN_URL . 'includes/assets/frontend.bundle.js', [], '1.0', true);
    
    $container_id = 'wpm-frontend-root';
    
    $data = [
        'apiRoot'    => esc_url_raw(rest_url('wp/v2/portfolios')),
        'nonce'      => wp_create_nonce('wp_rest'),
        'atts'       => ['id' => (int) $post->ID],
        'taxonomies' => wpm_get_taxonomy_terms_shortcode(),
    ];

    // Use wp_add_inline_script to pass data safely
    wp_add_inline_script('wpm-frontend-js',
        'window.wpmFrontend = ' . wp_json_encode($data) . ';',
        'before'
    );
    
    return '<div id="' . esc_attr($container_id) . '" data-wpm-settings="' . esc_attr(wp_json_encode($data)) . '"></div>';
});