<?php defined( 'ABSPATH' ) || exit;

$opts = get_site_option( 'essential_form' );
if( !$opts ){
    $opts = array();
}
if( !isset( $opts['activation_keys'] ) ){
    $keys = array();
    for( $n = 0;$n < 21;++$n ){
        $keys[] = uniqid();
    }
    $opts['activation_keys'] = $keys;
    essential_form_update_option( 'essential_form',$opts );
}