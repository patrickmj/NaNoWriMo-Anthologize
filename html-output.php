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

if($api->getProjectOutputParams('download') == 'on') {
	$fileName = $api->getFileName();
	header("Content-type: application/xml");
	header("Content-Disposition: attachment; filename=$fileName.html");
}
echo $htmler->output();
