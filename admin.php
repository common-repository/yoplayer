<?php
/**
 * @package yoplayer
 * @version 4.1.33.2
 *
 * Copyright (C) 2013 - 2016 Yospace Technologies Ltd. All rights reserved
 */

/*===========================================================================*
 * Add editor button(s)
 *===========================================================================*/
add_action('admin_print_footer_scripts', 'yoplayerAddQuicktags');
function yoplayerAddQuicktags() {
    if (wp_script_is('quicktags')) {
?>
<script type="text/javascript">
QTags.addButton( 'yoplayer_add_player', 'yoplayer', '[yoplayer miid="', '"]', '', 'Yoplayer (VOD)', 250 );
</script>
<?php
    }
}
add_filter('mce_buttons', 'yoplayerTinyMceButtons');
function yoplayerTinyMceButtons($buttons) {
    $buttons[] = 'yoplayer';
    $buttons[] = 'cdslookup';
    return $buttons;
}

/*===========================================================================*
 * Process every page save and cache video metadata
 *===========================================================================*/
add_action('save_post', 'yoplayerCacheMetadata');
function yoplayerCacheMetadata($post_id) {
    global $wpdb, $yoplayer_table_name;
    $content = @$_POST['content'];
    preg_match_all("/\[yoplayer[^]]*miid=[^[:digit:]\]]*(\d*)[^[:digit:]\]]*[^]]*\]/smx", $content, $matches);

//    header("Content-Type: text/plain");
//    print_r($matches);
    $miids = array();
    foreach($matches[1] as $miid) {
        $miids[$miid] = 1;
    }
    foreach($miids as $miid => $value) {
//        echo "$miid\n";
        $metadata = yoplayerGetMetadata($miid);
        if (count($metadata) == 0) {
            continue;
        }
//        echo "Got metadata for $miid\n";
        $wpdb->delete($yoplayer_table_name, array('miid' => $miid));
        foreach($metadata as $field => $value) {
            $wpdb->insert(
                $yoplayer_table_name,
                array(
                    'miid' => $miid,
                    'field' => $field,
                    'value' => $value
                )
            );
//            echo "$miid : $field => $value\n";
        }
    }
//    die();
}

function yoplayerDoPostRequest($url, $data, $optional_headers = null) {
    $params = array('http' => array(
        'method' => 'POST',
        'content' => $data
    ));
    if ($optional_headers !== null) {
        $params['http']['header'] = $optional_headers;
    }
    $ctx = stream_context_create($params);
    $fp = @fopen($url, 'rb', false, $ctx);
    if (!$fp) {
        throw new Exception("Problem with $url, $php_errormsg");
    }
    $response = @stream_get_contents($fp);
    if ($response === false) {
        throw new Exception("Problem reading data from $url, $php_errormsg");
    }
    return $response;
}

function yoplayerGetMetadata($miid) {
    $CLIENT_UN = get_option("yoplayer-username", '');
    $CLIENT_PW = get_option("yoplayer-password", '');
    $SOAPOUT = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
  <ns2:getMetaDataFields xmlns:ns2="http://www.yospace.com/tundra/MediaItemManagement/">
    <username>' . $CLIENT_UN . '</username>
    <password>' . $CLIENT_PW . '</password>
    <mediaItemId>' . $miid . '</mediaItemId>
  </ns2:getMetaDataFields>
  </soap:Body>
</soap:Envelope>';
    $xmlresult = simplexml_load_string(str_replace('ns2:', '', str_replace('soap:', '', yoplayerDoPostRequest("http://cds1.yospace.com/mediaitemmanager/manage", $SOAPOUT, array(
        "SOAPAction" => "http://www.yospace.com/tundra/MediaItemManagement",
        "Content-Type" => "text/xml;charset=UTF-8"
    )))));
    $retval = array();
    $result = $xmlresult->Body[0]->getMetaDataFieldsResponse[0];
    if ($result->statusCode[0] == "200") {
        foreach($result->mediaItemFields as $mediaItemField) {
            $retval[(string)$mediaItemField->fieldName] = (string)$mediaItemField->value;
        }
    }
    return $retval;
}

/*===========================================================================*
 * Construct options page and link in menu
 *===========================================================================*/
