<v-container>

  <v-row justify="space-around" v-show="showErrors">
    <v-col v-for="(error, index) in errorMsgs" class="text-left" cols="12" sm="12">
      <v-alert type="error" v-model="errorValues[index]" dismissible >{{ error.msg }} <v-btn text small color="primary" v-show="error.showSettings" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'hss-sku-gen-settings'), 'admin.php' ) ) );?>">Settings</v-btn></v-alert>
    </v-col>
  </v-row>
  <v-row justify="space-around">
    <v-col class="text-left" cols="12" md="6" sm="12">
      <v-text-field v-model="settings.prefix" type="text"  label="Prefix" placeholder="p-" hint="Global prefix added to all SKUs."></v-text-field>
    </v-col>
    <v-col class="text-left" cols="12" md="6" sm="12">
      <v-text-field v-model="settings.suffix" type="text" label="Suffix" placeholder="p-" hint="Global suffix added to all SKUs."></v-text-field>
    </v-col>
    <v-col class="text-left" cols="12" md="6" sm="12">
      <v-text-field v-model="settings.charSet" type="text" label="Character Set" placeholder="0123456789ABCDEFGHJKMNPQRTUVWXY" hint="The characters that are allowed to be in a SKU."></v-text-field>
    </v-col>
    <v-col class="text-left" cols="12" md="6" sm="12">
      <v-text-field v-model="settings.minChars" type="integer" label="Min Chars" placeholder="5" hint="The minimum number of characters in a SKU."></v-text-field>
    </v-col>
    <v-col class="text-left" cols="12" md="6" sm="12">
      <v-text-field v-model="settings.nextSKUNum" type="integer" label="Next SKU" placeholder="1" hint="The number used as the seed for the next SKU."></v-text-field>
    </v-col>
    <v-col class="text-left" cols="12" sm="12">
      <v-switch
        v-model="settings.useTaxonomy"
        label="Use Taxonomy prefix/suffix"
        hint="The prefix, suffix and SKU seed will be generated from the taxonomy value."
        persistent-hint
      ></v-switch>
    </v-col>
    <v-col class="text-left" cols="12" md="6" sm="12">
      <v-select
        v-model="settings.taxonomy"
        :items="taxItems"
        item-text="display"
        item-value="val"
        label="Taxonomy"
		
		@change="changedValue"
      ></v-select>
    </v-col>
    <v-col class="text-left" cols="12" md="6" sm="12">
      <v-select
        v-model="settings.taxConcatType"
        :items="taxConcatType"
        item-text="display"
        item-value="val"
        label="Concatenation Type"
      ></v-select>
    </v-col>
    <v-col class="float-right" cols="12" sm="12">
      <v-btn color="primary" id="btnSaveSettings" :disabled="loadingSettings" class="float-right" v-on:click="saveSettings"><v-icon left >mdi-content-save</v-icon>Save Settings</v-btn>
      <v-progress-circular
          indeterminate
          color="primary"
          v-show="loadingSettings"
          class="float-right"
      ></v-progress-circular>
    </v-col>
  </v-row>
</v-container>