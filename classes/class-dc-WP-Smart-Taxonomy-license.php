<?php

class DC_Wp_Smart_Taxonomy_License {
	
	/**
	 * Self Upgrade Values
	 */
	// Base URL to the remote upgrade API server
	public $upgrade_url = DC_WP_SMART_TAXONOMY_PLUGIN_SERVER_URL; // URL to access the Update API Manager.

	/**
	 * @var string
	 * This version is saved after an upgrade to compare this db version to $version
	 */
	public $api_manager_license_version_name = 'dc-WP-Smart-Taxonomy_license_version';

	
	/**
	 * Data defaults
	 * @var mixed
	 */
	private $license_software_product_id;

	public $license_product_id_key;

	public $license_plugin_name;
	public $license_product_id;
	public $license_domain;
	public $license_software_version;
	public $license_plugin_or_theme;
	public $license_plugin_or_theme_mode;

	public $license_update_version;

	/**
	 * Used to send any extra information.
	 * @var mixed array, object, string, etc.
	 */
	public $license_extra;

    /**
     * @var The single instance of the class
     */
    protected static $_instance = null;

    public static function instance() {
        
        if ( is_null( self::$_instance ) )
            self::$_instance = new self();

        return self::$_instance;
    }

	public function __construct() {
    global $DC_Wp_Smart_Taxonomy;
    
		if ( is_admin() ) {

			/**
			 * Software Product ID is the product title string
			 * This value must be unique, and it must match the API tab for the product in WooCommerce
			 */
			$this->license_software_product_id = $DC_Wp_Smart_Taxonomy->token;

			/**
			 * Set all data defaults here
			 */
			$this->license_product_id_key 			= 'dc_WP_Smart_Taxonomy_license_product_id';

			/**
			 * Set all software update data here
			 */
			$this->license_plugin_name 			= 'WP_Smart_Taxonomy/WP_Smart_Taxonomy.php'; // same as plugin slug. if a theme use a theme name like 'twentyeleven'
			$this->license_product_id 			= get_option( $this->license_product_id_key ); // Software Title
			$this->license_domain 				= site_url(); // blog domain name
			$this->license_software_version 	= $DC_Wp_Smart_Taxonomy->version; // The software version
			$this->license_plugin_or_theme 		= 'plugin'; // 'theme' or 'plugin'
			$this->license_plugin_or_theme_mode 		= 'free'; // 'paid' or 'free'
			
			if(!$this->license_product_id) $this->activation();
		}

	}
	
	/**
	 * Generate the default data arrays
	 */
	public function activation() {
		global $wpdb, $DC_Wp_Smart_Taxonomy;
    
		$single_options = array(
			$this->license_product_id_key 			=> $this->license_software_product_id
		);

		foreach ( $single_options as $key => $value ) {
			update_option( $key, $value );
		}

		$this->dc_plugin_tracker('activation');
	}

	/**
	 * Deletes all data if plugin deactivated
	 * @return void
	 */
	public function uninstall() {
		global $wpdb, $blog_id;
		

		// Remove options
		if ( is_multisite() ) {

			switch_to_blog( $blog_id );

			foreach ( array(
					$this->license_product_id_key
					) as $option) {

					delete_option( $option );

					}

			restore_current_blog();

		} else {

			foreach ( array(
					$this->license_product_id_key
					) as $option) {

					delete_option( $option );

			}
		}
		
		$this->dc_plugin_tracker('deactivation');
	}

	/**
	 * Keep track of plugin status on API server
	 */
	public function dc_plugin_tracker($status) {
	  global $DC_Wp_Smart_Taxonomy;
	  
	  $api_url = add_query_arg( 'wc-api', 'dc-plugin-tracker', $this->upgrade_url );
	  
	  $license_plugin_or_theme_mode = ($this->license_plugin_or_theme_mode) ? $this->license_plugin_or_theme_mode : 'free';
	  $api_email = '';
		$api_key = '';
		
	  
	  $args = array(
			'request' 			=> $status,
			'software_title' 		=> $this->license_software_product_id,
			'software_type' => $this->license_plugin_or_theme,
			'software_mode' => $license_plugin_or_theme_mode,
			'software_version' 	=> $this->license_software_version,
			'site_title' => get_bloginfo('name'),
			'site_url' 			=> $this->license_domain,
			'site_ip' => $_SERVER['REMOTE_ADDR'],
			'site_admin' => get_bloginfo('admin_email'),
			'licence_key' => $api_key,
			'licence_email' => $api_email
		);
		
		$target_url = $api_url . '&' . http_build_query( $args );
    
		$request = wp_remote_get( $target_url );
		
		$response = wp_remote_retrieve_body( $request );
	}

} // End of class

function DC_Wp_Smart_Taxonomy_LICENSE() {
  return DC_Wp_Smart_Taxonomy_License::instance();
}
