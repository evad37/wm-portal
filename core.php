 <?php
// set data vars
$tool_info = [
	"name" => "Knowledge Portal",
	"version" => "0.0.3-dev",
	"updated" => "2017-12-28",
	"author_name" => "Evad37",
	"author_contact" => "https://en.wikipedia.org/wiki/User:Evad37"
];

$site_logos = [
	"commons" => "",
	"wikipedia" => "https://upload.wikimedia.org/wikipedia/commons/thumb/1/18/OOjs_UI_icon_logo-wikipedia-invert.svg/200px-OOjs_UI_icon_logo-wikipedia-invert.svg.png",
	"wikisource" => "",
	"wikivoyage" => ""
];


/* ---------- Helper functions ------------------------------------------------------------------ */
// Get data from deeply nested arrays, or a default value if any of the nested keys aren't set;
// i.e. `getDeepData($arr, [key1, key2, key3])` is equivalent to `$arr[key1][key2[key3]`
// if all the keys are set.
function getDeepData($arr, $keys, $default="") {
	$data = $arr;
	foreach ( $keys as $key ) {
		if ( !isset($data[$key]) ) {
			return $default;
		}
		$data = $data[$key];
	}
	return $data;
}

function getSiteType($site_code) {
	if ( $site_code === 'commonswiki' ) {
		return 'commons';
	}
	if (strpos($site_code, 'wikisource') !== false) {
		return 'wikisource';
	}
	if (strpos($site_code, 'wikivoyage') !== false) {
		return 'wikivoyage';
	}
	return 'wikipedia';
}

function extractPageTitle($page) {
	return $page["title"];
}

function joinWithPipes($v1, $v2) {
	if ( $v1 === "" ) {
		return $v2;
	}
	return "{$v1}|{$v2}";
}	

function simplifyItemData($itemId, $itemData) {
	$lang_code = $GLOBALS['lang_code'];
	$label = getDeepData($itemData, ["labels", $lang_code, "value"], "[no {$lang_code} label]");
	$description = getDeepData($itemData, ["descriptions", $lang_code, "value"], "");
	return [
		"item" => $itemId,
		"label" => $label,
		"description" => $description
	];
}
	
// Api class
class Api
{

    private	function makeUserAgent() {
		$tool_info = $GLOBALS['tool_info'];
		return $tool_info["name"] . ' ' . $tool_info["version"] . ' (' . $tool_info["updated"] . ') Contact: ' . $tool_info["author_name"] . ' < ' . $tool_info["author_contact"] . ' >';
	}
	
    public function get($options) {
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_USERAGENT, $this->makeUserAgent());
		curl_setopt($ch, CURLOPT_URL, "https://www.wikidata.org/w/api.php?" . http_build_query($options));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		$response = curl_exec($ch);
		
		if(curl_errno($ch)){
			curl_close($ch);
			$fatal_error = [
				"error" => [
					"code" => "Fatal error",
					"info" => curl_error($ch) . " API connection failed."
				]
			];
		}
		
		curl_close($ch);
		
		$result = ( isset($fatal_error) ) ? $fatal_error : json_decode($response, true);
		
		if ( /*!isset($result['success']) ||*/ isset($result['error']) ) {
			$error_code = getDeepData($result, ['error', 'code'], false);
			$error_info = getDeepData($result, ['error', 'info'], 'An unknown error occurred.');
			echo ( $error_code ) ? "Api error {$error_code}: {$error_info}" : "Api error: {$error_info}";
			die();
		}
		
		return $result;
    }
}

$api = new Api();

/* ---------- Requests -------------------------------------------------------------------------- */
function lookupItemData ($item_id, $lang_code) {
	$api = $GLOBALS['api'];
	$result = $api->get([
		"action" => "wbgetentities",
		"format" => "json",
		"ids" => $item_id,
		"redirects" => "yes",
		"props" => "labels|descriptions|sitelinks|sitelinks/urls",
		"languages" => $lang_code,
		"sitefilter" => "{$lang_code}wiki|{$lang_code}wikivoyage|{$lang_code}wikisource|commonswiki"
	]);
	
	if ( !isset($result["entities"]) || !isset($result["entities"][$item_id]) ) {
		die('API error: no data for item ' . $item_id);
	}

	return $result["entities"][$item_id];
}

function lookupRelatedItemIds ($item_id) {
	$api = $GLOBALS['api'];
	
	$result = $api->get([
		"action" => "query",
		"format" => "json",
		"prop" => "linkshere",
		"titles" => $item_id,
		"lhprop" => "title",
		"lhnamespace" => "0",
		"lhshow" => "!redirect",
		"lhlimit" => "3"
	]);
	$pageid = array_keys($result["query"]["pages"])[0];
	$linkshere = getDeepData($result, ["query", "pages", $pageid, "linkshere"], []);
	$related_ids = array_map("extractPageTitle", $linkshere);
	return $related_ids;
}

