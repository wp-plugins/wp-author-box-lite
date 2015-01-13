<?php
/*##################################
	AUTHOR BOX DISPLAY
################################## */

class wpautbox_display{
	public $post;
	public $authordata;
	public $settings;

	function __construct(){
		global $post;
		global $authordata;
		global $settings;
		global $show_in;

		add_filter( 'the_content', array($this, 'build_content'), 10 );
		add_action( 'wp_enqueue_scripts', array($this, 'enqueue') );
		add_action('wp_head', array($this, 'wpautbox_wp_head'));
		add_action('wp_head', array($this, 'facebook_author_meta'));
		//add_action('init', array($this, 'register_shortcodes')); go pro for this option
	}

	function enqueue(){
		global $wpautbox_lite;
		$this->settings = $wpautbox_lite;

		wp_enqueue_style( 'css-wpautbox-tab', plugins_url( 'lib/css/jquery-a-tabs.css' , dirname(__FILE__) ) , array(), null );
		wp_enqueue_style( 'css-wpautbox', plugins_url( 'lib/css/wpautbox.css' , dirname(__FILE__) ) , array(), null );

		wp_register_style(
            'wpautbox-elusive-icon',
            plugins_url( 'includes/ReduxFramework/ReduxCore/assets/css/vendor/elusive-icons/elusive-webfont.css' , dirname(__FILE__) ),
            array(),
            '',
            'all'
        );

        wp_register_style(
            'wpautbox-elusive-icon-ie7',
            plugins_url( 'includes/ReduxFramework/ReduxCore/assets/css/vendor/elusive-icons/elusive-webfont-ie7.css' , dirname(__FILE__) ),
            array(),
            '',
            'all'
        );

		wp_register_script(
			'jquery-wpautbox-tab',
			plugins_url( 'lib/js/jquery.a-tab.js' , dirname(__FILE__) ),
			array( 'jquery' ),
			'',
			true
		);
		wp_register_script(
			'jquery-wpautbox-pro',
			plugins_url( 'lib/js/jquery.wpautbox.js' , dirname(__FILE__) ),
			array( 'jquery' ),
			'',
			true
		);

		wp_enqueue_script('jquery-wpautbox-tab');
		wp_enqueue_script('jquery-wpautbox-pro');
		wp_enqueue_style( 'wpautbox-elusive-icon' );
		wp_enqueue_style( 'wpautbox-elusive-icon-ie7' );
	}

	function build_content($content){
		$return = $content;
			if( ( is_single() && in_the_loop() ) || is_page() && in_the_loop() ){
			global $post, $authordata, $wpautbox_lite;
			$this->post = $post;
			$this->authordata = $authordata;
			$this->settings = $wpautbox_lite;
			$this->allowed_post_types();

			 //initialize return element
			if( isset($this->show_in[ $this->post->post_type ]) && 'no' != $this->show_in[ $this->post->post_type ] ){
				switch ( $this->show_in[ $this->post->post_type ] ) {
					case 'above':
						$return = $this->construct_authorbox('above', $this->authordata->ID);
						$return .= $content;
						break;

					case 'below':
						$return = $content;
						$return .= $this->construct_authorbox('below', $this->authordata->ID);
						break;

					case 'both':
						$return = $this->construct_authorbox('above', $this->authordata->ID);
						$return .= $content;
						$return .= $this->construct_authorbox('below', $this->authordata->ID);
						break;

					default:
						$return = $content;
						break;
				}
			}
		}
		return $return;
	}

	function allowed_post_types(){
		$allowed = array();
		$get_cpt_args = array(
			'public'   => true,
		); 
		$custom_post_types = get_post_types( $get_cpt_args, 'names', 'and');
		if(!empty($custom_post_types)):
			foreach( $custom_post_types as $type ){
				$custom_post_type_object = get_post_type_object( $type );
				$key = 'show_in_' . $custom_post_type_object->name;
				if( array_key_exists($key, $this->settings) ){
					$allowed[ $custom_post_type_object->name] = $this->settings[ $key ];
				}
			}
		$this->show_in = $allowed; 
		endif;
	}

