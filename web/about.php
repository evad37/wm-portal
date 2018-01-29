<?php
	echo_html_top();
	echo makeHeading("Free Knowledge Portal");
	echo makeSubheading("Stable urls that showcase a Wikidata item's sitelinks, and related items &ndash; suitable for QR codes.");
	if ( strlen($item_id) > 0 ) {
		echo("Bad item code: Must be a number prefixed with \"Q\".<hr>");
	}
	echo("Try <a href={$self}/Q100>{$self}/Q100</a> (or any other Wikidata item id) to see this tool in action.<br>");
	echo("Or try <a href={$self}/Q100>{$self}/Q100/fr</a> (or any other language) to see it in another language.<hr>");
	echo makefooter();
	echo "</body></html>";
?>