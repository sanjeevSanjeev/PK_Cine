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
    // CRITICAL: Do not output anything during REST / JSON requests
    if (wp_is_json_request()) {
        return '<!-- wedding_portfolio shortcode placeholder -->';
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

    // Get enabled taxonomies from settings
    $enabled_taxonomies = get_option('wpm_enabled_taxonomies', [
        'portfolioType' => false,
        'weddingType'   => false,
        'style'         => false,
        'location'      => false,
    ]);

    wp_enqueue_style('wpm-frontend-css', WPM_PLUGIN_URL . 'includes/assets/App.bundle.css', [], '1.0');
    wp_enqueue_script('wpm-frontend-js', WPM_PLUGIN_URL . 'includes/assets/frontend.bundle.js', [], '1.0', true);

    $unique = uniqid('wpm-frontend-');
    $container_id = 'wpm-frontend-root-' . $unique;

    $data = [
        'apiRoot'           => esc_url_raw(rest_url('wp/v2/portfolios')),
        'nonce'             => wp_create_nonce('wp_rest'),
        'atts'              => $atts,
        'taxonomies'        => wpm_get_taxonomy_terms_shortcode(),
        'enabledTaxonomies' => $enabled_taxonomies,
    ];

    // Output data (only on real frontend requests – REST is guarded above)
    echo '<script>window.wpmFrontendInstances = window.wpmFrontendInstances || {}; window.wpmFrontendInstances["' . esc_js($container_id) . '"] = ' . wp_json_encode($data) . ';</script>';

    return '<div id="' . esc_attr($container_id) . '" data-wpm-settings="' . esc_attr(wp_json_encode($data)) . '"></div>';
}

// Single portfolio shortcode (optional)
add_shortcode('wedding_portfolio_single', function() {
    if (wp_is_json_request()) {
        return '<!-- wedding_portfolio_single shortcode placeholder -->';
    }
    if (!is_singular('portfolio')) {
        return '<!-- This shortcode only works on single portfolio pages -->';
    }

    global $post;

    $enabled_taxonomies = get_option('wpm_enabled_taxonomies', [
        'portfolioType' => false,
        'weddingType'   => false,
        'style'         => false,
        'location'      => false,
    ]);

    wp_enqueue_style('wpm-frontend-css', WPM_PLUGIN_URL . 'includes/assets/App.bundle.css', [], '1.0');
    wp_enqueue_script('wpm-frontend-js', WPM_PLUGIN_URL . 'includes/assets/frontend.bundle.js', [], '1.0', true);

    $container_id = 'wpm-frontend-root';

    $data = [
        'apiRoot'           => esc_url_raw(rest_url('wp/v2/portfolios')),
        'nonce'             => wp_create_nonce('wp_rest'),
        'atts'              => ['id' => (int) $post->ID],
        'taxonomies'        => wpm_get_taxonomy_terms_shortcode(),
        'enabledTaxonomies' => $enabled_taxonomies,
    ];

    echo '<script>window.wpmFrontend = ' . wp_json_encode($data) . ';</script>';

    return '<div id="' . esc_attr($container_id) . '" data-wpm-settings="' . esc_attr(wp_json_encode($data)) . '"></div>';
});