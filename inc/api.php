 <?php
//require "helpers.php";
class Api
{
	private $tool_info = [
		"name" => "Knowledge Portal",
		"version" => "0.0.5-dev",
		"updated" => "2018-01-29",
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
		
		if ( /*!isset($result['success']) ||*/ isset($result['error']) ) {
			$error_code = getDeepData($result, ['error', 'code'], false);
			$error_info = getDeepData($result, ['error', 'info'], 'An unknown error occurred.');
			echo ( $error_code ) ? "Api error {$error_code}: {$error_info}" : "Api error: {$error_info}";
			die();
		}
		
		return $result;
    }
}

//$_api = new Api();

/* ---------- Requests -------------------------------------------------------------------------- */
class ApiDecorator
{
	public $var = 'a default value';

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

	function lookupRelatedItemIds ($item_id) {
		$result = $this->api->get([
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

}

$api = new ApiDecorator();

?>