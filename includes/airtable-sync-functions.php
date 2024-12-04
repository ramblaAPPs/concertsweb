<?php
/**
 * Funciones para sincronizar datos con Airtable
 */

// Sincronizar datos de Airtable
function at_sync_airtable_data() {
    // Obtener la API Key desde la configuración del plugin
    $api_key = get_option('at_sync_api_key');
    $app_id = get_option('at_sync_app_id');
    $tabla1_id = get_option('at_sync_tabla1_id');
    $vista1_id = get_option('at_sync_vista1_id');

    if (!$api_key || !$app_id || !$tabla1_id || !$vista1_id) {
        error_log('API Key, App ID, Tabla ID o Vista ID no configurados para Airtable Sync Plugin.');
        return;
    }

    // Construir la URL de la API para la Tabla 1
    $tabla1_url = "https://api.airtable.com/v0/{$app_id}/{$tabla1_id}?view={$vista1_id}";

    // Realizar la petición a Airtable para la Tabla 1
    $tabla1_response = wp_remote_get($tabla1_url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key
        )
    ));

    if (is_wp_error($tabla1_response)) {
        error_log('Error al conectar con Airtable: ' . $tabla1_response->get_error_message());
        return;
    }

    $tabla1_body = wp_remote_retrieve_body($tabla1_response);
    $tabla1_data = json_decode($tabla1_body, true);

    if (!isset($tabla1_data['records'])) {
        error_log('No se pudieron obtener los registros de la Tabla 1 de Airtable.');
        return;
    }

    // Guardar los datos de la tabla en la base de datos de WordPress
    update_option('at_sync_conciertos', $tabla1_data['records']);

    // Actualizar la hora de la última sincronización
    update_option('at_sync_last_sync_time', current_time('mysql'));
}

// Guardar la API Key y otros parámetros desde la página de administración
function at_sync_save_api_settings($api_key, $app_id, $tabla1_id, $vista1_id) {
    update_option('at_sync_api_key', sanitize_text_field($api_key));
    update_option('at_sync_app_id', sanitize_text_field($app_id));
    update_option('at_sync_tabla1_id', sanitize_text_field($tabla1_id));
    update_option('at_sync_vista1_id', sanitize_text_field($vista1_id));
}
?>
