<html>
<head>
<title>Free Knowledge Portal</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style> <?php include 'styles.css'; ?> </style>
</head>
<body>
<?php
$self = "http://" . htmlspecialchars($_SERVER["HTTP_HOST"]) . "/portal";
// item id and lang code from url query params
$item_id = ( isset($_GET['id']) ) ? $_GET['id'] : 'Q1';
$lang_code = ( isset($_GET['lang']) ) ? $_GET['lang'] : 'en';

// TODO: input validation

// i18n
$i18n = json_decode(file_get_contents("i18n/{$lang_code}.json"), true);


require "core.php";

$item_data = lookupItemData($item_id, $lang_code);
$item_label = getDeepData($item_data, ["labels", $lang_code, "value"], "({$item_id})");
$item_desc  = getDeepData($item_data, ["descriptions", $lang_code, "value"]); 
$related_items = lookupRelatedItemsData( lookupRelatedItemIds($item_id), $lang_code);

echo makeHeading($item_label, $item_desc);

echo "<div class='row'>";

foreach ($item_data["sitelinks"] as $site => $site_info) {
	$sitetype = getSiteType($site);
	echo makeBoxlink(
		$site_info["url"],
		"{$self}/images/{$sitetype}.png",
		getDeepData($i18n, [$sitetype, 'type'], $site_info['title']),
		getDeepData($i18n, [$sitetype, 'name'], parse_url($site_info['url'], PHP_URL_HOST))
	);
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

echo makefooter($item_id);
//print_r($result);

?>
 </body>
</html>