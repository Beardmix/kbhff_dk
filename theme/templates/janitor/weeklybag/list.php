<?php
global $action;
global $IC;
global $model;
global $itemtype;


$items = $IC->getItems(array("itemtype" => $itemtype, "extend" => true));

?>
<div class="scene i:scene defaultList <?= $itemtype ?>List">
	<h1>Weekly bags</h1>

	<ul class="actions">
		<?= $JML->listNew(array("label" => "New weekly bag")) ?>
	</ul>

	<div class="all_items i:defaultList filters"<?= $JML->jsData() ?>>
<?		if($items): ?>
		<ul class="items">

<?			foreach($items as $item): ?>
			<li class="item item_id:<?= $item["id"] ?>">
				<h3><?= strip_tags($item["name"]) ?></h3>

				<?= $JML->listActions($item) ?>
			 </li>
<?			endforeach; ?>

		</ul>
<?		else: ?>
		<p>No bags.</p>
<?		endif; ?>
	</div>

</div>
