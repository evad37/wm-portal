<html>
<head>
<title>Free Knowledge Portal</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style> <?php include 'styles.css'; ?> </style>
</head>
<body>
<?php
require "core.php";
require "getDefaultLanguage.php";

$protocol = ( !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ) ? "https://" : "http://";
$self = $protocol . htmlspecialchars($_SERVER["HTTP_HOST"]) . "/portal";
// item id and lang code from url query params
$item_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING);// ?: 'Q1';
$lang_code = getBaseLanguage(filter_input(INPUT_GET, 'lang', FILTER_SANITIZE_STRING) ?: getDefaultLanguage());

if ( !preg_match("/^Q\d+$/", $item_id) ) {
    echo("Bad item code.<hr>");
	echo makefooter($item_id);
	die();
}
	

$available_langs = json_decode(file_get_contents("i18n/_langs.json"), true);
if ( !isset($available_langs[$lang_code]) ) {
	echo "Sorry, the tool interface has not yet been translated for <code>{$lang_code}</code> language.";
	$i18n = json_decode(file_get_contents("i18n/en.json"), true);
} else {
	$i18n = json_decode(file_get_contents("i18n/{$lang_code}.json"), true);
}


$item_data = lookupItemData($item_id, $lang_code);
$item_label = getDeepData($item_data, ["labels", $lang_code, "value"], "({$item_id}: {$i18n['nolabel']})");
$item_desc  = getDeepData($item_data, ["descriptions", $lang_code, "value"]); 
$related_items = lookupMultipleItemsData( lookupRelatedItemIds($item_id), $lang_code);
$nearby_items  = lookupMultipleItemsData( lookupNearbyItemIds( lookupCoords($item_id) ), $lang_code);


echo makeHeading(
	$item_label . makeLangSelector($item_id, "{$self}/images/language_selection.png"),
	$item_desc
);

echo "<div class='row'>";
if ( isset($item_data["sitelinks"]) ) {
	foreach ($item_data["sitelinks"] as $site => $site_info) {
		$sitetype = getSiteType($site);
		echo makeBoxlink(
			$site_info["url"],
			"{$self}/images/{$sitetype}.png",
			getDeepData($i18n, [$sitetype, 'type'], $site_info['title']),
			getDeepData($i18n, [$sitetype, 'name'], parse_url($site_info['url'], PHP_URL_HOST))
		);
	}
}
echo makeBoxlink(
	"https://tools.wmflabs.org/reasonator/?q={$item_id}",
	"{$self}/images/reasonator.png",
	getDeepData($i18n, ['reasonator', 'type'], 'Data'),
	getDeepData($i18n, ['reasonator', 'name'], 'Reasonator')
);
echo "</div>";

if ( count($related_items) > 0 ) {
	echo "<div class='row main-desc'>" . getDeepData($i18n, ['related'], 'Related') . "</div>";
	//print_r($related_items);
	
	echo "<div class='row'>";
	
	foreach ($related_items as $r) {
		echo makeBoxlink(
			"{$self}/{$r['item']}/{$lang_code}",
			false,
			$r['label'],
			$r['description']
		);
	}
	echo "</div>";
}

if ( count($nearby_items) > 0 ) {
	echo "<div class='row main-desc'>" . getDeepData($i18n, ['nearby'], 'Nearby') . "</div>";
	//print_r($nearby_items);
	
	echo "<div class='row'>";
	
	foreach ($nearby_items as $n) {
		echo makeBoxlink(
			"{$self}/{$n['item']}/{$lang_code}",
			false,
			$n['label'],
			$n['description']
		);
	}
	echo "</div>";
}

echo makefooter($item_id);
//print_r($result);

?>
 </body>
</html>