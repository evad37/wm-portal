<?php

$item_data  = $api->lookupItemData($item_id, $lang_code, $sites);
$item_label = getDeepData($item_data, ["labels", $lang_code, "value"], "({$item_id}: {$i18n['nolabel']})");
$item_desc  = getDeepData($item_data, ["descriptions", $lang_code, "value"]);
$sitelinks  = sortByKey(
	addReasonator(
		getDeepData($item_data, ["sitelinks"], []),
		$item_id,
		$lang_code
	),
	$site_order
);
$related_items = $api->lookupMultipleItemsData(
	$api->lookupRelatedItemIds($item_id),
	$lang_code
);
$nearby_items  = $api->lookupMultipleItemsData(
	$api->lookupNearbyItemIds( $api->lookupCoords($item_id) ),
	$lang_code
);

?>