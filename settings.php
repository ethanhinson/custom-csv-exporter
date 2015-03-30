<?php
if(!class_exists('WP_CCSVE_Settings'))
{
  class WP_CCSVE_Settings
  {
        /**
         * Construct the plugin object
         */
        public function __construct()
        {
            // register actions
          add_action('admin_init', array(&$this, 'admin_init'));
          add_action('admin_menu', array(&$this, 'add_menu'));
        } // END public function __construct

        /**
         * hook into WP's admin_init action hook
         */
        public function admin_init()
        {
          register_setting('wp_ccsve-group', 'ccsve_post_type');
          register_setting('wp_ccsve-group', 'ccsve_std_fields');
          register_setting('wp_ccsve-group', 'ccsve_tax_terms');
          register_setting('wp_ccsve-group', 'ccsve_custom_fields');

          add_settings_section(
            'wp_ccsve_template-section',
            'Custom CSV Export Settings',
            array(&$this, 'settings_section_wp_ccsve_template'),
            'wp_ccsve_template'
            );

          add_settings_field(
            'ccsve_post_type',
            'Custom Post Type to Export',
            array(&$this, 'settings_field_select_post_type'),
            'wp_ccsve_template',
            'wp_ccsve_template-section'

            );
          add_settings_field(
            'ccsve_std_fields',
            'Standard fields to Export',
            array(&$this, 'settings_field_select_std_fields'),
            'wp_ccsve_template',
            'wp_ccsve_template-section'

            );
          add_settings_field(
            'ccsve_custom_fields',
            'Custom Fields to Export',
            array(&$this, 'settings_field_select_custom_fields'),
            'wp_ccsve_template',
            'wp_ccsve_template-section'
            );
          add_settings_field(
            'ccsve_tax_terms',
            'Taxonomy Terms to Export',
            array(&$this, 'settings_field_select_tax_terms'),
            'wp_ccsve_template',
            'wp_ccsve_template-section'
            );

        } // END public static function activate

        public function settings_section_wp_ccsve_template()
        {

          echo 'These are the settings for the Custom CSV Export Plugin.';
        }

        /**
         * This function provides text inputs for settings fields
         */
        public function settings_field_select_post_type()
        {

          $args=array(
            'public'   => true,
            );
            // Get the field name from the $args array
          $items = get_post_types($args);
            // Get the value of this setting
          $options = get_option('ccsve_post_type');
            // echo a proper input type="text"
          foreach ($items as $item) {
            $checked = ($options==$item) ? ' checked="checked" ' : '';
            echo '<input type="radio" id="post_type"'.$item.' name="ccsve_post_type" value="'.$item.'" '.$checked.'" />';
            echo '<label for=post_type'.$item.'> '.$item.'</label>';
            echo ' <br />';
          }
        } // END public function settings_field_input_text($args)

        public function settings_field_select_std_fields()
        {
          $ccsve_post_type = get_option('ccsve_post_type');
          $fields = generate_std_fields($ccsve_post_type);
          $ccsve_std_fields =get_option('ccsve_std_fields');
          $ccsve_std_fields_num = count($fields);
          echo '<select multiple="multiple" size="'.$ccsve_std_fields_num.'" name="ccsve_std_fields[selectinput][]">';
          foreach ($fields as $field) {
            if (in_array($field, $ccsve_std_fields['selectinput'])){
              echo '\n\t<option selected="selected" value="'. $field . '">'.$field.'</option>';
            } else {
              echo '\n\t\<option value="'.$field .'">'.$field.'</option>'; }
            } // END public function settings_field_input_text($args)
        } // END public function settings_field_Select_std_fields()

        public function settings_field_select_tax_terms()
        {
          $ccsve_post_type = get_option('ccsve_post_type');
          $object_tax = get_object_taxonomies($ccsve_post_type, 'names');
          $ccsve_tax_terms =get_option('ccsve_tax_terms');
          $ccsve_tax_terms_num = count($object_tax);
          echo '<select multiple="multiple" size="'.$ccsve_tax_terms_num.'" name="ccsve_tax_terms[selectinput][]">';
          foreach ($object_tax as $tax) {
            if (in_array($tax, $ccsve_tax_terms['selectinput'])){
              echo '\n\t<option selected="selected" value="'. $tax . '">'.$tax.'</option>';
            } else {
              echo '\n\t\<option value="'.$tax .'">'.$tax.'</option>'; }
            } // END public function settings_field_input_text($args)
        } // END public function settings_field_Select_std_fields()

        public function settings_field_select_custom_fields()
        {
          $ccsve_post_type = get_option('ccsve_post_type');
          $meta_keys = get_post_meta_keys($ccsve_post_type);
          $ccsve_custom_fields =get_option('ccsve_custom_fields');
          $ccsve_meta_keys_num = count($meta_keys);
          echo '<select multiple="multiple" size="'.$ccsve_meta_keys_num.'" name="ccsve_custom_fields[selectinput][]">';
          foreach ($meta_keys as $meta_key) {
            if (in_array($meta_key, $ccsve_custom_fields['selectinput'])){
              echo '\n\t<option selected="selected" value="'. $meta_key . '">'.$meta_key.'</option>';
            } else {
              echo '\n\t\<option value="'.$meta_key .'">'.$meta_key.'</option>'; }
        } // END public function settings_field_input_text($args)

        /**
         * add a menu
         */
      }
      public function add_menu()
      {
            // Add a page to manage this plugin's settings
       add_options_page(
         'CCSVE Settings',
         'Custom CSV',
         'manage_options',
         'wp_ccsve_template',
         array(&$this, 'plugin_settings_page')
         );
        } // END public function add_menu()

        /**
         * Menu Callback
         */
        public function plugin_settings_page()
        {
          if(!current_user_can('manage_options'))
          {
            wp_die(__('You do not have sufficient permissions to access this page.'));
          }

            // Render the settings template
          include(sprintf("%s/templates/settings.php", dirname(__FILE__)));
        } // END public function plugin_settings_page()
    } // END class wp_ccsve_template_Settings
} // END if(!class_exists('wp_ccsve_template_Settings'))

function generate_post_meta_keys($post_type){
  global $wpdb;
  $query = "
  SELECT DISTINCT($wpdb->postmeta.meta_key)
  FROM $wpdb->posts
  LEFT JOIN $wpdb->postmeta
  ON $wpdb->posts.ID = $wpdb->postmeta.post_id
  WHERE $wpdb->posts.post_type = '%s'
  AND $wpdb->postmeta.meta_key != ''
  AND $wpdb->postmeta.meta_key NOT RegExp '(^[_0-9].+$)'
  AND $wpdb->postmeta.meta_key NOT RegExp '(^[0-9]+$)'
  ";
  $meta_keys = $wpdb->get_col($wpdb->prepare($query, $post_type));
    set_transient($post_type.'post_meta_keys', $meta_keys, 60*60*24); # 1 Day Expiration
    return $meta_keys;
  }


  function generate_std_fields($post_type){
    $fields = array('permalink', 'post_thumbnail');
    $q = new WP_Query(array('post_type' => $post_type, 'post_status' => 'publish', 'posts_per_page' => 1));
    $p = $q->posts[0];

    foreach($p as $f => $v) {
      $fields[] = $f;
    }
    return $fields;
  }

  function get_post_meta_keys($post_type){
    $cache = get_transient($post_type.'post_meta_keys');
    $meta_keys = $cache ? $cache : generate_post_meta_keys($post_type);
    return $meta_keys;
  }

  function ccsve_checkboxes_fix($input) {

   $options = get_option('ccsve_custom_fields');
   $merged = $options;
   $merged[] = $input;
 }