add_action('admin_menu', 'yoplayer_admin_menu');
function yoplayer_admin_menu() {
    add_action( 'admin_init', 'yoplayer_init' );
    add_options_page('Yoplayer Defaults', 'Yoplayer Defaults', 'manage_options', 'yoplayer-defaults', 'yoplayer_defaults');
}

function yoplayer_init() {
    register_setting("yoplayer-defaults", "yoplayer-fid");
    register_setting("yoplayer-defaults", "yoplayer-width");
    register_setting("yoplayer-defaults", "yoplayer-height");
    register_setting("yoplayer-defaults", "yoplayer-skin");
    register_setting("yoplayer-defaults", "yoplayer-custom-skin");
    register_setting("yoplayer-defaults", "yoplayer-panning");
    register_setting("yoplayer-defaults", "yoplayer-enablecc");
    register_setting("yoplayer-defaults", "yoplayer-alternateabr");
    register_setting("yoplayer-defaults", "yoplayer-debug");
    register_setting("yoplayer-defaults", "yoplayer-autoplay");
    register_setting("yoplayer-defaults", "yoplayer-buffer");
    register_setting("yoplayer-defaults", "yoplayer-lwm");
    register_setting("yoplayer-defaults", "yoplayer-lss");
    register_setting("yoplayer-defaults", "yoplayer-username");
    register_setting("yoplayer-defaults", "yoplayer-password");
    register_setting("yoplayer-defaults", "yoplayer-metadata");
    add_settings_section(
        "yoplayer-defaults-cds",
        "Yospace CDS Settings",
        "yoplayer_presentation_header",
        "yoplayer-defaults"
    );
    add_settings_field("yoplayer-fid", "Feed ID (fid)", "yoplayer_field_fid", "yoplayer-defaults", "yoplayer-defaults-cds");
    add_settings_section(
        "yoplayer-defaults-presentation",
        "Presentation",
        "yoplayer_presentation_header",
        "yoplayer-defaults"
    );
    add_settings_field("yoplayer-width", "Width (width)", "yoplayer_field_width", "yoplayer-defaults", "yoplayer-defaults-presentation");
    add_settings_field("yoplayer-height", "Height (height)", "yoplayer_field_height", "yoplayer-defaults", "yoplayer-defaults-presentation");
    add_settings_field("yoplayer-skin", "Skin URL (skin)", "yoplayer_field_skin", "yoplayer-defaults", "yoplayer-defaults-presentation");
    add_settings_field("yoplayer-panning", "Panning (panning)", "yoplayer_field_panning", "yoplayer-defaults", "yoplayer-defaults-presentation");
    add_settings_field("yoplayer-enablecc", "Closed Captions (enablecc)", "yoplayer_field_enablecc", "yoplayer-defaults", "yoplayer-defaults-presentation");
    add_settings_field("yoplayer-alternateabr", "Alternate ABR (!abrlite)", "yoplayer_field_alternateabr", "yoplayer-defaults", "yoplayer-defaults-presentation");
    add_settings_field("yoplayer-debug", "Debugging (debug)", "yoplayer_field_debug", "yoplayer-defaults", "yoplayer-defaults-presentation");
    add_settings_field("yoplayer-autoplay", "Auto Play (autoplay)", "yoplayer_field_autoplay", "yoplayer-defaults", "yoplayer-defaults-presentation");
    add_settings_section(
        "yoplayer-defaults-playback",
        "Plaback Control",
        "yoplayer_playback_header",
        "yoplayer-defaults"
    );
    add_settings_field("yoplayer-buffer", "Buffer (buffer)", "yoplayer_field_buffer", "yoplayer-defaults", "yoplayer-defaults-playback");
    add_settings_field("yoplayer-lwm", "Low Water Mark (lwm)", "yoplayer_field_lwm", "yoplayer-defaults", "yoplayer-defaults-playback");
    add_settings_field("yoplayer-lss", "Live Stream Start (lss)", "yoplayer_field_lss", "yoplayer-defaults", "yoplayer-defaults-playback");
    add_settings_section(
        "yoplayer-defaults-metadata",
        "Download Metadata from yospaceCDS",
        "yoplayer_metadata_header",
        "yoplayer-defaults"
    );
    add_settings_field("yoplayer-username", "CDS Login", "yoplayer_field_username", "yoplayer-defaults", "yoplayer-defaults-metadata");
    add_settings_field("yoplayer-password", "CDS Password", "yoplayer_field_password", "yoplayer-defaults", "yoplayer-defaults-metadata");
    add_settings_field("yoplayer-metadata", "Default Metadata Keys", "yoplayer_field_metadata", "yoplayer-defaults", "yoplayer-defaults-metadata");
}

