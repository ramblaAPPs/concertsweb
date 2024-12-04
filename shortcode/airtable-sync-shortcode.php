<?php
/**
 * Shortcode para mostrar conciertos sincronizados
 */

// Registrar el shortcode
add_shortcode('at_sync_conciertos', 'at_sync_display_conciertos');
function at_sync_display_conciertos($atts) {
    // Obtener los grupos musicales desde la vista de la Tabla 2
    $grupos = get_option('at_sync_grupos', array());
    
    // Si no hay grupos, mostrar un mensaje
    if (empty($grupos)) {
        return '<p>No hay grupos disponibles para mostrar.</p>';
    }

    // Procesar los atributos del shortcode
    $atts = shortcode_atts(array(
        'grupo' => ''
    ), $atts);

    // Obtener los conciertos sincronizados desde la vista de la Tabla 1
    $conciertos = get_option('at_sync_conciertos', array());

    // Filtrar los conciertos por grupo si se especifica
    if (!empty($atts['grupo'])) {
        $conciertos = array_filter($conciertos, function ($concierto) use ($atts) {
            return isset($concierto['fields']['Grup o Espectacle']) &&
                   strtolower($concierto['fields']['Grup o Espectacle']) === strtolower($atts['grupo']);
        });
    }

    // Si no hay conciertos, mostrar un mensaje
    if (empty($conciertos)) {
        return '<p>No hay conciertos disponibles para este grupo.</p>';
    }

    // Generar la salida HTML para los conciertos
    ob_start();
    ?>
    <div class="at-sync-conciertos">
        <?php foreach ($conciertos as $concierto) : ?>
            <div class="at-sync-concierto">
                <h3><?php echo esc_html($concierto['fields']['Grup o Espectacle']); ?></h3>
                <p><strong>Fecha:</strong> <?php echo esc_html($concierto['fields']['Data__']); ?></p>
                <p><strong>Población:</strong> <?php echo esc_html($concierto['fields']['Població']); ?></p>
                <p><strong>Horario:</strong> <?php echo esc_html($concierto['fields']['Horari']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}

// Actualizar los grupos musicales al sincronizar datos
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
