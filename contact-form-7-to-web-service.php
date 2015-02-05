<?php
/*
 * Plugin Name: Contact Form 7 to Web Service
 * Version: 1.0
 * Plugin URI: http://mathieuforest.ca/
 * Description:  This plugin help you mapping data extract from contact form 7 and send to a web service with HTTP POST method.
 * Author: Mathieu Forest
 * Author URI: http://mathieuforest.ca/
Contact form 7 to Web Services. You need to install Dynamic hidden text field plugin. Add those shortcodes: [dynamichidden url-to-web-service hiddendefault "URL"] and [dynamichidden credentials hiddendefault "username:password"]. And map your fields directly in the file. Their is no web interface for now.
 */

add_action('wpcf7_before_send_mail', 'wpcf7_to_web_service');
function wpcf7_to_web_service ($WPCF7_ContactForm) {
	
	$submission = WPCF7_Submission::get_instance();
	$posted_data =& $submission->get_posted_data();	   

	if(isset($posted_data['url-to-web-service'])) {
				
		// Extract data form the mail an map it
		$mapped_field = array();
		$mapped_field['originalId'] = microtime();
		$mapped_field['listId'] = $posted_data['carmalistid'];
		$mapped_field['firstName'] = $posted_data['fname'];
		$mapped_field['lastName'] = $posted_data['lname'];
		$mapped_field['emailAddress'] = $posted_data['email'];
		$mapped_field['active'] = true;
		$mapped_field['title'] = $posted_data['function'];
		$mapped_field['country'] = $posted_data['country'];
		$mapped_field['properties']['2258'] = $posted_data['site'];
		$mapped_field['properties']['1703'] = $posted_data['phone'];
		$mapped_field['properties']['1577'] = $posted_data['ext'];
		$mapped_field['properties']['1574'] = $posted_data['company'];
		$mapped_field['properties']['1603'] = $posted_data['province-state'];
		$mapped_field['properties']['1604'] = $posted_data['industry'];
		$mapped_field['properties']['2259'] = $posted_data['found-us'];
		$mapped_field['properties']['2271'] = $posted_data['turnover'];
		
		$mapped_field['properties']['2275'] = $posted_data['blog'];
		$mapped_field['properties']['2273'] = $posted_data['news'];
		$mapped_field['properties']['2272'] = $posted_data['promotions'];
		$mapped_field['properties']['2274'] = $posted_data['publications'];

			
		$json_mapped_fields = json_encode($mapped_field);
			
		$url = $posted_data['url-to-web-service']; // API URL
				
		$response = wp_remote_post( $url, array(
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( $posted_data['credentials'] ), // Put API Credential
				'Content-Type' => 'application/json'
			),
			'body' => $json_mapped_fields,
			'cookies' => array()
		));
		
		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
		   
			$to = '';// Put email to to Debug
			$subject = 'Error submitting request';
			$message = '';
			$message = 'json request: ' . implode($json_mapped_fields);	  
			$message .= 'error message request: ' . implode($error_message);
			$headers = 'From: email@address.com' . "\r\n" .
			'Reply-To: email@address.com' . "\r\n" .
			'X-Mailer: PHP/' . phpversion();
		
			 //mail($to, $subject, $message, $headers); // Uncomment to Debug
		   
		} else {			
		     
			 $to = '';// Put email to to Debug
			 $subject = 'Response from third party';
			 $message = '';
			 $message .= 'json request: ' . $json_mapped_fields . "\r\n" ;
			 foreach( $response as $respons ) {
  				$message .= implode("\n", $respons) . "\r\n" ;
			 }
			 $message .= $response['body'];
			 $headers = 'From: email@address.com' . "\r\n" .
			 'Reply-To: email@address.com' . "\r\n" .
			 'X-Mailer: PHP/' . phpversion();
			 //mail($to, $subject, $message, $headers); // Uncomment to Debug
			 
		}	
	}
}
?>
