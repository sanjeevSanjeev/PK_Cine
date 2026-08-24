<?php
if (!defined('ABSPATH')) exit;

// Register settings
add_action('admin_init', 'wpm_register_settings');
function wpm_register_settings() {
    register_setting('wpm_settings_group', 'wpm_default_hero_image', [
        'type' => 'string',
        'sanitize_callback' => 'esc_url_raw',
    ]);
    register_setting('wpm_settings_group', 'wpm_enabled_taxonomies', [
        'type' => 'array',
        'sanitize_callback' => 'wpm_sanitize_enabled_taxonomies',
    ]);
}

function wpm_sanitize_enabled_taxonomies($input) {
    $default = [
        'portfolioType' => true,
        'weddingType' => true,
        'style' => true,
        'location' => true,
    ];
    if (!is_array($input)) return $default;
    foreach ($default as $key => $value) {
        $default[$key] = isset($input[$key]) && $input[$key] === '1';
    }
    return $default;
}

// Add submenu
add_action('admin_menu', 'wpm_add_settings_menu');
function wpm_add_settings_menu() {
    add_submenu_page(
        'wpm-dashboard',
        'Wedding Portfolio Settings',
        'Settings',
        'manage_options',
        'wpm-settings',
        'wpm_render_settings_page'
    );
}

function wpm_render_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }

    $default_hero = get_option('wpm_default_hero_image', '');
    $enabled_taxonomies = get_option('wpm_enabled_taxonomies', [
        'portfolioType' => true,
        'weddingType' => true,
        'style' => true,
        'location' => true,
    ]);
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form method="post" action="options.php">
            <?php settings_fields('wpm_settings_group'); ?>
            <?php do_settings_sections('wpm_settings_group'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="wpm_default_hero_image">Default Hero / Cover Image</label>
                    </th>
                    <td>
                        <input type="text" id="wpm_default_hero_image" name="wpm_default_hero_image" value="<?php echo esc_url($default_hero); ?>" class="regular-text" />
                        <button type="button" class="button wpm-media-picker" data-target="wpm_default_hero_image">Select Image</button>
                        <p class="description">This image will be used as the default hero/cover for new portfolios.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Enabled Taxonomies</th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text">Enabled Taxonomies</legend>
                            <label>
                                <input type="checkbox" name="wpm_enabled_taxonomies[portfolioType]" value="1" <?php checked($enabled_taxonomies['portfolioType']); ?> />
                                Portfolio Type
                            </label><br>
                            <label>
                                <input type="checkbox" name="wpm_enabled_taxonomies[weddingType]" value="1" <?php checked($enabled_taxonomies['weddingType']); ?> />
                                Wedding Type
                            </label><br>
                            <label>
                                <input type="checkbox" name="wpm_enabled_taxonomies[style]" value="1" <?php checked($enabled_taxonomies['style']); ?> />
                                Style
                            </label><br>
                            <label>
                                <input type="checkbox" name="wpm_enabled_taxonomies[location]" value="1" <?php checked($enabled_taxonomies['location']); ?> />
                                Location
                            </label>
                            <p class="description">Uncheck to hide a taxonomy from the portfolio editor.</p>
                        </fieldset>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <script>
    jQuery(document).ready(function($) {
        $('.wpm-media-picker').on('click', function(e) {
            e.preventDefault();
            var target = $(this).data('target');
            var frame = wp.media({
                title: 'Select Default Hero Image',
                button: { text: 'Use this image' },
                multiple: false
            });
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                $('#' + target).val(attachment.url);
            });
            frame.open();
        });
    });
    </script>
    <?php
}

// Enqueue media scripts on settings page
add_action('admin_enqueue_scripts', 'wpm_enqueue_settings_scripts');
function wpm_enqueue_settings_scripts($hook) {
    if ($hook !== 'wedding-stories_page_wpm-settings') return;
    wp_enqueue_media();
    wp_enqueue_script('jquery');
}