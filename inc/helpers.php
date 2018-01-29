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

/*
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
*/

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
	$txt_file    = file_get_contents('img/CREDITS');
	$images      = explode("*", $txt_file);
	
	function parseRow($image) {
		if ( strlen($image) < 1 ) {
			return [];
		}
		
		$images_data = explode("\n", $image);
		//print_r($images_data);
		//echo "<br><br>";
		
		$name = trim(preg_replace('/.+ <(.+)\.\w{3}>:/', '$1', $images_data[0]));
		$description = trim(preg_replace('/(.+) <.+>:/', '$1', $images_data[0]));
		$source = trim(preg_replace('/ Source: <(.+)>/', '$1', $images_data[1]));
		$authors = trim(preg_replace('/ Author\(s\): (.+)/', '$1', $images_data[2]));
		$licence = trim(preg_replace('/ Licence: (.+) <.+>/', '$1', $images_data[3]));
		$licenceurl = trim(preg_replace('/ Licence: .+ <(.+)>/', '$1', $images_data[3]));
	
		return [
			"name" => $name,
			"description" => $description,
			"source" => $source,
			"authors" => $authors,
			"licence" => $licence,
			"licenceurl" => $licenceurl 
		];
	}
	
	return array_map("parseRow", $images);
}

function makeImgCredits($names = ['language_selection']) {
	$parsed_data = parseImgCredits();
	
	echo '<hr>';
	//print_r($parsed_data);
	
	
	$needsCredit = function ($imgData) use ($names) {
		return isset($imgData["name"]);
		//return isset($imgData["name"]) && isset($names[$imgData["name"]]);
	};
	
	$makeCreditLine = function ($imgData) {
		$licence = ( isset($imgData['licenceurl']) ) ? "<a href={$imgData['licenceurl']}>{$imgData['licence']}</a>" : $imgData['licence'];
		return "<li><a href={$imgData['source']}>{$imgData['description']}</a> by {$imgData['authors']}: {$licence}</li>";
	};
	
	return implode( array_map($makeCreditLine, array_filter($parsed_data, $needsCredit)) );
}
?>