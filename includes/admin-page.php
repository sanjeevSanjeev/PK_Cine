<?php
if (!defined('ABSPATH')) exit;

function wpm_get_taxonomy_terms() {
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

add_action('admin_menu', 'wpm_admin_menu');
function wpm_admin_menu() {
    add_menu_page(
        'Wedding Portfolios',
        'Wedding Stories',
        'manage_options',
        'wpm-dashboard',
        'wpm_admin_page',
        'dashicons-format-image',
        30
    );
}

function wpm_admin_page() {
    echo '<div id="wpm-admin-root"></div>';
    
    // Enqueue CSS normally
    wp_enqueue_style('wpm-admin-css', WPM_PLUGIN_URL . 'includes/assets/App.bundle.css', [], '1.0');

    // Prepare localized data
    $data = [
        'apiRoot'    => esc_url_raw(rest_url('wp/v2/portfolios')),
        'nonce'      => wp_create_nonce('wp_rest'),
        'taxonomies' => wpm_get_taxonomy_terms(),
        'defaultHero' => get_option('wpm_default_hero_image', ''),
        'enabledTaxonomies' => get_option('wpm_enabled_taxonomies', [
            'portfolioType' => false,
            'weddingType'   => false,
            'style'         => false,
            'location'      => false,
        ]),
    ];

    // Output the data as a global JavaScript variable
    echo '<script>window.wpmAdmin = ' . wp_json_encode($data) . ';</script>';

    // Output the main entry script as an ES module (bypass WordPress enqueuing)
    echo '<script type="module" src="' . WPM_PLUGIN_URL . 'includes/assets/admin.bundle.js"></script>';
}