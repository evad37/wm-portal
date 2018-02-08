<?php
$sites = [
	"commons" => "commonswiki",
	"wikibooks" => "{$lang_code}wikibooks",
	"wikinews" => "{$lang_code}",
	"wikipedia" => "{$lang_code}wiki",
	"wikiquote" => "{$lang_code}wikiquote",
	"wikisource" => "{$lang_code}wikisource",
	"wikispecies" => "specieswiki",
	"wikiversity" => "{$lang_code}wikiversity",
	"wikivoyage" => "{$lang_code}wikivoyage",
	"wiktionary" => "{$lang_code}wiktionary",
	"reasonator" => "reasonator"
];
$site_types = array_flip( $sites );

$mapToSiteType = function($site) use ($site_types) {
	return $site_types[$site];
};

?>