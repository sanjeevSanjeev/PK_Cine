<?php
/**
 * Plugin Name: Wedding Portfolio Manager
 * Description: A premium wedding portfolio manager for photographers and videographers to showcase wedding stories, films, and galleries.
 * Version: 1.0.1
 * Author: Sanjeev Bhattarai
 * Author URI: https://sanjeevbhattarai.com.np/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wedding-portfolio-manager-6
 */

if (!defined('ABSPATH')) exit;

define('WPM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPM_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include core files
require_once WPM_PLUGIN_DIR . 'includes/post-types.php';
require_once WPM_PLUGIN_DIR . 'includes/rest-api.php';
require_once WPM_PLUGIN_DIR . 'includes/admin-page.php';
require_once WPM_PLUGIN_DIR . 'includes/shortcode.php';
require_once WPM_PLUGIN_DIR . 'includes/blocks.php';
require_once WPM_PLUGIN_DIR . 'includes/settings-page.php';

// Activation / Deactivation
register_activation_hook(__FILE__, 'wpm_activate');
function wpm_activate() {
    wpm_register_post_types();
    wpm_register_taxonomies();
    flush_rewrite_rules();
}

register_deactivation_hook(__FILE__, 'wpm_deactivate');
function wpm_deactivate() {
    flush_rewrite_rules();
}

// =============================================
// Force ES module for our scripts
// =============================================
add_filter('script_loader_tag', function($tag, $handle) {
    $handles = ['wpm-admin-js', 'wpm-frontend-js'];
    if (in_array($handle, $handles)) {
        $tag = str_replace("type='text/javascript'", "type='module'", $tag);
        $tag = str_replace('type="text/javascript"', 'type="module"', $tag);
        if (strpos($tag, 'type=') === false) {
            $tag = str_replace('<script ', '<script type="module" ', $tag);
        }
    }
    return $tag;
}, 10, 2);

// =============================================
// Inject React app into single portfolio content
// =============================================
add_filter('the_content', function($content) {
    if (is_singular('portfolio') && in_the_loop() && is_main_query() && !wp_is_json_request()) {
        $enabled_taxonomies = get_option('wpm_enabled_taxonomies', [
            'portfolioType' => false,
            'weddingType'   => false,
            'style'         => false,
            'location'      => false,
        ]);

        wp_enqueue_style('wpm-frontend-css', WPM_PLUGIN_URL . 'includes/assets/App.bundle.css', [], '1.0');
        wp_enqueue_script('wpm-frontend-js', WPM_PLUGIN_URL . 'includes/assets/frontend.bundle.js', [], '1.0', true);
        
        global $post;
        $data = [
            'apiRoot'           => esc_url_raw(rest_url('wp/v2/portfolios')),
            'nonce'             => wp_create_nonce('wp_rest'),
            'atts'              => ['id' => (int) $post->ID],
            'taxonomies'        => wpm_get_taxonomy_terms_shortcode(),
            'enabledTaxonomies' => $enabled_taxonomies,
        ];
        
        wp_add_inline_script('wpm-frontend-js', 'window.wpmFrontend = ' . wp_json_encode($data) . ';', 'before');
        
        return '<div id="wpm-frontend-root"></div>';
    }
    return $content;
}, 10, 1);