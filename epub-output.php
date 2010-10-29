<?php

include_once(ANTHOLOGIZE_TEIDOM_PATH);
include_once(ANTHOLOGIZE_TEIDOMAPI_PATH);
include_once('class-nanowrimo-anthologizer.php');
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

//TODO

$epub = new EpubBuilder($tei, $htmler->output);

//TODO: hack the toc.ncx file epub writes to make it conform to the ids produced.

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

<html>
	<head>
		<title><?php echo $api->getProjectTitle(true); ?></title>
	</head>
	<style type='text/css'>

		body {
			font-size: <?php echo $api->getProjectOutputParams('font-size'); ?>;
		}


		.anth-index-item {
			clear: both;
		}

		#anth-image-index img {
			float: left;
			margin: 10px;

		}



	</style>
<body>


<h1 class="anth-project-title"><?php echo $api->getProjectTitle(); ?></h1>
<p class="anth-project-subtitle"><?php echo $api->getProjectSubTitle(); ?></p>

<p>Copyright</p>
<?php echo $api->getProjectCopyright(); ?>

<p>Edition</p>

<?php echo $api->getProjectEdition(); ?>

<p>Created</p>



<p>Published</p>




<p>Anthologizer: <?php echo $api->getProjectCreator(); ?> </p>

<?php
//passing true to getProjectCreator gets an array of additional data about the creator
$curator = $api->getProjectCreator(true);
?>

<p>About the anthologizer:</p>
<img src="<?php echo $api->getPersonDetail($curator, 'gravatarUrl'); ?>" />

<?php
//getPersonDetail helps you navigate that array to the info you want
echo $api->getPersonDetail($curator, 'bio');

?>


<?php echo $api->getProjectCopyright(); ?>

<?php echo $api->getProjectEdition(); ?>

<h2><?php echo $api->getSectionPartItemTitle('front', 0, 0); ?></h2>
<?php echo $api->getSectionPartItemContent('front', 0, 0); ?>


<h2><?php echo $api->getSectionPartItemTitle('front', 0, 1); ?></h2>
<?php echo $api->getSectionPartItemContent('front', 0, 1); ?>


<?php for($i = 0; $i < $api->getSectionPartCount('body');  $i++): ?>
	<div class="anth-part" id="<?php echo $api->getSectionPartId('body', $i); ?>">
		<h2><?php echo $api->getSectionPartTitle('body', $i); ?></h2>
		<?php for($j = 0; $j < $api->getSectionPartItemCount('body', $i); $j++): ?>
			<div class="anth-item" id="<?php echo $api->getSectionPartItemId('body', $i, $j); ?>">
				<h3><?php echo $api->getSectionPartItemTitle('body', $i, $j); ?></h3>
				<?php
					$by = $api->getSectionPartItemAnthAuthor('body', $i, $j);
					if( ! $by) {
						$by = $api->getSectionPartItemOriginalCreator('body', $i, $j);
					}
				?>
				<p>By: <?php echo $by;  ?></p>
				<p>Added to "<?php echo $api->getProjectTitle() ?>" by: <?php echo $api->getSectionPartItemCreator('body', $i, $j); ?></p>
				<div class="anth-item-content">
					<?php echo $api->getSectionPartItemContent('body', $i, $j); ?>
				</div>
			</div>
		<?php endfor; ?>
	</div>
<?php endfor; ?>



</body>

</html>



<?php die(); ?>