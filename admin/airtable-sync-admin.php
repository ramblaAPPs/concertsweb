<?php
/**
 * Administración del plugin Airtable Sync
 */

// Agregar la página de administración del plugin
add_action('admin_menu', 'at_sync_add_admin_menu');
function at_sync_add_admin_menu() {
    add_menu_page(
        'Airtable Sync',
        'Airtable Sync',
        'manage_options',
        'airtable_sync',
        'at_sync_admin_page',
        'dashicons-update',
        20
    );
}

// Página de configuración del plugin
function at_sync_admin_page() {
    // Verificar permisos de usuario
    if (!current_user_can('manage_options')) {
        return;
    }

    // Guardar la API Key y URLs de las vistas si se ha enviado el formulario
    if (isset($_POST['at_sync_api_key'])) {
        check_admin_referer('at_sync_save_api_settings');
        update_option('at_sync_api_key', sanitize_text_field($_POST['at_sync_api_key']));
        update_option('at_sync_tabla1_url', esc_url_raw($_POST['at_sync_tabla1_url']));
        update_option('at_sync_tabla2_url', esc_url_raw($_POST['at_sync_tabla2_url']));
        echo '<div class="updated"><p>Configuración guardada correctamente.</p></div>';
    }

    // Obtener la configuración actual
    $api_key = get_option('at_sync_api_key', '');
    $tabla1_url = get_option('at_sync_tabla1_url', '');
    $tabla2_url = get_option('at_sync_tabla2_url', '');
    $last_sync_time = get_option('at_sync_last_sync_time', 'Nunca');
    ?>
    <div class="wrap">
        <h1>Airtable Sync Configuración</h1>
        <form method="post" action="">
            <?php wp_nonce_field('at_sync_save_api_settings'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">API Key de Airtable:</th>
                    <td><input type="text" name="at_sync_api_key" value="<?php echo esc_attr($api_key); ?>" size="50" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">URL de la Vista de la Tabla 1:</th>
                    <td><input type="text" name="at_sync_tabla1_url" value="<?php echo esc_attr($tabla1_url); ?>" size="100" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">URL de la Vista de la Tabla 2:</th>
                    <td><input type="text" name="at_sync_tabla2_url" value="<?php echo esc_attr($tabla2_url); ?>" size="100" /></td>
                </tr>
            </table>
            <?php submit_button('Guardar Configuración'); ?>
        </form>
        <h2>Sincronización Manual</h2>
        <p>Última sincronización: <strong><?php echo esc_html($last_sync_time); ?></strong></p>
        <form method="post" action="">
            <?php wp_nonce_field('at_sync_manual_sync'); ?>
            <input type="hidden" name="at_sync_manual_sync" value="1" />
            <?php submit_button('Sincronizar Ahora'); ?>
        </form>
        <?php
        // Ejecutar sincronización manual si se ha enviado el formulario
        if (isset($_POST['at_sync_manual_sync'])) {
            check_admin_referer('at_sync_manual_sync');
            at_sync_airtable_data();
            echo '<div class="updated"><p>Sincronización realizada correctamente.</p></div>';
        }
        ?>
    </div>
    <?php
}
