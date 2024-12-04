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

    // Guardar la API Key y otros parámetros si se ha enviado el formulario
    if (isset($_POST['at_sync_api_key'])) {
        check_admin_referer('at_sync_save_api_settings');
        at_sync_save_api_settings(
            $_POST['at_sync_api_key'],
            $_POST['at_sync_app_id'],
            $_POST['at_sync_tabla1_id'],
            $_POST['at_sync_vista1_id']
        );
        echo '<div class="updated"><p>Configuración guardada correctamente.</p></div>';
    }

    // Obtener la configuración actual
    $api_key = get_option('at_sync_api_key', '');
    $app_id = get_option('at_sync_app_id', '');
    $tabla1_id = get_option('at_sync_tabla1_id', '');
    $vista1_id = get_option('at_sync_vista1_id', '');
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
                    <th scope="row">App ID de Airtable:</th>
                    <td><input type="text" name="at_sync_app_id" value="<?php echo esc_attr($app_id); ?>" size="50" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Tabla ID:</th>
                    <td><input type="text" name="at_sync_tabla1_id" value="<?php echo esc_attr($tabla1_id); ?>" size="50" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Vista ID:</th>
                    <td><input type="text" name="at_sync_vista1_id" value="<?php echo esc_attr($vista1_id); ?>" size="50" /></td>
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
?>
