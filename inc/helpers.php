 <?php
/* ---------- Helper functions ------------------------------------------------------------------ */
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

function addReasonator($sitelinks, $item_id, $lang_code) {
	return array_merge($sitelinks, [
			"reasonator" => [
				"url" => "https://tools.wmflabs.org/reasonator/?q={$item_id}&lang={$lang_code}",
				"title" => "Data"
			]
		]
	);
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

function parseImgCredits() {
	$credits = json_decode(file_get_contents("img/CREDITS.json"), true);
	$licences = $credits["licences"];
	$images = $credits["images"];
	
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

function makeImgCredits($imgs_used = []) {
	$parsed_data = parseImgCredits();
	
	$needsCredit = function ($imgData) use ($imgs_used) {
		return getDeepData($imgs_used, [$imgData["name"]], false) !== false;
	};
	
	$makeCreditLine = function ($imgData) {
		return "<li><a href={$imgData['source']}>{$imgData['title']}</a> by {$imgData['authors']}: <a href={$imgData['licenceurl']}>{$imgData['licence']}</a></li>";
	};
	
	return implode( array_map($makeCreditLine, array_filter($parsed_data, $needsCredit)) );
}
?>