$( function($) {
// Globals:
var item_id     = $('input[name="id"]').val();
var lang_code   = $('select[name="lang"]').val();
var portal_url  = window.location.protocol + '//' +  window.location.hostname + '/portal/';
	
// Get data from deeply nested arrays, or a fallback value if any of the nested keys aren't set;
// i.e. `getNestedData(obj, [key1, key2, key3])` is equivalent to `obj[key1][key2[key3]`
// if all the keys are set.	
var getNestedData = function(obj, keys, fallback) {
	var result = keys.reduce(function(accumulator, key) {
		return accumulator && accumulator[key];
	}, obj);
	return result || fallback || '';
};
/* ========== DOM manipulation etc. ========== */
var showLoaderAndHideMore = function(elementSelector) {
	$(elementSelector).show();
	$(elementSelector).find('.loading').show();
};
var showMoreAndHideLoader = function(elementSelector) {
	$(elementSelector).find('.loadmore').show();
	$(elementSelector).find('.loading').hide();
};

var formatAsBoxes = function(boxData) {
	var $box = $("<div>").addClass('flex-cell').append(//TODO
		$("<div>").addClass('flex-content-wrapper').append(
			$('<a>').addClass('flex-content').attr('href', boxData.url).append(
				$('<div>').addClass('label').text(boxData.label),
				$('<div>').addClass('desc').text(boxData.description)
			)
		)
	);
	return $box;
};

var addToPage = function(selector, newElements) {
	$(selector).children().last().before( newElements );
};

/* ========== API requests ========== */
var doApiRequest = function(request) {
	var endpoint = 'https://www.wikidata.org/w/api.php';
	var data = $.extend({'origin':'*'}, request);
	return $.ajax( {
		url: endpoint,
		data: data,
		type: 'GET'//,
		//headers: { 'Api-User-Agent': 'Free Knowlegde Portal/1.0 (JS) Contact: Evad37 <https://en.wikipedia.org/wiki/User:Evad37>' }
	} );
};

// Returns a promise that resolves to an array of Wikidata item IDs
var lookupRelatedIds = function (id) {
	var request = {
		"action": "query",
		"format": "json",
		"prop": "linkshere",
		"titles": id,
		"lhprop": "title",
		"lhnamespace": "0",
		"lhshow": "!redirect",
		"lhlimit": "500" // max limit
	};
	
	return doApiRequest(request)
	.then(function(response) {
		var pageid = Object.keys(response.query.pages)[0];
		return response.query.pages[pageid].linkshere;
	})
	.then(function(results) {
		if ( !results ) {
			return [];
		}			
		return results.map(function(r) { return r.title; });
	});
};

// Returns a promise that resolves to an array of Wikidata item IDs
lookupNearbyIds = function (id, minDistanceAway) {
	var coordRequest = {
		"action": "query",
		"format": "json",
		"prop": "coordinates",
		"titles": id,
		"colimit": "1",
		"coprimary": "primary"
	};

	return doApiRequest(coordRequest)
	.then(function(response) {
		var pageid = Object.keys(response.query.pages)[0];
		return response.query.pages[pageid].coordinates || false;
	})
	.then(function(coords) {
		if ( !coords ) {
			return false;
		}
		var nearbyRequest = {
			"action": "query",
			"format": "json",
			"list": "geosearch",
			"gscoord": coords[0].lat + '|' + coords[0].lon,
			"gsradius": "5000", // meters
			"gsglobe": coords[0].globe,
			"gslimit": "500", // max limit
			"gsnamespace": "0",
			"gsprimary": "primary"
		};
		return doApiRequest(nearbyRequest);
	})
	.then(function(response) {
		return getNestedData(response, ['query', 'geosearch'], []);
	})
	.then(function(results) {
		return results.filter(function(r) { return r.dist >= minDistanceAway; });
	})
	.then(function(results) {
		return results.map(function(r) { return r.title; });
	});
};

// Returns a promise that resolves to an array of { id: ... , value: ... } objects,
// where id is an idenitier's Wikidata property id and the value is the unique value for that identifier
var lookupIdentifierClaims = function(id) {
	var request = {
		"action": "wbgetclaims",
		"format": "json",
		"entity": id,
		"props": ""
	};
	
	var simplifyAndFilterResults = function(resultInfo, resultId) {
		// Filter out those that aren't external ids
		if ( resultInfo[0].mainsnak.datatype !== "external-id" ) {
			return null;
		}
		// Simplify (return only the necessary fields)
		return { "id": resultId, "value": resultInfo[0].mainsnak.datavalue.value };
	};
	
	return doApiRequest(request)
	.then(function(response) {
		return response.claims;
	})
	.then(function(results) {
		return $.map(results, simplifyAndFilterResults);
	});
};

// Returns a promise that resolves to an array of { url: ... , title: ... , description: ... } objects
var lookupItemLabelsAndDescriptions = function(ids) {
	var request = {
		"action": "wbgetentities",
		"format": "json",
		"ids": ids.join('|'),
		"redirects": "yes",
		"props": "labels|descriptions",
		"languages": lang_code
	};
	
	var simplifyResults = function(resultInfo, resultId) {
		var url =  portal_url +	resultId + '/' + lang_code;
		var label = getNestedData(resultInfo, ['labels', lang_code, 'value'], '('+resultId+')');
		var description = getNestedData(resultInfo, ['descriptions', lang_code, 'value']);
		return {
			"url": url,
			"label":  label,
			"description": description
		};
	};

	return doApiRequest(request)
	.then(function(response) {
		return response.entities;
	})
	.then(function(results) {
		return $.map(results, simplifyResults);
	});
};


var lookupFormatterUrl = function(identifierProperty) {
	var request = {
		"action": "wbgetclaims",
		"format": "json",
		"entity": identifierProperty,
		"property": "P1630",
		"props": ""
	};

	return doApiRequest(request)
	.then(function(response) {
		if ( !response.claims.P1630 ) {
			return "https://www.wikidata.org/wiki/Property:"+identifierProperty;
		}
		return response.claims.P1630[0].mainsnak.datavalue.value;
	});
};

var lookupItemLabel = function(id) {
	var request = {
		"action": "wbgetentities",
		"format": "json",
		"ids": id,
		"redirects": "yes",
		"props": "labels",
		"languages": lang_code
	};
	
	return doApiRequest(request)
	.then(function(response) {
		return response.entities[id].labels[lang_code].value || '('+resultId+')';
	});
};

// Returns a promise that resolves to an array of { url: ... , title: ... , description: ... } objects
var lookupIdentifiers = function (data) {
	var identifiers = data.map(function(d) {
		return $.when(lookupFormatterUrl(d.id), lookupItemLabel(d.id))
		.then(function(formatterUrl, label) {
			return {
				'url': formatterUrl.replace('$1', d.value),
				'label': label,
				'description': d.value
			};
		});
	});
	
	return $.when.apply(null, identifiers)
	.then(function() {
		// Create a plain array from unknown number of arguments 
		return (arguments.length === 1 ? [arguments[0]] : Array.apply(null, arguments));
	});
};

/* ========== Update sections ========== */
var updateSectionFunction = function(type, lookupFn) {
	var f = function(data) {
		if ( data.lenth === 0 ) {
			return;
		}
		
		return lookupFn(data)
		.then(function(outputData) {
			return outputData.map(formatAsBoxes);
		})
		.then(function(elements){
			addToPage('#'+type, elements);
		});
	};
	return f;
};
var update = {
	'related': updateSectionFunction('related', lookupItemLabelsAndDescriptions),
	'nearby': updateSectionFunction('nearby', lookupItemLabelsAndDescriptions),
	'identifiers': updateSectionFunction('identifiers', lookupIdentifiers)
};
//console.log("update = "); console.log(update);

/* ========== Setup ========== */
var related_ids = lookupRelatedIds(item_id);
var minDistanceToNearbyItems = 5; // metres
var nearby_ids  = lookupNearbyIds(item_id, minDistanceToNearbyItems);
var external_identifiers = lookupIdentifierClaims(item_id);

var amountToInitiallyLoad = 3;

var initialiseLoadmoreAndLoading = function (section, dataLength, initialLoadAmount) {
	if ( dataLength === 0 ) {
		$('#'+section+'-heading, #'+section).remove();
	} else {
		$('#'+section+'-heading, #'+section).show();
		showLoaderAndHideMore('#'+section);
	}
	if ( dataLength <= initialLoadAmount ) {
		$('#'+section).find('.loadmore').remove();
	}
};

$.when(related_ids, nearby_ids, external_identifiers).then(function(related, nearby, identifiers) {
	initialiseLoadmoreAndLoading('related', related.length, amountToInitiallyLoad);
	initialiseLoadmoreAndLoading('nearby', nearby.length, amountToInitiallyLoad);
	initialiseLoadmoreAndLoading('identifiers', identifiers.length, amountToInitiallyLoad);
		
	update.related(related.slice(0, amountToInitiallyLoad)).then(function() {
		showMoreAndHideLoader('#related');
	});
	update.nearby(nearby.slice(0, amountToInitiallyLoad)).then(function() {
		showMoreAndHideLoader('#nearby');
	});
	update.identifiers(identifiers.slice(0, amountToInitiallyLoad)).then(function() {
		showMoreAndHideLoader('#identifiers');
	});
});

/* ========== Helpers for on-click loading ========== */

var getData = function(section) {
	if ( section === "related" ) {
		return related_ids;
	} else if ( section === "nearby" ) {
		return nearby_ids;
	} else if ( section === "identifiers" ) {
		return external_identifiers;
	}
};

// Handle clicks
$(".loadmore").click(function(e) {
	e.preventDefault();
	
	var $self = $(this);
	
	// Hide loadmore link to prevent multiple clicks, show loading graphic
	$self.hide().next().show();
	
	// Gather inputs
	var section = $self.closest('div.flex-grid').attr('id');
	var offset = $self.closest('div.flex-grid').children().length - 1;
	
	var numberToAdd = 5;
	
	var updateSection = update[section];
	
	$.when(getData(section))
	.then(function(data) {
		if ( offset+numberToAdd >= data.length ) {
			$self.remove();
		}
		return data.slice(offset, offset+numberToAdd);
	})
	.then(updateSection)
	//.then(addElementsToDom)
	.then(function() {
		showMoreAndHideLoader('#'+section);
	});
	//.fail(showError);
});

});