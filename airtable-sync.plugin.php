<?php
/**
 * Plugin Name: Concertweb - Airtable Sync Plugin
 * Description: Un plugin para sincronizar datos de Airtable y mostrar conciertos mediante shortcodes.
 * Version: 1.0
 * Author: Gonzalo Rambla
 */

// Definir constantes del plugin
if (!defined('AT_SYNC_PLUGIN_DIR')) {
    define('AT_SYNC_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

// Incluir los archivos necesarios
include_once AT_SYNC_PLUGIN_DIR . 'includes/airtable-sync-functions.php';
include_once AT_SYNC_PLUGIN_DIR . 'admin/airtable-sync-admin.php';
include_once AT_SYNC_PLUGIN_DIR . 'shortcode/airtable-sync-shortcode.php';

// Registrar activación del plugin para crear una opción de sincronización
register_activation_hook(__FILE__, 'at_sync_plugin_activate');
function at_sync_plugin_activate() {
    if (!wp_next_scheduled('at_sync_cron_job')) {
        wp_schedule_event(time(), 'eight_hours', 'at_sync_cron_job');
    }
}

// Desactivar cron al desinstalar
register_deactivation_hook(__FILE__, 'at_sync_plugin_deactivate');
function at_sync_plugin_deactivate() {
    wp_clear_scheduled_hook('at_sync_cron_job');
}

// Intervalo personalizado de 8 horas
add_filter('cron_schedules', 'at_sync_add_cron_interval');
function at_sync_add_cron_interval($schedules) {
    $schedules['eight_hours'] = array(
        'interval' => 8 * 60 * 60,
        'display'  => __('Cada 8 horas')
    );
    return $schedules;
}

// Acción de sincronización automática
add_action('at_sync_cron_job', 'at_sync_airtable_data');
?>
