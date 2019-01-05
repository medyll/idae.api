<?php include_once($_SERVER['CONF_INC']); ?>
<?php include_once('menu_top.php') ?>
<?php include_once('menu_run.php') ?>
<?php // include_once('menu_type.php') ?>
<?php

	$APP_SH = new App('shop');
	$rs     = $APP_SH->find();

	global $LATTE;

	$parameters['HTTPCUSTOMERSITE'] = HTTPCUSTOMERSITE;
	$orbit = $LATTE->renderToString(APPTPL.'fragments/orbit_big.html', $parameters);
?>
<?=$orbit?>
<section class="grid-container tctc_page_shop_list">
	<div class="grid-x grid-padding-x">
		<?php
			$ARR_T1 = ['Amorino', 'La panse intégrale', 'Burger centre', 'Chez fernand', 'Pizzica', 'Le Raja', 'Frites ville', 'Fleur de jade', 'Idéal brunch', 'La halle'];
			$ARR_T2 = ['gourmets', 'sur le pouce', 'découverte', 'du monde'];
			$ARR_T3 = ['italien', 'glace', 'dessert', 'rotisserie', 'pizzeria', 'burger', 'américain', 'poulet'];

		?>
		<div class="large-12 cell text-center tctc_main_title">
			<h3>7 restaurants livrent dans ce quartier à partir de 11h30</h3>
			<h4><?= $_GET['nomQuartier'] . ' ' . $_GET['nomVille'] ?></h4>
		</div>
		<div class="large-1 cell"></div>
		<div class="large-10 cell">
			<div class="grid-x grid-margin-x tctc_cell_shop">
				<?php
					while ($arr = $rs->getNext()) {
						$idshop   = $arr['idshop'];
						$nom      = $arr['nomShop'];
						$ville    = $arr['villeShop'];
						$quartier = empty($arr['quartierShop']) ? 'quartier' : $arr['quartierShop'];
						?>
						<div class="small-1 medium-6 large-6 cell tctc_shop_item    ">
							<div class="tctc_shop_item_info">
								<div class="img_holder" style="background-image: url('../images/site/JPEG/food (<?= rand(1, 6) ?>).jpg')"></div>
								<div class="tctc_shop_item_badge">40 / 50
									<br>
									<span class="small">min </span></div>
								<div class="tctc_shop_item_badge_type">Exclu</div>
								<h3>
									<a href="<?= HTTPSITE ?>/restaurant/<?= strtolower($ville) ?>/<?= strtolower($quartier) ?>/<?= $nom ?>/<?=$idshop?>"><?= $nom ?></a>
								</h3>
								<a><?= $ARR_T3[rand(0, sizeof($ARR_T3) - 1)] ?> </a>
								.
								<a><?= $ARR_T3[rand(0, sizeof($ARR_T3) - 1)] ?></a>
								.
								<a><?= $ARR_T3[rand(0, sizeof($ARR_T3) - 1)] ?></a>
							</div>
						</div>
						<?
					} ?>
			</div>
		</div>
		<div class="large-1 cell"></div>
</section>