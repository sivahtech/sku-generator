<?php
/***
* Class to add in basic functions for admin tools
* Timing Functions
* Logging Functions
* Basic Formating Functions
* csv processing Functions
* db Functions
***/
if (!class_exists('hssCommonTools', false)) {
    class hssCommonTools {
        
        var $log = array();
        function get_log() {
            return $this->log;
        }
        function clear_log() {
            $this->log = array();
        }

        
        function microtime_float()
        {
            list($usec, $sec) = explode(" ", microtime());
            return ((float)$usec + (float)$sec);
        }

        
        function hss_echo_message($message, $class = 'error'){
            ?> 
            <div class="<?php echo esc_html($class); ?>"><p><?php echo esc_html($message); ?></p></div>
            <?php
        }

        function print_messages() {
            if (!empty($this->log)) {

            
    ?>

    <div class="wrap">
        <?php if (!empty($this->log['error'])): ?>

        <div class="error">

            <?php foreach ($this->log['error'] as $error): ?>
                <p><?php echo esc_html($error); ?></p>
            <?php endforeach; ?>

        </div>

        <?php endif; ?>

        <?php if (!empty($this->log['notice'])): ?>

        <div class="updated fade">

            <?php foreach ($this->log['notice'] as $notice): ?>
                <p><?php echo esc_html($notice); ?></p>
            <?php endforeach; ?>

        </div>

        <?php endif; ?>
        
        <?php if (!empty($this->log['success'])): ?>

        <div class="updated fade">

            <?php foreach ($this->log['success'] as $success): ?>
                <p><?php echo esc_html($success); ?></p>
            <?php endforeach; ?>

        </div>

        <?php endif; ?>
    </div><!-- end wrap -->

    <?php
            // end messages HTML }}}

                $this->log = array();
            }
        }

        function print_messages_short() {
            if (!empty($this->log)) {

           
    ?>

    <div class="wrap">
        <?php if (!empty($this->log['error'])): ?>

        <div class="error">
                <p><?php echo esc_html(implode('<br />', $this->log['error'])); ?></p>
        </div>

        <?php endif; ?>

        <?php if (!empty($this->log['notice'])): ?>

        <div class="updated fade">
                <p><?php echo esc_html(implode('<br />', $this->log['notice'])); ?></p>
        </div>

        <?php endif; ?>
        
        <?php if (!empty($this->log['success'])): ?>

        <div class="updated fade">
                <p><?php echo esc_html(implode('<br />', $this->log['success'])); ?></p>
        </div>

        <?php endif; ?>
    </div><!-- end wrap -->

    <?php
           

                $this->log = array();
            }
        }


        
        function startPageWrap($header = '') {
            ?>
            <div class="wrap">
            <?php
            if ($header != ''){
                echo '<h1>'.esc_html($header).'</h1>';
            }
        }

        function endPageWrap() {
            ?>
            </div>
            <?php
        }

        function addToggleScript() {
            ?>
            <script>
            function toggleAdvanced() {
                //console.log("Hello!");
                jQuery('.admin-advanced').toggle();
                return false;
            }
            function toggleProgress() {
                //console.log("Hello!");
                jQuery('.admin-progress').toggle();
                jQuery('.admin-progress-button').show();
                return false;
            }
            function showProgress() {
                //console.log("Hello!");
                jQuery('.admin-progress').show();
                jQuery('.admin-progress-button').show();
                jQuery('#spanShowProgress').hide();
                return false;
            }
            </script>
            <?php           
        }

        /******* 
         * convert filename to display title
        ******/
        function fileNameToDisplay ($filename) {
            //remove the extension
            $tmp = explode('.', $filename);
            $out = $tmp[0];

            //replace _ and - with space
            $out = str_replace('_', ' ', $out);
            $out = str_replace('-', ' ', $out);

            //Capitalize first char
            $out = strtoupper(substr($out, 0, 1)) . substr($out, 1);

            return $out;
        }

        /*******
        csv file processing functions 
        *******/
        /** Reterive data from csv file to array format */
        function csvIndexArray($filePath, &$header, $delimiter='|', $skipLines = -1, $maxRows = -1) {
            $lineNumber = 0;
            $dataList = array();
            //$headerItems = array();
            if (($handle = fopen($filePath, 'r')) != FALSE) {
                
            while (($items = fgetcsv($handle, 0, $delimiter)) !== FALSE && ($maxRows == -1 || count($dataList) < $maxRows)) 
            {
                    if($lineNumber == 0)
                    { 
                        $header = $items; 
                        $lineNumber++; 
                        continue; 
                    }
                    if ($lineNumber <= $skipLines){
                        $lineNumber++; 
                        continue; 
                    }
                    
                    $record = array();
                    for($index = 0, $m = count($header); $index < $m; $index++){
                        //If column exist then and then added in data with header name
                        if(isset($items[$index])) {
                            $itmcont = trim(mb_convert_encoding(str_replace('"','',$items[$index]), "utf-8", "HTML-ENTITIES" ));
                            $record[$header[$index]] = str_replace('#',',',$itmcont);
                        }
                    }
                    $dataList[] = $record;
                    $lineNumber++; 				
                }			
                
                fclose($handle);
            }
            return $dataList;
        }

        /****
        * db Functions
        ****/
        /** This is a list of fields that are post fields 
        so if it is in header do not add it as post meta.  
        Eventually we can expand this so that it updates the post field and or adds product category. **/
        var $extraPostFields = array("guid");
        function isPostMeta($field){
            if (strpos($field, "product_cat") !== false){
                return false;
            }
            if (in_array($field, $this->extraPostFields))
            {
                return false;
            }
            return true;
        }

        /****
        * temporarily disable hooks
        ****/
        function with_filter_disabled( $hook, $handler, $callback ) {
            $priority = has_filter( $hook, $handler );
            if ( false !== $priority ) {
                remove_filter( $hook, $handler, $priority );
            }
            $retval = call_user_func( $callback );
            if ( false !== $priority ) {
                $accepted_args = PHP_INT_MAX; // for array_slice, can't use null since cast to int
                add_filter( $hook, $handler, $priority, $accepted_args );
            }
            return $retval;
        }

        /****
         * make ajax calls
        */

        // Method: POST, PUT, GET etc
        // Data: array("param" => "value") ==> index.php?param=value

        function callAPI($method, $url, $data = false)
        {
            $curl = curl_init();

            switch ($method)
            {
                case "POST":
                    curl_setopt($curl, CURLOPT_POST, 1);

                    if ($data)
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                    break;
                case "PUT":
                    curl_setopt($curl, CURLOPT_PUT, 1);
                    break;
                default:
                    if ($data)
                        $url = sprintf("%s?%s", $url, http_build_query($data));
            }

            // Optional Authentication:
            //curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            //curl_setopt($curl, CURLOPT_USERPWD, "username:password");

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            $result = curl_exec($curl);
            $info = curl_getinfo($curl);

            if (!$result){
                $this->log['error'][] =  "ERROR calling ajax. ::  ".curl_error($curl);
                $this->log['error'][] =  "INFO ::  ".print_r($info, true);
            }

            curl_close($curl);

            return $result;
        }

        /****
        * Select creation
        ****/
        function makeDropDown ($id, $name, $items, $selected = NULL, $class = NULL, $onchange = NULL, $compareDataValue = false) {
            
            ?>
            <select id="<?php echo esc_html($id); ?>" name="<?php echo esc_html($name); ?>" <?php if (!is_null ($class)) { echo 'class="'.esc_html($class).'"';} ?> <?php if (!is_null ($onchange)) { echo 'onchange="'.esc_html($onchange).'"';} ?> >
            <?php

            foreach ($items as $item) {
                $isSelected = false;
                if (!is_null($selected) && ((!$compareDataValue && $item['value'] === $selected) || ($compareDataValue && $item['dataValue'] == $selected))) {
                    $isSelected = true;
                }

                echo '        <option ';

                if (array_key_exists('value', $item)){
                    echo ' value="'.esc_html($item['value']).'"';
                }
                if (array_key_exists('dataValue', $item)){
                    echo " data-value='".json_encode($item['dataValue'])."'";
                }
                if (array_key_exists('class', $item)){
                    echo ' class="'.esc_html($item['class']).'"';
                }
                if ($isSelected) {
                    echo ' selected="selected"';
                }
                echo ' >'.esc_html($item['name']).'</option>';
            }

            ?>
            </select>
            <?php
        }
    }
}

