<?php

$portal = $getPortalInfo();

$jquerySrc = ( htmlspecialchars($_SERVER['HTTP_HOST']) == 'localhost' ) ? "http://code.jquery.com/jquery-3.3.1.min.js" : "https://tools-static.wmflabs.org/cdnjs/ajax/libs/jquery/3.3.1/jquery.min.js";
$scripts = "<script type='text/javascript' src='{$jquerySrc}' defer></script>
	<script type='text/javascript' src='{$self}/js/loadmore.js' defer></script>";
echo_html_top($portal["item_label"], $scripts);

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


if ( $portal["has_related_items"] ) {
	echo makeSubheading( getDeepData($i18n, ['related'], 'Related'), 'related-heading' );	
	echo "<div class='flex-grid' id='related'>";
	echo makeLoadMoreLink('related');
	echo "</div>";
}
if ( $portal["has_coords"] ) {
	echo makeSubheading( getDeepData($i18n, ['nearby'], 'Nearby'), 'nearby-heading' );
	echo "<div class='flex-grid' id='nearby'>";
	echo makeLoadMoreLink('nearby');
	echo "</div>";
}
if ( $portal["has_identifiers"] ) {
	echo makeSubheading( getDeepData($i18n, ['identifiers'], 'Identifiers'), 'identifiers-heading' );
	echo "<div class='flex-grid' id='identifiers'>";
	echo makeLoadMoreLink('identifiers');
	echo "</div>";
}
echo makefooter($item_id, $portal["sites_linked"]);

?>
 </body>
</html>