*&nbsp; Try it out: https://tools.wmflabs.org/portal*<br>
*&nbsp; See also: [Free Knowledge Portal](https://meta.wikimedia.org/wiki/Free_Knowledge_Portal) on Wikimedia Meta-Wiki*

# About
The Free Knowledge Portal is a tool by Evad37 that is a solution to the Wikimedia 2017 Community Wishlist Survey proposal "[Qr codes for all items](https://meta.wikimedia.org/wiki/2017_Community_Wishlist_Survey/Wikidata/Qr_codes_for_all_items)".

## Background
The existing QR-encoding tool, [QRpedia](https://meta.wikimedia.org/wiki/QRpedia), allows [GLAM](https://meta.wikimedia.org/wiki/GLAM) instituations, other organisations, or anyone else create a QR code for a Wikipedia article. The QR code is generated based on the article title in a specific primary language. These QR codes can be placed in a relevant physical location, printed on handouts, or otherwise made available so that members of the public can scan them, and get more information from the Wikipedia article.

The problems, per the wishlist proposal, include:
* Articles can be renamed, breaking the links (e.g. "Foo" is renamed to "Foo (disambiguated)").
* Only Wikipedia articles can be accessed from the QR codes, despite the wealth of relevant information that might be available in sister projects like Wikivoyage and Wikisource.
* QR codes for non-Latin languages are very large and more difficult to use ([example](https://meta.wikimedia.org/wiki/File:QRpedia_code_in_Odessa_-_Bristol_Hotel_-_2.jpg))

## The solution
The solution to all those problems is to use Wikidata:
* Wikidata item ids are stable, and don't change when pages are moved. If Wikidata items are merged into another item, a redirect is left behind which can be followed.
* Wikidata item ids store site links to Wikimedia projects. These can be presented to end-users as a portal, so they can choose which of the available wikis to go to, rather than just Wikipedia
* The length of the url encoded in the QR code is determined by the Wikidata item id, not the page title, so you don't need huge QR codes for non-Latin languages.

The Free Knowledge Portal is a tool (hosted on Wikimedia's Toolforge) that provides this solution.

## Additional features
* Since links are displayed in a portal page, rather than just redirected to Wikipedia, the portal can also show:<!--
--><ul>
* Related items (items that link to the subject item)
* Nearby items (for items which have coordinates specified)
* External identifiers (such as those for GLAM partner institutions)<!--
--></ul>
* Responsive design that adapts to mobile, tablet, and desktop views
* Language switcher that not only translates the interface, but also changes the site links to that language version of the site (e.g. French Wikipedia when the language selected is French)
* Automatically detects device language, and uses that language by default
* Backwards-compatible with existing QRpedia codes, by using a page title and site to determine the relevant Wikidata item id. (But of course its up to the QRpedia people to redirect the codes to these urls if they choose to)

## Examples
* Boston (Q100), using your device's language: https://tools.wmflabs.org/portal/Q100
* Boston (Q100), using French: https://tools.wmflabs.org/portal/Q100/fr
* Boston (Q100), using Spanish: https://tools.wmflabs.org/portal/Q100/es
* Backwards-compatible url for Boston on English Wikipedia: https://tools.wmflabs.org/portal/?title=Boston&site=enwiki
* To generate a QR code, go to https://tools.wmflabs.org/portal and enter a Wikidata item id.

# Translations
This tool supports internationalisation.
* **Content:** Item labels, descriptions, and sitelinks are retrieved from Wikidata in the specified langauge. Any updates need to be made on Wikidata.
* **Interface:** Translations for strings used in the interface are located in JSON files in the /i18n directory. To add another langauge:<!--
--><ul>
* Create a new JSON file, using the same format as the existing files
* Save it as <code>{lang-code}.json</code>
* Add the langauge code and name to the <code>/i18n/_langs.json</code> file (sorted in alphabetical order by langauge code)<!--
--></ul>

# Updating
This tool is located on the Wikimedia Toolforge, at https://tools.wmflabs.org/portal/.
To update:
<ol>
<li>Login to toolforge with ssh:<p><code>$</code> <code>ssh -i ~/.ssh/id_rsa <i>user</i>@login.tools.wmflabs.org</code></li>
<li>Become the tool account:<p><code>$</code> <code>become portal</code></li>
<li>Pull from GitHub repo into the <code>public_html</code> folder:<p><code>$</code> <code>cd public_html<p>$</code> <code>git pull</code></li>
<li>If the <code>.lighttpd.conf</code> file has changed, that file needs to be copied to the root directory:<p><code>$</code> <code>cp public_html/.lighttpd.conf .lighttpd.conf</code></li>
<li>...then restart the webservice:<p><code>$</code> <code>webservice stop<p>$</code> <code>webservice start</code>
</ol>

## Database
Page views are stored in an SQL database. To connect manually:
<ol>
<li>Login to toolforge with ssh, become the tool account (as above)</li>
<li>Retreive database username: <code>$</code> <code>cat replica.my.cnf</code>
<li>Connect with mysql: <code>$</code> <code>mysql --defaults-file=$HOME/replica.my.cnf -h tools.db.svc.eqiad.wmflabs <i>USER</i>__views</code>
</ol>
