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

echo "<div class='flex-grid' id='sitelinks'>";
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

if ( true /*count($portal["related_items"]) > 0*/ ) {
	echo makeSubheading( getDeepData($i18n, ['related'], 'Related') );	
	echo "<div class='flex-grid' id='related'>";
	echo makeLoadMoreLink();
	/*
	foreach ($portal["related_items"] as $r) {
		echo makeBoxlink(
			"{$self}/{$r['item']}/{$lang_code}",
			false,
			$r['label'],
			$r['description']
		);
	}
	*/
		//echo makeBoxlink("", false, "more", "");
	echo "</div>";
}

if ( true /*count($portal["nearby_items"]) > 0 */) {
	echo makeSubheading( getDeepData($i18n, ['nearby'], 'Nearby') );
	echo "<div class='flex-grid' id='nearby'>";
	echo makeLoadMoreLink();
	/*
	foreach ($portal["nearby_items"] as $n) {
		echo makeBoxlink(
			"{$self}/{$n['item']}/{$lang_code}",
			false,
			$n['label'],
			$n['description']
		);
	}
	*/
	echo "</div>";
}

if ( true /*count($portal["identifiers"]) > 0*/ ) {
	echo makeSubheading( getDeepData($i18n, ['identifiers'], 'Identifiers') );
	echo "<div class='flex-grid' id='identifiers'>";
	echo makeLoadMoreLink();
	/*
	foreach ($portal["identifiers"] as $ident) {
		echo makeBoxlink(
			$ident["url"],
			false,
			$ident['name'],
			"<div style='word-break:break-all'>{$ident['value']}</div>"
		);
	}
	*/
	echo "</div>";
}

echo makefooter($item_id, $portal["sites_linked"]);

?>
 </body>
</html>