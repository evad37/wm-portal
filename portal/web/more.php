<?php

switch ($show_more) {
    case "related":
		$portal = $getRelatedInfo();
		break;
    case "nearby":
		$portal = $getNearbyInfo();
        break;
    case "identifiers":
		$portal = $getIdentifiersInfo();
        break;
}

echo_html_top($portal["item_label"] . ': ' . getDeepData($i18n, [$show_more], $show_more) );

echo makeHeading(
	getDeepData($i18n, [$show_more], $show_more)
);

$itemLabelAndDescription = "<strong>{$portal['item_label']}</strong>" .
 (( $portal["item_desc"] ) ? ": {$portal["item_desc"]}" : '');
echo makeSubheading($itemLabelAndDescription);

echo "<div class='flex-grid' id='{$show_more}'>";

switch ($show_more) {
    case "related":
		foreach ($portal["related_items"] as $r) {
			echo makeBoxlink(
				"{$self}/{$r['item']}/{$lang_code}",
				false,
				$r['label'],
				$r['description']
			);
		}
		break;
    case "nearby":
		foreach ($portal["nearby_items"] as $n) {
			echo makeBoxlink(
				"{$self}/{$n['item']}/{$lang_code}",
				false,
				$n['label'],
				$n['description']
			);
		}
        break;
    case "identifiers":
		foreach ($portal["identifiers"] as $ident) {
			echo makeBoxlink(
				$ident["url"],
				false,
				$ident['name'],
				"<div style='word-break:break-all'>{$ident['value']}</div>"
			);
		}
        break;
}

echo "</div>";

echo makefooter($item_id);

?>
 </body>
</html>