function yoplayer_cds_header() {
    echo "<div>Default values from your Yospace CDS account.</div>";
}

function yoplayer_field_fid() {
    echo '<input type="text" name="yoplayer-fid" id="yoplayer-fid" size="50" class="regular-text" value="' . get_option("yoplayer-fid", '') . '" />';
}

function yoplayer_presentation_header() {
    echo "<div>Options to alter how Yoplayer appears in your pages.</div>";
}

function yoplayer_field_width() {
    echo '<input type="text" name="yoplayer-width" id="yoplayer-width" size="50" class="regular-text" value="' . get_option("yoplayer-width", 640) . '" />';
}

function yoplayer_field_height() {
    echo '<input type="text" name="yoplayer-height" id="yoplayer-height" class="regular-text" value="' . get_option("yoplayer-height", 352) . '" />';
}

function yoplayer_field_skin() {
    echo '<input type="radio" name="yoplayer-skin" id="yoplayer-skin-default" value=""';
    if (get_option("yoplayer-skin", "") == "") {
        echo " checked";
    }
    echo ' /> <label for="yoplayer-skin-default">Default</label> <i style="font-size: smaller">(HLS-SDK Default Skin)</i><br/>';
    echo '<input type="radio" name="yoplayer-skin" id="yoplayer-skin-blacklight" value="Black-Light.skin"';
    if (get_option("yoplayer-skin", "") == "Black-Light.skin") {
        echo " checked";
    }
    echo ' /> <label for="yoplayer-skin-blacklight">Black-Light</label> <i style="font-size: smaller">(Floating, auto-hide control bar with full controls)</i><br/>';
    echo '<input type="radio" name="yoplayer-skin" id="yoplayer-skin-minimal" value="Minimal.skin"';
    if (get_option("yoplayer-skin", "") == "Minimal.skin") {
        echo " checked";
    }
    echo ' /> <label for="yoplayer-skin-minimal">Minimal</label> <i style="font-size: smaller">(Floating, auto-dim control bar with minimal controls)</i><br/>';
    echo '<input type="radio" name="yoplayer-skin" id="yoplayer-skin-fixed" value="Fixed.skin"';
    if (get_option("yoplayer-skin", "") == "Fixed.skin") {
        echo " checked";
    }
    echo ' /> <label for="yoplayer-skin-fixed">Fixed</label> <i style="font-size: smaller">(Fixed control bar below video - Allow an additional 32px height)</i><br/>';
    echo '<input type="radio" name="yoplayer-skin" id="yoplayer-skin-custom" value="custom"';
    if (get_option("yoplayer-skin", "") == "custom") {
        echo " checked";
    }
    echo ' /> <label for="yoplayer-skin-custom">Custom:</label> ';
    echo '<input type="text" name="yoplayer-custom-skin" id="yoplayer-custom-skin" class="regular-text" value="' . get_option("yoplayer-custom-skin", "int://internal.skin") . '"';
    if (get_option("yoplayer-skin", "") != "custom") {
        echo " disabled";
    }
    echo ' />';
}

function yoplayer_field_panning() {
    echo '<input type="checkbox" name="yoplayer-panning" id="yoplayer-panning" value="true"';
    if (get_option("yoplayer-panning", "false") == "true") {
        echo " checked";
    }
    echo ' />';
}

function yoplayer_field_enablecc() {
    echo '<input type="checkbox" name="yoplayer-enablecc" id="yoplayer-enablecc" value="true"';
    if (get_option("yoplayer-enablecc", "false") == "true") {
        echo " checked";
    }
    echo ' />';
}

function yoplayer_field_alternateabr() {
    echo '<input type="checkbox" name="yoplayer-alternateabr" id="yoplayer-alternateabr" value="true"';
    if (get_option("yoplayer-alternateabr", "false") == "true") {
        echo " checked";
    }
    echo ' />';
}

