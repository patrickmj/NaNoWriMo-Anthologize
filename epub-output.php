<?php



include_once(ANTHOLOGIZE_TEIDOM_PATH);
include_once(ANTHOLOGIZE_TEIDOMAPI_PATH);
include_once(WP_PLUGIN_DIR . '/nanowrimo/class-nanowrimo-anthologizer.php');
include_once(WP_PLUGIN_DIR . '/nanowrimo/includes/class-epub-builder.php');

global $tocDOM;

$ops = array('includeStructuredSubjects' => false, //Include structured data about tags and categories
		'includeItemSubjects' => false, // Include basic data about tags and categories
		'includeCreatorData' => false, // Include basic data about creators
		'includeStructuredCreatorData' => false, //include structured data about creators
		'includeOriginalPostData' => true, //include data about the original post (true to use tags and categories)
		'checkImgSrcs' => false, //whether to check availability of image sources
		'linkToEmbeddedObjects' => false,
		'indexSubjects' => false,
		'indexCategories' => false,
		'indexTags' => false,
		'indexAuthors' => false,
		'indexImages' => false,
		);


$ops['outputParams'] = $_SESSION['outputParams'];

$tei = new TeiDom($_SESSION, $ops);
$api = new TeiApi($tei);


$htmler = new NaNoWriMoHTMLer($api, true);

$epub = new EpubBuilder($tei, $htmler->output);

//Hack the toc.ncx file epub writes to make it conform to the ids produced.
$tocDOM = new DOMDocument();
$tocDOM->load($epub->oebpsDir . "toc.ncx");
rewriteTOC($htmler->output);
$tocDOM->save($epub->oebpsDir . "toc.ncx");
$epub->output();

function rewriteTOC($htmlDOM) {
	global $tocDOM;
	//remove <navMap>
	//change depth?
	$htmlXPath = new DOMXPath($htmlDOM);
	$navMap = $tocDOM->getElementsByTagName('navMap')->item(0);
	while($navMap->childNodes->length != 0 ) {
		$navMap->removeChild($navMap->firstChild);
	}
	$parts = $htmlXPath->query("//div[@id='body']/div[@class='part']");

	for($partN = 0; $partN < $parts->length; $partN++) {
		$part = $parts->item($partN);
		$title = $part->firstChild->firstChild->textContent; //shitty practice, I know
		$partNavPoint = newNavPoint("body-$partN", $title);
		$partNavPoint = $navMap->appendChild($partNavPoint);
		$partNavPoint->setAttribute('playOrder', $partN);
		//set playorder on $partNavPoint
		$navMap->appendChild($partNavPoint);
		$items = $htmlXPath->query("div[@class='item']", $part);
		for($itemN = 0; $itemN < $items->length; $itemN++) {
			$item = $items->item($itemN);
			$itemTitle = $item->firstChild->firstChild->textContent; //shitty practice, I know
			$itemNavPoint = newNavPoint("body-$partN-$itemN", $itemTitle);
			//set playOrder
			//append where it goes
			//lets try this
			$itemNavPoint->setAttribute('playOrder', $itemN);
			$partNavPoint->appendChild($itemNavPoint);
		}

	}

}

function newNavPoint($id, $label) {
	global $tocDOM;
	$label = htmlspecialchars($label);
	$navPoint = $tocDOM->createElement('navPoint');
	$navPoint->setAttribute('id', $id);
	$navLabelNode = $tocDOM->createElement('navLabel');
	$text = $tocDOM->createElement('text', $label);
	$navLabelNode->appendChild($text);
	$navPoint->appendChild($navLabelNode);
	$content = $tocDOM->createElement('content');
	$content->setAttribute('src', "main_content.html#$id");
	$navPoint->appendChild($content);
	return $navPoint;
}

die();

?>