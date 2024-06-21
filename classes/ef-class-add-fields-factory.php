<?php
/**
 * The superclass of Essential Form to create custom fields
 *
 * Handles the creation of custom fields
 *
 * @class       Essential_Form_Fields_Factory
 * @version     1.0.0
 * @package     Essential Form
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Abstract Essetnail Form Fields Factory
 *
 * Implemented by classes using the same pattern.
 *
 * @version  1.0.0
 * @package  Essential Form\Finals
 */
final class Essential_Form_Fields_Factory {

	/**
	 * Slug.
	 *
	 * @since 0.0.6
	 * @var string
	 */
	public $slug;

	/**
	 * Uniq ID.
	 *
	 * @since 0.0.6
	 * @var string
	 */
	public $uniqid;

	/**
	 * Keys.
	 *
	 * @since 0.0.6
	 * @var array
	 */
	public $keys;

	/**
	 * Input type.
	 *
	 * @since 0.0.6
	 * @var string
	 */
	public $type;

    /**
	 * Page title.
	 *
	 * @since 0.0.6
	 * @var string
	 */
	public $title;

	/**
	 * Near Field.
	 *
	 * @since 0.0.6
	 * @var string
	 */
	public $near_field;

    /**
	 * Required.
	 *
	 * @since 0.0.6
	 * @var bool
	 */
	public $required;

	/**
	 * Error message.
	 *
	 * @since 0.0.6
	 * @var string
	 */
	public $error_message;

	/**
	 * Section CSS class.
	 *
	 * @since 0.0.6
	 * @var string
	 */
	public $field_class;

	/**
	 * Default constructor.
	 *
	 * @param string $type input type
	 * @param string $title Input label
	 * @param string $near_field near field
	 * @param bool $required if field is required
	 * @param string $field_class field class
	 */
	final function __construct( $slug, $type, $title, $required = false, $near_field = 'message', $error_message = false, $field_class = '' ) {
        $this->slug = $slug;
        $this->uniqid = uniqid();
        $this->keys = essential_form_get_keys();
        $this->type = $type;
        $this->title = $title;
        $this->near_field = $near_field;
        $this->required = $required;
        $this->error_message = $error_message ? $error_message : sprintf( __( 'Missing %s.', 'essential-form' ), $this->title );
        $this->field_class = $field_class;
        add_filter( 'essential_form_after_' . sanitize_key( $near_field ), array( $this, 'add_field' ), 10, 4 );
        add_filter( 'essential_form_fields_array', array( $this, 'fields_array' ), 10 );
        add_filter( 'essential_form_append_custom_field', array( $this, 'custom_field_js' ), 10 );
        add_filter( 'essential_form_before_message', array( $this, 'add_to_message' ), 10 );
        add_filter( 'essential_form_missing_custom_field_error', array( $this, 'missing_field_erroor' ), 10 );
	}

	/**
	 * Fields array.
	 *
	 */
    final function fields_array( $arr ) {
        $arr[] = 'custom_' . $this->slug;
        return $arr;
    }

	/**
	 * Add field.
	 *
	 */
    final function add_field( $output ) {
        $uniqid = $this->uniqid;
        $keys = $this->keys;
        $required = $this->required ? ' required' : '';
        $html = '<div class="ef-ctrl-wrp">';
        $html .= '<div><label for="custom_' . esc_attr( $keys[9] ) . '-' . esc_attr( $uniqid ) . '">'.esc_html( $this->title ).'</label></div>';
        $html .= '<div><input type="' . esc_attr( $this->type ) . '" id="custom_' . esc_attr( $keys[9] ).'-'. esc_attr( $uniqid ) .'" class="' . esc_attr( $keys[9] ) . '-' . esc_attr( $uniqid ) . '" ' . $required . ' /></div>';
        $html .= '</div>';
        return $output . $html;
    }

	/**
	 * Custom field JS.
	 *
	 */
    final function custom_field_js( $js ) {
        $keys = $this->keys;
        $js = 'fd.append("custom_'.esc_js( esc_attr( $this->slug ) ).'",document.getElementById("'.esc_js( 'custom_' . esc_attr( $keys[9] ).'-'. esc_attr( $this->uniqid ) ).'").value);';
        return $js;
    }

	/**
	 * Missing field error.
	 *
	 */
    final function missing_field_erroor() {
        if( $this->required && ( !isset( $_POST['custom_' . $this->slug ] ) || empty( $_POST['custom_' . $this->slug ] ) ) ){
            echo esc_html( $this->error_message );
            die();
            exit;
        }
    }

    /**
	 * Add field value to message.
	 *
	 */
    final function add_to_message( $before_message ) {
        if( isset( $_POST['custom_' . $this->slug ] ) ){
            $html = sprintf( '%s: %s', $this->slug, $_POST['custom_' . $this->slug] );
        }
        return $before_message  . PHP_EOL . sanitize_text_field( $html );
    }
}