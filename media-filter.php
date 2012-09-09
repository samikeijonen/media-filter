<?php
/**
 * Plugin Name: Media Filter
 * Plugin URI: http://foxnet.fi/en
 * Description: Media Filter adds image width and height, clickable author link and 'mine' link in Media Library (upload.php).
 * Version: 0.1
 * Author: Sami Keijonen
 * Author URI: http://foxnet.fi/en
 * Contributors: samikeijonen
 * Thanks: Justin Tadlock
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU 
 * General Public License version 2, as published by the Free Software Foundation.  You may NOT assume 
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without 
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package MediaFilter
 * @version 0.1
 * @author Sami Keijonen <sami.keijonen@foxnet.fi>
 * @copyright Copyright (c) 2012, Sami Keijonen
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/* Set up the plugin on the 'plugins_loaded' hook. */
add_action( 'plugins_loaded', 'media_filter_setup' );

/**
 * Plugin setup function.  Loads actions and filters to their appropriate hook.
 *
 * @since 0.1.0
 */
function media_filter_setup() {

	if( is_admin() ) {
	
		/* Load the translation of the plugin. */
		load_plugin_textdomain( 'media-filter', false, 'media-filter/languages' );

		// Add Sortable Width and Height Columns to the Media Library
		add_filter( 'manage_media_columns', 'media_filter_columns_register' );
		add_filter( 'manage_media_custom_column', 'media_filter_columns_display', 10, 2 );
		add_filter( 'manage_upload_sortable_columns', 'media_filter_columns_sortable' );
	
		// Add pdf mime type  
		add_filter( 'post_mime_types', 'media_filter_post_mime_types' );
	
		// Add 'mine' media
		add_filter( 'views_upload', 'media_filter_upload_views_filterable' );
	
	}
	
}

/*
 * Adding Width and Height columns
 *
 * @since 0.1.0
 */
function media_filter_columns_register( $columns ) {

	/* Add colums in media (upload.php). */
	$columns['my-author'] = __( 'Author', 'media-filter' );
	$columns['width'] = __( 'Width', 'media-filter' );
	$columns['height'] = __( 'Height', 'media-filter' );
	$date = $columns['date'];
	$comments = $columns['comments'];
	unset( $columns['date'] );
	unset( $columns['comments'] );
	$columns['comments'] = $comments; // make this column after author, width and height
	$columns['date'] = $date; // make this column after comments
	
	/* Remove original author. */
	unset( $columns['author'] );

	return $columns;
	
}


/*
 * Display the columns
 *
 * @since 0.1.0
 */
function media_filter_columns_display( $column_name, $post ) {
	
	/* Get metainfo from image. */
	$meta = wp_get_attachment_metadata( get_the_ID() );

	switch( $column_name ) {

		/* If displaying the 'width' column. */
		case 'width' :

			if ( !empty( $meta['width'] ) )
				echo $meta['width'];
			else
				echo __( '&nbsp;', 'media-filter' );

			break;

		/* If displaying the 'height' column. */
		case 'height' :
			
		if ( !empty( $meta['height'] ) )	
			echo $meta['height'];
		else
			echo __( '&nbsp;', 'media-filter' );

			break;
			
		/* If displaying the 'my-author' column. */
		case 'my-author' :
		
		printf( '<a href="%s">%s</a>',
			esc_url( add_query_arg( array( 'author' => get_the_author_meta( 'ID' ) ), 'upload.php' )),
			get_the_author()
		);
		
			break;

		/* Just break out of the switch statement for everything else. */
		default :
			break;
			
	}
		
}

/*
 * Registering columns as sortable
 *
 * @since 0.1.0
 */
function media_filter_columns_sortable( $columns ) {

    $columns['width'] = 'width';
    $columns['height'] = 'height';

    return $columns;
	
}

/*
 * Add pdf documents in mime types.
 *
 * @since 0.1.0
 */
function media_filter_post_mime_types( $post_mime_types ) {

	/* PDF is 'application/pdf', ZIP is 'application/zip'.  */

	$post_mime_types['application/pdf'] = array( __( 'PDFs', 'media-filter' ), __( 'Manage PDFs', 'media-filter' ), _n_noop( 'PDF <span class="count">(%s)</span>', 'PDFs <span class="count">(%s)</span>', 'media-filter' ) );
	$post_mime_types['application/zip'] = array( __( 'ZIPs', 'media-filter' ), __( 'Manage ZIPs', 'media-filter' ), _n_noop( 'ZIP <span class="count">(%s)</span>', 'ZIPs <span class="count">(%s)</span>', 'media-filter' ) );
	
	/* Return the $post_mime_types variable. */
	return $post_mime_types;

}

/*
 * Add 'Mine' media file after mime type. Hook is views_upload.
 *
 * @since 0.1.0
 */
function media_filter_upload_views_filterable( $views ) {

	//$class = ( isset( $_GET['author'] ) && $_GET['author'] == get_current_user_id() ) ? ' class="current"' : '';
	
	if ( isset( $_GET['author'] ) && $_GET['author'] == get_current_user_id() ) {
		
		/* Current class. */
		$class = ' class="current"';
		
		/* Remove 'current' class from all-link. */
		add_action( 'admin_footer', 'media_filter_footer_scripts', 20 );
		
	}
	else {
		$class = '';
	}

	$new_views = array(
		'mine-media' => sprintf( '<a %s href="%s">%s</a>', $class, esc_url( add_query_arg( 'author', get_current_user_id(), 'upload.php' ) ), __( 'Mine', 'media-filter' ) )
	);

	return array_merge( $new_views, $views );
	
}

/*
 * Remove 'current' class from all-link.
 *
 * @since 0.1.0
 */
function media_filter_footer_scripts() { ?>

<script type="text/javascript">
jQuery(document).ready(
	function() {
		jQuery( '.all a' ).removeClass('current');
	}
);
</script>

<?php }

?>