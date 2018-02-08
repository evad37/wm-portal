<?php

require "inc/items.php";

echo_html_top($item_label);

echo makeHeading(
	$item_label . makeLangSelector($item_id, "{$self}/img/language_selection.png", $lang_code, $available_langs)
);
echo makeSubheading($item_desc);

echo "<div class='flex-grid'>";
foreach ($sitelinks as $site => $site_info) {
	$sitetype = $site_types[$site];
	echo makeBoxlink(
		$site_info["url"],
		"{$self}/img/{$sitetype}.png",
		getDeepData($i18n, [$sitetype, 'type'], $site_info['title']),
		getDeepData($i18n, [$sitetype, 'name'], parse_url($site_info['url'], PHP_URL_HOST))
	);
}
echo "</div>";

if ( count($related_items) > 0 ) {
	echo makeSubheading( getDeepData($i18n, ['related'], 'Related') );	
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

$sites_used = array_flip(array_map($mapToSiteType, array_keys($sitelinks)));
echo makefooter($item_id, $sites_used);

?>
 </body>
</html>