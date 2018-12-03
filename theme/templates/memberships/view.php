<?php
global $IC;
global $action;
global $itemtype;
global $model;


$IC = new Items();

$sindex = $action[0];
$related_items = false;


$item = $IC->getItem(array("sindex" => $sindex, "extend" => array("tags" => true, "user" => true, "mediae" => true, "comments" => true, "readstate" => true, "prices" => true, "subscription_method" => true)));
if($item) {
	$this->sharingMetaData($item);


	$next = $IC->getNext($item["item_id"], array("itemtype" => $item["itemtype"], "tags" => $item["tags"], "order" => "position ASC", "status" => 1, "extend" => true));
	$prev = $IC->getPrev($item["item_id"], array("itemtype" => $item["itemtype"], "tags" => $item["tags"], "order" => "position ASC", "status" => 1, "extend" => true));

	// set related pattern
	$related_pattern = array("itemtype" => $item["itemtype"], "tags" => $item["tags"], "exclude" => $item["id"]);
	// add base pattern properties
	$related_pattern["limit"] = 3;
	$related_pattern["extend"] = array("tags" => true, "user" => true, "mediae" => true, "prices" => true, "subscription_method" => true, "readstate" => true);

	// get related items
	$related_items = $IC->getRelatedItems($related_pattern);
}

?>

<div class="scene membership i:scene">


<? if($item):
	$media = $IC->sliceMedia($item); ?>

	<div class="article i:article id:<?= $item["item_id"] ?> service" itemscope itemtype="http://schema.org/Article"
		data-csrf-token="<?= session()->value("csrf") ?>"
		>

		<? if($media): ?>
		<div class="image item_id:<?= $item["item_id"] ?> format:<?= $media["format"] ?> variant:<?= $media["variant"] ?>"></div>
		<? endif; ?>


		<?= $HTML->articleTags($item, [
			"context" => false
		]) ?>


		<h1 itemprop="headline"><?= $item["name"] ?></h1>


		<?= $HTML->articleInfo($item, "/memberships/".$item["sindex"], [
			"media" => $media,
			"sharing" => true
		]) ?>



		<div class="articlebody" itemprop="articleBody">
			<?= $item["html"] ?>
		</div>

		<? if($item["mediae"]): ?>
			<? foreach($item["mediae"] as $media): ?>
		<div class="image item_id:<?= $item["item_id"] ?> format:<?= $media["format"] ?> variant:<?= $media["variant"] ?>">
			<p>Image: <a href="/images/<?= $item["item_id"] ?>/<?= $media["variant"] ?>/500x.<?= $media["format"] ?>"><?= $media["name"] ?></a></p>
		</div>
			<? endforeach; ?>
		<? endif; ?>

		<?= $HTML->frontendOffer($item, SITE_URL."/memberships", $item["introduction"]) ?>

		<?= $model->formStart("/memberships/addToCart", array("class" => "signup labelstyle:inject")) ?>
			<?= $model->input("quantity", array("value" => 1, "type" => "hidden")); ?>
			<?= $model->input("item_id", array("value" => $item["item_id"], "type" => "hidden")); ?>

			<ul class="actions">
				<?= $model->submit("Join now", array("class" => "primary", "wrapper" => "li.signup")) ?>
			</ul>
		<?= $model->formEnd() ?>


		<?= $HTML->frontendComments($item, "/janitor/service/addComment") ?>

	</div>


	<? if($next || $prev): ?>
	<div class="pagination i:pagination">
		<ul>
		<? if($prev): ?>
			<li class="previous"><a href="/memberships/<?= $prev[0]["sindex"] ?>"><?= strip_tags($prev[0]["name"]) ?></a></li>
		<? endif; ?>
		<? if($next): ?>
			<li class="next"><a href="/memberships/<?= $next[0]["sindex"] ?>"><?= strip_tags($next[0]["name"]) ?></a></li>
		<? endif; ?>
		</ul>
	</div>
	<? endif; ?>


	<? if($related_items): ?>
		<div class="related">
			<h2>Other memberships <a href="/memberships">(overview)</a></h2>

			<ul class="items memberships">
	<?		foreach($related_items as $item): 
				$media = $IC->sliceMedia($item); ?>
				<li class="item membership item_id:<?= $item["item_id"] ?>" itemscope itemtype="http://schema.org/NewsArticle"
					data-readstate="<?= $item["readstate"] ?>"
					>

					<h3 itemprop="headline"><a href="/memberships/<?= $item["sindex"] ?>"><?= strip_tags($item["name"]) ?></a></h3>


					<?= $HTML->frontendOffer($item, SITE_URL."/memberships") ?>


					<?= $HTML->articleInfo($item, "/memberships/".$item["sindex"], [
						"media" => $media
					]) ?>


					<? if($item["description"]): ?>
					<div class="description" itemprop="description">
						<p><?= nl2br($item["description"]) ?></p>
					</div>
					<? endif; ?>

				</li>
		<?	endforeach; ?>
			</ul>
		</div>
	<? endif; ?>

<? else: ?>


	<h1>Technology clearly doesn't solve everything on it's own.</h1>
	<h2>Technology needs humanity.</h2>
	<p>We could not find the specified service.</p>


<? endif; ?>


</div>
