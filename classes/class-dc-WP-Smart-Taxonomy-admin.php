<?php
class DC_Wp_Smart_Taxonomy_Admin {
  
  public $settings;

	public function __construct() {
		//admin script and style
		add_action('admin_enqueue_scripts', array(&$this, 'enqueue_admin_script'));
		
		add_action('dc_WP_Smart_Taxonomy_dualcube_admin_footer', array(&$this, 'dualcube_admin_footer_for_dc_WP_Smart_Taxonomy'));
		
		add_action( 'save_post', array(&$this, 'assign_smart_taxonomy') );

		$this->load_class('settings');
		$this->settings = new DC_Wp_Smart_Taxonomy_Settings();
	}
	
	public function assign_smart_taxonomy($post_id) {
	  
	  // If this is just a revision, don't send the email.
    if ( wp_is_post_revision( $post_id ) )
      return;
  
    if( get_post_type($post_id) != 'post' )
      return;
    
    $post_categories = get_terms( 'category', array( 'hide_empty' => 0 ) );
    if(count($post_categories) == 0)
      return;
    
    $smart_cat_settings = get_WP_Smart_Taxonomy_settings('', 'dc_WP_ST_general');
    
    if(!$smart_cat_settings['is_enable'])
      return;
    
    $post_title = get_the_title( $post_id );
    $post_tags = wp_get_post_tags( $post_id );
    
    $smart_cats = array();
    
    // Choose Samrt Cats from Post Title
    if($smart_cat_settings['is_title']) {
      foreach($post_categories as $post_category) {
        if(strpos(strtolower($post_title), strtolower($post_category->name)) !== false) {
          $smart_cats[] = $post_category->term_id;
        }
      }
    }
    
    // Choose Samrt Cats from associated Tags
    if($smart_cat_settings['is_tag']) {
      if(!empty($post_tags)) {
        foreach($post_tags as $post_tag) {
          foreach($post_categories as $post_category) {
            if(strtolower($post_category->name) == strtolower($post_tag->name)) {
              $smart_cats[] = $post_category->term_id;
            }
          }
        }
      }
    }
    
    if(!empty($smart_cats)) {
      $smart_cats = array_map('intval', $smart_cats);
      $smart_cats = array_unique( $smart_cats );
      if($smart_cat_settings['is_append'])
        wp_set_object_terms( $post_id, $smart_cats, 'category', true );
      else
        wp_set_object_terms( $post_id, $smart_cats, 'category' );
    }
    
	}

	function load_class($class_name = '') {
	  global $DC_Wp_Smart_Taxonomy;
		if ('' != $class_name) {
			require_once ($DC_Wp_Smart_Taxonomy->plugin_path . '/admin/class-' . esc_attr($DC_Wp_Smart_Taxonomy->token) . '-' . esc_attr($class_name) . '.php');
		} // End If Statement
	}// End load_class()
	
	function dualcube_admin_footer_for_dc_WP_Smart_Taxonomy() {
    global $DC_Wp_Smart_Taxonomy;
    ?>
    <div style="clear: both"></div>
    <div id="dc_admin_footer">
      <?php _e('Powered by', $DC_Wp_Smart_Taxonomy->text_domain); ?> <a href="http://dualcube.com" target="_blank"><img src="<?php echo $DC_Wp_Smart_Taxonomy->plugin_url.'/assets/images/dualcube.png'; ?>"></a><?php _e('Dualcube', $DC_Wp_Smart_Taxonomy->text_domain); ?> &copy; <?php echo date('Y');?>
    </div>
    <?php
	}

	/**
	 * Admin Scripts
	 */

	public function enqueue_admin_script() {
		global $DC_Wp_Smart_Taxonomy;
		$screen = get_current_screen();
		
		// Enqueue admin script and stylesheet from here
		if (in_array( $screen->id, array( 'toplevel_page_dc-WP-Smart-Taxonomy-setting-admin' ))) :   
		  $DC_Wp_Smart_Taxonomy->library->load_qtip_lib();
		  $DC_Wp_Smart_Taxonomy->library->load_upload_lib();
		  $DC_Wp_Smart_Taxonomy->library->load_colorpicker_lib();
		  $DC_Wp_Smart_Taxonomy->library->load_datepicker_lib();
		  wp_enqueue_script('admin_js', $DC_Wp_Smart_Taxonomy->plugin_url.'assets/admin/js/admin.js', array('jquery'), $DC_Wp_Smart_Taxonomy->version, true);
		  wp_enqueue_style('admin_css',  $DC_Wp_Smart_Taxonomy->plugin_url.'assets/admin/css/admin.css', array(), $DC_Wp_Smart_Taxonomy->version);
	  endif;
	}
}