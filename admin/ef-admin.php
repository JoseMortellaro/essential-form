<?php
defined( 'ABSPATH' ) || exit;

// Helper function to update the settings.
function essential_form_update_options( $new_options ){
	$writeAccess = false;
	$access_type = get_filesystem_method();
	if( $access_type === 'direct' ){
		/* you can safely run request_filesystem_credentials() without any issues and don't need to worry about passing in a URL */
		$creds = request_filesystem_credentials( admin_url(), '', false, false, array() );
		/* initialize the API */
		if ( ! WP_Filesystem( $creds ) ) {
			/* any problems and we exit */
			return false;
		}
		global $wp_filesystem;
		$writeAccess = true;
		if( empty( $wp_filesystem ) ){
			require_once ( ABSPATH . '/wp-admin/includes/file.php' );
			WP_Filesystem();
		}
		if( !$wp_filesystem->is_dir( WPMU_PLUGIN_DIR ) ){
			/* directory didn't exist, so let's create it */
			$wp_filesystem->mkdir( WPMU_PLUGIN_DIR );
		}
        $mu_content = "<?php".PHP_EOL."defined( 'ABSPATH' ) || exit;";
        $mu_content .= PHP_EOL.'/*';
        $mu_content .= PHP_EOL.'Plugin Name: essential form [ef]';
        $mu_content .= PHP_EOL.'Description: mu-plugin automatically installed by essential form';
        $mu_content .= PHP_EOL.'Version: '.ESSENTIAL_FORM_VERSION;
        $mu_content .= PHP_EOL.'Plugin URI: https://josemortellaro.com/';
        $mu_content .= PHP_EOL.'Author: Jose Mortellaro';
        $mu_content .= PHP_EOL.'Author URI: https://josemortellaro.com/';
        $mu_content .= PHP_EOL.'License: GPLv2';
        $mu_content .= PHP_EOL.'*/';

        foreach( $new_options as $key => $value ){
            $mu_content .= PHP_EOL."if( !defined( 'ESSENTIAL_FORM_".strtoupper( sanitize_key( $key ) )."' ) ){";
            if( is_array( $value ) ){
                $mu_content .= PHP_EOL.chr( 9 )."define( 'ESSENTIAL_FORM_".strtoupper( $key )."',array( ".sanitize_text_field( implode( ',',$value ) )." ) );";
            }
            elseif( is_string( $value ) ){
                $mu_content .= PHP_EOL.chr( 9 )."define( 'ESSENTIAL_FORM_".strtoupper( $key )."',".sanitize_text_field( $value )." );";
            }
            $mu_content .= PHP_EOL."}";
        }
		$updated = @$wp_filesystem->put_contents( WPMU_PLUGIN_DIR.'/essential-form-mu.php',$mu_content,FS_CHMOD_FILE );
		if ( !$updated ) {
			_e( 'Settings not updated','essential-form' );
		}

	}
	else{
         _e( 'Access to the filesystem denied','essential-form' );
	}
}

add_filter( 'eos_dp_integration_action_plugins',function( $args ){
	//It adds custom ajax actions to the FDP Actions Settings Pages
    $args['essential-form'] = array(
        'is_active' => true,
        'ajax_actions' => array(
            ESSENTIAL_FORM_AJAX_KEY.'_get_key' => array( 'description' => __( 'Getting secret key during submission','essential-form' ) ),
            ESSENTIAL_FORM_AJAX_KEY => array( 'description' => __( 'Form submission','essential-form' ) ), )
    );
    return $args;
} );

add_filter( 'plugin_row_meta', 'essential_form_plugin_row_meta', 20, 2 );
// Add links to the plugins page.
function essential_form_plugin_row_meta( $links, $file ) {
	if ( ESSENTIAL_FORM_BASE_NAME === $file ) {
		$path               = 'https://translate.wordpress.org/projects/wp-plugins/essential-form/';
		$links['translate'] = '<a href="' . $path . '" target="_blank" aria-label="' . esc_attr__( 'Translate', 'essential-form' ) . '">' . esc_html__( 'Translate', 'essential-form' ) . '</a>';
	}
	return $links;
}