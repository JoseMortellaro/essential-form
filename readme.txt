=== Essential Form - The lightest plugin for contact forms, ultra lightweight and no spam ===

Contributors:      giuse
Requires at least: 4.6
Tested up to:      6.5
Requires PHP:      7.2
Stable tag:        0.0.8
License:           GPLv2 or later
Donate link: buymeacoffee.com/josem
License URI:       https://www.gnu.org/licenses/gpl-2.0.html
Tags:              contact form, light, anti-spam, leightweight, email



The **lightest contact form** for WordPress. It's so essential you'll either love it or hate it. Ultra lightweight and no spam.


== Description ==


Use the shortcode [essential_form] where you want a **contact form** with the fields: name, email, message, and agreement checkbox.

<a href="https://wordpress.org/plugins/essential-form/" target="_blank">Essential Form</a> has a very powerful inbuilt anti-spam system that will block all the robots. It will not be possible for robots to send you spam. Only humans will be able to do it manually.

Most of the time, in your **contact form** you just need the fields name, email, message, and an agreement checkbox. If you need more, this plugin is not for you. In that case install a **Contact Form** plugin like <a href="https://wordpress.org/plugins/contact-form-7/">Contact Form 7</a>.

<a href="https://wordpress.org/plugins/essential-form/">Essential Form</a> adds no HTTP requests. It consumes zero. When we say it's the **lightest contact form** we are not joking at all.

The entire zip of the plugin is 14 kB. On the page where you add the shortcode it inlines a very tiny script of pure Vanilla JavaScript that is around 1 kilobyte. On the pages where you have no forms, of course, it doesn't exist.

No jQuery, no libraries, nothing of this kind of bloated stuff.

The anti-spam system runs behind the scenes in a very clever way without the need for any annoying captcha or other similar systems which some times even fail.

Just to give you an idea of the security level of this plugin, the name of the Ajax action that manages the sending of the email is something like essential_form_fbe52b696, where the last part is different on every website.

A robot will never be able to guess the names of the functions that are involved during the process.

Morover, the submission is based on a random token that is always different at each form submission, and 20 random keys which are unique for every website.

All the anti-spam system works behind the scenes without disturbing the users, and most of all, it works.


== How to add a contact form on the page ==
* Add the shortcode [essential_form]
* Done!

== Features of the contanct form ==
* Extremely lightweight. It inlines 1 kb (when the document is compressed it will be even smaller) of pure Vanilla JavaScript only on the page where you add the shortcode, and never above the fold. On the other pages, it doesn't exist. We can say it exists on the page where you add the shortcode only because you see the **contact form**, in another case, it would be impossible to see that this plugin exists on that page. No tools will be able to measure consumption due to this plugin.
* The **contact form** will have only the fields name, email, message, and an agreement checkbox. Nothing else. If you need more, better you use a different plugin. If those fields are all that you need, you will love this plugin.
* It inherits the style of your theme. If you want a different style, you need to write your custom CSS or use a different plugin.
* It has a very powerful anti-spam system. It will be impossible for robots to send spam through the **contact form**. Only humans can send spam manually.
* No need for annoying captcha or similar systems that make visitors lose their nerves. The anti-spam system is behind the scenes, and it's very powerful.


== Shortcode parameters ==
* label_email
* label_message
* button_text
* agreement_text
* success_message

If assigned, the shortcode will look like [essential_form label_emal="Your email" lable_message="Your message" button_text="Send" agreement_text="You agree with our privacy policy" success_message="Thank you for your message!"]

If you don't assign the parameters of the shortcode, the plugin will take the default settings.


== How to customize the contact forms ==

You can also customize the contact forms throught the filter hook 'essential_form_settings'.

Here an example.

`
add_filter( 'essential_form_settings',function( $options ){
    return array(
        'email_from' => 'youremail@mail.com',
        'email_to' => 'youremail@mail.com',
        'email_subject' => sprintf( esc_html__( 'Message from %s','your-domain' ),get_bloginfo( 'name' ) ),
        'label_name' => __( 'Name','your-domain' ),
        'label_email' => __( 'Email','your-domain' ),
        'label_message' => __( 'Message','your-domain' ),
        'button_text' => __( 'Send','your-domain' ),
        'agreement_text' => __( 'By submitting this form I agree with the privacy policy','your-domain' ),
        'success_message' => __( 'Form submitted successfully! Thank you for your message!','your-domain' ),
        'name_missing_error' => __( 'Name is a required field!','your-domain' ),
        'email_missing_error' => __( 'Email is a required field!','your-domain' ),
        'email_not_valid_error' => __( 'Email not valid!','your-domain' ),
        'message_missing_error' => __( 'Message is a required field!','your-domain' ),
        'message_too_long_error' => __( 'This message is too long! Please, write not more than 50000 characters.','your-domain' ),
        'missing_agreement_error' => __( 'You have to agree with our privacy policy to submit the form.','your-domain' )
    );
} );
`