	/**
	 * Add Author Box Tabs
	 *
	 * @since 1.0
	 */
	function navtabs($tabs = array()){
		$html = '<ul class="a-tab-nav">';
			if( ( isset($tabs['hide']) && !in_array('about_tab', $tabs['hide']) ) ){
				$html .= '<li class="a-tab-active"><a href="#wpautbox_about"><i class="el-icon-user wpautbox-icon"></i> '. __( $this->settings['about-tab-label'] , 'wpautbox') .'</a></li>';
			}
			if( ( isset($tabs['hide']) && !in_array('latest_tab', $tabs['hide']) ) ){
				$html .= '<li><a href="#wpautbox_latest-post"><i class="el-icon-list wpautbox-icon"></i> '. __($this->settings['latest-tab-label'], 'wpautbox') .'</a></li>';
			}
		$html .= '</ul>';

		return $html;
	}

	/**
	 * Add Author Box Contents
	 *
	 * @since 1.0
	 */
	function nav_contents($tabs = array()){
		if(!isset($tabs['authorid']) || empty($tabs['authorid'])){
			$tabs['authorid'] = $this->authordata->ID;
		}
		$html = '<div class="a-tab-container">';
			if( ( isset($tabs['hide']) && !in_array('about_tab', $tabs['hide']) ) ){
				$html .= '<div class="a-tab-content" id="wpautbox_about">';
					$html .= $this->authorbio($tabs['authorid']);
				$html .= '</div>';
			}
			if( ( isset($tabs['hide']) && !in_array('latest_tab', $tabs['hide']) ) ){
				$html .= '<div class="a-tab-content" id="wpautbox_latest-post">';
					$html .= $this->custom_tab('post_type', array('post_type' => 'post', 'authorid' => $tabs['authorid']));
				$html .= '</div>';
			}
		$html .= $this->build_socials( $tabs['authorid'] );
		$html .= '</div>';

		return $html;
	}

	/**
	 * Author Bio
	 *
	 * @since 1.0
	 */
	function authorbio($authorid){
		if(!empty($authorid)){
			if(is_array($authorid)){
				$authorid = $authorid['authorid'];
			}
			$html = '';
			$author = get_userdata( $authorid );
			$wpautbox_meta = get_user_meta( $authorid, 'wpautbox_user_fields', false );
			if(isset($wpautbox_meta[0])){
				$wpautbox_meta = unserialize( base64_decode($wpautbox_meta[0]) );
			}
			
			$avatar = get_avatar( $authorid, 80 );
			if(!empty($avatar)){
				$html .= '<div class="wpautbox-avatar">';
					$html .= $avatar;
				$html .= '</div>';
			}

			$html .= '<div class="wpautbox-author-meta">';
				$html .= '<h4 class="wpautbox-name">'. __('About', 'wpautbox') . ' ' . get_the_author_meta( 'display_name', $authorid )  .'</h4>';
				if(!empty($wpautbox_meta['user']['wpautbox_tagline'])){
					$html .= '<span class="wpautbox-tagline">'. __( $wpautbox_meta['user']['wpautbox_tagline'] , 'wpautbox') .'</span>';
				}
				$html .= get_the_author_meta('description', $authorid);
			$html .= '</div>';
			return $html;
		}
	}

	/**
	 * Build Custom Tabs for Content and Post Types
	 *
	 * @since 1.0
	 */
	function custom_tab($type, $custom){
		$html = '';
		switch ($type) {
			case 'html':
				//go pro for this content
				break;
			case 'post_type' :
				if( isset($custom['authorid']) ){
					$authorid = $custom['authorid'];
				}else{
					$authorid = $this->authordata->ID;
				}
				if(!empty($authorid) && isset( $custom['post_type'] )):
				 	$args = array( 
				 				'post_type'			=>	$custom['post_type'],
				 				'post_status'		=>	'publish',
				 				'author'			=>	$authorid,
				 				'posts_per_page' 	=>	$this->settings['latest-posts-num']
				 			);
				 	$the_query = new WP_Query( $args );
				 	// The Loop
					if ( $the_query->have_posts() ) {
					        $html .= '<ul class="wpautbox-post_type-list wpautbox-latest-'. $custom['post_type'] .'">';
						while ( $the_query->have_posts() ) {
							$the_query->the_post();
							$html .= '<li><a href="'. get_permalink( get_the_ID() ) .'">' . get_the_title() . '</a> <span class="wpautbox-date">- '. get_the_date() .'</span></li>';
						}
					        $html .= '</ul>';
					} else {
						$html = __('No Posts for this author.', 'wpautbox');
					}
				 	wp_reset_postdata();
			 	endif;
				break;
			default:
				# code...
				break;
		}
		return $html;
	}
	/**
	 * Build Social Media icons
	 *
	 * @since 1.0
	 */
	function build_socials( $authorid ){
		if(isset( $this->settings['socials'] ) && !empty($this->settings['socials']) && 1 == $this->settings['social_display']){
			$html = '<div class="wpautbox-socials wpautbox-socials-square wpautbox-socials-colored">';
			$wpautbox_meta = get_user_meta( $authorid, 'wpautbox_user_fields', false );
			$wpautbox_user_meta = array(); //initialize user meta variable
			if(!empty( $wpautbox_meta )){
				$wpautbox_meta = unserialize( base64_decode($wpautbox_meta[0]) );
				if(isset( $wpautbox_meta['user'] )){
					$wpautbox_user_meta = $wpautbox_meta['user'];
				}
			}
			foreach ($this->settings['socials'] as $key => $value) {
				if( isset($wpautbox_user_meta['socials'][$key]) && !empty($wpautbox_user_meta['socials'][$key]) && ( !empty($value) && '1' == $value )){
					$label = '';
					$classes = 'wpautbox-icon';
					if('googleplus' == $key){
						$html .= '<a rel="author" href="'. $wpautbox_user_meta['socials'][$key] .'?rel=author" target="_blank" class="wpautbox-google" data-toggle="tooltip" data-original-title="'. __('Google+', 'wpautbox' ) .'" ><span class="'. $classes .' wpautbox-icon-googleplus">'. $label .'</span></a> ';
					}else{
						$html .= '<a href="'. $wpautbox_user_meta['socials'][$key] .'" target="_blank" class="wpautbox-'. $key .'" data-toggle="tooltip" data-original-title="'. ucfirst( __($key, 'wpautbox') ) .'" ><span class="'. $classes .' wpautbox-icon-'. $key .'">'. $label .'</span></a> ';
					}
				}
			}
			$html .= '</div>';
		}

		return $html;
	}

