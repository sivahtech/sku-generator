<?php
/**********************
 * AJAX Functions
 */
 add_action('wp_ajax_hss_get_product_cat_subcategory','hassgetsubcatselectedcat');
 function hassgetsubcatselectedcat()
 {
	if(isset($_POST['catid'])){
	 $categoryids=esc_html($_POST['catid']);
	 $args = array(
       'hide_empty' => 0,
       'parent' => $categoryids,
       'taxonomy' => 'product_cat'
    );
	$subcats = get_categories($args);
		$response=array();
	 foreach($subcats as $product_categories)
		{
			
			$id = $product_categories->term_id;
			$name = $product_categories->name;;

			$response[] = array("val"=>$id,"display"=>$name);
			
		}
		
			echo json_encode($response);
		
	}	
		wp_die();
	 
 }
 
 add_action( 'wp_ajax_hss_get_product_sub_category', 'hassgetallproductsubcategory' );
 function hassgetallproductsubcategory()
	{
		

		
		$args = array(
			'taxonomy'   => "product_cat",
			'hide_empty' => false,
			
			
		);
		$product_categories = get_terms($args);
		$response=array();
		foreach($product_categories as $product_categories)
		{
			
			$id = $product_categories->term_id;
			$name = $product_categories->name;;

			$response[] = array("val"=>$id,"display"=>$name);
			
		}
		   echo json_encode($response);
		
		wp_die();
		
	}
 add_action( 'wp_ajax_hss_get_product_category', 'hassgetallproductcategory' );
 function hassgetallproductcategory()
	{
		

		
		$args = array(
			'taxonomy'   => "product_cat",
			'hide_empty' => false,
			'parent'  => 0
			
		);
		$product_categories = get_terms($args);
		$response=array();
		foreach($product_categories as $product_categories)
		{
			
			$id = $product_categories->term_id;
			$name = $product_categories->name;;

			$response[] = array("val"=>$id,"display"=>$name);
			
		}
		   echo json_encode($response);
		
		wp_die();
		
	}
  add_action( 'wp_ajax_hss_save_sku_gen_settings', 'hssAjaxUpdateSkuGenSettings' );
  function hssAjaxUpdateSkuGenSettings() {
    // need to check nonce for security
    $nonce = $_POST['security'];

   // $notes = new ajaxNotes();

    if ( ! wp_verify_nonce( $nonce, 'hss_sku_gen_nonce' ) ) {
      //$notes->addError('Nonce invalid');
     wp_send_json_error(); 
    }

    // check I have all the variables I need
    if (!isset($_POST['settings']) ) {
      //$notes->addError('Need to send Settings.');
      wp_send_json_error(); 
    }

	//delete_option('sku_already_added');
	if(isset($_POST['settings'])){
		update_option('hss_sku_gen_settings', stripslashes (esc_html($_POST['settings'])));
	}
    

    //$notes->addSuccess('Successfully updated SKU Generator Settings');

    wp_send_json_success(array( 'notes' => 'Successfully updated SKU Generator Settings'));
  }

  add_action( 'wp_ajax_hss_run_sku_gen_test', 'hssAjaxRunSkuGenTest' );
  function hssAjaxRunSkuGenTest() {
    // need to check nonce for security
    $nonce = $_POST['security'];

    $notes = new ajaxNotes();

    if ( ! wp_verify_nonce( $nonce, 'hss_sku_gen_nonce' ) ) {
      $notes->addError('Nonce invalid');
      wp_send_json_error(array('notes' => $notes->getNotes()));
    }

    // check I have all the variables I need
    if (!isset($_POST['options']) ) {
      $notes->addError('Need to send Options.');
      wp_send_json_error(array('notes' => $notes->getNotes()));
    }
    
    // Call generate SKU
    $skuGenObj = new hssSkuCalculator();
    // $sku = $skuGenObj->generateSKU($nextNum);
    $sku = $skuGenObj->numToCode (100);

    $notes->addSuccess('Successfully updated SKU Generator Settings');

    wp_send_json_success(array('sku' => $sku, 'notes' => $notes->getNotes()));
  }
