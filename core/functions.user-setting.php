<?php

/*##################################
	USER SETTINGS
################################## */

/**
 * Additional user fields for Author Box 
 *
 * @since 1.0
 */
class wpautbox_author_settings{
	function __construct(){

		add_action( 'edit_user_profile', array($this, 'user_fields') );
		add_action( 'show_user_profile', array($this, 'user_fields') );
		add_action( 'personal_options_update', array($this,'wpautbox_save_profile_fields') );
		add_action( 'edit_user_profile_update', array($this,'wpautbox_save_profile_fields') );
	}

	function user_fields( $user ){
		global $wpautbox_lite;
			$wpautbox_socials = apply_filters('wpautbox-socials',array()); // REMOVE LATER
	        $wpautbox_socials = apply_filters('wpautbox/socials',$wpautbox_socials);
			$wpautbox_meta = get_user_meta( $user->ID, 'wpautbox_user_fields', false );
			$wpautbox_user_meta = array(); //initialize user meta variable
			if(!empty( $wpautbox_meta )){
				$wpautbox_meta = unserialize( base64_decode($wpautbox_meta[0]) );
				if(isset( $wpautbox_meta['user'] )){
					$wpautbox_user_meta = $wpautbox_meta['user'];
				}
			} ?>
			<h3><?php _e( 'WP Author Box User Fields', 'wpautbox' ); ?></h3>

			<table class="form-table">
				<tbody>
					<tr>
						<th><label for="wpautbox_user_disable"><?php _e( 'Disable Author Box', 'wpautbox' ); ?></label></th>
						<td>
							<label for="wpautbox_user_disable">
								<input type="checkbox" name="wpautbox[user][disable]" id="wpautbox_user_disable" value="true" <?php if(isset( $wpautbox_user_meta['disable'] ) && !empty( $wpautbox_user_meta['disable'] ) ){ echo 'checked="checked"'; } ?> />
								<span class="description"><?php _e( 'Do not show Author Box in your posts, pages and custom posts', 'wpautbox' ); ?></span>
							</label>
						</td>
					</tr>
					<?php 
					if(isset($wpautbox_lite['social_display']) && 1 == $wpautbox_lite['social_display']){
						foreach($wpautbox_socials as $key => $field):
						if(!empty($wpautbox_lite['socials']) && isset($wpautbox_lite['socials'][$key]) && 1 == $wpautbox_lite['socials'][$key]):
					?>
					<tr>
						<th><label for="wpautbox_user_<?php echo $key;?>"><?php _e( $field, 'wpautbox' ); ?></label></th>
						<td>
							<input type="text" class="regular-text" id ="wpautbox_user_<?php echo $key;?>" name="wpautbox[user][socials][<?php echo $key;?>]" value="<?php if(isset( $wpautbox_user_meta['socials'][ $key ] )){ echo  $wpautbox_user_meta['socials'][ $key ]; };?>"><br />
							<?php if( 'tagline' == $key ) :?>
								<span class="description"><?php _e('Your Profile Tagline','wpautbox');?></span>
							<?php else:?>
								<span class="description"><?php _e('Your ' . $field . ' Profile Link','wpautbox');?></span>
							<?php endif;?>
						</td>
					</tr>
					<?php endif; endforeach; }?>
				</tbody>
			</table>

		<?php
	}
	function wpautbox_save_profile_fields( $user_id ) {
		if ( !current_user_can( 'edit_user', $user_id ) )
			return false;

		if(isset( $_POST['wpautbox'] )){
			$wpautbox_meta = $_POST['wpautbox'];
			
			foreach ($wpautbox_meta['user'] as $key => $value) {
				if( 'socials' == $key ){
					foreach ($value as $social_key => $social_value) {
						if(!empty( $social_value )){
							$wpautbox_meta['user'][ $key ][ $social_key ] = $this->wpautbox_addhttp( $social_value );
						}
					}
				}
				if( 'tabs' == $key ){
					//go pro for this options
				}
			}
			$wpautbox_meta = base64_encode(serialize( $wpautbox_meta ));
			update_user_meta( $user_id, 'wpautbox_user_fields', $wpautbox_meta );
		}
	}

	/**
	 * Add http to the url values
	 *
	 * @since 1.0
	 */
	function wpautbox_addhttp($url) {
	    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
	        $url = "http://" . $url;
	    }
	    return $url;
	}
}
$wpautbox_author_settings = new wpautbox_author_settings();
?>