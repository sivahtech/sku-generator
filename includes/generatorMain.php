<?php
/****
 * TODO put in description of function here. 
****/

class hssSkuCalculator {
  // var $charSet = str_split('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ');
  public $charSet; 
  public $maxDigit;

  function __construct () {
    //TODO load charset from settings
	  $savedoptionnew=get_option('hss_sku_gen_settings');
	  $savedoptionnew=json_decode($savedoptionnew);
	 
	  $charcters=$savedoptionnew->charSet;
      $this->charSet = str_split($charcters);
      $this->maxDigit =$savedoptionnew->minChars;
  }

  function getSKUwithCategory ($prefix,$num, $precision,$sufix,$nextSKUNum)  {
      

      return getSKUwithPrefix ($prefix, $num, $precision,$sufix,$nextSKUNum);
  }

  function getSKUwithPrefix ( $num, $precision,$nextSKUNum)  {
	    
      return $this->numToCode ($num, $precision,$nextSKUNum);
  }
  function getSKUwithPrefix_automatic ( $num, $precision,$nextSKUNum)  {
	    
      return $this->numToCode_button ($num, $precision,$nextSKUNum);
  }
	function numToCode_button ($num, $precision,$nextSKUNum) 
	{
		   
		 
		  if ($num >= pow(count($this->charSet), $precision) ) {
			
			$digit = $this->maxDigit;
		  } else {
			$digit = $precision - 1;
		  }
		 
		  $remainder = $num;
		 
		  $code = '';
		  
		  $lastsavedoptions=get_option('sku_already_added');
		 
		  if($lastsavedoptions !=''){
			  $newvalue= $lastsavedoptions;
			  $newvalue++;
			  $returnedsku=$newvalue;
			  return $returnedsku;
			}
		
		  for ($i=$digit; $i > 0 ; $i--) { 
			  $index = intdiv($remainder, pow(count($this->charSet), $i));
			  if ($index > 0 || $i < $precision) {
				  
				  
				  $code = $code . $this->charSet[$index];
				  
			  }
		   $remainder = $remainder % pow(count($this->charSet), $i);
		  }
		  
		update_option('sku_already_added',$code.$nextSKUNum);
		  return $code.$nextSKUNum;
		  
	}

  function numToCode ($num, $precision,$nextSKUNum) 
  {
	   
      
      if ($num >= pow(count($this->charSet), $precision) ) {
        
        $digit = $this->maxDigit;
      } else {
        $digit = $precision - 1;
      }
	  
      $remainder = $num;
	 
      $code = '';
	  
	   $lastsavedoptions=get_option('sku_already_added');
	 
	  if($lastsavedoptions !=''){
		  $newvalue= $lastsavedoptions;
		   $newvalue++;
		  $returnedsku=$newvalue;
		  
		  update_option('sku_already_added', $returnedsku);
		   return $returnedsku;
		  
	  }
	
      for ($i=$digit; $i > 0 ; $i--) { 
          $index = intdiv($remainder, pow(count($this->charSet), $i));
          if ($index > 0 || $i < $precision) {
			  
			  
              $code = $code . $this->charSet[$index];
			  
          }
          $remainder = $remainder % pow(count($this->charSet), $i);
      }
	  
	  update_option('sku_already_added',$code.$nextSKUNum);
      return $code.$nextSKUNum;
	  
  }
  function check_for_sku_exist( $sku ) 
  {
    global $wpdb;
    
        $args = array(
            'posts_per_page'  => -1,
            'post_type'       => 'product',
            'meta_query' => array(
                array(
                    'key' => '_sku',
                    'value' => $sku,
                    'compare' => 'LIKE'
                )
            )
        );
        $posts = get_posts($args);
        if(empty($posts)){ return false; }
		else{ return true;}
        
    
  }

}

 /*********************
  * UI object
  */