function yoplayer_field_debug() {
    echo '<input type="checkbox" name="yoplayer-debug" id="yoplayer-debug" value="true"';
    if (get_option("yoplayer-debug", "false") == "true") {
        echo " checked";
    }
    echo ' />';
}

function yoplayer_field_autoplay() {
    echo '<input type="checkbox" name="yoplayer-autoplay" id="yoplayer-autoplay" value="true"';
    if (get_option("yoplayer-autoplay", "false") == "true") {
        echo " checked";
    }
    echo ' />';
}

function yoplayer_playback_header() {
    echo "<div>Options to alter how Yoplayer downloads and plays your videos.</div>";
    echo "<div>These are advanced features and should only be changed after reading the HLS-SDK documentation.</div>";
}

function yoplayer_field_buffer() {
    echo '<input type="text" name="yoplayer-buffer" id="yoplayer-buffer" size="50" class="regular-text" value="' . get_option("yoplayer-buffer", 30) . '" />';
}

function yoplayer_field_lwm() {
    echo '<input type="text" name="yoplayer-lwm" id="yoplayer-lwm" class="regular-text" value="' . get_option("yoplayer-lwm", 5) . '" />';
}

function yoplayer_field_lss() {
    echo '<input type="text" name="yoplayer-lss" id="yoplayer-lss" class="regular-text" value="' . get_option("yoplayer-lss", 3) . '" />';
}

function yoplayer_metadata_header() {
    echo "<div>To download metadata for each video from yospaceCDS, enter your yospaceCDS username and password here. (Optional)</div>";
}

function yoplayer_field_username() {
    echo '<input type="text" name="yoplayer-username" id="yoplayer-username" size="50" class="regular-text" value="' . get_option("yoplayer-username", '') . '" />';
}

function yoplayer_field_password() {
    echo '<input type="password" name="yoplayer-password" id="yoplayer-password" class="regular-text" value="' . get_option("yoplayer-password", '') . '" />';
}

function yoplayer_field_metadata() {
    echo '<input type="text" name="yoplayer-metadata" id="yoplayer-metadata" class="regular-text" value="' . get_option("yoplayer-metadata", '') . '" />';
}

function yoplayer_defaults() {
?>
<div class="wrap">
<style type="text/css">
input[type="text"][disabled] {
    background-color: #e0e0e0;
}
</style>
<?php screen_icon(); ?>
<h2>Yoplayer Defaults</h2>
<div>Each option shown has the name of the shortcode parameter in brackets
    after. The value shown here will be used unless you override it in the
    shortcode.</div>
<div>For example <code>[yoplayer miid="123456"]</code> will take all the values
    shown below, while <code>[yoplayer miid="123456" fid="987654" width="320" height="176"]</code>
    will override just the on-screen dimensions of the player.</div>
<form method="post" action="options.php">
    <?php settings_fields('yoplayer-defaults'); ?>
    <?php do_settings_sections('yoplayer-defaults'); ?>
    <?php submit_button(); ?>
</form>
<p>Don&apos;t forget to upload your <code>yospace.lic</code> file to the root of
    your webserver, e.g. it must be available as <code>/yospace.lic</code>.
</div>
<script type="text/javascript">
var rbDefault = document.getElementById("yoplayer-skin-default");
var rbBlackLight = document.getElementById("yoplayer-skin-blacklight");
var rbMinimal = document.getElementById("yoplayer-skin-minimal");
var rbFixed = document.getElementById("yoplayer-skin-fixed");
var rbCustom = document.getElementById("yoplayer-skin-custom");
var txtCustom = document.getElementById("yoplayer-custom-skin");
rbDefault.addEventListener("click", function() {
    txtCustom.setAttribute("disabled", true); }
);
rbBlackLight.addEventListener("click", function() {
    txtCustom.setAttribute("disabled", true); }
);
rbMinimal.addEventListener("click", function() {
    txtCustom.setAttribute("disabled", true); }
);
rbFixed.addEventListener("click", function() {
    txtCustom.setAttribute("disabled", true); }
);
rbCustom.addEventListener("click", function() {
    txtCustom.removeAttribute("disabled"); }
);
</script>
<?php
}
