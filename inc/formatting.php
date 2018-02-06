 <?php

function echo_html_top($title = false) {
	$pagetitle = ( $title ) ? "Free Knowledge Portal: {$title}" : "Free Knowledge Portal";
	echo "<html>
	<head>
	<title>{$pagetitle}</title>
	<meta charset='UTF-8'>
	<meta name='viewport' content='width=device-width, initial-scale=1.0'>
	<style>";
	include 'css/styles.css';
	echo "</style>
	</head>
	<body>";
}

function makeLangOption($lang, $lang_name) {
	$current_lang =  $GLOBALS['lang_code'];
	$selected = ( $lang === $current_lang ) ? ' selected' : '';
	return "<option value='{$lang}'{$selected}>{$lang}: {$lang_name}</option>";
}
function makeLangSelector ($item_id, $imgsrc, $lang_code, $available_langs) {
	$indexphp = $GLOBALS['self'] . "/index.php";
	$options_array = array_map("makeLangOption", array_keys($available_langs), $available_langs);
	if ( !isset($available_langs[$lang_code]) ) {
		array_push($options_array, makeLangOption($lang_code, 'Unknown language'));
	}
	$options = implode("", $options_array);
	return "<div class='main-extra' style='background: url({$imgsrc}) no-repeat left center;'>
		<form id='langswitch' action='{$indexphp}' method='get' style='display:inline;margin-left:35px'>
			<input type='hidden' name='id' value='{$item_id}' />
			<select name='lang' style='height:20px;margin:5px 0px' onchange='document.getElementById(\"langswitch\").submit();'>
				{$options}
			</select>
			
			<noscript><input type='submit' value='>>'></noscript>
		</form>
	</div>";
}

function makeHeading ($label) {
	return "<div class='main-label'>{$label}</div>";
}
function makeSubheading ($description) {
	return "<div class='main-desc'>{$description}</div>";
}
function makeBoxlink ($url, $logo, $title, $subtitle) {
	$img = ( $logo ) ? "<img class='logo' src='{$logo}' alt='{$subtitle}'>" : '';
	return "<div class='flex-cell'>
		<div class='flex-content-wrapper'>
			<a class='flex-content' href='{$url}'>
				{$img}
				<div class='label'>{$title}</div>
				<div class='desc'>{$subtitle}</div>
			</a>
		</div>
	</div>";
}

function makefooter ($id = false, $sites_used = []) {
	$itemlink = ( $id ) ? ": <a href='https://www.wikidata.org/wiki/{$id}'>{$id}</a>" : '';
	
	$default_images = ( $id ) ? ['reasonator'=>true] : [];
	$imgs_used = array_merge($default_images, $sites_used);
	
	$tm_note = ( count($imgs_used) === 0 ) ? '' : "<br>	
		Wikimedia site icons ™ Wikimedia Foundation, Inc. (used here under the
		<a href='https://wikimediafoundation.org/wiki/Trademark_policy'>Trademark policy</a>,
		section 3.6)";
	$imagecredits = makeImgCredits($imgs_used);
	
	return "<div class='footer small'>
	The Free Knowledge Portal uses data from <a href='https://www.wikidata.org/'>Wikidata</a>{$itemlink}.
	<br>
	Source code is <a href='https://github.com/evad37/wm-portal'>available on Github</a>;
	Text is available under the terms of the <a href='https://creativecommons.org/publicdomain/zero/1.0/CC0 license'>Creative Commons CC0 License</a>.
	{$tm_note}
	<ul style=text-align:left;margin-top:5px>
	{$imagecredits}
	</ul>
	</div>";
}

?>