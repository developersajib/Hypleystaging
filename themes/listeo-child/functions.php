<?php
add_action( 'wp_enqueue_scripts', 'listeo_enqueue_styles');
function listeo_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css',array('bootstrap','listeo-icons','listeo-woocommerce') );
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/css/cristian_style.css');
    //wp_enqueue_style( 'child-style-2', get_stylesheet_directory_uri() . '/css/sahil_style.css');

}


// add_action( 'wp_enqueue_scripts', 'listeo_cristian_behind_scripts', 9999);
add_action( 'wp_head', 'listeo_cristian_behind_scripts', 9999);
function listeo_cristian_behind_scripts() {
	//dequeue frontend js because send message with widget has error
	//wp_dequeue_script('listeo_core-frontend');
    //wp_deregister_script('listeo_core-frontend');
    //wp_register_script( 'listeo_core-frontend', get_stylesheet_directory_uri() . '/js/frontend.js', array( 'jquery' ));
	//wp_enqueue_script('listeo_core-frontend');

	// wp_dequeue_script('daterangerpicker');
 //    wp_deregister_script('daterangerpicker');
    // wp_register_script( 'daterangerpicker', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js', array( 'jquery','moment' ) );
	// wp_enqueue_script('daterangerpicker');

	wp_register_script( 'cristian_script', get_stylesheet_directory_uri() . '/js/cristian_script.js', array( 'jquery' ));
	//wp_register_script( 'counterup', get_stylesheet_directory_uri() . '/js/counterup.js', array( 'jquery' ));
	wp_register_script( 'cm_scripts', get_stylesheet_directory_uri() . '/js/cm_scripts.js', array( 'jquery' ), time());

	wp_enqueue_script('cristian_script');
	//wp_enqueue_script('counterup');
	wp_enqueue_script('cm_scripts');
}

function remove_parent_theme_features() {

}
add_action( 'after_setup_theme', 'remove_parent_theme_features', 10 );

function listing_category_slider(){
	$termArray = get_terms( array(
	    'taxonomy' => 'listing_category',
	    'hide_empty' => false,
	) );
	$html  = '<link rel="stylesheet" href="'.site_url() . '/wp-content/themes/listeo-child/css/flexslider.css"/>
				<script type="text/javascript" src="'.site_url(). '/wp-content/themes/listeo-child/js/jquery.flexslider-min.js"></script>
	<div class="flexslider">
  			<ul class="slides">';
	foreach ($termArray as $singleTerm) {
		$metaData = get_term_meta($singleTerm->term_id);
		$coverImageID = $metaData['_cover'][0];
		$coverImage = wp_get_attachment_image_src($coverImageID, array('784','500'));
		if ($coverImage) :
			$html .='<li><img src="'.$coverImage[0].'" /></li>';
		endif;
	}

	$html .='</ul></div>';
	$html .= '<script>
jQuery(window).load(function() {
  jQuery(".flexslider").flexslider({
    animation: "slide",
    controlNav: false
  });
});</script>';
	return $html;
}
add_shortcode( 'listing-category', 'listing_category_slider' );
if (!is_admin()) {
    add_filter( 'script_loader_tag', function ( $tag, $handle ) {
              if ( strpos( $tag, "jquery-migrate.min.js" ) || strpos( $tag, "jquery.min.js") ) {
            return $tag;
        }
        return str_replace( ' src', ' defer src', $tag );
    }, 10, 2 );

}

function mz_footer(){

	if(isset($_GET['page_id']) && $_GET['page_id'] == 71){

	?>

	<script>

		jQuery(document).ready(function(){

			setTimeout(function(){

				jQuery('p#_gallery-description').html('Photo are the first thing that guests will see. We recommend adding 10 or more high quality photos.<br>Photo requirments:<br><ul><li>High resolution - Atleast 1,000 pixels wide</li><li>Horizontal orientation - No vertical photos</li><li>Color photos - No block & white</li><li>Mics. - No collages, screenshots, No watermarks</li></ul>');

			},200);

		});

	</script>

<?php

	}

	if(is_page(66)){
	    ?>
	    <script>
	        jQuery(document).ready(function() {
	        	if(jQuery(".message-content").length){
				    jQuery(".message-content").animate({
				        scrollTop: jQuery('.message-content').get(0).scrollHeight
				    }, 2000);
				}
			});
	    </script>
	    <?php
	}
}

