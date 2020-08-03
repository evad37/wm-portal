<?php 

// --------- Configuration variables --------------------------------------------------------------
$protocol = ( htmlspecialchars($_SERVER['HTTP_HOST']) == 'localhost' ) ? "http://" : "https://";
$self = $protocol . htmlspecialchars($_SERVER["HTTP_HOST"]);
// item id and lang code from url query params
$item_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING);
$lang_code = getBaseLanguage(filter_input(INPUT_GET, 'lang', FILTER_SANITIZE_STRING) ?: getDefaultLanguage());
$page_title = filter_input(INPUT_GET, 'title', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
$page_site = filter_input(INPUT_GET, 'site', FILTER_SANITIZE_STRING);
$show_more = filter_input(INPUT_GET, 'more', FILTER_SANITIZE_STRING) ?: false;

$sites = [
	"wikipedia" => "{$lang_code}wiki",
	"wikivoyage" => "{$lang_code}wikivoyage",
	"wikibooks" => "{$lang_code}wikibooks",
	"wikinews" => "{$lang_code}wikinews",
	"wikiquote" => "{$lang_code}wikiquote",
	"wikisource" => "{$lang_code}wikisource",
	"wikispecies" => "specieswiki",
	"wikiversity" => "{$lang_code}wikiversity",
	"wiktionary" => "{$lang_code}wiktionary",
	"commons" => "commonswiki",
	"reasonator" => "reasonator"
];
$site_types = array_flip($sites);
$site_order = array_keys($site_types);

/* ---------- Array mapping functions ----------------------------------------------------------- */

$mapToSiteType = function($site) use ($site_types) {
	return $site_types[$site];
};

function extractPageTitle($page) {
	return $page["title"];
}

function extractDataValue($claim) {
	return getDeepData($claim, [0, 'mainsnak', 'datavalue', 'value']);
}

function simplifyIdentiferData($value, $formatterUrl, $propertyData) {
	return [
		"name" => $propertyData['label'],
		"value" => $value,
		"url" => str_replace('$1', $value, $formatterUrl)
	];
}

/* ---------- Array reducing functions ---------------------------------------------------------- */

function joinWithPipes($v1, $v2) {
	if ( $v1 === "" ) {
		return $v2;
	}
	return "{$v1}|{$v2}";
}

/* ---------- Array filtering functions --------------------------------------------------------- */

function claimIsForExternalId($claim) {
	return getDeepData($claim, [0, 'mainsnak', 'datatype']) === "external-id";
}

/* ---------- Other array functions ------------------------------------------------------------- */

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

// Sort an associative array by its keys, where the order is given in an array (numerically indexed)
function sortByKey($arr, $key_order) {
	$orderOfKeys = array_flip($key_order);
	uksort($arr, function($key1, $key2) use ($orderOfKeys) {
		return ( getDeepData($orderOfKeys, [$key1], 0) - getDeepData($orderOfKeys, [$key2], 0) );
	});
	return $arr;
}

function addReasonator($sitelinks, $item_id, $lang_code) {
	return array_merge($sitelinks, [
			"reasonator" => [
				"url" => "https://tools.wmflabs.org/reasonator/?q={$item_id}&lang={$lang_code}",
				"title" => "Data"
			]
		]
	);
}

/* ---------- Other helper functions ------------------------------------------------------------------ */
// Returns the base language (i.e. `en` for `en-gb`), except for language variants which have a
// Wikipedia.
function getBaseLanguage($code) {
	if ( !strpos($code, '-') ) {
		return $code;
	}
	
	switch ($code) {
		// Languages with dashes which have Wikipedias can be returned as-is:
		case 'zh-yue':
		case 'roa-rup':
		case 'map-bms':
		case 'nds-nl':
		case 'bat-smg':
		case 'roa-tara':
		case 'fiu-vro':
		case 'zh-min-nan':
		case 'zh-classical':
		case 'cbk-zam':
		case 'be-x-old':
			return $code;
			break;
		default:
			// Strip everything after and including the first dash
			return explode("-", $code)[0];
	}
}

function parseImgCredits() {
	$credits = json_decode(file_get_contents("img/CREDITS.json"), true);
	$licences = $credits["licences"];
	if ( isset($GLOBALS['sites']) ) {
		$images = sortByKey($credits["images"], array_keys($GLOBALS['sites']));
	} else {
		$images = $credits["images"];
	}
	
	$parseRow = function ($name, $image_data) use ($licences) {	
		$licence = $licences[ $image_data["license"] ];
		return [
			"name" => $name,
			"title" => $image_data["title"],
			"source" =>  $image_data["source"],
			"authors" => $image_data["authors"],
			"licence" => $licence["name"],
			"licenceurl" => $licence["url"]
		];
	};
	
	return array_map($parseRow, array_keys($images), $images);
}

function filterImageCredits($all_image_credits, $imgs_used) {
	$needsCredit = function ($imgData) use ($imgs_used) {
		return getDeepData($imgs_used, [$imgData["name"]], false) !== false;
	};

	return array_filter($all_image_credits, $needsCredit);
}

function getRelevantImageCredits($imgs_used = []) {
	if ( count($imgs_used) === 0 ) {
		return [];
	}
	return filterImageCredits( parseImgCredits(), $imgs_used );
}

/* ---------- Wikidata API ---------------------------------------------------------------------- */ 
class Api
{
	private $tool_info = [
		"name" => "Free Knowledge Portal",
		"version" => "1.1.0",
		"updated" => "2018-02-12",
		"author_name" => "Evad37",
		"author_contact" => "https://en.wikipedia.org/wiki/User:Evad37"
	];
	
    private	function makeUserAgent() {
		$info = $this->tool_info;
		return $info["name"] . ' ' . $info["version"] . ' (' . $info["updated"] . ') Contact: ' . $info["author_name"] . ' < ' . $info["author_contact"] . ' >';
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
		
		if ( isset($result['error']) ) {
			$error_code = getDeepData($result, ['error', 'code'], false);
			$error_info = getDeepData($result, ['error', 'info'], 'An unknown error occurred.');
			echo ( $error_code ) ? "Api error {$error_code}: {$error_info}" : "Api error: {$error_info}";
			die();
		}
		
		return $result;
    }
}
class ApiManager
{
	private $api;
	public function __construct() {
		$this->api = new Api(); 
	}

	function lookupItemData ($item_id, $lang_code, $sites) {
		$result = $this->api->get([
			"action" => "wbgetentities",
			"format" => "json",
			"ids" => $item_id,
			"redirects" => "yes",
			"props" => "labels|descriptions|sitelinks|sitelinks/urls",
			"languages" => $lang_code,
			"sitefilter" => implode("|", $sites)
		]);
		
		if ( !isset($result["entities"]) || !isset($result["entities"][$item_id]) ) {
			die('API error: no data for item ' . $item_id);
		}

		return $result["entities"][$item_id];
	}

	function lookupRelatedItemIds ($item_id, $limit=8) {
		$result = $this->api->get([
			"action" => "query",
			"format" => "json",
			"prop" => "linkshere",
			"titles" => $item_id,
			"lhprop" => "title",
			"lhnamespace" => "0",
			"lhshow" => "!redirect",
			"lhlimit" => $limit
		]);
		$pageid = array_keys($result["query"]["pages"])[0];
		$linkshere = getDeepData($result, ["query", "pages", $pageid, "linkshere"], []);
		$related_ids = array_map("extractPageTitle", $linkshere);
		return $related_ids;
	}

	function lookupCoords($item_id) {			
		$result = $this->api->get([
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
			
		$result = $this->api->get([
			"action" => "query",
			"format" => "json",
			"list" => "geosearch",
			"gscoord" => joinWithPipes($coords['lat'], $coords['lon']),
			"gsradius" => "5000",
			"gsglobe" => $coords['globe'],
			"gslimit" => "20",
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
		$nearby_shortlist =  array_slice($nearby, 0, 8);
		$nearby_ids = array_map("extractPageTitle", $nearby_shortlist);

		return $nearby_ids;
	}

	function lookupMultipleItemsData($related_ids, $lang_code) {
		if (count($related_ids) == 0 ){
			return [];
		}
		
		$result = $this->api->get([
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

		$simplifyItemData = function ($itemId, $itemData) use ($lang_code) {
			$i18n = $GLOBALS['i18n'];
			$label = getDeepData($itemData, ["labels", $lang_code, "value"], "({$itemId}: {$i18n['nolabel']})");
			$description = getDeepData($itemData, ["descriptions", $lang_code, "value"], "");
			return [
				"item" => $itemId,
				"label" => $label,
				"description" => $description
			];
		};
		
		return array_map($simplifyItemData, array_keys($entities), $entities);
	}
	
	function lookupIdForPage($title, $site) {
		$result = $this->api->get([
			"action" => "wbgetentities",
			"format" => "json",
			"sites" => $site,
			"titles" => $title,
			"redirects" => "yes",
			"props" => ""		
		]);
		$id = array_keys($result["entities"])[0];
		return $id;
	}
	
	function lookupExternalIdentifiers($item_id, $limit=8) {
		$result = $this->api->get([
			"action" => "wbgetclaims",
			"format" => "json",
			"entity" => $item_id,
			"props" => ""
		]);
		
		if ( !isset($result["claims"]) ) {
			return [];
		}
		
		$identifierClaims = array_filter($result["claims"], 'claimIsForExternalId');
		$identifiersValues = array_map('extractDataValue', $identifierClaims);
		return array_slice($identifiersValues, 0, $limit);
	}
	
	function lookupFormatterUrl($property_id) {
		$result = $this->api->get([
			"action" => "wbgetclaims",
			"format" => "json",
			"entity" => $property_id,
			"property" => "P1630",
			"props" => ""
		]);
		return getDeepData($result, ['claims', 'P1630', '0', 'mainsnak', 'datavalue', 'value']);
	}

}

$api = new ApiManager();

/* ---------- Item data ------------------------------------------------------------------ */
$getPortalInfo = function() use ($api, $item_id, $lang_code, $sites, $site_order, $mapToSiteType) {
	$i18n = $GLOBALS['i18n'];
	
	$item_data  = $api->lookupItemData($item_id, $lang_code, $sites);
	if ( isset($item_data["redirects"]) ) {
		$item_id = $item_data["redirects"]["to"];
	}
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
	$has_coords = !!($api->lookupCoords($item_id));
	$has_related_items = count($api->lookupRelatedItemIds($item_id, 1)) > 0;
	$has_identifiers = count($api->lookupExternalIdentifiers($item_id, 1)) > 0;
	$sites_linked = array_flip(array_map($mapToSiteType, array_keys($sitelinks)));
	$image_credits = getRelevantImageCredits($sites_linked);
	
	return [
		"item_id" => $item_id,
		"item_label" => $item_label,
		"item_desc" => $item_desc,
		"sitelinks" => $sitelinks,
		"has_coords" => $has_coords,
		"has_related_items" => $has_related_items,
		"has_identifiers" => $has_identifiers,
		"sites_linked" => $sites_linked,
		"image_credits" => $image_credits
	];
	
};

$getRelatedInfo = function() use ($api, $item_id, $lang_code, $sites) {
	$i18n = $GLOBALS['i18n'];
	
	$item_data  = $api->lookupItemData($item_id, $lang_code, $sites);
	$item_label = getDeepData($item_data, ["labels", $lang_code, "value"], "({$item_id}: {$i18n['nolabel']})");
	$item_desc  = getDeepData($item_data, ["descriptions", $lang_code, "value"]);
	
	$related_items = $api->lookupMultipleItemsData(
		$api->lookupRelatedItemIds($item_id),
		$lang_code
	);
	
	return [
		"item_label" => $item_label,
		"item_desc" => $item_desc,
		"related_items" => $related_items
	];
	
};

$getNearbyInfo = function() use ($api, $item_id, $lang_code, $sites) {
	$i18n = $GLOBALS['i18n'];
	
	$item_data  = $api->lookupItemData($item_id, $lang_code, $sites);
	$item_label = getDeepData($item_data, ["labels", $lang_code, "value"], "({$item_id}: {$i18n['nolabel']})");
	$item_desc  = getDeepData($item_data, ["descriptions", $lang_code, "value"]);
	
	$nearby_items  = $api->lookupMultipleItemsData(
		$api->lookupNearbyItemIds( $api->lookupCoords($item_id) ),
		$lang_code
	);
	
	return [
		"item_label" => $item_label,
		"item_desc" => $item_desc,
		"nearby_items" => $nearby_items
	];
	
};

$getIdentifiersInfo = function() use ($api, $item_id, $lang_code, $sites) {
	$i18n = $GLOBALS['i18n'];
	
	$item_data  = $api->lookupItemData($item_id, $lang_code, $sites);
	$item_label = getDeepData($item_data, ["labels", $lang_code, "value"], "({$item_id}: {$i18n['nolabel']})");
	$item_desc  = getDeepData($item_data, ["descriptions", $lang_code, "value"]);
	
	$identifiers_values = $api->lookupExternalIdentifiers($item_id);
	$identifiers_formatterUrls = array_map(
		function($id) use ($api) {
			return $api->lookupFormatterUrl($id);
		},
		array_keys($identifiers_values)
	);
	$identifiers_propertyData = $api->lookupMultipleItemsData(
		array_keys($identifiers_values),
		$lang_code
	);
	$identifiers = array_map(
		"simplifyIdentiferData",
		$identifiers_values,
		$identifiers_formatterUrls,
		$identifiers_propertyData
	);
	
	return [
		"item_label" => $item_label,
		"item_desc" => $item_desc,
		"identifiers" => $identifiers
	];
	
};
?>