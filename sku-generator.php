<?php
/**
 * Plugin Name: SKU Generator
 
 * Description: This plugin generates SKU codes for products programatically.
 * Version: 1.0.0
 * Author: Sivahtech Team
 * Author URI: https://www.sivahtech.com/
 
 */

if ( ! defined( 'ABSPATH' )) {
	exit; 
}

$wcActive = false;
if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
  require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
}

//check for multisite and regular
if ( ! is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) && ! is_plugin_active( 'woocommerce/woocommerce.php' )) {
  // error_log('Woocommerce does not appear to be enabled');
  function SKU_GEN_require_woocommerce_plugin(){?>
      <div class="notice notice-error" >
          <p> Please Enable Woocommerce Plugin before using TODO Plugin Name</p>
      </div><?php
   @trigger_error(__('Please Enable Woocommerce Plugin before using TODO Plugin Name.', 'hss_sku_gen'), E_USER_ERROR);
  }

  add_action('network_admin_notices','SKU_GEN_require_woocommerce_plugin');
  register_activation_hook(__FILE__, 'SKU_GEN_require_woocommerce_plugin');
} else {
  $wcActive = true;
}

if ( ! defined( 'SKU_GEN_JS_VERSION' ) ) {
	define( 'SKU_GEN_JS_VERSION', 1 );
}
if ( ! defined( 'SKU_GEN_ENVIRONMENT' ) ) {
	define( 'SKU_GEN_ENVIRONMENT', 'development' );
 
}