if (!class_exists('hssAjaxNotes', false)) {
    class hssAjaxNotes {
        public $notes = array(
            'error' => array(),
            'notice' => array(),
            'success' => array(),
        );
        
        function addError($message) {
            $this->notes['error'][] = $message;
        }
        function addErrorObj($err) {
            $this->notes['error'] = array_merge ($this->notes['error'], $err->get_error_messages());
        }
        function addNotice($message) {
            $this->notes['notice'][] = $message;
        }
        function addSuccess($message) {
            $this->notes['success'][] = $message;
        }
        function addNote($type, $message) {
            $this->notes[$type][] = $message;
        }
        function getNotes() {
            return $this->notes;
        }
    }
}

if (!function_exists('hssGetProductFromIdentifier')) {
    function hssGetProductFromIdentifier ($productIdentifier) {
        try {
            global $wpdb;
            $sql = "";
            $product_id = -1;

            // check if it is a product_id
            if ( is_numeric( $productIdentifier ) && in_array ( get_post_type( intval($productIdentifier) ), array( 'product', 'product_variation' ) ) ) { 
                $product_id = intval($productIdentifier);
            } else {
                $sql = $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_value='%s' AND meta_key IN ('_sku', '_hss_ComponentCode') LIMIT 1", $productIdentifier );
                $product_id = $wpdb->get_var( $sql );
            }

            if (! $product_id ) {
                throw new Exception( __( 'Invalid Product Identifier ', 'woocommerce' ).$productIdentifier );
            }

            if ( ! in_array( get_post_type( $product_id), array( 'product', 'product_variation' ) ) ) {
                throw new Exception( $productIdentifier . __( ' is not a product.', 'woocommerce' ) );
            }

            return wc_get_product( $product_id);  
        } catch ( Exception $e ) {
            error_log('Error thrown in hssGetProductFromIdentifier: '. $e->getMessage() );
            return NULL;
        }
    }
}
