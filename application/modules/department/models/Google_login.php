<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Google_login extends CI_Model {

    function __construct() {
        
    }

    function google_signin() {
        
        
        // Request parameters
        $google_discover_url = "https://www.google.com/accounts/o8/id";
        $openid_mode = "checkid_setup";
        $openid_ns = "http://specs.openid.net/auth/2.0";
        $openid_return_to = base_url() . "users/google_signin";
        $openid_assoc_handle = "ABSmpf6DNMw";
        $openid_claimed_id = "http://specs.openid.net/auth/2.0/identifier_select";
        $openid_identity = "http://specs.openid.net/auth/2.0/identifier_select";
        $openid_realm = base_url();

        // PAPE extension
        $openid_ns_pape = "http://specs.openid.net/extensions/pape/1.0";
        $openid_pape_max_auth_age = "300"; // 5 mins
        // User interface extension
        $openid_ui_ns = "http://specs.openid.net/extensions/ui/1.0";
        $openid_ui_mode = "popup";
        $openid_ui_icon = "true";

        // Attribute exchange extension
        $openid_ns_ax = "http://openid.net/srv/ax/1.0";
        $openid_ax_mode = "fetch_request";
        $openid_ax_required = "country,email,firstname,language,lastname";
        $openid_ax_type_country = "http://axschema.org/contact/country/home";
        $openid_ax_type_email = "http://axschema.org/contact/email";
        $openid_ax_type_firstname = "http://axschema.org/namePerson/first";
        $openid_ax_type_language = "http://axschema.org/pref/language";
        $openid_ax_type_lastname = "http://axschema.org/namePerson/last";

        // Send discovery request to obtain the Google login authentication endpoint
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // See http://unitstep.net/blog/2009/05/05/using-curl-in-php-to-access-https-ssltls-protected-sites/
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $google_discover_url);
        $content = curl_exec($ch);
        curl_close($ch);
        $xml = simplexml_load_string($content);
        $google_endpoint = (string) $xml->XRD->Service->URI;

        // Send login authentication request to the Google endpoint address
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // See http://unitstep.net/blog/2009/05/05/using-curl-in-php-to-access-https-ssltls-protected-sites/
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $google_endpoint .
            '?openid.mode=' . $openid_mode .
            '&openid.ns=' . $openid_ns .
            '&openid.return_to=' . $openid_return_to .
            '&openid.assoc_handle=' . $openid_assoc_handle .
            '&openid.claimed_id=' . $openid_claimed_id .
            '&openid.identity=' . $openid_identity .
            '&openid.realm=' . $openid_realm .
            '&openid.ns.pape=' . $openid_ns_pape .
            '&openid.pape.max_auth_age=' . $openid_pape_max_auth_age .
            '&openid.ui.ns=' . $openid_ui_ns .
            '&openid.ui.mode=' . $openid_ui_mode .
            '&openid.ui.icon=' . $openid_ui_icon .
            '&openid.ns.ax=' . $openid_ns_ax .
            '&openid.ax.mode=' . $openid_ax_mode .
            '&openid.ax.required=' . $openid_ax_required .
            '&openid.ax.type.country=' . $openid_ax_type_country .
            '&openid.ax.type.email=' . $openid_ax_type_email .
            '&openid.ax.type.firstname=' . $openid_ax_type_firstname .
            '&openid.ax.type.language=' . $openid_ax_type_language .
            '&openid.ax.type.lastname=' . $openid_ax_type_lastname
        );
        $content = curl_exec($ch);
        curl_close($ch);
       
        // echo $content;
        // Obtain the Google sign-in page url
        list($header) = explode("\r\n\r\n", $content, 2);
        $matches = array();
        preg_match('/(Location:)(.*?)\n/', $header, $matches);
        $signin_url = trim(array_pop($matches));

        // Either open in redirect or popup , for now just give user a hyperlink
        return $signin_url;
    }

}

?>
