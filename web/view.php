<?php

// Get hits from database
function simplifyHits($hitsArray) {
	return $hitsArray["count"];
}
try {
	$page_hits = array_map('simplifyHits', getHits($item, $from, $to));
} catch(Exception $e) {
	echo "<strong>Database connection error</strong>";
	echo makefooter($item);
	die();
}

if ( count($page_hits) == 0 ) {
	echo "<strong>Sorry, no results for this period</strong>";
	echo makefooter($item);
	die();
}

$maxHits = max(array_values($page_hits));
$totalHits = array_sum($page_hits);

// Days between dates, inclusive
function daysBetween($start, $end) {
	$datetime1 = new DateTime($start);
	$datetime2 = new DateTime($end);
	$difference = $datetime1->diff($datetime2);
	return $difference->days + 1;
}

?>

<div style='display:table;margin:0.5em auto;'>
	<?php
	// Constants:
	$total_width = 500;
	$total_height = (daysBetween($from, $to)*20)+25;
	$horiz_offset = 100;
	$right_gutter = 45;
	$max_bar_width = $total_width - $horiz_offset - $right_gutter;
	$min_bar_width = 0.7;
	// Initial value:	
	$verticl_offset = 0;
	
	echo "<svg class='chart' viewbox='0 0 {$total_width} {$total_height}'  aria-labelledby='title desc' role='img'>
	<title id='title'>{$item}: Pageviews</title>
	<desc id='desc'>{$from} to {$to}</desc>
	<g class='bar'>
		<text class='heading' x='0' y='8' dy='.4em'>DATE</text>
		<text class='heading' x='{$horiz_offset}' y='8' dy='.4em'>VIEWS</text>
	</g>";
	$verticl_offset += 20;
	for ( $date_i = $from; $date_i <= $to; $noop=0 ) {
		//echo $date_i;
		if ( !isset($page_hits[$date_i]) ) {
			$hits_i = 0;
		} else {
			$hits_i = $page_hits[$date_i];
		}
		
		$bar_i_width = max(($hits_i / $maxHits * $max_bar_width), $min_bar_width);
		$value_x = $horiz_offset + $bar_i_width + 5;
		$label_y = $verticl_offset + 8;
		echo "<g class='bar'>
			<text class='label' x='0' y='{$label_y}' dy='.35em'>{$date_i}</text>
			<rect width='{$bar_i_width}' height='19' x='{$horiz_offset}' y='{$verticl_offset}'></rect>
			<text class='value' x='{$value_x}' y='{$label_y}' dy='.4em'>{$hits_i}</text>
		</g>";
		$verticl_offset += 20;
		$date_i = date("Y-m-d", strtotime($date_i . ' + 1 day'));
	}
	?>
	</svg>
	<div><strong>Total:</strong> <?php echo $totalHits; ?></div>
	<div><strong>Average:</strong> <?php echo round($totalHits/daysBetween($from, $to), 1); ?></div>
</div>
