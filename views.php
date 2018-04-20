<?php
require "inc/external/getDefaultLanguage.php";
require "inc/formatting.php";
require "inc/core.php";

require_once('inc/db.php');
$today = gmdate("Y-m-d");
$item = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING);
$from = filter_input(INPUT_GET, 'from', FILTER_SANITIZE_STRING) ?: gmdate('Y-m-d', strtotime('-30 day'));
$to = filter_input(INPUT_GET, 'to', FILTER_SANITIZE_STRING) ?: $today;


	echo_html_top();
	echo '<link href="css/styles.css" rel="stylesheet">';
	echo makeHeading("Free Knowledge Portal");
	echo makeSubheading("Page view statistics");
	?>
	<form action="<?php echo $self ?>/views.php" method="get">
		<table class="noborder" style="margin:auto; max-width:100%; ">
		<caption></caption>
			<tbody>
				<tr>
					<td class="label-desktop"><?php echo /*$label["item"]*/"Item" ?></td>
					<td>
						<div class="label-mobile"><?php echo /*$label["item"]*/"Item" ?></div>
						<input type="text" <?php
						if ( preg_match("/^Q\d+$/", $item) ) {
							echo "value='{$item}'";
						} ?> placeholder="Q1" pattern="Q\d+" name="id" id="codenumber-input" style="width:100%; max-width:12em; font-family:inherit">
					</td>
				</tr>
				<tr>
					<td class="label-desktop"><?php echo /*$label["fromDate"]*/"From date" ?></td>
					<td>
						<div class="label-mobile"><?php echo /*$label["fromDate"]*/"From date" ?></div>
						<input type="date" value="<?php echo $from ?>" min="2018-03-01" name="from" id="fromDate-input" style="width:100%; max-width:12em; font-family:inherit">
					</td>
				</tr>			
				<tr>
					<td class="label-desktop"><?php echo /*$label["toDate"]*/"To date" ?></td>
					<td>
						<div class="label-mobile"><?php echo /*$label["toDate"]*/"To date" ?></div>
						<input type="date" value="<?php echo $to ?>" max="<?php echo $today ?>" name="to" id="toDate-input" style="width:100%; max-width:12em; font-family:inherit">
					</td>
				</tr>
				<tr>
					<td cellspan='2'><input type="submit"></td> 

			</tbody>
		</table>
	</form>
	<?php

if ( preg_match("/^Q\d+$/", $item) ) {
	require_once('web/view.php');
}
echo makefooter($item);
die();