function lookupCoords($item_id) {
	$api = $GLOBALS['api'];
		
	$result = $api->get([
		"action" => "query",
		"format" => "json",
		"prop" => "coordinates",
		"titles" => $item_id,
		"colimit" => "1",
		"coprimary" => "primary"
	]);
	$pageid = array_keys($result["query"]["pages"])[0];
	$coords = getDeepData($result, ["query", "pages", $pageid, "coordinates", 0], false);
	return $coords;
}

function lookupNearbyItemIds($coords) {
	if ( !$coords ) {
		return [];
	}
	if ( !isset($coords['lat']) || !isset($coords['lon']) || !isset($coords['globe']) ) {
		return [];
	}
	
	$api = $GLOBALS['api'];
		
	$result = $api->get([
		"action" => "query",
		"format" => "json",
		"list" => "geosearch",
		"gscoord" => joinWithPipes($coords['lat'], $coords['lon']),
		"gsradius" => "5000",
		"gsglobe" => $coords['globe'],
		"gslimit" => "10",
		"gsnamespace" => "0",
		"gsprimary" => "primary"
	]);

	$geosearch = getDeepData($result, ['query', 'geosearch'], false);
	if ( !$geosearch ) {
		return [];
	}
	
	function notTooClose($item) {
		return getDeepData($item, ['dist'], -1) > 5;
	}
	
	$nearby = array_filter($geosearch, 'notTooClose');
	$nearby_shortlist =  array_slice($nearby, 0, 3);
	$nearby_ids = array_map("extractPageTitle", $nearby_shortlist);

	return $nearby_ids;
}

function lookupMultipleItemsData($related_ids, $lang_code) {
	if (count($related_ids) == 0 ){
		return [];
	}

	$api = $GLOBALS['api'];
	
	$result = $api->get([
		"action" => "wbgetentities",
		"format" => "json",
		"ids" => array_reduce($related_ids, "joinWithPipes", ""),
		"redirects" => "yes",
		"props" => "labels|descriptions",
		"languages" => $lang_code
	]);
	$entities = getDeepData($result, ["entities"], false);
	if ( !$entities ) {
		return [];
	}
	

	
	return array_map("simplifyItemData", array_keys($entities), $entities);
}


/* ---------- Formatting ------------------------------------------------------------------------ */
function makeHeading ($label, $description) {
	return "<div class='row main-label'>{$label}</div>
		<div class='row main-desc'>{$description}</div>";
}

function makeBoxlink ($url, $logo, $title, $subtitle) {
	$img = ( $logo ) ? "<img class='logo' src='{$logo}' alt='{$subtitle}'>" : '';
	return "<div class=column>
	<a class='box' href='{$url}'>
		{$img}
		<div class='box-label'>{$title}</div>
		<div class='box-desc'>{$subtitle}</div>
	</a></div>";
}

function makefooter ($id) {
	return "<div class='footer small'>
	The Free Knowledge Portal uses data from <a href='https://www.wikidata.org/'>Wikidata</a>.
	Text is available under the terms of the <a href=https://creativecommons.org/publicdomain/zero/1.0/CC0 license>Creative Commons CC0 License</a>.
	<br>
	<a href='https://commons.wikimedia.org/wiki/File:Reasonator_logo_proposal.png'>Reasonator icon</a> by CristianCantoro: <a href='https://creativecommons.org/licenses/by-sa/3.0/deed.en'>CC-BY-SA 3.0 Unported license</a></li>
	</br>
	Wikimedia site icons ™ Wikimedia Foundation, Inc. (used here under the <a href='https://wikimediafoundation.org/wiki/Trademark_policy'>Trademark policy</a>, section 3.6):
	<ul style=text-align:left;margin-top:5px>
	<li><a href='https://commons.wikimedia.org/wiki/File:Notification-icon-Commons-logo.svg'>Wikimedia Commons icon</a> by User:Jdforrester (WMF), User:3247, User:Reidab: Public domain</li>
	<li><a href='https://commons.wikimedia.org/wiki/File:Notification-icon-Wikipedia-logo.svg'>Wikipedia icon</a> by User:Jdforrester (WMF), Jonathan Hoefler: Public domain</li>
	<li><a href='https://commons.wikimedia.org/wiki/File:Notification-icon-Wikisource-logo.svg'>Wikisource icon</a> by User:Jdforrester (WMF), User:Rei-artur, Nicholas Moreau: <a href='https://creativecommons.org/licenses/by-sa/3.0/deed.en'>CC-BY-SA 3.0 Unported license</a></li>
	<li><a href='https://commons.wikimedia.org/wiki/File:Notification-icon-Wikivoyage-logo.svg'>Wikivoyage icon</a> by User:Jdforrester (WMF), User:AleXXw, User:‎Danapit:  <a href='https://creativecommons.org/licenses/by-sa/3.0/deed.en'>CC-BY-SA 3.0 Unported license</a></li>
	</ul>";
}