<?php
require "inc/helpers.php";
require "inc/external/getDefaultLanguage.php";
require "inc/formatting.php";

$protocol = ( !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ) ? "https://" : "http://";
$self = $protocol . htmlspecialchars($_SERVER["HTTP_HOST"]) . "/portal";
// item id and lang code from url query params
$item_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING);
$lang_code = getBaseLanguage(filter_input(INPUT_GET, 'lang', FILTER_SANITIZE_STRING) ?: getDefaultLanguage());

// When there's no valid item id, show the about page instead 
if ( !preg_match("/^Q\d+$/", $item_id) ) {
	echo_html_top();
	require "web/about.php";
	require "web/qr.php";
	echo makefooter();
	echo "</body></html>";
	die();
}

require "inc/sites.php";
require "inc/api.php";

$available_langs = json_decode(file_get_contents("i18n/_langs.json"), true);
if ( !isset($available_langs[$lang_code]) ) {
	echo "Sorry, the tool interface has not yet been translated for <code>{$lang_code}</code> language.";
	$i18n = json_decode(file_get_contents("i18n/en.json"), true);
} else {
	$i18n = json_decode(file_get_contents("i18n/{$lang_code}.json"), true);
}

require "web/portal.php";

?>