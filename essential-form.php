<?php
/*
Plugin Name: Essential Form
Description: The lightest contact form for WordPress. So essential you'll either love it or hate it. Ultra lightweight and no spam.
Author: Jose Mortellaro
Author URI: https://josemortellaro.com/
Domain Path: /languages/
Text Domain: essential-form
Version: 0.0.8
*/
/*  This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
*/
defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

// Definitions.
define( 'ESSENTIAL_FORM_VERSION','0.0.8' );
define( 'ESSENTIAL_FORM_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );
define( 'ESSENTIAL_FORM_PLUGIN_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );
define( 'ESSENTIAL_FORM_BASE_NAME', untrailingslashit( plugin_basename( __FILE__ ) ) );

$keys = essential_form_get_keys();
if( $keys && is_array( $keys ) ){
    define( 'ESSENTIAL_FORM_AJAX_KEY','essential_form_'.sanitize_key( $keys[0] ) );
}

if( wp_doing_ajax() && defined( 'ESSENTIAL_FORM_AJAX_KEY' ) ){
    if( isset( $_REQUEST['action'] ) && in_array( sanitize_text_field( $_REQUEST['action'] ),apply_filters( 'essential_form_allowed_actions',array( 'essential_form_'.sanitize_key( $keys[0] ),'essential_form_'.sanitize_key( $keys[0] ).'_get_key' ) )  ) ){
        require_once ESSENTIAL_FORM_PLUGIN_DIR.'/inc/ef-ajax.php';
    }
}
elseif( is_admin() ){
    require_once ESSENTIAL_FORM_PLUGIN_DIR.'/admin/ef-admin.php';
}

// Actions triggered after plugin activation.
register_activation_hook( __FILE__,function( $networkwide ){
	require ESSENTIAL_FORM_PLUGIN_DIR.'/plugin-activation.php';
} );

// Get options in case of single or multisite installation.
function essential_form_get_option( $option ){
    if( !is_multisite() ){
        return get_option( $option );
    }
    else{
        return get_blog_option( get_current_blog_id(),$option );
    }
}

// Update options in case of single or multisite installation.
function essential_form_update_option( $option,$newvalue,$autoload = false ){
	if( !is_multisite() ){
		return update_option( $option,$newvalue,$autoload );
	}
	else{
		return update_blog_option( get_current_blog_id(),$option,$newvalue );
	}
}

// Filtered settings.
function essential_form_filtered_settings(){
    $settings = array();
    $default_settings = essential_form_default_settings();
    $filtered = apply_filters( 'essential_form_settings',$default_settings );
    foreach( $default_settings as $key => $value ){
        $settings[$key] = isset( $filtered[$key] ) ? $filtered[$key] : $default_settings[$key];
    }
    return $settings;
}

// Default settings.
function essential_form_default_settings(){
    $privacy_page = (int) get_site_option( 'wp_page_for_privacy_policy' );
    if( function_exists( 'icl_object_id' ) ){
        $privacy_page_lang = icl_object_id( $privacy_page,'page', false,ICL_LANGUAGE_CODE );
        $privacy_page = $privacy_page_lang ? $privacy_page_lang : $privacy_page;
    }
    $url = get_permalink( $privacy_page );
    $url = $url ? $url : '#';
    return array(
        'email_from' => get_bloginfo( 'admin_email' ),
        'email_to' => get_bloginfo( 'admin_email' ),
        'email_subject' => sprintf( esc_html__( 'Message from %s','essential-form' ),get_bloginfo( 'name' ) ),
        'label_name' => __( 'Name','essential-form' ),
        'label_email' => __( 'Email','essential-form' ),
        'label_message' => __( 'Message','essential-form' ),
        'button_text' => __( 'Send','essential-form' ),
        'agreement_text' => sprintf( __( 'By submitting this form I agree with the %sprivacy policy%s','essential-form' ),'<a href="'.esc_url( $url ).'" target="_blank">','</a>' ),
        'success_message' => __( 'Form submitted successfully! Thank you for your message!','essential-form' ),
        'name_missing_error' => __( 'Name is a required field!','essential-form' ),
        'email_missing_error' => __( 'Email is a required field!','essential-form' ),
        'email_not_valid_error' => __( 'Email not valid!','essential-form' ),
        'message_missing_error' => __( 'Message is a required field!','essential-form' ),
        'message_too_long_error' => __( 'This message is too long! Please, write not more than 50000 characters.','essential-form' ),
        'missing_agreement_error' => __( 'You have to agree with our privacy policy to submit the form.','essential-form' )
    );
}

add_shortcode( 'essential_form',function( $atts ){
    //Form shortcode.
    static $s = 0;
    $opts = essential_form_get_option( 'essential_form' );
    if( isset( $opts['activation_keys'] ) ){
        do_action( 'essential_form_init' );
        global $post;
        ++$s;
        $settings = essential_form_filtered_settings();
        extract( $settings );
        $label_name = isset( $atts['label_name'] ) ? sanitize_text_field( $atts['label_name'] ) : apply_filters( 'essential_form_label_name',$label_name );
        $label_email = isset( $atts['label_email'] ) ? sanitize_text_field( $atts['label_email'] ) : apply_filters( 'essential_form_label_email',$label_email );
        $label_message = isset( $atts['label_message'] ) ? sanitize_text_field( $atts['label_message'] ) : apply_filters( 'essential_form_label_message',$label_message );
        $button_text = isset( $atts['button_text'] ) ? sanitize_text_field( $atts['button_text'] ) : apply_filters( 'essential_form_button_text',$button_text );
        $agreement_text = isset( $atts['agreement_text'] ) ? wp_kses( $atts['agreement_text'],array( 'a' => array( 'href' => array(),'target' => array() ) ) ) : apply_filters( 'essential_form_agreement_text',$agreement_text );
        $success_message = isset( $atts['success_message'] ) ? sanitize_text_field( $atts['success_message'] ) : apply_filters( 'essential_form_success_message',$success_message );
        $keys = essential_form_get_keys();
        $output = '<style id="'.esc_attr( $keys[0] ).'-'.$s.'-css">';
        $output .= '.'.esc_attr( $keys[0] ).'-wrp .ef-error{border:1px solid red;padding:10px;margin-bottom:16px}';
        $output .= '.'.esc_attr( $keys[0] ).'-wrp .ef-success{border:1px solid green;padding:10px;margin-bottom:16px}';
        $output .= '.'.esc_attr( $keys[0] ).' input:not([type=checkbox]),.'.esc_attr( $keys[0] ).' textarea{width:100%}.'.esc_attr( $keys[0] ).' textarea{min-height:150px}.'.esc_attr( $keys[0] ).'>div{margin-bottom:16px}.'.esc_attr( $keys[0] ).' button{background-position:-999999px -9999999px !important}.'.esc_attr( $keys[0] ).'.ef-progress button{background-position:center !important;pointer-events:none !important}';
        $output .= '</style>';
        $output .= '<div id="fw-'.esc_attr( $keys[0] ).'-'.$s.'" class="'.esc_attr( $keys[0] ).'-wrp ef-ctrl-wrp">';
        $output .= '<div id="'.esc_attr( $keys[7] ).'-'.$s.'" style="display:none" class="ef-success">'.esc_html( $success_message ).'</div>';
        $output .= '<div id="'.esc_attr( $keys[8] ).'-'.$s.'" style="display:none" class="ef-error"></div>';
        $output .= '<form id="'.esc_attr( $keys[0] ).'-'.$s.'" class="'.esc_attr( $keys[0] ).'">';
        $output = apply_filters( 'essential_form_before_name', $output, $post, $settings, $atts );
        $output .= '<div class="ef-ctrl-wrp">';
        $output .= '<div><label for="'.esc_attr( $keys[1] ).'-'.$s.'">'.esc_html( $label_name ).'</label></div>';
        $output .= '<div><input type="text" id="'.esc_attr( $keys[1] ).'-'.$s.'" class="'.esc_attr( $keys[1] ).'" required /></div>';
        $output .= '</div>';
        $output = apply_filters( 'essential_form_after_name', $output, $post, $settings, $atts );
        $output .= '<div class="ef-ctrl-wrp">';
        $output .= '<div><label for="'.esc_attr( $keys[2] ).'-'.$s.'">'.esc_html( $label_email ).'</label></div>';
        $output .= '<div><input type="email" id="'.esc_attr( $keys[2] ).'-'.$s.'" class="'.esc_attr( $keys[2] ).'" required /></div>';
        $output .= '</div>';
        $output = apply_filters( 'essential_form_after_email', $output, $post, $settings, $atts );
        $output .= '<div class="ef-ctrl-wrp">';
        $output .= '<div><label for="'.esc_attr( $keys[3] ).'-'.$s.'">'.esc_html( $label_message ).'</label></div>';
        $output .= '<div><textarea id="'.esc_attr( $keys[3] ).'-'.$s.'" class="'.esc_attr( $keys[3] ).'" required></textarea></div>';
        $output .= '</div>';
        $output = apply_filters( 'essential_form_after_message', $output, $post, $settings, $atts );
        $checkbox_required = '';
        $checkbox_wrapper_style = ' style="display:none"';
        if( ! defined( 'ESSENTIAL_FORM_ASK_FOR_AGREEMENT' ) || ESSENTIAL_FORM_ASK_FOR_AGREEMENT ) {
            $checkbox_required = ' ' . apply_filters( 'essential_form_agreement_checkbox_required', 'required' );
            $checkbox_wrapper_style = '';
        }
        $checkbox_input_style = ' required' === $checkbox_required ? '' : ' style="display:none"';
        $output .= '<div' . $checkbox_wrapper_style . '><input' . $checkbox_input_style . ' type="checkbox" id="'.esc_attr( $keys[4] ).'-'.$s.'" class="'.esc_attr( $keys[4] ).'"' . $checkbox_required . ' value="on" /> '.wp_kses( $agreement_text ,array( 'a' => array( 'href' => array(),'target' => array() ) ) ).'</div>';
        $output = apply_filters( 'essential_form_after_checkbox', $output, $post, $settings, $atts );
        $output .= '<div><button id="'.esc_attr( $keys[3] ).'-'.$s.'" type="submit" class="button" style="background-size:22px 22px !important;background-repeat:no-repeat !important;background-image:url('.includes_url( '/images/spinner.gif' ).') !important" onclick="essential_form_'.sanitize_key( $keys[0] ).'('.$s.');return false;">'.esc_html( $button_text ).'</div>';
        $output = apply_filters( 'essential_form_after_submit', $output, $post, $settings, $atts );
        $output .= '<input type="hidden" value="'.esc_attr( implode( ',',$keys ) ).'" id="'.esc_attr( $keys[5] ).'-'.$s.'" />';
        $output .= '<input type="hidden" value="" id="'.esc_attr( $keys[6] ).'-'.$s.'" />';
        $output .= '</form>';
        $output .= '<div id="'.esc_attr( $keys[8] ).'-'.$s.'" style="margin-bottom:16px"></div>';
        $output .= '</div>';
        if( 1 === $s ){
            $output .= '<script id="'.esc_attr( $keys[0] ).'-'.$s.'-js" type="text/javascript">';
            $output .= 'essential_form_random_key = Math.floor(Math.random() * 99999999999999);';
            $output .= 'function essential_form_'.sanitize_key( $keys[0] ).'_init(){';
            $output .= 'var r = new XMLHttpRequest(),f=new FormData();';
            $output .= 'r.open("POST","'.esc_js( admin_url( 'admin-ajax.php' ) ).'?action='.ESSENTIAL_FORM_AJAX_KEY.'_get_key",true);';
            $output .= 'r.onload = function(e){';
            $output .= 'if(this.readyState === 4 && "" !== e.target.responseText){';
            $output .= 'document.getElementById("'.esc_attr( $keys[6] ).'-'.$s.'").value = e.target.responseText;';
            $output .= '}';
            $output .= '};';    
            $output .= 'f.append("random_key",essential_form_random_key);';
            $output .= 'r.send(f);';
            $output .= '}';
            $output .= 'essential_form_'.sanitize_key( $keys[0] ).'_init();';
            $output .= 'function essential_form_'.sanitize_key( $keys[0] ).'(n){';
            $output .= 'var req = new XMLHttpRequest(),fd=new FormData(),succ=document.getElementById("'.esc_attr( $keys[7] ).'-'.$s.'"),err=document.getElementById("'.esc_attr( $keys[8] ).'-'.$s.'"),fe=document.getElementById("'.esc_attr( $keys[0] ).'-" + n);';
            $output .= 'fe.className += " ef-progress";';
            $output .= 'succ.style.display="none";';
            $output .= 'err.style.display="none";';
            $output .= 'req.onload = function(e){';
            $output .= 'if(this.readyState === 4 && "" !== e.target.responseText){';
            $output .= 'if("1" === e.target.responseText){';
            $output .= 'succ.style.display="block";';
            $output .= 'err.style.display="none";';
            $output .= 'document.getElementById("'.esc_attr( $keys[0] ).'-'.$s.'").style.visibility="hidden";';
            $output .= '}else{';
            $output .= 'err.style.display="block";';
            $output .= 'err.innerHTML=e.target.responseText;';
            $output .= '}';
            $output .= '}';
            $output .= 'fe.className = fe.className.replace(" ef-progress","");';
            $output .= 'return false;';
            $output .= '};';
            $f = 1;
            foreach( essential_form_fields_array() as $fkey ){
                if( 'agreement' === $fkey ){
                    $output .= 'fd.append("'.esc_js( esc_attr( $fkey ) ).'",document.getElementById("'.esc_js( esc_attr( $keys[$f] ) ).'-" + n).checked);';
                }
                elseif( false === strpos(  $fkey, 'custom_' ) ){
                    $output .= 'fd.append("'.esc_js( esc_attr( $fkey ) ).'",document.getElementById("'.esc_js( esc_attr( $keys[$f] ) ).'-" + n).value);';
                }
                ++$f;
            }
            $output .= apply_filters( 'essential_form_append_custom_field', '' );
            global $post;
            if( $post && is_object( $post ) && isset( $post->ID ) ){
                $output .= 'fd.append("post_id",'.absint( $post->ID ).');';
            }
            $output .= 'fd.append("random_key",essential_form_random_key);';
            $output .= 'req.open("POST","'.esc_js( admin_url( 'admin-ajax.php' ) ).'?action='.ESSENTIAL_FORM_AJAX_KEY.'",true);';
            $output .= 'req.send(fd);';
            $output .= 'return false;';
            $output .= '}';
            $output .= '</script>';
        }
        return $output;
    }
} );

// Return array of fields.
function essential_form_fields_array(){
    return apply_filters( 'essential_form_fields_array', array(
        'name',
        'email',
        'message',
        'agreement',
        'keys',
        'temp_key'
    ) );
}

// Get keys for the anti-spam system.
function essential_form_get_keys(){
    $keys = false;
    $opts = essential_form_get_option( 'essential_form' );
    if( $opts && isset( $opts['activation_keys'] ) ){
        $keys = $opts['activation_keys'];
        foreach( $keys as &$key ){
            $key = 'f'.sanitize_text_field( substr( md5( $key ),0,8 ) );
        }
    }
    return $keys;
}

add_filter( 'plugin_action_links_'.untrailingslashit( plugin_basename( __FILE__ ) ),function( $links ) {
    // It action links in the plugins page.
    $links[] = sprintf( 'Shortcode: %s','<span class="ef-shortcode" style="color:#000">[essential_form]</span>' );
    $links[] = '<a href="https://wordpress.org/plugins/essential-form/" target="_blank" rel="noopener">'.esc_html__( 'Description','essential-form' ).'</a>';
  	return $links;
} );

add_action( 'init',function(){
    // It loads plugin translation files.
	load_plugin_textdomain( 'essential-form',false,ESSENTIAL_FORM_PLUGIN_DIR.'/languages/' );
} );

add_filter( 'load_textdomain_mofile',function( $mofile,$domain ){
    // Filter function to read plugin translation files.
	if ( 'essential-form' === $domain ) {
		$loc = function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$mofile = defined( 'WP_LANG_DIR' ) && WP_LANG_DIR && file_exists( WP_LANG_DIR . '/plugins/essential-form-' . $loc . '.mo' ) ? WP_LANG_DIR . '/plugins/essential-form-' . $loc . '.mo' : ESSENTIAL_FORM_PLUGIN_DIR . '/languages/essential-form-' . $loc . '.mo';
	}
	return $mofile;
},10,2 );


// Helper function to add custom field.
function essential_form_add_field( $slug, $type, $title, $required = false, $near_field = 'message', $error_message = false, $field_class = '' ) {
    require_once ESSENTIAL_FORM_PLUGIN_DIR . '/classes/ef-class-add-fields-factory.php';
    $new_field = new Essential_Form_Fields_Factory( $slug, $type, $title, $required, $near_field, $error_message, $field_class );
}