If you need to do a custom action after the sending of the email, you can use the action hook 'essential_form_after_sending'.

Here an example.

`
add_action( 'essential_form_after_sending',function( $name,$email,$message,$post_id ){

    //$name is the name of the user who submitted the contant form
    //$message is the message which is sent through the contact form
    //$post_id is the ID of the page where is included the contact form

    //Your code here

},10,4 );
`

If you need to customize the message that is included in the email, use the filter hook 'essential_form_message'.

Here you have an example.

`
add_filter('essential_form_message',function( $message,$name,$email,$post_id ){
    if( isset( $_SERVER['REMOTE_ADDR'] ) ){
        $message .= '<p>IP: '.sanitize_text_field( $_SERVER['REMOTE_ADDR'] ).'</p>';
    }
    return $message;
},10,4 );
`

If you need to customize the agreement text, use the filter hook 'essential_form_agreement_text'.

Here you have an example.

`
add_filter( 'essential_form_agreement_text',function( $text ){
	return 'By submitting this form I agree with the <a href="https://yourdomain.com/privacy-policy/">Privacy Policy</a>';
} );
`


== Limitations ==
The limits of <a href="https://wordpress.org/plugins/essential-form/">Essential Form</a> are many, but they are what make this plugin the best if you need a ultra-lightweight contact form with just name, email, comment, and privacy agreement.
If you need more, you can always install more complete but also heavier contact forms like:

<a href="https://wordpress.org/plugins/contact-form-7/">Contact Form 7</a>
<a href="https://wordpress.org/plugins/wpforms-lite/">WPForms</a>
<a href="https://wordpress.org/plugins/forminator/">Forminator</a>
<a href="https://wordpress.org/plugins/formidable/">Formidable Forms</a>
<a href="https://wordpress.org/plugins/ninja-forms/">Ninja Forms</a>

and many other amazing plugins for contact forms.


== How to speed up the form submission and avoid conflicts with other plugins ==
* Install and activate <a href="https://wordpress.org/plugins/freesoul-deactivate-plugins/">Freesoul Deactivate Plugins</a>
* Go to Freesoul Deactivate Plugins => Plugin Manger => Actions => Essential Form
* Deactivate all the plugins for the actions "Getting secret key during submission" and "Form submission"

By using <a href="https://wordpress.org/plugins/freesoul-deactivate-plugins/">Freesoul Deactivate Plugins</a> to clean up all the other plugins, the form submission will be faster and without any conflict with third plugins.



== Demo ==
You can see <a href="https://wordpress.org/plugins/essential-form/" target="_blank">Essential Form</a> in action on my blog post <a href="https://josemortellaro.com/the-lightest-contact-form-plugin-ever/" target="_blank">The Lightest Contact Form Plugin Ever</a>
You don't need any demo for the backend, because there are no settings for this plugin. Just use the shortcode [essential_form] where you want to add the form, and customized as mentioned in the description.

== Changelog ==

= 0.0.8 =
* Added: translated in French. Many thanks to @queertimes for the translation.
* Fixed: translation files not loaded from the WordPress language directory.

= 0.0.7 =
* Added: translated in German. Many thanks to @cutu234 for the translation.
* Added: possibility to remove only the agreement checkbox without removing the text by using the filter add_filter( 'essential_form_agreement_checkbox_required', '__return_empty_string' );

= 0.0.6 =
* Added: possibility to remove the agreement checkbox by adding define( 'ESSENTIAL_FORM_ASK_FOR_AGREEMENT', false ); in wp-config.php

= 0.0.5 =
* Improved: increased the expiration time of the random key

= 0.0.4 =
* Added: integration with Freesoul Deactivate Plugins to unload all the other plugins during the form submission

= 0.0.3 =
* Fix: warning on activation

= 0.0.2 =
* Added: allowed link in the privacy aggreement chcekbox
* Translated in Italian

= 0.0.1 =
* Initial release