add_action('wp_footer','mz_footer');

function whero_limit_image_size($file) {

	// Calculate the image size in KB
	$image_size = $file['size']/1024;

	// File size limit in KB
	$limit = 200;

	// Check if it's an image
	$is_image = strpos($file['type'], 'image');

	if ( ( $image_size > $limit ) && ($is_image !== false) )
        	$file['error'] = 'Your picture is too large. It has to be smaller than '. $limit .'KB';

	return $file;

}
//add_filter('wp_handle_upload_prefilter', 'whero_limit_image_size');


add_action("widgets_init","register_unveryfie_siderbar");
    function register_unveryfie_siderbar()
    {
      register_sidebar(array(
      'name' => 'Single Unveryfie Listing Sidebar',
      'id' => 'single_unveryfie_siderbar',
      'before_widget' => '<div id="%1$s" class="widget %2$s">',
      'after_widget' => '</div>',
      'before_title' => '<h3>',
      'after_title' => '</h3>'
       ));
    }
// // keep users logged in for longer in wordpress
// function wcs_users_logged_in_longer( $expirein ) {
//     // 1 month in seconds
//     return 2628000;
// }
// add_filter( 'auth_cookie_expiration', 'wcs_users_logged_in_longer' );



/**
 * Load Core User Class.
 */
//require_once( get_stylesheet_directory(). '/inc/hypley-modify-listeo-core-users.php' );





add_action('fluentform_user_registration_completed', 'send_fluentform_email_after_registration_complete');

function send_fluentform_email_after_registration_complete($user_id){
	$code 				= sha1( $user_id . time() );

	global $wpdb;
	$wpdb->update(
		$wpdb->prefix . 'users',
		array(
			'user_activation_key' => $code,
			//'user_pass' => $hash_password
		),
		array( 'ID' => $user_id ),
		array(
			'%s',   // value1
		),
		array( '%d' )
	);


	update_user_meta($user_id, 'account_activated', 0);
	update_user_meta($user_id, 'activation_code', $code);

	if( function_exists('listeo_core_get_option') && get_option('listeo_submit_display',true) ) {
		 $login_url = get_permalink( listeo_core_get_option( 'listeo_profile_page' ) );
	} else {
		 $login_url = wp_login_url();
	}


	$user = get_user_by( 'id', $user_id );

	$mail_args = array(
		'email'         => $user->user_email,
		'login'         => $user->user_login,
		'password'      => $user->user_pass,
		'first_name' 	=> $user->first_name,
		'last_name' 	=> $user->last_name,
		'display_name' 	=> $user->display_name,
		'login_url' 	=> $login_url,
		'user_id'		=> $user_id,
		'code'			=> $code,
	);

	return hypley_welcome_mail($mail_args);
}


function hypley_welcome_mail($args){
	$email =  $args['email'];
	$code  =  $args['code'];
	$user_id = $args['user_id'];

	$activation_link = get_the_permalink(3438).'?key='.$code.'&user_id='.$user_id;

	$args = array(
		'user_mail'         => $email,
		'login'         => $args['login'],
		'password'      => $args['password'],
		'first_name' 	=> $args['first_name'],
		'last_name' 	=> $args['last_name'],
		'user_name' 	=> $args['display_name'],
		'user_mail' 	=> $email,
		'login_url' 	=> $args['login_url'],

		);

	$subject 	 = get_option('listeo_listing_welcome_email_subject','Welcome to {site_name}!');
	$subject 	 = hypley_replace_shortcode( $args, $subject );

	//$activation_link =  site_url().'/wp-content/webservices/registration-confirmation.php?key='.$code.'&user='.$user_id;

	$body 	 = get_option('listeo_listing_welcome_email_content','Welcome to {site_name}! You can log in {login_url}, your username: "{login}", and password: "{password}".');

	$body 	 = hypley_replace_shortcode( $args, $body );

	hypley_send( $email, $subject, $body, $activation_link  );
}


