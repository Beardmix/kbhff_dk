<?php
$IC = new Items();

$page_item = $IC->getItem(array("tags" => "page:front", "status" => 1, "extend" => array("user" => true, "mediae" => true, "tags" => true)));
if($page_item) {
	$this->sharingMetaData($page_item);
}

$WBC = $IC->typeObject("weeklybag");
$weeklybag_item = $WBC->getWeeklyBag();

$post_items = $IC->getItems([
	"itemtype" => "post",
	"tags" => "on:frontpage",
	"status" => 1,
	"limit" => 3,
	"extend" => [
		"tags" => true,
		"readstate" => true,
		"user" => true,
		"mediae" => true
	]
]);
?>
<div class="scene front i:scene i:front">
	<div class="banner i:banner variant:random format:jpg"></div>

	<div class="c-wrapper">
		
		<div class="c-box actions">
			<? if(session()->value("user_id") != 1): ?>
				<a href="https://wiki.kbhff.dk/tiki-index.php?page=Vagtplaner" class="shift">Ta' en vagt</a>
				<a href="/butik" class="order">Bestil en pose</a>
			<? else: ?>
				<a href="/bliv-medlem" class="member">Bliv medlem</a>
				<a href="/login" class="login">Login</a>
			<? endif; ?>
		</div>

		<!-- icons from https://icons.getbootstrap.com/ -->
		<div class="steps">
			<h3 itemprop="headline">Nemt at komme i gang</h3>
			<ul class="items articles">
				<li class="item article one">
					<a href="/bliv-medlem">
						<div class="image"> 
							<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-person-check-fill" viewBox="0 0 16 16">
								<path fill-rule="evenodd" d="M15.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L12.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0"/>
								<path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
							</svg>
						</div>
						<h3 itemprop="headline">1.</h3>
						<div class="description" itemprop="description">
							<p>Bliv medlem</p>
						</div>
					</a>
				</li>
				<li class="item article two">
					<a href="/butik">
						<div class="image"> 
							<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-bag-heart" viewBox="0 0 16 16">
								<path fill-rule="evenodd" d="M10.5 3.5a2.5 2.5 0 0 0-5 0V4h5zm1 0V4H15v10a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V4h3.5v-.5a3.5 3.5 0 1 1 7 0M14 14V5H2v9a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1M8 7.993c1.664-1.711 5.825 1.283 0 5.132-5.825-3.85-1.664-6.843 0-5.132"/>
							</svg>
						</div>
						<h3 itemprop="headline">2.</h3>
						<div class="description" itemprop="description">
							<p>Bestil en pose</p>
						</div>
					</a>
				</li>
				<li class="item article three">
					<a href="/afdelinger">
						<div class="image"> 
							<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-geo-alt" viewBox="0 0 16 16">
								<path d="M12.166 8.94c-.524 1.062-1.234 2.12-1.96 3.07A32 32 0 0 1 8 14.58a32 32 0 0 1-2.206-2.57c-.726-.95-1.436-2.008-1.96-3.07C3.304 7.867 3 6.862 3 6a5 5 0 0 1 10 0c0 .862-.305 1.867-.834 2.94M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10"/>
								<path d="M8 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4m0 1a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
							</svg>
						</div>
						<h3 itemprop="headline">3.</h3>
						<div class="description" itemprop="description">
							<p>Hent din ordre</p>
						</div>
					</a>
				</li>
				<li class="item article four">
					<a href="/ugens-pose/<?= $weeklybag_item["sindex"] ?>">
						<div class="image"> 
							<!-- icon666.com - MILLIONS vector ICONS FREE -->
							<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="64" height="64" viewBox="0 0 512 512" xml:space="preserve">
								<path d="M497.122,169.794c-9.541-26.686-23.401-51.482-41.192-73.699c-2.625-3.278-7.411-3.807-10.689-1.182 c-3.277,2.625-3.807,7.41-1.182,10.688c34.499,43.081,52.734,95.088,52.734,150.398c0,64.317-25.046,124.786-70.527,170.266 c-45.478,45.48-105.947,70.526-170.265,70.526c-64.317,0-124.786-25.046-170.266-70.526S15.208,320.318,15.208,256 S40.254,131.214,85.734,85.734C131.214,40.255,191.683,15.208,256,15.208c64.91,0,125.773,25.443,171.375,71.643 c2.951,2.989,7.766,3.019,10.753,0.07c2.989-2.95,3.02-7.764,0.07-10.753C389.715,27.05,325.01,0,256,0 C187.62,0,123.332,26.629,74.981,74.981S0,187.62,0,256s26.629,132.668,74.981,181.019C123.333,485.37,187.62,512,256,512 c68.381,0,132.668-26.629,181.019-74.981C485.372,388.668,512,324.381,512,256C512,226.411,506.995,197.407,497.122,169.794z"/></g></g><g><g><path d="M329.253,409.514c-1.639-3.867-6.101-5.671-9.969-4.033c-20.035,8.494-41.327,12.8-63.283,12.8 c-41.121,0-80.345-15.407-110.447-43.383c-29.945-27.831-48.178-65.518-51.338-106.119c-0.325-4.187-3.985-7.311-8.171-6.992 c-4.186,0.326-7.317,3.984-6.992,8.172c3.457,44.418,23.398,85.643,56.147,116.079c32.925,30.599,75.826,47.452,120.8,47.452 c24.007,0,47.297-4.712,69.22-14.006C329.085,417.844,330.891,413.381,329.253,409.514z"/></g></g><g><g><path d="M256,78.511c-44.243,0-86.625,16.376-119.341,46.109c-32.489,29.529-52.814,69.746-57.232,113.24 c-0.425,4.178,2.619,7.909,6.797,8.333c4.179,0.426,7.909-2.619,8.333-6.797c4.037-39.757,22.622-76.522,52.33-103.523 c29.91-27.185,68.66-42.156,109.112-42.156c89.481,0,162.281,72.798,162.281,162.281c0,17.02-2.62,33.78-7.789,49.817 c-1.289,3.997,0.908,8.281,4.906,9.57c0.776,0.249,1.56,0.368,2.334,0.368c3.212,0,6.197-2.052,7.236-5.274 c5.653-17.547,8.52-35.876,8.52-54.482C433.489,158.133,353.868,78.511,256,78.511z"/></g></g><g><g><path d="M360.915,167.718c-3.305-1.502-6.731-2.522-10.243-3.056c-3.074-12.007-11.673-21.823-23.533-26.422 c-11.858-4.598-24.827-3.147-35.194,3.65c-2.953-1.974-6.173-3.53-9.626-4.65c-18.695-6.061-38.971,2.839-47.154,20.703 c-4.511,9.848-4.758,20.78-0.698,30.782c4.005,9.863,11.991,17.725,21.912,21.572c2.018,0.783,3.195,2.895,2.798,5.024 l-8.224,44.125c-0.67,3.596,1.315,7.16,4.727,8.483l36.801,14.271c0.899,0.349,1.829,0.515,2.748,0.515 c2.567,0,5.042-1.305,6.462-3.594l23.674-38.133c1.142-1.839,3.439-2.606,5.454-1.823c9.923,3.847,21.122,3.425,30.728-1.161 c9.742-4.65,16.929-12.89,20.237-23.205C387.785,196.089,378.812,175.849,360.915,167.718z M367.303,210.153 c-2.015,6.282-6.385,11.298-12.305,14.124c-5.929,2.829-12.561,3.079-18.68,0.707c-8.835-3.427-18.872-0.07-23.872,7.981 L292.1,265.739l-25.038-9.71l7.069-37.922c1.737-9.315-3.415-18.564-12.249-21.989c-6.121-2.372-10.851-7.03-13.321-13.114 c-2.469-6.079-2.315-12.73,0.433-18.728c4.887-10.67,17.469-16.189,28.639-12.569c3.415,1.107,6.448,2.909,9.018,5.356 c2.909,2.772,7.472,2.801,10.416,0.067c6.677-6.196,16.095-8,24.575-4.711c8.483,3.29,14.22,10.972,14.975,20.048 c0.333,4.004,3.724,7.044,7.739,6.973c3.564-0.08,7.004,0.64,10.27,2.123c0,0,0,0,0.001,0 C365.318,186.421,370.887,198.978,367.303,210.153z"/></g></g><g><g><path d="M419.909,324.565c-7.299-7.748-17.588-12.192-28.23-12.192c-2.165,0-4.026-1.543-4.424-3.672l-8.286-44.114 c-0.675-3.595-3.815-6.2-7.473-6.2h-39.472c-4.2,0-7.604,3.405-7.604,7.604c0,4.199,3.404,7.604,7.604,7.604h33.162l7.121,37.912 c1.748,9.313,9.894,16.074,19.371,16.074c6.564,0,12.657,2.632,17.161,7.412c4.5,4.775,6.76,11.032,6.367,17.618 c-0.699,11.714-10.432,21.41-22.158,22.073c-3.582,0.205-7.061-0.38-10.343-1.734c-3.716-1.532-7.978,0.09-9.736,3.704 c-3.984,8.19-12.112,13.278-21.21,13.278c-9.098,0-17.226-5.088-21.21-13.278c-1.758-3.612-6.022-5.235-9.737-3.703 c-3.28,1.353-6.759,1.935-10.343,1.733c-11.724-0.662-21.457-10.358-22.157-22.073c-0.393-6.585,1.868-12.842,6.366-17.617 c4.503-4.78,10.597-7.412,17.161-7.412c9.477,0,17.623-6.759,19.372-16.073l4.174-22.226c0.776-4.127-1.943-8.102-6.069-8.876 c-4.129-0.776-8.102,1.942-8.877,6.07l-4.174,22.226c-0.4,2.128-2.261,3.672-4.425,3.672c-10.641,0-20.931,4.444-28.231,12.192 c-7.402,7.857-11.123,18.14-10.476,28.952c1.17,19.614,16.852,35.241,36.478,36.35c3.62,0.207,7.187-0.081,10.655-0.855 c7.207,10.083,18.774,16.127,31.494,16.127c12.72,0,24.291-6.046,31.498-16.129c3.467,0.773,7.034,1.061,10.654,0.855 c19.626-1.109,35.31-16.736,36.48-36.35C431.033,342.704,427.312,332.423,419.909,324.565z"/></g></g><g><g><path d="M264.148,366.007c-0.249-0.066-25.175-6.832-47.457-27.2c-2.551-2.333-4.958-4.748-7.237-7.233 c-1.856-5.566-8.794-29.746,5.326-43.866c2.97-2.97,2.97-7.784,0-10.753c-2.533-2.533-6.406-2.898-9.333-1.109 c-1.267-3.761-5.248-5.952-9.14-4.921c-4.061,1.074-6.482,5.236-5.408,9.295l3.228,12.211c-1.481,3.967-2.374,8.012-2.834,11.982 c-9.926-21.533-12.7-46.911-8.243-75.883c0.639-4.151-2.208-8.033-6.359-8.672c-4.152-0.639-8.033,2.208-8.672,6.359 c-2.081,13.529-2.587,25.904-1.9,37.232c-0.154-0.177-0.31-0.347-0.462-0.529c-8.27-9.851-10.851-21.477-7.67-34.552 c0.993-4.08-1.511-8.193-5.591-9.186c-4.08-0.994-8.193,1.511-9.186,5.591c-7.729,31.772,12.113,52.832,26.067,61.169 c5.295,23.442,16.023,41.191,27.536,54.277c0.116,0.146,0.242,0.278,0.367,0.415c2.217,2.498,4.459,4.826,6.698,6.987 c-13.157-0.594-30.969-4.963-43.012-22.022c-2.423-3.431-7.166-4.247-10.598-1.827c-3.431,2.422-4.249,7.166-1.827,10.598 c3.02,4.279,6.298,7.931,9.736,11.061l-2.222,1.267c-3.648,2.081-4.918,6.725-2.837,10.373c1.403,2.459,3.97,3.837,6.611,3.837 c1.277,0,2.572-0.322,3.76-1.001l8.538-4.871c12.27,6.292,25.028,7.919,34.937,7.919c6.125,0,11.15-0.62,14.307-1.145 c20.107,13.848,38.097,18.666,39.023,18.909c0.644,0.168,1.289,0.247,1.923,0.247c3.37,0,6.446-2.261,7.343-5.675 C270.63,371.237,268.203,367.081,264.148,366.007z"/></g></g><g><g><path d="M210.531,159.109c4.895-4.624,7.959-11.167,7.959-18.416c0-13.976-11.37-25.346-25.347-25.346 s-25.346,11.37-25.346,25.346c0,1.684,0.169,3.33,0.484,4.923c-2.096-0.563-4.296-0.868-6.567-0.868 c-13.976-0.001-25.346,11.369-25.346,25.346c0,13.976,11.37,25.346,25.346,25.346c4.907,0,9.491-1.406,13.376-3.83 c3.969,8.824,12.838,14.986,23.123,14.986c13.976,0,25.346-11.37,25.346-25.346C223.559,171.743,218.296,163.446,210.531,159.109z M161.714,180.233c-5.59,0-10.139-4.548-10.139-10.139c0-5.59,4.548-10.139,10.139-10.139c5.59,0,10.139,4.548,10.139,10.139 C171.853,175.685,167.305,180.233,161.714,180.233z M183.005,140.693c0-5.59,4.548-10.139,10.139-10.139 c5.59,0,10.139,4.548,10.139,10.139c0,5.59-4.548,10.139-10.139,10.139C187.553,150.831,183.005,146.283,183.005,140.693z M198.213,191.389c-5.59,0-10.139-4.548-10.139-10.139c0-5.59,4.548-10.139,10.139-10.139c5.59,0,10.139,4.548,10.139,10.139 C208.352,186.84,203.804,191.389,198.213,191.389z"/>
							</svg>
						</div>
						<h3 itemprop="headline">4.</h3>
						<div class="description" itemprop="description">
							<p>Nyd ugens pose</p>
						</div>
					</a>
				</li>
			</ul>
		</div>
		<div class="whatisit">
			<h3 itemprop="headline">Hvad er det egentlig?</h3>
			<video width="100%" controls>
				<source src="../assets/kbhff-skin/videos/KBHFF_explainer.mp4">
				Your browser does not support HTML video.
			</video>
			<div class="cta actions">
				<a href="/om">Læs mere</a>
			</div>
		</div>
		
		<? if($post_items): ?>
			<div class="news">
				<h3 itemprop="headline">Nyheder</h3>
				<ul class="items articles">
				<? foreach($post_items as $item): 
					$media = $IC->sliceMediae($item, "mediae"); ?>
					<li class="item article id:<?= $item["item_id"] ?>" itemscope itemtype="http://schema.org/NewsArticle" data-readstate="<?= $item["readstate"] ?>">

						<? if($media): ?>
						<div class="image item_id:<?= $media["item_id"] ?> format:<?= $media["format"] ?> variant:<?= $media["variant"] ?>"></div>
						<? endif; ?>


						<?= $HTML->articleTags($item, [
							"context" => ["post"],
							"url" => "/nyheder/tag"
						]) ?>


						<h3 itemprop="headline"><a href="/nyheder/<?= $item["sindex"] ?>"><?= preg_replace("/<br>|<br \/>/", "", $item["name"]) ?></a></h3>


						<?= $HTML->articleInfo($item, "/nyheder/".$item["sindex"], [
							"media" => $media, 
							"sharing" => true
						]) ?>


						<? if($item["description"]): ?>
						<div class="description" itemprop="description">
							<p><?= nl2br($item["description"]) ?></p>
						</div>
						<? endif; ?>

					</li>
				<? endforeach; ?>
				</ul>
				<div class="cta actions">
					<a href="/nyheder">Se alle nyheder</a>
				</div>
			</div>
		<? endif ?>

		<div class="c-box newsletter i:newsletter">
			<h3>Tilmeld Nyhedsbrev</h3>
	
			<form action="//kbhff.us15.list-manage.com/subscribe/post?u=d2a926649ebcf316af87a05bb&amp;id=141ae6f59f" method="post" target="_blank">
				<input type="hidden" name="b_d2a926649ebcf316af87a05bb_141ae6f59f" value="">
				<div class="field email required">
					<label for="input_email">E-mail</label>
					<input type="email" value="" name="EMAIL" id="input_email" />
				</div>

				<ul class="actions">
					<li class="submit"><input type="submit" value="Tilmeld" name="subscribe" class="button" /></li>
				</ul>
			</form>

		</div>

		<div class="hero">
			<img src="https://kbhff.dk/images/273/single_media/380x.png"/>
		</div>

	</div>



</div>
