<?php

$portal = $getPortalInfo();

echo_html_top($portal["item_label"]);

echo makeHeading(
	$portal["item_label"] . makeLangSelector(
		$item_id,
		"{$self}/img/language_selection.png",
		$lang_code,
		$available_langs
	)
);
echo makeSubheading($portal["item_desc"]);

echo "<div class='flex-grid'>";
foreach ($portal["sitelinks"] as $site => $site_info) {
	$sitetype = $site_types[$site];
	echo makeBoxlink(
		$site_info["url"],
		"{$self}/img/{$sitetype}.png",
		getDeepData($i18n, [$sitetype, 'type'], $site_info['title']),
		getDeepData($i18n, [$sitetype, 'name'], parse_url($site_info['url'], PHP_URL_HOST))
	);
}
echo "</div>";

if ( count($portal["related_items"]) > 0 ) {
	echo makeSubheading( getDeepData($i18n, ['related'], 'Related') );	
	echo "<div class='flex-grid'>";
	foreach ($portal["related_items"] as $r) {
		echo makeBoxlink(
			"{$self}/{$r['item']}/{$lang_code}",
			false,
			$r['label'],
			$r['description']
		);
	}
	echo "</div>";
}

if ( count($portal["nearby_items"]) > 0 ) {
	echo makeSubheading( getDeepData($i18n, ['nearby'], 'Nearby') );
	echo "<div class='flex-grid'>";	
	foreach ($portal["nearby_items"] as $n) {
		echo makeBoxlink(
			"{$self}/{$n['item']}/{$lang_code}",
			false,
			$n['label'],
			$n['description']
		);
	}
	echo "</div>";
}

echo makefooter($item_id, $portal["sites_linked"]);

?>
 </body>
</html>