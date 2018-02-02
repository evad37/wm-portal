<?php
require "inc/helpers.php";
require "inc/external/getDefaultLanguage.php";
require "inc/formatting.php";

$protocol = ( htmlspecialchars($_SERVER['HTTP_HOST']) == 'localhost' ) ? "http://" : "https://";
$self = $protocol . htmlspecialchars($_SERVER["HTTP_HOST"]) . "/portal";
// item id and lang code from url query params
$item_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING);
$lang_code = getBaseLanguage(filter_input(INPUT_GET, 'lang', FILTER_SANITIZE_STRING) ?: getDefaultLanguage());
$page_title = filter_input(INPUT_GET, 'title', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
$page_site = filter_input(INPUT_GET, 'site', FILTER_SANITIZE_STRING);

// When there's no valid item id, nor a page title & site, show the about page instead
$has_title_and_site = !!$page_title && !! $page_site;
if ( !preg_match("/^Q\d+$/", $item_id) && !$has_title_and_site ) {
	echo_html_top();
	require "web/about.php";
	require "web/qr.php";
	echo makefooter();
	echo "</body></html>";
	die();
}

require "inc/sites.php";
require "inc/api.php";

// Backwards compatibility, when given page title & site instead of id
if ( !$item_id && $has_title_and_site ) {
	$page_id = $api->lookupIdForPage($page_title, $page_site);
	if ( !preg_match("/^Q\d+$/", $page_id) ) {
		echo_html_top();
		echo "<p style='font-size:120%;margin:0.5em;'>Sorry, page <span style='font-style: italic;'>{$page_title}</span>
		was not found on site <span style='font-style: italic;'>{$page_site}</span>.</p>
		<p style='margin:0.5em;'>Either the page does not exist on that wiki, or it is not connected to Wikidata.</p>
		<hr>";
		require "web/about.php";
		echo makefooter();
		echo "</body></html>";
		die();
	}
	header("Location:{$self}/{$page_id}");
	die();
}

$available_langs = json_decode(file_get_contents("i18n/_langs.json"), true);
if ( !isset($available_langs[$lang_code]) ) {
	echo "Sorry, the tool interface has not yet been translated for <code>{$lang_code}</code> language.";
	$i18n = json_decode(file_get_contents("i18n/en.json"), true);
} else {
	$i18n = json_decode(file_get_contents("i18n/{$lang_code}.json"), true);
}

require "web/portal.php";

?>