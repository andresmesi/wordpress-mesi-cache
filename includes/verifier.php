<?php
if (!defined('ABSPATH')) exit;

function mesi_cache_check_status(){
    $r=wp_remote_head(home_url('/'),['timeout'=>5]);
    if(is_wp_error($r)) return ['status'=>'error','msg'=>__('Error checking cache','mesi-cache')];
    $h=wp_remote_retrieve_headers($r);
    if(isset($h['x-mesi-static'])||isset($h['last-modified'])) return ['status'=>'static','msg'=>__('Served statically by Apache','mesi-cache')];
    if(isset($h['x-powered-by'])) return ['status'=>'dynamic','msg'=>__('Served dynamically (PHP)','mesi-cache')];
    return ['status'=>'unknown','msg'=>__('Unknown state','mesi-cache')];
}
