<?php 

/**
 * bp_media_upload_photo function.
 * 
 * @access public
 * @return void
 */
function bp_media_upload_photo() {

	check_ajax_referer('photo-upload');
	
	// upload file
	$file = $_FILES['async-upload'];
	$status = wp_handle_upload( $file, array( 'test_form' => true, 'action' => 'photo_gallery_upload' ) );
	
	// output the results to console
	echo 'Uploaded to: ' . $status['file'];
	
	$wp_upload_dir = wp_upload_dir();
	
	//Adds file as attachment to WordPress
	$attachment = array(
		'guid'           	=> $wp_upload_dir['url'] . '/' . basename( $status['file'] ), 
		'post_mime_type' 	=> $status['type'],
		'post_title' 		=> preg_replace( '/\.[^.]+$/', '', basename( $status['file'] ) ),
		'post_content' 		=> '',
		'post_status' 		=> 'inherit'
	);
	$attach_id = wp_insert_attachment( $attachment, $status['file'], (int) $_POST['gallery_id'] );
	
	// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
	require_once( ABSPATH . 'wp-admin/includes/image.php' );
	
	// Generate the metadata for the attachment, and update the database record.
	$attach_data = wp_generate_attachment_metadata( $attach_id, $status['file'] );
	wp_update_attachment_metadata( $attach_id, $attach_data );
	
	update_post_meta( $attach_id, 'title', $_POST['title'] );
	update_post_meta( $attach_id, 'description', $_POST['description'] );
	
	// output the results to console
	echo "\n Attachment ID: " . $attach_id;
	
	
	exit;
}
add_action('wp_ajax_photo_gallery_upload', 'bp_media_upload_photo' );


/**
 * bp_media_get_image function.
 * 
 * @access public
 * @return void
 */
function bp_media_get_image(){
	
	$photo_id =  $_GET['id'];
	$guid =  $_GET['guid'];
	$user_id =  $_GET['user'];
	
	$user = get_user_by( 'id', (int) $user_id );
	
	include( bp_media_get_template_part( 'single/image') );
	
	die();
}
add_action('wp_ajax_bp_media_get_image', 'bp_media_get_image');
add_action('wp_ajax_nopriv_bp_media_get_image', 'bp_media_get_image');



/**
 * bp_media_add_album function.
 * 
 * @access public
 * @return void
 */
function bp_media_add_album(){
	
	include_once( bp_media_get_template_part( 'single/add-album') );
	
	die();
}
add_action('wp_ajax_bp_media_add_album', 'bp_media_add_album');
//add_action('wp_ajax_nopriv_bp_media_add_album', 'bp_media_add_album');



/**
 * bp_media_ajax_create_album function.
 * 
 * @access public
 * @return void
 */
function bp_media_ajax_create_album(){

	check_ajax_referer( 'create-album', 'nonce' );

	$title =  $_GET['title'];
	$content =  $_GET['description'];
	$user_id =  $_GET['user_id'];

	// Create post object
	$my_post = array(
	  'post_title'    => sanitize_text_field( $title ),
	  'post_content'  => sanitize_text_field( $content ),
	  'post_status'   => 'publish',
	  'post_author'   => (int) $user_id,
	  'post_type' => 'bp_media'
	);
	
	// Insert the post into the database
	$post = wp_insert_post( $my_post );	
	
	// return album link
	$data = array(
		'url' =>  bp_core_get_user_domain( $user_id ) . BP_MEDIA_SLUG . '/album/' . $post . '?new=true'
	);
	
	
	wp_send_json( $data );

}
add_action('wp_ajax_bp_media_ajax_create_album', 'bp_media_ajax_create_album');
//add_action('wp_ajax_nopriv_bp_media_ajax_create_album', 'bp_media_ajax_create_album');



/**
 * bp_media_ajax_edit_album function.
 * 
 * @access public
 * @return void
 */
function bp_media_ajax_edit_album(){

	check_ajax_referer( 'edit-album', 'nonce' );

	$title =  $_GET['title'];
	$content =  $_GET['description'];
	$user_id =  $_GET['user_id'];
	$post_id =  $_GET['post_id'];
	
	// Update post
	$my_post = array(
	  'ID'           => (int) $post_id,
	  'post_title'    => sanitize_text_field( $title ),
	  'post_content'  => sanitize_text_field( $content ),
	);
	
	// Update the post into the database
	$post = wp_update_post( $my_post );
	
	// return post id
	$data = array(
		'url' =>  bp_core_get_user_domain( $user_id ) . BP_MEDIA_SLUG . '/album/' . $post . '/edit'
	);
	
	wp_send_json( $data );

}
add_action('wp_ajax_bp_media_ajax_edit_album', 'bp_media_ajax_edit_album');
//add_action('wp_ajax_nopriv_bp_media_ajax_edit_album', 'bp_media_ajax_edit_album');



/*
 * bp_media_ajax_delete_album function.
 * 
 * @access public
 * @return void
 */
function bp_media_ajax_delete_album(){

	check_ajax_referer( 'edit-album', 'nonce' );

	$user_id =  $_GET['user_id'];
	$post_id =  $_GET['post_id'];
		
	// delete the post
	wp_delete_post( (int) $post_id, true );
	
	// return post id
	$data = array(
		'url' =>  bp_core_get_user_domain( $user_id ) . BP_MEDIA_SLUG
	);
	
	
	wp_send_json( $data );

}
add_action('wp_ajax_bp_media_ajax_delete_album', 'bp_media_ajax_delete_album');
//add_action('wp_ajax_nopriv_bp_media_ajax_delete_album', 'bp_media_ajax_delete_album');


function bp_media_ajax_delete_image(){

	check_ajax_referer( 'edit-album', 'nonce' );

	$user_id =  $_GET['user_id'];
	$image_id =  $_GET['image_id'];
		
	// delete the post
	wp_delete_attachment( (int) $image_id, true );
	
	// return post id
	$data = array(
		'id' =>  $image_id
	);
	
	
	wp_send_json( $data );

}
add_action('wp_ajax_bp_media_ajax_delete_image', 'bp_media_ajax_delete_image');
//add_action('wp_ajax_nopriv_bp_media_ajax_delete_image', 'bp_media_ajax_delete_image');


function bp_media_ajax_add_comment(){

	check_ajax_referer( 'add-comment', 'nonce' );

	$user_id =  $_GET['user_id'];
	$post_id =  $_GET['post_id'];
	$comment =  $_GET['upload_comment'];
	
	if( empty( $comment ) ) return;
		
	$time = current_time('mysql');
	
	$data = array(
	    'comment_post_ID' => $post_id,
	    'comment_author' => '',
	    'comment_author_email' => '',
	    'comment_author_url' => '',
	    'comment_content' => $comment,
	    'comment_type' => '',
	    'comment_parent' => 0,
	    'user_id' => $user_id,
	    'comment_author_IP' => '',
	    'comment_agent' => '',
	    'comment_date' => $time,
	    'comment_approved' => 1,
	);
	
	$comment_id = wp_insert_comment( $data );
	$comment = array( get_comment( $comment_id ) );
	
    wp_list_comments(array(
    	'type' => 'comment',
    	'callback' => 'bp_media_comments',
        'per_page' => 10, //Allow comment pagination
        'reverse_top_level' => false
    ), $comment );
	
	die();
	
}
add_action('wp_ajax_bp_media_ajax_add_comment', 'bp_media_ajax_add_comment');
//add_action('wp_ajax_nopriv_bp_media_ajax_add_comment', 'bp_media_ajax_add_comment');