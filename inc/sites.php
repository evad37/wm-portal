<?php
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

$mapToSiteType = function($site) use ($site_types) {
	return $site_types[$site];
};

?>