<?php
/**
 * Funciones para sincronizar datos con Airtable
 */

// Sincronizar datos de Airtable
function at_sync_airtable_data() {
    // Obtener la API Key y URLs de las vistas desde la configuración del plugin
    $api_key = get_option('at_sync_api_key');
    $tabla1_url = get_option('at_sync_tabla1_url');
    $tabla2_url = get_option('at_sync_tabla2_url');

    if (!$api_key || !$tabla1_url || !$tabla2_url) {
        error_log('API Key o URLs de vistas no configuradas para Airtable Sync Plugin.');
        return;
    }

    // Extraer app_id, table_id y view_id desde la URL de la vista de la Tabla 1
    $tabla1_data = at_sync_extract_ids_from_url($tabla1_url);
    if (!$tabla1_data) {
        error_log('No se pudo extraer información de la URL de la vista de la Tabla 1.');
        return;
    }

    // Realizar la petición a Airtable para la Tabla 1
    $response1 = at_sync_fetch_airtable_data($api_key, $tabla1_data);
    if (!$response1) {
        error_log('Error al conectar con Airtable Tabla 1.');
        return;
    }

    // Guardar los datos de la Tabla 1 en la base de datos de WordPress
    update_option('at_sync_conciertos', $response1);

    // Extraer app_id, table_id y view_id desde la URL de la vista de la Tabla 2
    $tabla2_data = at_sync_extract_ids_from_url($tabla2_url);
    if (!$tabla2_data) {
        error_log('No se pudo extraer información de la URL de la vista de la Tabla 2.');
        return;
    }

    // Realizar la petición a Airtable para la Tabla 2
    $response2 = at_sync_fetch_airtable_data($api_key, $tabla2_data);
    if (!$response2) {
        error_log('Error al conectar con Airtable Tabla 2.');
        return;
    }

    // Guardar los grupos musicales en la base de datos de WordPress
    at_sync_update_grupos($response2);

    // Actualizar la hora de la última sincronización
    update_option('at_sync_last_sync_time', current_time('mysql'));
}

// Extraer app_id, table_id y view_id de la URL de la vista de Airtable
function at_sync_extract_ids_from_url($url) {
    if (preg_match('/app([a-zA-Z0-9]+)/', $url, $app_match) &&
        preg_match('/tbl([a-zA-Z0-9]+)/', $url, $table_match) &&
        preg_match('/viw([a-zA-Z0-9]+)/', $url, $view_match)) {
        return array(
            'app_id' => $app_match[0],
            'table_id' => $table_match[0],
            'view_id' => $view_match[0]
        );
    }
    return false;
}

// Hacer petición de datos a Airtable
function at_sync_fetch_airtable_data($api_key, $ids) {
    $url = "https://api.airtable.com/v0/{$ids['app_id']}/{$ids['table_id']}?view={$ids['view_id']}";
    $response = wp_remote_get($url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key
        )
    ));

    if (is_wp_error($response)) {
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    return json_decode($body, true)['records'];
}

// Guardar los grupos musicales al sincronizar datos
function at_sync_update_grupos($tabla2_data) {
    $grupos = array();
    foreach ($tabla2_data as $record) {
        if (isset($record['fields']['Grup o Espectacle'])) {
            $grupos[] = $record['fields']['Grup o Espectacle'];
        }
    }
    update_option('at_sync_grupos', $grupos);
}
?>
