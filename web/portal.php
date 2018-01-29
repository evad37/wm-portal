<?php

$item_data = $api->lookupItemData($item_id, $lang_code, $sites);
$item_label = getDeepData($item_data, ["labels", $lang_code, "value"], "({$item_id}: {$i18n['nolabel']})");
$item_desc  = getDeepData($item_data, ["descriptions", $lang_code, "value"]); 
$related_items = $api->lookupMultipleItemsData(
	$api->lookupRelatedItemIds($item_id),
	$lang_code
);
$nearby_items  = $api->lookupMultipleItemsData(
	$api->lookupNearbyItemIds( $api->lookupCoords($item_id) ),
	$lang_code
);

echo_html_top($item_label);

echo makeHeading(
	$item_label . makeLangSelector($item_id, "{$self}/img/language_selection.png", $lang_code, $available_langs)
);
echo makeSubheading($item_desc);

echo "<div class='flex-grid'>";
if ( isset($item_data["sitelinks"]) ) {
	foreach ($item_data["sitelinks"] as $site => $site_info) {
		$sitetype = $site_types[$site];
		echo makeBoxlink(
			$site_info["url"],
			"{$self}/img/{$sitetype}.png",
			getDeepData($i18n, [$sitetype, 'type'], $site_info['title']),
			getDeepData($i18n, [$sitetype, 'name'], parse_url($site_info['url'], PHP_URL_HOST))
		);
	}
}
echo makeBoxlink(
	"https://tools.wmflabs.org/reasonator/?q={$item_id}&lang={$lang_code}",
	"{$self}/img/reasonator.png",
	getDeepData($i18n, ['reasonator', 'type'], 'Data'),
	getDeepData($i18n, ['reasonator', 'name'], 'Reasonator')
);
echo "</div>";

if ( count($related_items) > 0 ) {
	echo makeSubheading( getDeepData($i18n, ['related'], 'Related') );
	//print_r($related_items);
	
	echo "<div class='flex-grid'>";
	
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
	echo makeSubheading( getDeepData($i18n, ['nearby'], 'Nearby') );
	//print_r($nearby_items);
	
	echo "<div class='flex-grid'>";
	
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