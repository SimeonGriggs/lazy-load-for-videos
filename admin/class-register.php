<?php
/**
 * register_activation_hook() and register_deactivation_hook() MUST NOT be called with action 'plugins_loaded' or any 'admin_init'
 * @package Admin
 */
register_activation_hook( LL_FILE, 'lazyloadvideos_plugin_activation' );
register_deactivation_hook( LL_FILE, 'lazyloadvideos_plugin_deactivation' );

function lazyloadvideos_plugin_activation() {
	$signup = '<div id="mc_embed_signup">
			<form action="'.LL_NEWS_ACTION_URL.'" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
				<div class="mc-field-group">
					<label for="mce-EMAIL" style="line-height:2.5em">'.LL_NEWS_TEXT.'</label><br>
					<input type="email" value="Enter your email address" name="EMAIL" class="required email" id="mce-EMAIL" onclick="this.focus();this.select()" onfocus="if(this.value == \'\') { this.value = this.defaultValue; }" onblur="if(this.value == \'\') { this.value = this.defaultValue; }">
					<input type="hidden" name="GROUPS" id="GROUPS" value="'.LL_NEWS_GROUP.'" />
					<input type="submit" value="'.LL_NEWS_BUTTON.'" name="subscribe" id="mc-embedded-subscribe" class="button">
				</div>
				<div id="mce-responses" class="clear">
					<div class="response" id="mce-error-response" style="display:none"></div>
					<div class="response" id="mce-success-response" style="display:none"></div>
				</div>
			    <div style="position: absolute; left: -5000px;"><input type="text" name="'.LL_NEWS_NAME.'" tabindex="-1" value=""></div>
			</form>
			</div>';


	$notices = get_option( 'lazyloadvideos_deferred_admin_notices', array() );
	$notices[] = $signup . '<br>Edit your plugin settings: <strong>
					<a href="options-general.php?page='. LL_ADMIN_URL .'">Lazy Load for Videos</a>
					</strong>';
				;
	update_option( 'lazyloadvideos_deferred_admin_notices', $notices );

	lazyloadvideos_update_posts_with_embed();
}

function lazyloadvideos_plugin_deactivation() {
	delete_option( 'lazyloadvideos_deferred_admin_notices' );
	lazyloadvideos_update_posts_with_embed();
}

function lazyloadvideos_update_posts_with_embed() {
	require_once( LL_PATH . 'admin/inc/class-update-posts.php' );
	$lazyload_admin = new Lazy_Load_For_Videos_Update_Posts();
	$lazyload_admin->delete_oembed_caches();
}

class Lazy_Load_For_Videos_Register {

	function __construct() {
		add_action( 'admin_notices', array( $this, 'plugin_notice_activation' ) );
		add_action( 'save_post', array( $this, 'delete_oembed_cache' ) );
        $this->check_for_plugin_version();
	}

	/**
	 * Display notification when plugin is activated
	 */
	function plugin_notice_activation() {
	  if ( $notices = get_option( 'lazyloadvideos_deferred_admin_notices' ) ) {
	    foreach ($notices as $notice) {
	      echo "<div class='updated'><p>$notice</p></div>";
	    }
	    delete_option( 'lazyloadvideos_deferred_admin_notices' );
	  }
	}

	/**
	 * Ensure that the oembed cache for an updated post is going to be deleted
	 * @since 2.0.3
	 */
	function delete_oembed_cache( $post_id ) {
		require_once( LL_PATH . 'admin/inc/class-update-posts.php' );
		$lazyload_admin = new Lazy_Load_For_Videos_Update_Posts();
		$lazyload_admin->delete_oembed_cache( $post_id );
	}

	/**
	 * Check plugin's version and deal with special cases
	 * @since 2.2.3
	 */
	function check_for_plugin_version() {
      $version = get_option(LL_VERSION_KEY);
      
      if (!$version || version_compare($version, '2.2.3', '<')) {
        lazyloadvideos_update_posts_with_embed();
      }
      
      if (!$version || version_compare($version, LL_VERSION, '<')) {
        $this->update_plugin_version();
      }
	}
  
	/**
	 * Add/update plugin version
     * Make sure this is done AFTER the checks in check_for_plugin_version()
	 * @since 2.2.3
	 */
    function update_plugin_version() {
      // Store the plugin version for necessary fixes that might be needed in future
      add_option( LL_VERSION_KEY, LL_VERSION );
      update_option( LL_VERSION_KEY, LL_VERSION );
    }

}

function initialize_lazyloadvideos_register() {
	new Lazy_Load_For_Videos_Register();
}
add_action( 'init', 'initialize_lazyloadvideos_register' );