class hssSkuGeneratorTools extends hssCommonTools {
  function hss_load_plugin_main_page() {
    

    add_action( 'admin_enqueue_scripts', array($this, 'hss_lm_enqueue_admin_js') );
  }
  function hss_lm_enqueue_admin_js() {
    
	
    $default_settings = array('prefix'=> '', 'suffix'=> '', 'charSet'=> '1234567890', 'minChars'=> 5, 'nextSKUNum'=> 0, 'useTaxonomy'=> true, 'taxonomy'=> '1', 'taxConcatType'=> 'all');
    
    $skuGeneratorSettings = get_option('hss_sku_gen_settings', json_encode($default_settings));
	$skuGeneratorSettings =get_option('hss_sku_gen_settings');

    $params = array(
      'ajax_url'        => admin_url( 'admin-ajax.php' ),
      'ajax_nonce'			=> wp_create_nonce('hss_sku_gen_nonce'),
      'jsVerReq'        => HSS_SKU_GEN_JS_VERSION,
      'settings'        => $skuGeneratorSettings,
      'tab'             => 0
    );

    // Enqueue vue, vuetify, and d3
    wp_register_style('hss-roboto-font', 'https://fonts.googleapis.com/css?family=Libre Barcode 39');
    wp_register_style('hss-roboto-font', 'https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900');
    wp_register_style('hss-material-designs', 'https://cdn.jsdelivr.net/npm/@mdi/font@4.x/css/materialdesignicons.min.css');
    wp_register_style('hss-vuetify-style', 'https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css');
    wp_enqueue_style('hss-roboto-font');
    wp_enqueue_style('hss-material-designs');
    wp_enqueue_style('hss-vuetify-style');

    if (HSS_SKU_GEN_ENVIRONMENT === 'development'  || (defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG )) {
      // Development or debugging load non-minified versions
      wp_enqueue_script( 'hss-vue-script', HSS_SKU_GEN_PLUGIN_URL.'includes/js/vue.js', array('jquery'));
      wp_enqueue_script( 'hss-vuetify-script', HSS_SKU_GEN_PLUGIN_URL.'includes/js/vuetify.js', array('jquery'));
    } else {
      //production environment load minified scripts
      wp_enqueue_script( 'hss-vue-script', HSS_SKU_GEN_PLUGIN_URL.'includes/js/vue.min.js', array('jquery'));
      wp_enqueue_script( 'hss-vuetify-script', HSS_SKU_GEN_PLUGIN_URL.'includes/js/vuetify.min.js', array('jquery'));
    }
    
    //TODO when minified versions of these files are made move them into the above if.
    wp_register_style( 'hss-sku-gen-css', HSS_SKU_GEN_PLUGIN_URL.'includes/css/hssSkuGenerator.css');
    wp_enqueue_style( 'hss-sku-gen-css' );
    wp_enqueue_script( 'hss-sku-gen-helper', HSS_SKU_GEN_PLUGIN_URL.'includes/js/hssSkuGenerator.js', array('jquery'));
    wp_localize_script( 'hss-sku-gen-helper', 'hss_sku_gen_data', $params );

  }
  /****
  * Page Display Functions
  ****/
    ////////
    // Main Page
    function hss_sku_gen_page() {
      $this->startPageWrap('SKU Generator');
      $pageName = 'hss-sku-gen-page';
      ?>
          <div id="vueWrapper">
            <v-app v-cloak >
              <v-main>
                <input type="hidden" id="hssPtTodoJSVer" name="hssPtTodoJSVer" value="<?php echo HSS_SKU_GEN_JS_VERSION; ?>">
                <v-tabs v-model="tab">
                  <v-tab key="settings">Settings</v-tab>
                  <v-tab key="test">Test</v-tab>
                  <v-tab-item key="settings">
                    <?php include_once HSS_SKU_GEN_PLUGIN_PATH.'includes/tabs/settingsTab.php'; ?>
                  </v-tab-item>
                  <v-tab-item key="test">
                    <?php include_once HSS_SKU_GEN_PLUGIN_PATH.'includes/tabs/testTab.php'; ?>
                  </v-tab-item>
                </v-tabs>
              </v-main>
            </v-app>
          </div>
      <?php

      $this->endPageWrap();
    }

  //
}
// End class