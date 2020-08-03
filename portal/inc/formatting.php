 <?php

function echo_html_top($title = false, $extra='') {
	$pagetitle = ( $title ) ? "Free Knowledge Portal: {$title}" : "Free Knowledge Portal";
	echo "<html>
	<head>
	<title>{$pagetitle}</title>
	<meta charset='UTF-8'>
	<meta name='viewport' content='width=device-width, initial-scale=1.0'>
	<style>";
	include 'css/styles.css';
	echo "</style>
	{$extra}
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
function makeSubheading ($description, $divId=false) {
	$idAttribute = ( $divId ) ? " id='{$divId}'" : "";
	$text = $description ?: '&nbsp;';
	return "<div class='main-desc'{$idAttribute}>{$text}</div>";
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

function makeLoadMoreLink ($section) {
	$indexphp = $GLOBALS['self'] . "/index.php";
	$label = getDeepData($GLOBALS['i18n'], [$section], $section);
	$noJsForm =	"<noscript class='flex-content-wrapper'>
		<div class='flex-content loadmore-no-js'>
			<form id='{$section}-no-js' action='{$indexphp}' method='get'>
				<input type='hidden' name='id' value='{$GLOBALS['item_id']}' />
				<input type='hidden' name='lang' value='{$GLOBALS['lang_code']}' />
				<input type='hidden' name='more' value='{$section}' />
				<input type='submit' value='{$label}'>
			</form>
		</div>
	</noscript>";
	
	return 	"<div class='flex-cell'>
			<div class='loadmore' style='display:none;'>&nbsp;.&nbsp;.&nbsp;.&nbsp;</div>
			<div class='loading' style='display:none;'><img src={$GLOBALS['self']}/img/Ajax-loader.gif></div>
			{$noJsForm}
		</div>";
}

function makeFormattedImageCredits($imgs_used = []) {
	$makeCreditLine = function ($imgData) {
		return "<li><a href={$imgData['source']}>{$imgData['title']}</a> by {$imgData['authors']}: <a href={$imgData['licenceurl']}>{$imgData['licence']}</a></li>";
	};
	
	return implode( array_map($makeCreditLine, getRelevantImageCredits($imgs_used)) );
}

function makefooter ($id = false, $sites_used = []) {
	$itemlink = ( $id ) ? ": <a href='https://www.wikidata.org/wiki/{$id}'>{$id}</a>" : '';
	
	$tm_note = ( count($sites_used) === 0 ) ? '' : "<br>	
		Wikimedia site icons ™ Wikimedia Foundation, Inc. (used here under the
		<a href='https://wikimediafoundation.org/wiki/Trademark_policy'>Trademark policy</a>,
		section 3.6)";
	$imagecredits = ( count($sites_used) === 0 ) ? '' : makeFormattedImageCredits($sites_used);
	
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