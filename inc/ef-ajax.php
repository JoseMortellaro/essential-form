<?php
defined( 'ESSENTIAL_FORM_AJAX_KEY' ) || exit;

$keys = essential_form_get_keys();
if( !ESSENTIAL_FORM_AJAX_KEY ){
    wp_die( 'This file can be called only by Essential Form during a form submission' );
}
if( 'essential_form_'.sanitize_key( $keys[0] ) !== ESSENTIAL_FORM_AJAX_KEY ){
    wp_die( 'This file can be called only by Essential Form during a form submission' );
}
add_action( 'wp_ajax_'.ESSENTIAL_FORM_AJAX_KEY.'_get_key','essential_form_ajax_handler_get_key' );
add_action( 'wp_ajax_nopriv_'.ESSENTIAL_FORM_AJAX_KEY.'_get_key','essential_form_ajax_handler_get_key' );
//Handler for the form submission
function essential_form_ajax_handler_get_key(){
    if( !isset( $_POST['random_key'] ) || empty( $_POST['random_key'] ) ){
        die();
        exit;
    }
    $key = md5( microtime(1).uniqid() );
    set_site_transient( 'essential_form_key_'.sanitize_key( $_POST['random_key'] ),$key,apply_filters( 'essential_form_random_key_expiring_time', 60 * 24 ) );
    echo $key;
    die();
    exit;
}

add_action( 'wp_ajax_'.ESSENTIAL_FORM_AJAX_KEY,'essential_form_ajax_handler' );
add_action( 'wp_ajax_nopriv_'.ESSENTIAL_FORM_AJAX_KEY,'essential_form_ajax_handler' );

//Handler for the form submission
function essential_form_ajax_handler(){
    if( !isset( $_POST['random_key'] ) || empty( $_POST['random_key'] ) ){
        die();
        exit;
    }
    if( !isset( $_POST['keys'] ) ){
        die();
        exit;
    }
    $pkeys = array_map( 'sanitize_text_field',explode( ',',$_POST['keys'] ) );
    $temp_key = get_site_transient( 'essential_form_key_'.sanitize_key( $_POST['random_key'] ) );
    if( $temp_key !== sanitize_key( $_POST['temp_key'] ) ){
        die();
        exit;
    }
    $keys = essential_form_get_keys();
    $f = 1;
    foreach( essential_form_fields_array() as $fkey ){
        if( !isset( $_POST[$fkey] ) || $keys[$f] !== $pkeys[$f] ){
            die();
            exit;
        }
    }
    $name = sanitize_text_field( $_POST['name'] );
    $email = sanitize_email( $_POST['email'] );
    $message = wp_kses_post( $_POST['message'] );
    $agreement = sanitize_text_field( $_POST['agreement'] );
    $settings = essential_form_filtered_settings();
    extract( $settings );
    if( empty( $name ) ){
        echo apply_filters( 'essential_form_missing_name_missing_error',esc_html( $name_missing_error ) );
        die();
        exit;
    }
    if( empty( $email ) ){
        echo apply_filters( 'essential_form_missing_email_missing_error',esc_html( $email_missing_error ) );
        die();
        exit;
    }
    if(  $email !== $_POST['email'] ){
        echo apply_filters( 'essential_form_missing_email_notvalid_error',esc_html( $email_not_valid_error ) );
        die();
        exit;
    }
    if( empty( $message ) ){
        echo apply_filters( 'essential_form_missing_message_missing_error',esc_html( $message_missing_error ) );
        die();
        exit;
    }
    if( strlen( $message ) > 50000 ){
        echo apply_filters( 'essential_form_message_toolong_error',esc_html( $message_too_long_error ) );
        die();
        exit;
    }
    if( ( ! defined( 'ESSENTIAL_FORM_ASK_FOR_AGREEMENT' ) || ESSENTIAL_FORM_ASK_FOR_AGREEMENT ) && 'required' === apply_filters( 'essential_form_agreement_checkbox_required', 'required' ) ) {
        if( empty( $agreement ) || 'true' !== $agreement ){
            echo apply_filters( 'essential_form_missing_agreement_error',esc_html( $missing_agreement_error ) );
            die();
            exit;
        }
    }
    do_action( 'essential_form_missing_custom_field_error' );
    $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : false;
    do_action( 'essential_form_before_sending',$name,$email,$message,$post_id );
    $from = apply_filters( 'essential_form_email_from',$email_from,$post_id );
    $to =  apply_filters( 'essential_form_admin_email',$email_to,$post_id );
    $subject = apply_filters( 'essential_form_email_subject',$email_subject );
	$headers = array( 
        'Content-Type: text/html; charset=UTF-8',
        'From: "'.esc_attr( get_bloginfo( 'name' ) ).'" <'.sanitize_email( $from ).'>',
        'Reply-To: <'.sanitize_email( $email ).'>'
    );
    $before_message = '<p>'.sprintf( esc_html__( 'Name: %s','essential-form' ),esc_html( $name ) ).'</p>';
    $before_message .= '<p>'.sprintf( esc_html__( 'Email: %s','essential-form' ),esc_html( $email ) ).'</p>'.PHP_EOL.PHP_EOL;
    $before_message = apply_filters( 'essential_form_before_message', $before_message );
    if( $post_id  ){
        $before_message .= '<p>'.sprintf( 
            __( 'Form submited from the page %s','essential-form' ),
            '<a href="'.esc_url( get_the_permalink( $post_id ) ).'" rel="noopener">'.esc_html( get_the_title( $post_id  ) ).'</a>' 
            ).'</p><p style="height:32px"></p>'.PHP_EOL.PHP_EOL;
    }
    elseif( isset( $_SERVER['HTTP_REFERER'] ) ){
        $before_message .= '<p>'.sprintf( 
            __( 'Form submited from the URL %s','essential-form' ),
            '<a href="'.esc_url( $_SERVER['HTTP_REFERER'] ).'" rel="noopener">'.esc_url( $_SERVER['HTTP_REFERER'] ).'</a>' 
            ).'</p><p style="height:32px"></p>'.PHP_EOL.PHP_EOL;        
    }
    delete_site_transient( 'essential_form_key_'.sanitize_key( $_POST['random_key'] ) );
	echo wp_mail( $to, str_replace( '&#039;','\'',esc_html( $subject ) ), str_replace( '&#039;','\'',wp_kses_post( $before_message.apply_filters( 'essential_form_message',$message,$name,$email,$post_id ) ) ),$headers ) ? 1 : 0;
    do_action( 'essential_form_after_sending',$name,$email,$message,$post_id,$from,$to,$subject );
    die();
    exit;
}