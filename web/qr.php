<?php
/*
  - Based on: QR Code generator demo (HTML+JavaScript)
  - https://github.com/nayuki/QR-Code-generator/blob/master/javascript/qrcodegen-js-demo.html
  -
  - Original file:
  - Copyright (c) Project Nayuki. (MIT License)
  - https://www.nayuki.io/page/qr-code-generator-library
  - 
  - Permission is hereby granted, free of charge, to any person obtaining a copy of
  - this software and associated documentation files (the "Software"), to deal in
  - the Software without restriction, including without limitation the rights to
  - use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
  - the Software, and to permit persons to whom the Software is furnished to do so,
  - subject to the following conditions:
  - * The above copyright notice and this permission notice shall be included in
  -   all copies or substantial portions of the Software.
  - * The Software is provided "as is", without warranty of any kind, express or
  -   implied, including but not limited to the warranties of merchantability,
  -   fitness for a particular purpose and noninfringement. In no event shall the
  -   authors or copyright holders be liable for any claim, damages or other
  -   liability, whether in an action of contract, tort or otherwise, arising from,
  -   out of or in connection with the Software or the use or other dealings in the
  -   Software.
  -
  - This modified file:
  - BSD-3-Clause License (see the LICENSE file for full details).
  */
?>
<div id="qr-hidden-without-js" style="display:none;">
<?php
echo makeSubheading("QR generator");
$label = [
	"item" => "<strong>Item id:</strong>",
	"qr" => "<strong>Qr code:</strong>",
	"format" => "Output format:",
	"border" => "Border:",
	"scale" => "Scale:",
	"svg" => "SVG XML code:"
];
?>

<form action="#" method="get" onsubmit="return false;overflow-x:scroll">
	<table class="noborder" style="margin:auto; max-width:100%; ">
	<caption>Enter a Wikidata item id to generate a QR&nbsp;code.</caption>
		<tbody>
			<tr>
				<td class="label-desktop"><?php echo $label["item"] ?></td>
				<td>
					<div class="label-mobile"><?php echo $label["item"] ?></div>
					Q<input type="number" value="1" min="1"  id="codenumber-input" style="width:100%; max-width:12em; font-family:inherit" oninput="redrawQrCode();">
				</td>
			</tr>
			<tr>
				<td class="label-desktop"><?php echo $label["qr"] ?></td>
				<td>
					<div class="label-mobile"><?php echo $label["qr"] ?></div>
					<canvas id="qrcode-canvas" style="padding:1em; background-color:#E8E8E8"></canvas>
					<svg id="qrcode-svg" style="width:30em; height:30em; padding:1em; background-color:#E8E8E8">
						<rect width="100%" height="100%" fill="#FFFFFF" stroke-width="0"></rect>
						<path d="" fill="#000000" stroke-width="0"></path>
					</svg>
				</td>
			</tr>
			<tr>
				<td class="label-desktop"><?php echo $label["format"] ?></td>
				<td>
					<div class="label-mobile"><?php echo $label["format"] ?></div>
					<input type="radio" name="output-format" id="output-format-bitmap" onchange="redrawQrCode();" checked="checked"><label for="output-format-bitmap">Bitmap</label>
					<input type="radio" name="output-format" id="output-format-vector" onchange="redrawQrCode();"><label for="output-format-vector">Vector</label>
				</td>
			</tr>
			<tr>
				<td class="label-desktop"><?php echo $label["border"] ?></td>
				<td>
					<div class="label-mobile"><?php echo $label["border"] ?></div>
					<input type="number" value="4" min="0" max="100" step="1" id="border-input" style="width:4em" oninput="redrawQrCode();"> modules
				</td>
			</tr>
			<tr id="scale-row">
				<td class="label-desktop"><?php echo $label["scale"] ?></td>
				<td>
					<div class="label-mobile"><?php echo $label["scale"] ?></div>
					<input type="number" value="8" min="1" max="30" step="1" id="scale-input" style="width:4em" oninput="redrawQrCode();"> pixels per module
				</td>
			</tr>
			<tr id="svg-xml-row">
				<td class="label-desktop"><?php echo $label["svg"] ?></td>
				<td>
					<div class="label-mobile"><?php echo $label["svg"] ?></div>
					<textarea id="svg-xml-output" readonly="readonly" style="width:100%; max-width:50em; height:15em; font-family:monospace"></textarea>
				</td>
			</tr>
		</tbody>
	</table>
</form>
<script type="application/javascript" src="js/external/QR-Code-generator/javascript/qrcodegen.js"></script>
<script type="application/javascript" src="js/qr.js"></script>
</div>