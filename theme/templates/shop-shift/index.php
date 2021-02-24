<? 
global $TC;
global $SC;
global $action;

include_once("classes/users/superuser.class.php");
$UC = new SuperUser();

include_once("classes/shop/pickupdate.class.php");
$PC = new Pickupdate();

$department = $UC->getUserDepartment(["user_id" => session()->value("user_id")]);

$tally = $TC->getTally(["department_id" => $department ? $department["id"] : false]);
$tally_id = $tally["id"];

$pickupdate = false;
$previous_pickupdate = $PC->getPickupdates(["after" => date("Y-m-d", strtotime("-8 days")), "before" => date("Y-m-d")]);
$upcoming_pickupdates = $PC->getPickupdates(["after" => date("Y-m-d"), "before" => date("Y-m-d", strtotime("+15 days"))]);
$all_pickupdates = array_merge($previous_pickupdate, $upcoming_pickupdates);

$pickupdate_is_today = true;
if($action) {
	$pickupdate = $PC->getPickupdate(["id" => $action[0]]);
	$pickupdate_is_today = false;
}
if(!$pickupdate) {
	$pickupdate = $PC->getPickupdate(["pickupdate" => date("Y-m-d")]);
}
if(!$pickupdate) {
	
	$next_pickupdate = $upcoming_pickupdates ? $upcoming_pickupdates[0] : false;
	$pickupdate = $next_pickupdate;
	$pickupdate_is_today = false;
}


$department_pickupdate_order_items = $SC->getPickupdateOrderItems($pickupdate["id"], ["department_id" => $department["id"]]);

$order_items_total_quantity = 0;
$order_items_total_delivered_quantity = 0;
$order_items_total_undelivered_quantity = 0;

if($department_pickupdate_order_items) {

	foreach ($department_pickupdate_order_items as $order_item) {
		
		$order_items_total_quantity += $order_item["quantity"];
		if($order_item["status"] == 1) {
			$order_items_total_undelivered_quantity += $order_item["quantity"];
		}
		else if($order_item["status"] == 2) {
			$order_items_total_delivered_quantity += $order_item["quantity"];
		}
	}
}
?>

<div class="scene shop_shift i:scene" itemscope itemtype="http://schema.org/NewsArticle">
	
	<div class="banner i:banner variant:1 format:jpg"></div>

	<h1>Ordreliste</h1>
	<div class="c-wrapper">
		<div class="intro">
			<p>I tabellen herunder kan du som kassemester se aktuelle og fremtidige bestillinger i din KBHFF-afdeling. Hvert punkt svarer til en vare, og derfor kan der godt være flere linjer med samme navn, hvis der er bestilt flere varer (grøntsagspose og frugtpose fx).</p>
			<p>Kassemestre kan på en aktuel afhentningsdag bruge ‘Udlever’-knapperne til at registrere, at medlemmer har fået udleveret deres varer.</p>
		</div>
		<div class="c-two-thirds info">
			<ul>
				<li class="date">Dato: 
					<?= $PC->formStart("selectPickupdate", ["class" => "labelstyle:inject form"]); ?>
					<?= $PC->input("pickupdate_id", ["type" => "select", "value" => $pickupdate["id"], "options" => $PC->toOptions($all_pickupdates, "id", "pickupdate")]); ?>
					
					<ul class="actions">
					<?= $PC->submit("Vælg", ["wrapper" => "li.select"]); ?>
					</ul>
					<?= $PC->formEnd(); ?>
				</li>
				<li class="department">Afdeling: <span class="value"><?= $department["abbreviation"] ?: " - " ?></span></li>
				<li class="ordered">Bestilt: <span class="value"><?= $order_items_total_quantity ?></span></li>
				<li class="delivered">Udleveret: <span class="value"><?= $order_items_total_delivered_quantity ?></span></li>
				<li class="undelivered">Afventer: <span class="value"><?= $order_items_total_undelivered_quantity ?></span></li>
			</ul>
		</div>
		<div class="c-one-third tally">
			<ul class="actions">
				<li class="shop"><a href="/butiksvagt/kasse/<?= $tally_id ?>" class="button primary">Åbn kasseregnskab</a></li>
			</ul>
		</div>
		<div class="order items">
		<? if($department_pickupdate_order_items): ?>
			<ul class="list">
				<li class="labels">
					<span class="user_names">Navn</span>
					<span class="product_names">Vare</span>
					<span class="buttons"></span>
				</li>
			<? foreach($department_pickupdate_order_items as $order_item): 

				$user = $UC->getUsers(["user_id" => $order_item["user_id"]]);
				
			?>
				<li class="listing">
					<span class="user_name"><?= $user ? $user["nickname"] : "" ?></span>
					<span class="product_name"><?= $order_item["quantity"] ?> x <?= $order_item["name"] ?></span>
					<span class="button">
						<? if($pickupdate_is_today): ?>
						<ul class="actions">
							<? if($order_item["status"] == 1): ?>
							<?= $HTML->oneButtonForm("Udlevér", "/butiksvagt/updateDeliveryStatus/".$order_item["id"], [
								"inputs" => [
									"status" => 2,
								],
								"confirm-value" => "Bekræft",
								"wait-value" => "Vent",
								"dom-submit" => true,
								"class" => "primary"
							]) ?>
							<? elseif($order_item["status"] == 2): ?>
							<?= $HTML->oneButtonForm("Fortryd", "/butiksvagt/updateDeliveryStatus/".$order_item["id"], [
								"inputs" => [
									"status" => 1,
								],
								"confirm-value" => "Bekræft",
								"wait-value" => "Vent",
								"dom-submit" => true,
							]) ?>
							<? endif; ?>
						</ul>
						<? endif; ?>
					</span>
				</li>
			<? endforeach; ?>
			</ul>
		<? else: ?>
			<p>Ingen produkter til udlevering</p>
		<? endif; ?>
		</div>
	</div>
	
	
</div>