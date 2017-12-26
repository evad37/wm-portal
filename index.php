<html>
 <head>
  <title>PHP Curl Test</title>
 </head>
 <body>
 <?php
	// set data vars
	$tool_info = [
		"name" => "Knowledge Portal",
		"version" => "0.0.1-dev [just doing some initial tests]",
		"updated" => "2017-12-14",
		"author_name" => "Evad37",
		"author_contact" => "https://en.wikipedia.org/wiki/User:Evad37"
	];
	
	$api_user_agent = "{$tool_info["name"]} {$tool_info["version"]} ({$tool_info["updated"]}) Contact: {$tool_info["author_name"]} < {$tool_info["author_contact"]} >";
	
	// item id and lang code from url query params
	$item_id = ( isset($_GET['id']) ) ? $_GET['id'] : 'Q1';
	$lang_code = ( isset($_GET['lang']) ) ? $_GET['lang'] : 'en';

	// helper function for api results, which are deeply nested arrays
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

/* ========== API class ========================================================================= */
class Api
{
	/* private  = [
		"name" => "Knowledge Portal",
		"version" => "0.0.1-dev [just doing some initial tests]",
		"updated" => "2017-12-14",
		"author_name" => "Evad37",
		"author_contact" => "https://en.wikipedia.org/wiki/User:Evad37"
	]; */
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
			return [
				"error" => [
					"code" => "Fatal error",
					"info" => curl_error($ch) . " API connection failed."
				]
			];
		}
		
		curl_close($ch);
		return json_decode($response, true);
    }
}

$api = new Api();
	
	
/* ---------- Get item data --------------------------------------------------------------------- */
	// create curl resource 
	$ch = curl_init();	
	// set user agent
	curl_setopt($ch, CURLOPT_USERAGENT, $api_user_agent);
	// query
	$query = http_build_query([
		"action" => "wbgetentities",
		"format" => "json",
		"ids" => $item_id,
		"redirects" => "yes",
		"props" => "labels|descriptions|sitelinks|sitelinks/urls",
		"languages" => $lang_code,
		"sitefilter" => $lang_code . 'wiki|' . $lang_code . 'wikivoyage|' . $lang_code . 'wikisource|commonswiki'
	]);
	// set url 
	curl_setopt($ch, CURLOPT_URL, "https://www.wikidata.org/w/api.php?" . $query);
	//return the transfer as a string 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	// $output contains the output string 
	$response = curl_exec($ch);
	if(curl_errno($ch)){
    	echo "Error 003: " . curl_error($ch),"API connection failed.";
		die();
	}
	// close curl resource to free up system resources 
	curl_close($ch);
	
	$result = json_decode($response, true);
	
	if (
		!isset($result['success']) ||
		isset($result['error'])
	) {
		//print_r($result);
		echo ('API error');
		if ( isset($result['error']) ) {
			if ( isset($result['error']['code']) ) {
				echo (' ' . $result['error']['code']);
			}	
			if ( isset($result['error']) && isset($result['error']['info']) ) {
				echo (': ' . $result['error']['info']);
			}
		} else {
			echo ': unknown error';
		}
		die();
	}
	if (
		!isset($result["entities"]) ||
		!isset($result["entities"][$item_id])
	) {
		die('API error: no data for item ' . $item_id);
	}

	$item_data = $result["entities"][$item_id];
	
/* ---------- Get related items ----------------------------------------------------------------- */
	function extractId($obj) {
		return $obj["title"];
	}
	function joinWithPipes($v1, $v2) {
		if ( $v1 === "" ) {
			return $v2;
		}
		return "{$v1}|{$v2}";
	}

	$related = $api->get([
		"action" => "query",
		"format" => "json",
		"prop" => "linkshere",
		"titles" => $item_id,
		"lhprop" => "title",
		"lhnamespace" => "0",
		"lhshow" => "!redirect",
		"lhlimit" => "10"
	]);
	//print_r($related);
	
	$pageid = array_keys($related["query"]["pages"])[0];
	echo "<br>";
	
	if ( getDeepData($related, ["query", "pages", $pageid, "linkshere"], false) ) {
		
		$related_ids = array_reduce(
			array_map("extractId", $related["query"]["pages"][$pageid]["linkshere"]),
			"joinWithPipes",
			""
		);
		$related_items_data = $api->get([
			"action" => "wbgetentities",
			"format" => "json",
			"ids" => $related_ids,
			"redirects" => "yes",
			"props" => "labels",
			"languages" => $lang_code
		]);
		
		function extractLabel($obj) {
			$lang_code = $GLOBALS['lang_code'];
			$label = getDeepData($obj, ["labels", $lang_code, "value"], false);
			if ( !$label ) {
				return '[NO LABEL]';
			}
			return $label;
		}
		$related_items = array_map("extractLabel", $related_items_data["entities"]);		
		
		//print_r($related_items);
	} else {
		echo "No related items";
	}
	
	
	
	$item_label = getDeepData($item_data, ["labels", $lang_code, "value"], "({$item_id})");
	$item_desc  = getDeepData($item_data, ["descriptions", $lang_code, "value"]); 
	
	echo "<h1>{$item_label}</h1><h2>{$item_desc}</h2>";
	echo "<h3>Learn more</h3>"; 
	echo "<ul>";
	foreach ($item_data["sitelinks"] as $site => $site_info) {
		echo "<li><a href='{$site_info["url"]}'>{$site_info["title"]}</a> on {$site}</li>";
	}
	echo "</ul>";
	
	if ( isset($related_items) ) {
		echo "<h3>Related</h3>";
		echo "<ul>";
		$self = htmlspecialchars($_SERVER["PHP_SELF"]);
		//print_r($related_items);
		foreach ($related_items as $r_id => $r_label) {
			echo "<li><a href='{$self}?id={$r_id}&lang={$lang_code}'>{$r_label}</a></li>";
		}
		echo "</ul>";
	}
	echo "<hr>";
	//print_r($result);

 ?>
 </body>
</html>