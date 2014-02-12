<?php

/**
	ReduxFramework Sample Config File
	For full documentation, please visit: https://github.com/ReduxFramework/ReduxFramework/wiki
**/

if ( !class_exists( "ReduxFramework" ) ) {
	return;
} 

if ( !class_exists( "Redux_Framework_wpauthbox_config" ) ) {
	/*
	 * Fix issue with custom post types
	 * Load custom config last
	 */
	add_action( 'init', 'wpauthbox_settings_init', 999 );
	function wpauthbox_settings_init()
	{
	    new Redux_Framework_wpauthbox_config();
	}

	class Redux_Framework_wpauthbox_config {

		public $args = array();
		public $sections = array();
		public $theme;
		public $ReduxFramework;

		public function __construct( ) {


			// Set the default arguments
			$this->setArguments();
			
			// Set a few help tabs so you can see how it's done
			$this->setHelpTabs();

			// Create the sections and fields
			$this->setSections();
			
			if ( !isset( $this->args['opt_name'] ) ) { // No errors please
				return;
			}
			
			$this->ReduxFramework = new ReduxFramework($this->sections, $this->args);
			

			// If Redux is running as a plugin, this will remove the demo notice and links
			//add_action( 'redux/plugin/hooks', array( $this, 'remove_demo' ) );
			
			// Function to test the compiler hook and demo CSS output.
			//add_filter('redux/options/'.$this->args['opt_name'].'/compiler', array( $this, 'compiler_action' ), 10, 2); 
			// Above 10 is a priority, but 2 in necessary to include the dynamically generated CSS to be sent to the function.

			// Change the arguments after they've been declared, but before the panel is created
			//add_filter('redux/options/'.$this->args['opt_name'].'/args', array( $this, 'change_arguments' ) );
			
			// Change the default value of a field after it's been set, but before it's been used
			//add_filter('redux/options/'.$this->args['opt_name'].'/defaults', array( $this,'change_defaults' ) );

			// Dynamically add a section. Can be also used to modify sections/fields
			add_filter('redux/options/'.$this->args['opt_name'].'/sections', array( $this, 'dynamic_section' ) );
			add_action('redux/options/' . $this->args['opt_name'] . '/validate',  array( $this, "on_redux_save" ) );
			add_action('redux/options/' . $this->args['opt_name'] . '/saved',  array( $this, "on_redux_save" ) );
			do_action( "redux-saved-{$this->args['opt_name']}", array( $this, "on_redux_save" ) );
			//add entypo font
			add_action( 'redux/page/'. $this->args['opt_name'] .'/enqueue', array( $this, 'wpautbox_entypo' ) );
		}


		/**

			This is a test function that will let you see when the compiler hook occurs. 
			It only runs if a field	set with compiler=>true is changed.

		**/

		function compiler_action($options, $css) {
			echo "<h1>The compiler hook has run!";
			//print_r($options); //Option values
			
			// print_r($css); // Compiler selector CSS values  compiler => array( CSS SELECTORS )
			/*
			// Demo of how to use the dynamic CSS and write your own static CSS file
		    $filename = dirname(__FILE__) . '/style' . '.css';
		    global $wp_filesystem;
		    if( empty( $wp_filesystem ) ) {
		        require_once( ABSPATH .'/wp-admin/includes/file.php' );
		        WP_Filesystem();
		    }

		    if( $wp_filesystem ) {
		        $wp_filesystem->put_contents(
		            $filename,
		            $css,
		            FS_CHMOD_FILE // predefined mode settings for WP files
		        );
		    }
			*/
		}

		/*
		 * Add Entypo Icon
		 */
		function wpautbox_entypo() {
			// Uncomment this to remove elusive icon from the panel completely
			//wp_deregister_style( 'redux-elusive-icon' );
			//wp_deregister_style( 'redux-elusive-icon-ie7' );

		    wp_register_style(
		        'wpautbox-entypo',
		        plugins_url( '../lib/fonts/entypo.css' , __FILE__ ),
		        array(),
		        time(),
		        'all'
		    );	
		    wp_enqueue_style( 'wpautbox-entypo' );
		}



		/**
		 
		 	Custom function for filtering the sections array. Good for child themes to override or add to the sections.
		 	Simply include this function in the child themes functions.php file.
		 
		 	NOTE: the defined constants for URLs, and directories will NOT be available at this point in a child theme,
		 	so you must use get_template_directory_uri() if you want to use any of the built in icons
		 
		 **/

		function dynamic_section($sections){
		    //$sections = array();
		    $sections[] = array(
		        'title' => __('Section via hook', 'wpautbox'),
		        'desc' => __('<p class="description">This is a section created by adding a filter to the sections array. Can be used by child themes to add/remove sections from the options.</p>', 'redux-framework-demo'),
				'icon' => 'el-icon-paper-clip',
				    // Leave this as a blank section, no options just some intro text set above.
		        'fields' => array()
		    );

		    return $sections;
		}
		
		
		/**

			Filter hook for filtering the args. Good for child themes to override or add to the args array. Can also be used in other functions.

		**/
		
		function change_arguments($args){
		    //$args['dev_mode'] = true;
		    
		    return $args;
		}
			
		
		/**

			Filter hook for filtering the default value of any given field. Very useful in development mode.

		**/

		function change_defaults($defaults){
		    $defaults['str_replace'] = "Testing filter hook!";
		    
		    return $defaults;
		}


		// Remove the demo link and the notice of integrated demo from the redux-framework plugin
		function remove_demo() {
			
			// Used to hide the demo mode link from the plugin page. Only used when Redux is a plugin.
			if ( class_exists('ReduxFrameworkPlugin') ) {
				remove_filter( 'plugin_row_meta', array( ReduxFrameworkPlugin::get_instance(), 'plugin_meta_demo_mode_link'), null, 2 );
			}

			// Used to hide the activation notice informing users of the demo panel. Only used when Redux is a plugin.
			remove_action('admin_notices', array( ReduxFrameworkPlugin::get_instance(), 'admin_notices' ) );	

		}


		public function setSections() {

			/**
			 	Used within different fields. Simply examples. Search for ACTUAL DECLARATION for field examples
			 **/



			// ACTUAL DECLARATION OF SECTIONS

			//get custom post type
		 	$get_cpt_args = array(
				'public'   => true,
				'_builtin' => false
			); 
			$custom_post_types = get_post_types( $get_cpt_args, 'names', 'and'); 
			$section_post_type = array();
			$post_types = array( 'post' => 'Posts', 'page' => 'Pages' );

			$section_post_type[] = array(
									'id'=>'show_in_post',
									'type' => 'select',
									'title' => __('Show in Posts', 'wpautbox'), 
									'options' => array('no'=>'No', 'above'=>'Above', 'below'=>'Below', 'both'=>'Both'),
									'default' => 'below',
									);
			$section_post_type[] = array(
									'id'=>'show_in_page',
									'type' => 'select',
									'title' => __('Show in Pages', 'wpautbox'), 
									'options' => array('no'=>'No', 'above'=>'Above', 'below'=>'Below', 'both'=>'Both'),
									'default' => 'no',
									);
			if(!empty($custom_post_types)):
				foreach ( $custom_post_types  as $custom_post_type ) {
					$custom_post_type_object = get_post_type_object( $custom_post_type );
					$section_post_type[] = array(
						'id'=>'show_in_' . $custom_post_type_object->name,
						'type' => 'select',
						'title' => __('Show in '. $custom_post_type_object->label, 'wpautbox'), 
						'options' => array('no'=>'No', 'above'=>'Above', 'below'=>'Below', 'both'=>'Both'),
						'default' => 'no',
						);
					$post_types[ $custom_post_type_object->name ] = $custom_post_type_object->label;
				}
			endif;
			$this->sections[] = array(
				'title' => __('Display Settings', 'wpautbox'),
				'desc' => __('Select where you want the author box to appear on your posts, pages and custom posts.', 'wpautbox'),
				'icon' => 'el-icon-cogs',
			    // 'submenu' => false, // Setting submenu to false on a given section will hide it from the WordPress sidebar menu!
				'fields' => $section_post_type
			);

			$this->sections[] = array(
				'title' => __('Appearance Settings', 'wpautbox'),
				'desc' => __('Change author box color to match your theme :)', 'wpautbox'),
				'icon' => 'el-icon-eye-open',
			    // 'submenu' => false, // Setting submenu to false on a given section will hide it from the WordPress sidebar menu!
				'fields' => array(
								//active tab
								array(
									'id'=>'active_title',
									'type' => 'info',
									'raw_html'=> true,
									'desc' => '<h3 style="border:0px;margin-bottom:-20px;">'. __('Active Tab','wpautbox') .'</h3><br />' . __('Select Custom Skin to Enable Edit on this Section','wpautbox'),
									
								),
								array(
									'id'=>'active-color',
									'type' => 'color',
									'output' => array('.active-color'),
									'title' => __('Text Color', 'wpautbox'),
									'subtitle' => __('Pick a text color for active tab', 'wpautbox'),
									'default' => 'default',
									'validate' => 'color',
								),
								array(
									'id'=>'active-bgcolor',
									'type' => 'color',
									'output' => array('.active-bgcolor'),
									'title' => __('Background Color', 'wpautbox'),
									'subtitle' => __('Pick a background color for active tab', 'wpautbox'),
									'default' => 'default',
									'validate' => 'color',
								),
								array(
									'id'=>'active-bordercolor',
									'type' => 'color',
									'output' => array('.active-bordercolor'),
									'title' => __('Border Color', 'wpautbox'),
									'subtitle' => __('Pick a border color for active tab', 'wpautbox'),
									'default' => 'default',
									'validate' => 'color',
								),	

								//inactive tab
								array(
									'id'=>'inactive_title',
									'type' => 'info',
									'raw_html'=> true,
									'desc' => '<h3 style="border:0px;margin-bottom:-20px;">'. __('Inactive Tab', 'wpautbox') .'</h3><br />' . __('Select Custom Skin to Enable Edit on this Section','wpautbox'),
								),
								array(
									'id'=>'inactive-color',
									'type' => 'color',
									'output' => array('.inactive-color'),
									'title' => __('Text Color', 'wpautbox'),
									'subtitle' => __('Pick a text color for inactive tabs', 'wpautbox'),
									'default' => 'default',
									'validate' => 'color',
								),
								array(
									'id'=>'inactive-bgcolor',
									'type' => 'color',
									'output' => array('.inactive-bgcolor'),
									'title' => __('Background Color', 'wpautbox'),
									'subtitle' => __('Pick a background color for inactive tabs', 'wpautbox'),
									'default' => 'default',
									'validate' => 'color',
								),
								array(
									'id'=>'inactive-bordercolor',
									'type' => 'color',
									'output' => array('.inactive-bordercolor'),
									'title' => __('Border Color', 'wpautbox'),
									'subtitle' => __('Pick a border color for inactive tabs', 'wpautbox'),
									'default' => 'default',
									'validate' => 'color',
								),

								//tabcontents
								array(
									'id'=>'tabcontent_title',
									'type' => 'info',
									'raw_html'=> true,
									'desc' => '<h3 style="border:0px;margin-bottom:-20px;">'. __('Tab Contents', 'wpautbox') .'</h3><br />' . __('Select Custom Skin to Enable Edit on this Section','wpautbox'),
								),
								array(
									'id'=>'tabcontent-color',
									'type' => 'color',
									'output' => array('.tabcontent-color'),
									'title' => __('Text Color', 'wpautbox'),
									'subtitle' => __('Pick a text color for tab contents', 'wpautbox'),
									'default' => 'default',
									'validate' => 'color',
								),
								array(
									'id'=>'tabcontent-bgcolor',
									'type' => 'color',
									'output' => array('.tabcontent-bgcolor'),
									'title' => __('Background Color', 'wpautbox'),
									'subtitle' => __('Pick a background color for tab contents', 'wpautbox'),
									'default' => 'default',
									'validate' => 'color',
								),
								array(
									'id'=>'tabcontent-bordercolor',
									'type' => 'color',
									'output' => array('.tabcontent-bordercolor'),
									'title' => __('Border Color', 'wpautbox'),
									'subtitle' => __('Pick a border color for tab contents', 'wpautbox'),
									'default' => 'default',
									'validate' => 'color',
								),
								array(
									'id'=>'tab_footer_title',
									'type' => 'info',
									'raw_html'=> true,
									'desc' => '<h3 style="border:0px;margin-bottom:-20px;">'. __('Tab Footer', 'wpautbox') .'</h3><br />' . __('Select Custom Skin to Enable Edit on this Section','wpautbox'),
								),
								array(
									'id'=>'tabfooter-bgcolor',
									'type' => 'color',
									'output' => array('.tabfooter-bgcolor'),
									'title' => __('Background Color', 'wpautbox'),
									'subtitle' => __('Pick a background color for tab footer', 'wpautbox'),
									'default' => 'default',
									'validate' => 'color',
								),
								array(
									'id'=>'tabfooter-bordercolor',
									'type' => 'color',
									'output' => array('.tabfooter-bordercolor'),
									'title' => __('Top Border Color', 'wpautbox'),
									'subtitle' => __('Pick a border color for tab footer', 'wpautbox'),
									'default' => 'default',
									'validate' => 'color',
								),
							)
			);

			$this->sections[] = array(
				'title' => __('Tab Settings', 'wpautbox'),
				'desc' => __(' Globally set which tab you want to display on the author box.', 'wpautbox'),
				'icon' => 'el-icon-website',
			    // 'submenu' => false, // Setting submenu to false on a given section will hide it from the WordPress sidebar menu!
				'fields' => array(
								array(
									'id'=>'show-about',
									'type' => 'switch', 
									'title' => __('Show About Tab', 'wpautbox'),
									'subtitle' => __('Switch On to show about the author tab', 'wpautbox'),
									"default" 		=> 1,
								),
								array(
										'id'=>'about-tab-label',
										'type' => 'text',
										'title' => __('About Tab Label', 'wpautbox'),
										'subtitle' => __('This will appear as tab label value', 'wpautbox'),
										'default' => __('About the Author', 'wpautbox')
									),
								array(
									'id'=>'show-latest-posts',
									'type' => 'switch', 
									'title' => __('Show Latest Posts Tab', 'wpautbox'),
									'subtitle' => __('Switch On to show latest posts tab', 'wpautbox'),
									"default" 		=> 1,
								),
								array(
										'id'=>'latest-tab-label',
										'type' => 'text',
										'title' => __('Latest Posts', 'wpautbox'),
										'subtitle' => __('This will appear as tab label value', 'wpautbox'),
										'default' => __('Latest Posts', 'wpautbox')
									),
								array(
									'id'=>'latest-posts-num',
									'type' => 'text',
									'title' => __('Number of latest posts to show', 'wpautbox'),
									'subtitle' => __('Set how many posts you want to show on latest posts tab', 'wpautbox'),
									"default" 		=> 10,
								),
				)
			);

			$wpautbox_socials = apply_filters('wpautbox-socials',array()); // REMOVE LATER
            $wpautbox_socials = apply_filters('wpautbox/socials',$wpautbox_socials);
            $wpautbox_socials_defaults = array();
            foreach ($wpautbox_socials as $wpautbox_socials_key => $wpautbox_socials_value) {
            	$wpautbox_socials_defaults[ $wpautbox_socials_key ] = '1';
            }
			
			$this->sections[] = array(
				'title' => __('Social Settings', 'wpautbox'),
				'desc' => __('Select social profiles you want to show on the author box', 'wpautbox'),
				'icon' => 'el-icon-star',
			    // 'submenu' => false, // Setting submenu to false on a given section will hide it from the WordPress sidebar menu!
				'fields' => array(
								array(
									'id'=>'social_display',
									'type' => 'switch', 
									'title' => __('Display Social Links', 'wpautbox'),
									'subtitle'=> __('Show social link/icons on author box', 'wpautbox'),
									"default" 		=> 1,
									),
								array(
									'id'=>'socials',
									'type' => 'sortable',
                					'mode' => 'checkbox', // checkbox or text
									'title' => __('Social Profiles', 'wpautbox'), 
									'subtitle' => __('Select Social Media Profile allowed on author box', 'wpautbox'),
									'desc' => __('Checked Fields will appear on the author edit fields', 'redux-framework-demo'),
									'options' => $wpautbox_socials, //Must provide key => value pairs for multi checkbox options
									'default' => $wpautbox_socials_defaults, //See how std has changed? you also don't need to specify opts that are 0.
								),
				)
			);

		}	

		public function setHelpTabs() {

			// Custom page help tabs, displayed using the help API. Tabs are shown in order of definition.
			$this->args['help_tabs'][] = array(
			    'id' => 'redux-opts-1',
			    'title' => __('Theme Information 1', 'redux-framework-demo'),
			    'content' => __('<p>This is the tab content, HTML is allowed.</p>', 'redux-framework-demo')
			);

			$this->args['help_tabs'][] = array(
			    'id' => 'redux-opts-2',
			    'title' => __('Theme Information 2', 'redux-framework-demo'),
			    'content' => __('<p>This is the tab content, HTML is allowed.</p>', 'redux-framework-demo')
			);

			// Set the help sidebar
			$this->args['help_sidebar'] = __('<p>This is the sidebar content, HTML is allowed.</p>', 'redux-framework-demo');

		}


		/**
			
			All the possible arguments for Redux.
			For full documentation on arguments, please refer to: https://github.com/ReduxFramework/ReduxFramework/wiki/Arguments

		 **/
		public function setArguments() {
			
			$this->args = array(
	            
	            // TYPICAL -> Change these values as you need/desire
				'opt_name'          	=> 'wpautbox_lite', // This is where your data is stored in the database and also becomes your global variable name.
				'display_name'			=> __('WP Author Box Lite','wpautbox'), // Name that appears at the top of your panel
				'display_version'		=> '1.0', // Version that appears at the top of your panel
				'menu_type'          	=> 'menu', //Specify if the admin menu should appear or not. Options: menu or submenu (Under appearance only)
				'allow_sub_menu'     	=> true, // Show the sections below the admin menu item or not
				'menu_title'			=> __( 'Author Box Lite', 'wpautbox' ),
	            'page'		 	 		=> __( 'Author Box Lite', 'wpautbox' ),
	            'google_api_key'   	 	=> '', // Must be defined to add google fonts to the typography module
	            'global_variable'    	=> '', // Set a different name for your global variable other than the opt_name
	            'dev_mode'           	=> false, // Show the time the page took to load, etc
	            'customizer'         	=> true, // Enable basic customizer support

	            // OPTIONAL -> Give you extra features
	            'page_priority'      	=> null, // Order where the menu appears in the admin area. If there is any conflict, something will not show. Warning.
	            'page_parent'        	=> 'themes.php', // For a full list of options, visit: http://codex.wordpress.org/Function_Reference/add_submenu_page#Parameters
	            'page_permissions'   	=> 'manage_options', // Permissions needed to access the options panel.
	            'menu_icon'          	=> '', // Specify a custom URL to an icon
	            'last_tab'           	=> '', // Force your panel to always open to a specific tab (by id)
	            'page_icon'          	=> 'icon-themes', // Icon displayed in the admin panel next to your menu_title
	            'page_slug'          	=> '_options', // Page slug used to denote the panel
	            'save_defaults'      	=> true, // On load save the defaults to DB before user clicks save or not
	            'default_show'       	=> false, // If true, shows the default value next to each field that is not the default value.
	            'default_mark'       	=> '', // What to print by the field's title if the value shown is default. Suggested: *


	            // CAREFUL -> These options are for advanced use only
	            'transient_time' 	 	=> 60 * MINUTE_IN_SECONDS,
	            'output'            	=> true, // Global shut-off for dynamic CSS output by the framework. Will also disable google fonts output
	            'output_tag'            => true, // Allows dynamic CSS to be generated for customizer and google fonts, but stops the dynamic CSS from going to the head
	            //'domain'             	=> 'redux-framework', // Translation domain key. Don't change this unless you want to retranslate all of Redux.
	            //'footer_credit'      	=> '', // Disable the footer credit of Redux. Please leave if you can help it.
	            

	            // FUTURE -> Not in use yet, but reserved or partially implemented. Use at your own risk.
	            'database'           	=> '', // possible: options, theme_mods, theme_mods_expanded, transient. Not fully functional, warning!
	            
	        
	            'show_import_export' 	=> false, // REMOVE
	            'system_info'        	=> false, // REMOVE
	            
	            'help_tabs'          	=> array(),
	            'help_sidebar'       	=> '', // __( '', $this->args['domain'] );            
				);


			// SOCIAL ICONS -> Setup custom links in the footer for quick links in your panel footer icons.		
			$this->args['share_icons'][] = array(
			    'url' => 'http://codecanyon.net/user/phpbits?ref=phpbits',
			    'title' => 'Follow me on Envato', 
			    'icon' => 'el-icon-user'
			);

			
	 
			// Panel Intro text -> before the form
			if (!isset($this->args['global_variable']) || $this->args['global_variable'] !== false ) {
				if (!empty($this->args['global_variable'])) {
					$v = $this->args['global_variable'];
				} else {
					$v = str_replace("-", "_", $this->args['opt_name']);
				}
				$this->args['intro_text'] = sprintf( __('<p>Awesome Author box that you\'ll fall inlove with. Fall a lot more by upgrading to <a href="http://codecanyon.net/item/wp-author-box/6815678?ref=phpbits" target="_blank">WP Author Box Pro</a>.</p>', 'wpautbox' ), $v );
			} else {
				$this->args['intro_text'] = __('<p>Awesome Author box that you\'ll fall inlove with.</p>', 'wpautbox' );
			}

			// Add content after the form.
			$this->args['footer_text'] = __('<p>If you need any help feel free to contact me using my <a href="http://codecanyon.net/user/phpbits?ref=phpbits" target="_blank">profile page.</a></p>', 'wpautbox');

		}
		
		public static function on_redux_save( $values ) {
	        // var_dump($values);
	        // die("SAVED");   
	    }
	}
}


/** 

	Custom function for the callback referenced above

 */
if ( !function_exists( 'wpautbox_custom_field' ) ):
	function wpautbox_custom_field($field, $value) {
	    print_r($field);
	    print_r($value);
	}
endif;

/**
 
	Custom function for the callback validation referenced above

**/
if ( !function_exists( 'wpautbox_validate_callback_function' ) ):
	function wpautbox_validate_callback_function($field, $value, $existing_value) {
	    $error = false;
	    $value =  'just testing';
	    /*
	    do your validation
	    
	    if(something) {
	        $value = $value;
	    } elseif(something else) {
	        $error = true;
	        $value = $existing_value;
	        $field['msg'] = 'your custom error message';
	    }
	    */
	    
	    $return['value'] = $value;
	    if($error == true) {
	        $return['error'] = $field;
	    }
	    return $return;
	}
endif;
