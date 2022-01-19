/**
 * Revenue line graph Vue file
**/
/*jshint esversion: 6 */

window.onload=function(){
  var url_string = window.location.href;
  var url = new URL(url_string);
  var curPage = url.searchParams.get("page");
  // console.log(curPage);

  if (curPage === 'hss-sku-gen-tools') {
    var vm = new Vue({
      el: '#vueWrapper',
      vuetify: new Vuetify(),
      data: {
        testTitle: 'SKU Generator',
        jsVer: 1,
        jsVerReq: hss_sku_gen_data.jsVerReq,
        ajax_url: hss_sku_gen_data.ajax_url,
        ajax_nonce: hss_sku_gen_data.ajax_nonce,
        tab: 0,
        settings: {prefix: '', suffix: '', charSet: '', minChars: 5, nextSKUNum: 0, useTaxonomy: true, taxonomy: '1', taxConcatType: 'all'},
        test: {prefix: 'P', suffix: 'S', charSet: '0123456789ABCDEFGHJKMNPQRTUVWXY', minChars: 5, nextSKUNum: 100, useTaxonomy: true, taxonomy: '1', taxConcatType: 'all'},
        taxConcatType: [],
        taxItems: [],
        errorMsgs: [
          // {'msg': 'Test Error Messages.', 'showSettings': true},
          // {'msg': 'Test Error 2 Messages.', 'showSettings': false}
        ],
        errorValues: [],
        loadingSettings: false,
        executingTest: false
      },
      props: {
      },
      watch: {
      },
      mounted () {
        this.checkVer();
        this.fixVuetifySelectInWP();
        this.settings = JSON.parse(hss_sku_gen_data.settings);
		this.get_category();
		this.get_subcategory();
      },
      methods: {
        testMethod: function () {
          alert('Got test Event ajax.url: ' + this.ajax_url);
        },
		changedValue:function(value){
			var categoryids=value;
			 var data = 'action=hss_get_product_cat_subcategory&security=' + this.ajax_nonce;
			data = data + '&catid=' + categoryids;
      
          // console.log('nonce = '+ hss_admin_product_tool_helper.ajax_nonce);
          console.log('data = '+ data);
          console.log('URL = '+ this.ajax_url);
          jQuery.ajax({
            type : "post",
            dataType : "json",
            url : this.ajax_url,
            data : data,
            success: function(response) {
				
				vm.taxConcatType=response;
			},
            error: this.ajaxError,
            complete: this.ajaxDone
          });
			
			
		},
		get_subcategory: function() {
          

          var data = 'action=hss_get_product_sub_category&security=' + this.ajax_nonce;
         
          jQuery.ajax({
            type : "post",
            dataType : "json",
            url : this.ajax_url,
            data : data,
            success: function(response) {
				
				vm.taxConcatType=response;
			},
            error: this.ajaxError,
            complete: this.ajaxDone
          });
  
        },
		get_category: function() {
          

          var data = 'action=hss_get_product_category&security=' + this.ajax_nonce;
         
          jQuery.ajax({
            type : "post",
            dataType : "json",
            url : this.ajax_url,
            data : data,
            success: function(response) {
				console.log(response);
				vm.taxItems=response;
			},
            error: this.ajaxError,
            complete: this.ajaxDone
          });
  
        },
        checkVer: function () {
          var that = this;
          
          if (that.jsVer < that.jsVerReq) {
            that.addError('Error 1: You are using an old javascript version.  Try clearing browsing history and refreshing the page.', false);
          } else if (that.jsVer > that.jsVerReq) {
            that.addError('Error 2: Your javascript is more recent than your plugin. If you have any problems please notify your administrator.', false);
          }
        },

        ///
        // Data functions
        ///
        saveSettings: function() {
          this.loadingSettings = true;
          console.log('Save Settings ');

          var data = 'action=hss_save_sku_gen_settings&security=' + this.ajax_nonce;
          data = data + '&settings=' + JSON.stringify(this.settings);
      
          // console.log('nonce = '+ hss_admin_product_tool_helper.ajax_nonce);
          console.log('data = '+ data);
          console.log('URL = '+ this.ajax_url);
          jQuery.ajax({
            type : "post",
            dataType : "json",
            url : this.ajax_url,
            data : data,
            success: function(response) {console.log('data = '+ JSON.stringify(response.data));},
            error: this.ajaxError,
            complete: this.ajaxDone
          });
  
        },
        runTest: function () {
          console.log('Run Test hss_run_sku_gen_test');
          this.executingTest = true;

          var data = 'action=hss_run_sku_gen_test&security=' + this.ajax_nonce;
          data = data + '&options=' + JSON.stringify(this.test);
      
          // console.log('nonce = '+ hss_admin_product_tool_helper.ajax_nonce);
          // console.log('data = '+ data);
          // console.log('URL = '+ this.ajax_url);
          jQuery.ajax({
            type : "post",
            dataType : "json",
            url : this.ajax_url,
            data : data,
            success: function(response) {console.log('data = '+ JSON.stringify(response.data));},
            error: this.ajaxError,
            complete: this.ajaxDone
          });

        },
        ajaxError: function( XHR, textStatus, errorThrown ) {
          console.log('An ERROR occurred with ajax call to add attributes');
          console.log(XHR);
          console.log(textStatus);
          console.log(errorThrown);
        },
        ajaxDone: function( XHR, textStatus ) {
          this.loadingSettings = false;
          this.executingTest = false;
        },
        ///
        // Common
        ///
        getAdjustedMarginText: function (lMargin, lPadding) {
          // if lPadding != 0 
          //if units match
          // console.log('getAdjustedMarginText: ' + lMargin + ', ' + lPadding);

          let numPadding = parseInt(lPadding);
          if (!isNaN(numPadding) && numPadding > 0) {
            //separate units from numbers
            let patt = /([+-]?\d+(?:\.\d+)?)(.*)/i;
            let marginParse = patt.exec(lMargin);
            let paddingParse = patt.exec(lPadding);
            // console.log ('padParse: ', paddingParse);
            if (marginParse.length > 2 && paddingParse.length > 2 && paddingParse[2] == marginParse[2]) {
              let tot = parseInt(marginParse[1]) + parseInt(paddingParse[1]);
              // console.log ('-' + tot + marginParse[2]);
              return '-' + tot + marginParse[2];
            }
          }

          //default return - lMargin
          return '-' + lMargin;
        },
        fixVuetifySelectInWP: function () {
          //get the width of admin 
          // let menuWidth = document.getElementById('adminmenuback').offsetWidth;
          // console.log('menu width: ', menuWidth);
          var that = this;

          jQuery('div.v-select').each( function () {
            jQuery(this).click (function() {
              let wpDoc = jQuery('#wpcontent');
              // console.log('doc left Margin: ', wpDoc.css('margin-left'));
              // console.log('doc left Padding: ', wpDoc.css('padding-left'));
              // let pos = jQuery(this).offset();
              // console.log('button left:', pos.left);
              // let x = jQuery('div.menuable__content__active').css('left');
              // console.log('menu left:', x);
              jQuery('div.menuable__content__active').css('margin-left', that.getAdjustedMarginText(wpDoc.css('margin-left'), wpDoc.css('padding-left')));
              // jQuery('div.menuable__content__active').css('margin-left', '-' + wpDoc.css('margin-left'));
              // jQuery('div.menuable__content__active').css('padding-left', '-' + wpDoc.css('padding-left'));
            });
          });


          jQuery('div.v-menu__content').each( function() {
            let x = jQuery(this).css('left');
            // console.log('left:', x);
          });
        },
		
        addError(msg, showSettings) {
          this.errorMsgs.push({'msg': msg, 'showSettings': showSettings});
          this.errorValues.push(true);
        },
		created: function(){
		this.get_category();
	  }

      },
      computed: {
        showErrors: function() {
          let out = false;
          for (let index = 0; index < this.errorValues.length; index++) {
            if (this.errorValues[index]) {
              out = true;
              break;
            }
          }
          return out;
          // return this.errorMsgs.length > 0;
        }
      }
    });
  }
}
