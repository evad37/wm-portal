<?php
	echo makeHeading("Free Knowledge Portal");
	echo makeSubheading("Stable urls that showcase a Wikidata item's sitelinks, and related items.");
	if ( strlen($item_id) > 0 ) {
		echo("Bad item code: Must be a number prefixed with \"Q\".<hr>");
	}
	echo (
		"<p style='text-align:center'>
		Try <a href={$self}/Q100>{$self}/Q100</a> (or any other Wikidata item id) to see this tool in action.<br>
		Or try <a href={$self}/Q100>{$self}/Q100/fr</a> (or any other language) to see it in another language.
		</p>"
	);
?>