<?php
/**
 * Plugin Name: WP Airtable Sync
 * Description: Sincroniza datos desde Airtable y permite mostrarlos mediante shortcodes.
 * Version: 1.0.0
 * Author: Tu Nombre
 */

if (!defined('ABSPATH')) exit;

// Clase principal del plugin
class WP_Airtable_Sync {
    private static $api_key;
    private static $base_id = 'appgfemQpxUPTD7PW';
    private static $table_1 = 'tblXDMebx466CynuS';
    private static $table_2 = 'tbl2e5yy3SOW69Dzl';

    public static function init() {
        self::$api_key = get_option('wp_airtable_api_key', '');
        add_action('admin_menu', [__CLASS__, 'add_settings_page']);
        add_action('admin_post_wp_airtable_sync', [__CLASS__, 'manual_sync']);
        add_action('wp_ajax_wp_airtable_sync_shortcode', [__CLASS__, 'ajax_shortcode']);
        add_shortcode('airtable_concerts', [__CLASS__, 'display_concerts']);
        self::schedule_sync();
    }

    public static function add_settings_page() {
        add_menu_page(
            'WP Airtable Sync',
            'Airtable Sync',
            'manage_options',
            'wp-airtable-sync',
            [__CLASS__, 'settings_page']
        );
    }

    public static function settings_page() {
    if ($_POST['api_key']) {
        update_option('wp_airtable_api_key', sanitize_text_field($_POST['api_key']));
        echo '<div class="updated"><p>API Key guardada.</p></div>';
    }
    $api_key = esc_attr(get_option('wp_airtable_api_key', ''));
    $table_2 = get_option('wp_airtable_table_2', []);
    $selected_group = isset($_POST['selected_group']) ? sanitize_text_field($_POST['selected_group']) : '';
    ?>
    <div class="wrap">
        <h1>WP Airtable Sync</h1>
        <form method="post">
            <label for="api_key">API Key:</label>
            <input type="text" id="api_key" name="api_key" value="<?php echo $api_key; ?>" />
            <button type="submit" class="button button-primary">Guardar</button>
        </form>

        <h2>Sincronización</h2>
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="wp_airtable_sync" />
            <button type="submit" class="button button-primary">Sincronizar Ahora</button>
        </form>

        <h2>Mostrar Resultados por Grupo</h2>
        <form method="post">
            <label for="selected_group">Selecciona un grupo:</label>
            <select id="selected_group" name="selected_group">
                <?php foreach ($table_2 as $record): ?>
                    <option value="<?php echo esc_attr($record['fields']['Nombre del Grupo']); ?>"
                        <?php selected($selected_group, $record['fields']['Nombre del Grupo']); ?>>
                        <?php echo esc_html($record['fields']['Nombre del Grupo']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="button button-primary">Mostrar Resultados</button>
        </form>

        <?php if ($selected_group): ?>
            <h3>Shortcode Generado</h3>
            <p>[airtable_concerts group="<?php echo esc_html($selected_group); ?>"]</p>

            <h3>Resultados</h3>
            <?php echo self::display_concerts(['group' => $selected_group]); ?>
        <?php endif; ?>
    </div>
    <?php
}


    public static function manual_sync() {
        check_admin_referer(); // Verifica autenticidad
        self::sync_data();
        wp_redirect(admin_url('admin.php?page=wp-airtable-sync&synced=true'));
        exit;
    }

    public static function sync_data() {
        $table_1_data = self::fetch_table(self::$table_1);
        $table_2_data = self::fetch_table(self::$table_2);

        if (!empty($table_1_data)) {
            update_option('wp_airtable_table_1', $table_1_data);
        }
        if (!empty($table_2_data)) {
            update_option('wp_airtable_table_2', $table_2_data);
        }
        update_option('wp_airtable_last_sync', current_time('mysql'));
    }

    public static function fetch_table($table) {
        $url = "https://api.airtable.com/v0/" . self::$base_id . "/$table";
        $response = wp_remote_get($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . self::$api_key,
            ]
        ]);
        if (is_wp_error($response)) return [];
        $data = json_decode(wp_remote_retrieve_body($response), true);
        return $data['records'] ?? [];
    }

    public static function display_concerts($atts) {
        $atts = shortcode_atts(['group' => ''], $atts);
        $table_1 = get_option('wp_airtable_table_1', []);
        $group = $atts['group'];
        $output = '<ul>';
        foreach ($table_1 as $record) {
            if ($record['fields']['Grup o Espectacle'] === $group) {
                $output .= '<li>' . esc_html($record['fields']['Data__'] . ' - ' . $record['fields']['Població']) . '</li>';
            }
        }
        $output .= '</ul>';
        return $output;
    }

    public static function schedule_sync() {
        if (!wp_next_scheduled('wp_airtable_sync_cron')) {
            wp_schedule_event(time(), '8_hours', 'wp_airtable_sync_cron');
        }
        add_action('wp_airtable_sync_cron', [__CLASS__, 'sync_data']);
    }

    public static function clear_schedule() {
        wp_clear_scheduled_hook('wp_airtable_sync_cron');
    }
}

// Inicializar el plugin
add_action('plugins_loaded', ['WP_Airtable_Sync', 'init']);

// Activar y desactivar hooks
register_activation_hook(__FILE__, ['WP_Airtable_Sync', 'schedule_sync']);
register_deactivation_hook(__FILE__, ['WP_Airtable_Sync', 'clear_schedule']);