	/**
	 * Build Author Box Tabs
	 *
	 * @since 1.0
	 */
	function construct_authorbox($position = 'below', $authorid, $hide = array(), $type = ''){
		$disabled = false;
		if(empty($type)){ // fix hidden tabs when not shortcode
			if(!$this->settings['show-about']){
				$hide[] = 'about_tab';
			}
			if(!$this->settings['show-latest-posts']){
				$hide[] = 'latest_tab';
			}
			if (isset($this->settings['tabs']) && !empty($this->settings['tabs'])) {
				foreach ($this->settings['tabs'] as $key => $value) {
					if($value['show_custom_tab'] != "1"){
						$hide[] = 'custom_tab_'. $key;
					}
				}
			}

			$wpautbox_meta = get_user_meta( $authorid, 'wpautbox_user_fields', false );
			$wpautbox_user_meta = array(); //initialize user meta variable
			if(!empty( $wpautbox_meta )){
				$wpautbox_meta = unserialize( base64_decode($wpautbox_meta[0]) );
				if(isset( $wpautbox_meta['user'] )){
					$wpautbox_user_meta = $wpautbox_meta['user'];
				}
			}
			if(!empty($wpautbox_user_meta) && isset($wpautbox_user_meta['disable']) && 'true' == $wpautbox_user_meta['disable']){
				$disabled = true;
			}
		}
		$html = '<div id="wpautbox-'. $position .'">';
			$html .= $this->navtabs( array( 'hide' => $hide ) );
			$html .= $this->nav_contents( array('authorid' => $authorid, 'hide' => $hide ) );
		$html .= '</div>';
		if(!$disabled){
			return $html;
		}
	}

	/**
     * Get the Open Graph for the current author
     * @since 1.1
     */
    function facebook_author_meta() {
    	global $post;
    	if(isset($post->post_author) && !empty($post->post_author )){
    		$wpautbox_meta = get_user_meta( $post->post_author , 'wpautbox_user_fields', false );
	    	$wpautbox_user_meta = '';
	    	if(!empty( $wpautbox_meta )){
				$wpautbox_meta = unserialize( base64_decode($wpautbox_meta[0]) );
				if(isset( $wpautbox_meta['user'] )){
					$wpautbox_user_meta = $wpautbox_meta['user'];
				}
			}
			if(!empty($wpautbox_user_meta) && !empty($wpautbox_user_meta['socials']['facebook']) && is_single() ){
				echo "\n" . '<meta property="article:author" content="' . $wpautbox_user_meta['socials']['facebook'] . '" />' . "\n";
			}
    	}
    }