function hypley_send( $emailto, $subject, $body , $activation_link='', $reply_to=''){

	$from_name 	= get_option('listeo_emails_name',get_bloginfo( 'name' ));
	$from_email = get_option('listeo_emails_from_email', get_bloginfo( 'admin_email' ));
	$headers 	= sprintf( "From: %s <%s>\r\n Content-type: text/html; charset=UTF-8\r\n", $from_name, $from_email );
	if($reply_to != ''){
		$headers .='Reply-To: '.$reply_to.' <cristian@hypley.com>';
	}

	if( empty($emailto) || empty( $subject) || empty($body) ){
		return ;
	}

	$template_loader = new listeo_core_Template_Loader;
	ob_start();

		$template_loader->get_template_part( 'emails/header' ); ?>
		<tr>
			<td align="left" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 25px; padding-right: 25px; padding-bottom: 28px; width: 87.5%; font-size: 16px; font-weight: 400;
			padding-top: 28px;
			color: #666;
			font-family: sans-serif;" class="paragraph">
			<?php
				echo $body;
			?>
			<?php
				if($activation_link != '')
				{
					?>
						<p> Your Account Activation Link : <a href="<?php echo $activation_link; ?>">here</a></p>
						<p>If you are facing any problems with verifying link, try copying and pasting the below url to your browser</p>
						<p><?php echo $activation_link; ?></p>
					<?php
				}
			?>
			</td>
		</tr>
	<?php
		$template_loader->get_template_part( 'emails/footer' );
		$content = ob_get_clean();

	wp_mail( @$emailto, @$subject, @$content, $headers );

}



function hypley_replace_shortcode( $args, $body ) {

	$tags =  array(
		'user_mail' 	=> "",
		'user_name' 	=> "",
		'booking_date' => "",
		'listing_name' => "",
		'listing_url' => '',
		'listing_address' => '',
		'site_name' => '',
		'site_url'	=> '',
		'payment_url'	=> '',
		'expiration'	=> '',
		'dates'	=> '',
		'children'	=> '',
		'adults'	=> '',
		'user_message'	=> '',
		'tickets'	=> '',
		'service'	=> '',
		'details'	=> '',
		'login'	=> '',
		'password'	=> '',
		'first_name'	=> '',
		'last_name'	=> '',
		'login_url'	=> '',
		'sender'	=> '',
		'conversation_url'	=> '',
		'client_first_name' => '',
		'client_last_name' => '',
		'client_email' => '',
		'client_phone' => '',
		'billing_address' => '',
		'billing_postcode' => '',
		'billing_city' => '',
		'billing_country' => '',
		'price' => '',
		'expiring' => '',
	);
	$tags = array_merge( $tags, $args );

	extract( $tags );

	$tags 	 = array( '{user_mail}',
					  '{user_name}',
					  '{booking_date}',
					  '{listing_name}',
					  '{listing_url}',
					  '{listing_address}',
					  '{site_name}',
					  '{site_url}',
					  '{payment_url}',
					  '{expiration}',
					  '{dates}',
					  '{children}',
					  '{adults}',
					  '{user_message}',
					  '{tickets}',
					  '{service}',
					  '{details}',
					  '{login}',
					  '{password}',
					  '{first_name}',
					  '{last_name}',
					  '{login_url}',
					  '{sender}',
					  '{conversation_url}',
					'{client_first_name}',
					'{client_last_name}',
					'{client_email}',
					'{client_phone}',
					'{billing_address}',
					'{billing_postcode}',
					'{billing_city}',
					'{billing_country}',
					'{price}',
					'{expiring}',
					);

	$values  = array(   $user_mail,
						$user_name ,
						$booking_date,
						$listing_name,
						$listing_url,
						$listing_address,
						get_bloginfo( 'name' ) ,
						get_home_url(),
						$payment_url,
						$expiration,
						$dates,
						$children,
						$adults,
						$user_message,
						$tickets,
						$service,
						$details,
						$login,
						$password,
						$first_name,
						$last_name,
						$login_url,
						$sender,
						$conversation_url,
						$client_first_name,
						$client_last_name,
						$client_email,
						$client_phone,
						$billing_address,
						$billing_postcode,
						$billing_city,
						$billing_country,
						$price,
						$expiring,
	);

	$message = str_replace($tags, $values, $body);

	$message = nl2br($message);
	$message = htmlspecialchars_decode($message,ENT_QUOTES);

	return $message;
}


