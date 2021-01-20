/*
l4s.me link shortening module
PHP Snippet to be used make compatible shortlinks
© 2020 Jason Chua, Connor Coddington

Changelog:
07.2020 - Initial Creation
01.2021 - Proper Shortlink Hooks
*/
function l4s_shortlink_hook() {

    // Post object if needed
    // global $post;

    // Page conditional if needed
    // if( is_page() ){}
     $a = l4s_get_shortlink( $id = 0, $context = 'post', true );
     $b = l4s_get_shortlink( $id = 0, $context = 'post', false );
     ?>

    <link rel="shortlink" type="text/html" href="<?php  echo $a ?>">

     <?php
     if (strcmp($a, $b) != 0) {
          ?>

               <link rel="shortlink" type="text/html" href="<?php  echo $b ?>">
      
          <?php
     }

}
add_action( 'wp_head', 'l4s_shortlink_hook' );

if ( !function_exists( 'l4s_encode' ) ) {
	function l4s_encode( $num ) {
		$index = '23456789abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
          $out = "";

		for ( $t = floor( log10( $num ) / log10( 56 ) ); $t >= 0; $t-- ) {
			$a = floor( $num / pow( 56, $t ) );
			$out = $out . substr( $index, $a, 1 );
			$num = $num - ( $a * pow( 56, $t ) );
		}

		return $out;
	}
}

//Splits between different types of links and returns completed shortlink
//Borrows from https://github.com/Automattic/jetpack/blob/master/modules/shortlinks.php
function l4s_get_shortlink( $id = 0, $context = 'post', $allow_slugs = true ) {
	global $wp_query;

	if ( 'query' == $context ) {
		if ( is_singular() ) {
			$id = $wp_query->get_queried_object_id();
			$context = 'post';
		} elseif ( is_front_page() ) {
			$context = 'blog';
		} else {
			return '';
		}
	}

     //Homepage
	if ( 'blog' == $context ) {
		return 'https://l4s.me/' . l4s_encode( $id );
	}

	$post = get_post( $id );

	if ( empty( $post ) )
			return '';

	$post_id = $post->ID;
	$type = '';

	if ( 
          $allow_slugs && 
          'publish' == $post->post_status && 
          //'post' == $post->post_type && 
          false === strpos( $post->post_name, '%')
          ) {
		$id = $post->post_name;
        return 'https://l4s.me/' . $id;
	} else {
		$id = l4s_encode( $post_id );
          if ( 'page' == $post->post_type )
               //Page
               $type = 'P';
		elseif ( 'post' == $post->post_type || post_type_supports( $post->post_type, 'shortlinks' ) )
               //Article
               $type= 'A';
          elseif ( 'attachment' == $post->post_type )
               //Media
			$type = 'M';
	}

	if ( empty( $type ) )
		return '';
	//Final shortlink
	return 'https://l4s.me/' . $type . $id;
}

//Override the shortlink wordpress style
add_filter( 'pre_get_shortlink', 'l4s_get_shortlink_handler', 10, 4 );

function l4s_get_shortlink_handler( $shortlink, $id, $context, $allow_slugs ) {
	return l4s_get_shortlink( $id, $context, $allow_slugs );
}
