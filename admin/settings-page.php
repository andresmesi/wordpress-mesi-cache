<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', function(){
    add_options_page('Mesi Cache','Mesi Cache','manage_options','mesi-cache','mesi_cache_admin_page');
});

function mesi_cache_admin_page(){
    if (!current_user_can('manage_options')) return;

    // procesar acciones del formulario
    if (isset($_POST['mesi_cache_action']) && check_admin_referer('mesi_cache_actions')) {
        $action = sanitize_text_field( wp_unslash( $_POST['mesi_cache_action'] ) );

        // ejecuta la acción y muestra el resultado inmediato
        switch ($action) {
            case 'regen_home':
                $ok = mesi_cache_generate_home();
                $msg = $ok ? __('Home cache generated','mesi-cache') : __('Could not generate home cache','mesi-cache');
                $class = $ok ? 'updated' : 'error';
                break;

            case 'regen_all':
                $res = mesi_cache_generate_all();
                /* translators: 1: number of generated items, 2: number of errors */
                $msg = sprintf(__('Generated %1$d items, %2$d errors','mesi-cache'),
                    intval($res['generated']), intval($res['errors']));
                $class = 'updated';
                break;

            case 'clear_all':
                mesi_cache_clear_all();
                $msg = __('Cache cleared','mesi-cache');
                $class = 'updated';
                break;

            case 'htaccess_add':
                $r = mesi_cache_insert_htaccess_block();
                $msg = is_wp_error($r)
                    ? __('Could not write .htaccess','mesi-cache')
                    : __('MESI-Cache block written to .htaccess','mesi-cache');
                $class = is_wp_error($r) ? 'error' : 'updated';
                break;

            case 'htaccess_remove':
                $r = mesi_cache_remove_htaccess_block();
                $msg = is_wp_error($r)
                    ? __('Could not modify .htaccess','mesi-cache')
                    : __('MESI-Cache block removed','mesi-cache');
                $class = is_wp_error($r) ? 'error' : 'updated';
                break;

            case 'check_status':
                $res = mesi_cache_check_status();
                $msg = $res['msg'];
                if ($res['status'] === 'static') $class = 'updated';
                elseif ($res['status'] === 'dynamic') $class = 'notice-warning';
                else $class = 'error';
                break;

            default:
                $msg = __('Unknown action','mesi-cache');
                $class = 'error';
        }

        // imprimir aviso inline
        echo '<div class="notice ' . esc_attr($class) . '"><p>' . esc_html($msg) . '</p></div>';
    }

    // interfaz principal
    ?>
    <div class="wrap">
        <h1>Mesi Cache</h1>

        <h2><?php esc_html_e('Cache Management','mesi-cache'); ?></h2>
        <form method="post" style="display:inline-block;margin-right:6px;">
            <?php wp_nonce_field('mesi_cache_actions'); ?>
            <input type="hidden" name="mesi_cache_action" value="regen_home">
            <button class="button button-primary"><?php esc_html_e('Regenerate Home','mesi-cache'); ?></button>
        </form>

        <form method="post" style="display:inline-block;margin-right:6px;">
            <?php wp_nonce_field('mesi_cache_actions'); ?>
            <input type="hidden" name="mesi_cache_action" value="regen_all">
            <button class="button"><?php esc_html_e('Generate All','mesi-cache'); ?></button>
        </form>

        <form method="post" style="display:inline-block;">
            <?php wp_nonce_field('mesi_cache_actions'); ?>
            <input type="hidden" name="mesi_cache_action" value="clear_all">
            <button class="button"><?php esc_html_e('Clear All Cache','mesi-cache'); ?></button>
        </form>

        <hr><h2><?php esc_html_e('Apache Integration','mesi-cache'); ?></h2>
        <form method="post" style="display:inline-block;margin-right:6px;">
            <?php wp_nonce_field('mesi_cache_actions'); ?>
            <input type="hidden" name="mesi_cache_action" value="htaccess_add">
            <button class="button"><?php esc_html_e('Generate / Update MESI-Cache Block','mesi-cache'); ?></button>
        </form>

        <form method="post" style="display:inline-block;">
            <?php wp_nonce_field('mesi_cache_actions'); ?>
            <input type="hidden" name="mesi_cache_action" value="htaccess_remove">
            <button class="button"><?php esc_html_e('Remove MESI-Cache Block','mesi-cache'); ?></button>
        </form>

        <hr><h2><?php esc_html_e('Status Verifier','mesi-cache'); ?></h2>
        <form method="post">
            <?php wp_nonce_field('mesi_cache_actions'); ?>
            <input type="hidden" name="mesi_cache_action" value="check_status">
            <button class="button button-secondary"><?php esc_html_e('Check Cache Status','mesi-cache'); ?></button>
        </form>

        <p><small>
            <?php 
            /* translators: %s: plugin version number */
            printf( esc_html__('Mesi Cache v%s — immediate feedback version.', 'mesi-cache'), esc_html(MESI_CACHE_VERSION) ); 
            ?>
        </small></p>
    </div>
    <?php
}