if ( ! defined( 'SKU_GEN_PLUGIN_BASENAME' ) ) {
	define( 'SKU_GEN_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}
if ( ! defined( 'SKU_GEN_PLUGIN_DIRNAME' ) ) {
	define( 'SKU_GEN_PLUGIN_DIRNAME', dirname( SKU_GEN_PLUGIN_BASENAME ) );
}
if ( ! defined( 'SKU_GEN_PLUGIN_URL' ) ) {
	define( 'SKU_GEN_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'SKU_GEN_PLUGIN_PATH' ) ) {
	define( 'SKU_GEN_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}


if (is_admin() && $wcActive) {
  
  include_once SKU_GEN_PLUGIN_PATH.'includes/tools.php';
  
  include_once SKU_GEN_PLUGIN_PATH.'includes/generatorMain.php';
  include_once SKU_GEN_PLUGIN_PATH.'includes/generatorAjax.php';
  
 
  add_action('admin_menu', 'sku_gen_load_menu');
  function sku_gen_load_menu() {
    
    $objGUI = new hssSkuGeneratorTools();
  
    $menuPage = add_menu_page('SKU Generator', 'SKU Generator Tools', 'edit_posts', 'hss-sku-gen-tools', array($objGUI, 'hss_sku_gen_page'), 'dashicons-excerpt-view');
    
    add_action( 'load-' . $menuPage, array($objGUI, 'hss_load_plugin_main_page'));

  }
  
	add_action( 'save_post', 'SKU_GEN_update_sku_product', 50, 3 );
	function SKU_GEN_update_sku_product( $post_id, $post, $update ) {
		
		if ( $post->post_type != 'product') return;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id; 

		if ( $post->post_status != 'publish' )
			return $post_id; 

		if ( ! current_user_can( 'edit_product', $post_id ) )
			return $post_id;
		
			$savedoption=get_option('hss_sku_gen_settings');
			$savedoption=json_decode($savedoption);
			
			if($savedoption->useTaxonomy==1){
				$terms = get_the_terms ( $post_id, 'product_cat' );
				
				foreach ( $terms as $term ) {
					
					
					 $allcategory[] = $term->term_id;
				}
				
				if($savedoption->taxonomy !=''){
					$categoryid=$savedoption->taxonomy;
					$catname = get_the_category_by_ID( $categoryid );
					if(in_array($savedoption->taxonomy, $allcategory))
					{
						$prefix=substr($catname,0,1);
					}else{
						$prefix=$savedoption->prefix;
					}
				}else{
					$prefix=$savedoption->prefix;
				}
				if($savedoption->taxConcatType !=''){
					$subcategoryid=$savedoption->taxConcatType;
					$subcatname = get_the_category_by_ID( $subcategoryid );
					
					if(in_array($savedoption->taxConcatType, $allcategory))
					{
						$suffix=substr($subcatname,0,1);
					}else{
						$suffix=$savedoption->suffix;
					}
				}else{
					$suffix=$savedoption->suffix;
				}
			}else{
				$prefix=$savedoption->prefix;
				$suffix=$savedoption->suffix;	
			}
			$num=$savedoption->minChars;
			$productsku=$savedoption->prefix.$savedoption->suffix;
			$pskulength=strlen($productsku);
			$precision=$num-$pskulength;
			$nextSKUNum=$savedoption->nextSKUNum;
			$notes = new hssSkuCalculator();
			
			$codereturn=$notes->getSKUwithPrefix($num,$precision,$nextSKUNum);
			update_option('sku_already_added',$codereturn);
			$codereturn=$prefix.$codereturn.$suffix;
			
			$newskuarray=array(
				'prefix'=>$savedoption->prefix,
				'suffix'=>$savedoption->suffix,
				'charSet'=>$savedoption->charSet,
				'minChars'=>$savedoption->minChars,
				'nextSKUNum'=>$codereturn,
				'useTaxonomy'=>$savedoption->useTaxonomy,
				'taxonomy'=>$savedoption->taxonomy,
				'taxConcatType'=>$savedoption->taxConcatType
			);
			$mynewsettings = json_encode($newskuarray);
			update_option('hss_sku_gen_settings', stripslashes ($mynewsettings));
			
			
			if($codereturn){
				$checkforsku=$notes->check_for_sku_exist($codereturn);
				if($checkforsku){
					
					
				}else{
					update_post_meta( $post_id, '_sku', $codereturn );
				}
			}
			 
			 
		
		
	}


  
	add_action( 'admin_init', 'SKU_GEN_my_admin_button' );
	function SKU_GEN_my_admin_button() {
		add_meta_box( 'product_meta_box',
			'Product Details',
			'SKU_GEN_display_product_meta_box',
			'product', 'normal', 'high'
		);
	}
	function SKU_GEN_display_product_meta_box( $movie_review ) {
		
		?>
		<table>
			<tr>
				<td style="width: 100%">Generate Sku</td>
				<td><button type="button" class="generatesku btn btn-info" onclick="myfunctiongeneratesku()">Generate Sku</button></td>
			</tr>
			
			
		</table>
		<?php
	}
	add_action( 'admin_footer', 'SKU_GEN_my_action_javascript' ); 

	function SKU_GEN_my_action_javascript() { ?>
		<script type="text/javascript" >
		
			function myfunctiongeneratesku(){
				var categoryids = [];
				jQuery("#product_catchecklist input:checkbox:checked").each(function () {
					var data= jQuery(this).val();
					categoryids.push(data);
				});
				var allcatids = categoryids.toString();
				//alert(allcatids);
				jQuery.ajax({
					type: "post",
					
					url: "<?php echo site_url(); ?>/wp-admin/admin-ajax.php",
					data: {
						action: "automatically_generate_sku",
						allcatids:allcatids
						
					   
					},
					success: function(response) {
						jQuery("#_sku").val(response);
					}
				});
				
				
			}
		
		</script> <?php
	}
	add_action( 'wp_ajax_automatically_generate_sku','automatically_generate_sku' );
	add_action( 'wp_ajax_nopriv_automatically_generate_sku', 'automatically_generate_sku' );

	function automatically_generate_sku()
	{
			$savedoption=get_option('hss_sku_gen_settings');
			$savedoption=json_decode($savedoption);
			
			$postedcategory=$_POST['allcatids'];
			$allcategory=explode(",",$postedcategory);
			if($savedoption->useTaxonomy==1){
				if($savedoption->taxonomy !=''){
					$categoryid=$savedoption->taxonomy;
					
					$catname = get_the_category_by_ID( $categoryid );
					if(in_array($savedoption->taxonomy, $allcategory))
					{
						$prefix=substr($catname,0,1);
					}else{
						$prefix=$savedoption->prefix;
					}
				}else{
					$prefix=$savedoption->prefix;
				}
				if($savedoption->taxConcatType !=''){
					$subcategoryid=$savedoption->taxConcatType;
					$subcatname = get_the_category_by_ID( $subcategoryid );
					
					if(in_array($savedoption->taxConcatType, $allcategory))
					{
						$suffix=substr($subcatname,0,1);
					}else{
						$suffix=$savedoption->suffix;	
					}
				}else{
					$suffix=$savedoption->suffix;	
				}
				
				
			}else{
				$prefix=$savedoption->prefix;
				$suffix=$savedoption->suffix;	
			}
			
			$num=$savedoption->minChars;
			$productsku=$savedoption->prefix.$savedoption->suffix;
			$pskulength=strlen($productsku);
			$precision=$num-$pskulength;
			$nextSKUNum=$savedoption->nextSKUNum;
			$notes = new hssSkuCalculator();
			
			$codereturn=$notes->getSKUwithPrefix_automatic($num,$precision,$nextSKUNum);
			echo $codereturn=$prefix.$codereturn.$suffix;
			wp_die();
		
	}
	
	
  
  
}

