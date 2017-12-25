# wm-portal
The free knowledge portal.
--------------------------

The basic idea is to provide stable urls that show a Wikidata item's sitelinks in a single language. Possibly also showing related items, e.g. items found from "What links here" and/or items with nearby coordinates. If combined with something to generate QR codes that map to the stable urls, this would be a solution to  https://meta.wikimedia.org/wiki/2017_Community_Wishlist_Survey/Wikidata/Qr_codes_for_all_items

Notes
-----

My thoughts so far.

Stable url format would be: <code>{base-url}/portal/{item}/{lang-code}</code>
(e.g. {base-url}/portal/Q12345/en)
with <code>/{lang-code}</code> being optional.<br/>
This would maps to  <code>{base-url}/portal/index.php?id={item}&lang={lang-code}</code>

index.php (or more likely functions it calls from another file) would retrieve item title, description, and sitelinks from the Wikidata api. Getting related items would take several queries, so maybe do that with client-side javascript rather than php so as not to stall load times of the main content. Modern devices that support QR-code scanning would likely support javascript, I would think.

I18n: labels and descriptions from Wikidata will already be in the correct langauge. Other strings ("encyclopedia article", "travel guide", "Wikipedia", "Wikivoyage" etc) will need i18n -- can these be pulled from elsewhere?

Mock output for <a href=https://www.wikidata.org/wiki/Q1129708>Q1129708</a>/en:
-----------
<h3>Coolgardie</h3><h4>town in Western Australia</h4>

<ul>
<li><a href=https://en.wikipedia.org/wiki/Coolgardie,_Western_Australia>Encycylopedia article</a><br>&nbsp;&nbsp;&nbsp; Wikipedia</li>
<li><a href=https://en.wikivoyage.org/wiki/Coolgardie>Travel guide</a><br>&nbsp;&nbsp;&nbsp; Wikivoyage</li>
<li><a href=https://commons.wikimedia.org/wiki/Category:Coolgardie,_Western_Australia>Multimedia</a><br>&nbsp;&nbsp;&nbsp; Wikimedia Commons</li>
<li><a href=https://tools.wmflabs.org/reasonator/?q=1129708>Data</a><br>&nbsp;&nbsp;&nbsp; Reasonator</li>
</ul>
<h5>Related:</h5>
<ul>
<li><a href=index.php?id=Q226071>Ernest Giles</a><br>&nbsp;&nbsp;&nbsp; explorer</li>
<li><a href=index.php?id=Q21958604>Mount Mine</a><br>&nbsp;&nbsp;&nbsp; mine in Coolgardie, Western Australia</li>
<li><a href=index.php?id=Q3367421>Pascal Garnier</a><br>&nbsp;&nbsp;&nbsp; French engineer</li>
</ul>
<h5>Nearby:</h5>
<ul>
<li><a href=index.php?id=Q45918845>Cremorne Theatre and Gardens</a><br>&nbsp;&nbsp;&nbsp; former cinema in Coolgardie, Western Australia, Australia</li>
<li><a href=index.php?id=Q45918833>Coolgardie Town Hall and Gardens</a><br>&nbsp;&nbsp;&nbsp; former cinema in Coolgardie, Western Australia, Australia</li>
</ul>
Free knowledge portal -- [CC0 logo] CC0 -- <a href=https://www.wikidata.org/wiki/Q1129708>data available on Wikidata</a>
<hr>
