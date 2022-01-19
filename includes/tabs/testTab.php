<v-container>

  <v-row justify="space-around" v-show="showErrors">
    <v-col v-for="(error, index) in errorMsgs" class="text-left" cols="12" sm="12">
      <v-alert type="error" v-model="errorValues[index]" dismissible >{{ error.msg }} <v-btn text small color="primary" v-show="error.showSettings" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'hss-sku-gen-settings'), 'admin.php' ) ) );?>">Settings</v-btn></v-alert>
    </v-col>
  </v-row>

  <v-row justify="space-around" >
    <v-col class="text-left" cols="12" md="6" sm="12">
      <v-text-field v-model="test.prefix" type="text" label="Prefix" placeholder="p-" hint="Global prefix added to all SKUs."></v-text-field>
    </v-col>
    <v-col class="text-left" cols="12" md="6" sm="12">
      <v-text-field v-model="test.suffix" type="text" label="Suffix" placeholder="p-" hint="Global suffix added to all SKUs."></v-text-field>
    </v-col>
    <v-col class="text-left" cols="12" md="6" sm="12">
      <v-text-field v-model="test.charSet" type="text" label="Character Set" placeholder="0123456789ABCDEFGHJKMNPQRTUVWXY" hint="The characters that are allowed to be in a SKU."></v-text-field>
    </v-col>
    <v-col class="text-left" cols="12" md="6" sm="12">
      <v-text-field v-model="test.minChars" type="integer" label="Min Chars" placeholder="5" hint="The minimum number of characters in a SKU."></v-text-field>
    </v-col>
    <v-col class="text-left" cols="12" md="6" sm="12">
      <v-text-field v-model="test.nextSKUNum" type="integer" label="Next SKU" placeholder="1" hint="The number used as the seed for the next SKU."></v-text-field>
    </v-col>
    <v-col class="float-right" cols="12" sm="12">
      <v-btn color="primary" id="btnSaveSettings" :disabled="executingTest" class="float-right" v-on:click="runTest"><v-icon left >mdi-run</v-icon>Run Test</v-btn>
      <v-progress-circular
          indeterminate
          color="primary"
          v-show="executingTest"
          class="float-right"
      ></v-progress-circular>
    </v-col>
  </v-row>

</v-container>