	/**
	 * Add Appearance Options value on custom css
	 *
	 * @since 1.0
	 */
	function wpautbox_wp_head(){
		global $wpautbox_lite;
		$style = '<style type="text/css">';
		if(isset($wpautbox_lite['tab_shadow']) && 0 == $wpautbox_lite['tab_shadow']){
			$style .= 'body .a-tabs .a-tab-container, body .a-tabs>ul.a-tab-nav>li.a-tab-first,body .a-tabs>ul.a-tab-nav>li.a-tab-last{ -webkit-box-shadow: none; -moz-box-shadow: none; box-shadow: none; }';
		}
		//inactive tab
		if(isset($wpautbox_lite['inactive-color']) && !empty($wpautbox_lite['inactive-color']) &&'default' != $wpautbox_lite['inactive-color']){
			$style .= 'body .a-tabs>ul.a-tab-nav>li>a{ color: '. $wpautbox_lite['inactive-color'] .'; text-shadow: none;}';
		}
		if(isset($wpautbox_lite['inactive-bgcolor']) && !empty($wpautbox_lite['inactive-bgcolor']) &&'default' != $wpautbox_lite['inactive-bgcolor']){
			$style .= 'body .a-tabs>ul.a-tab-nav>li>a{ background: '. $wpautbox_lite['inactive-bgcolor'] .'; background-image: none;}';
		}
		if(isset($wpautbox_lite['inactive-bordercolor']) && !empty($wpautbox_lite['inactive-bordercolor']) &&'default' != $wpautbox_lite['inactive-bordercolor']){
			$style .= 'body .a-tabs>ul.a-tab-nav>li>a{ border-color: '. $wpautbox_lite['inactive-bordercolor'] .';}';
		}

		//active tab
		if(isset($wpautbox_lite['active-color']) && !empty($wpautbox_lite['active-color']) &&'default' != $wpautbox_lite['active-color']){
			$style .= 'body .a-tabs>ul.a-tab-nav>li.a-tab-active a{ color: '. $wpautbox_lite['active-color'] .'; text-shadow: none;}';
		}
		if(isset($wpautbox_lite['active-bgcolor']) && !empty($wpautbox_lite['active-bgcolor']) &&'default' != $wpautbox_lite['active-bgcolor']){
			$style .= 'body .a-tabs>ul.a-tab-nav>li.a-tab-active a{ background: '. $wpautbox_lite['active-bgcolor'] .'; background-image: none;}';
		}
		if(isset($wpautbox_lite['active-bordercolor']) && !empty($wpautbox_lite['active-bordercolor']) &&'default' != $wpautbox_lite['active-bordercolor']){
			$style .= 'body .a-tabs>ul.a-tab-nav>li.a-tab-active a{ border-color: '. $wpautbox_lite['active-bordercolor'] .';}';
		}

		//content tab
		if(isset($wpautbox_lite['tabcontent-color']) && !empty($wpautbox_lite['tabcontent-color']) &&'default' != $wpautbox_lite['tabcontent-color']){
			$style .= 'body .a-tabs .a-tab-container{ color: '. $wpautbox_lite['tabcontent-color'] .'; }';
		}
		if(isset($wpautbox_lite['tabcontent-bgcolor']) && !empty($wpautbox_lite['tabcontent-bgcolor']) &&'default' != $wpautbox_lite['tabcontent-bgcolor']){
			$style .= 'body .a-tabs .a-tab-container{ background: '. $wpautbox_lite['tabcontent-bgcolor'] .';}';
		}
		if(isset($wpautbox_lite['tabcontent-bordercolor']) && !empty($wpautbox_lite['tabcontent-bordercolor']) &&'default' != $wpautbox_lite['tabcontent-bordercolor']){
			$style .= 'body .a-tabs .a-tab-container{ border-color: '. $wpautbox_lite['tabcontent-bordercolor'] .';}';
		}

		//content tab
		if(isset($wpautbox_lite['tabfooter-bordercolor']) && !empty($wpautbox_lite['tabfooter-bordercolor']) &&'default' != $wpautbox_lite['tabfooter-bordercolor']){
			$style .= 'body .a-tabs .wpautbox-socials{ border-color: '. $wpautbox_lite['tabfooter-bordercolor'] .'; }';
		}
		if(isset($wpautbox_lite['tabfooter-bgcolor']) && !empty($wpautbox_lite['tabfooter-bgcolor']) &&'default' != $wpautbox_lite['tabfooter-bgcolor']){
			$style .= 'body .a-tabs .wpautbox-socials{ background: '. $wpautbox_lite['tabfooter-bgcolor'] .';}';
		}

		//social icons - go pro for this option

		$style .= '</style>';

		echo $style;
	}
	
}
$wpautbox_display = new wpautbox_display();
?>