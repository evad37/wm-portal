<html>
<head>
<title>Free Knowledge Portal</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style> <?php include 'styles.css'; ?> </style>
</head>
<body>
<?php
$self = htmlspecialchars($_SERVER["PHP_SELF"]);
$host = htmlspecialchars($_SERVER["HTTP_HOST"]);
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

echo makeHeading($item_label, $item_desc);

echo "<div class='row'>";
foreach ($item_data["sitelinks"] as $site => $site_info) {
	echo makeBoxlink(
		$site_info["url"],
		"http://{$host}/portal/images/" . getSiteType($site) . ".png",
		getDeepData($i18n, [getSiteType($site), 'type'], $site_info['title']),
		getDeepData($i18n, [getSiteType($site), 'name'], parse_url($site_info['url'], PHP_URL_HOST))
	);
}
echo makeBoxlink(
	"https://tools.wmflabs.org/reasonator/?q={$item_id}",
	"http://{$host}/portal/images/reasonator.png",
	getDeepData($i18n, ['reasonator', 'type'], 'Data'),
	getDeepData($i18n, ['reasonator', 'name'], 'Reasonator')
);
echo "</div>";

if ( isset($related_items) ) {
	echo "<h3>Related</h3>";
	echo "<div class='row'>";
	
	//print_r($related_items);
	foreach ($related_items as $r_id => $r_label) {
		echo makeBoxlink(
			"{$self}?id={$r_id}&lang={$lang_code}",
			$r_label,
			''
		);
	}
	echo "</div>";
}
echo "<hr>";
echo makefooter($item_id);
//print_r($result);

?>
 </body>
</html>