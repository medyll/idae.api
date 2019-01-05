<?php
	include_once($_SERVER['CONF_INC']);

	$APP_SH = new App('shop');
	$rs_sh  = $APP_SH->find();

	global $LATTE;

	$parameters['HTTPCUSTOMERSITE'] = HTTPCUSTOMERSITE;
	$orbit = $LATTE->renderToString(APPTPL.'orbit_big.html', $parameters);
?>

<?=$orbit?>
<div class="tctc_hero" style="display:none;">
	<header class="hero grid-container ">
		<div class="grid-x grid-padding-x">
			<div class="large-6 cell">
				<h3>Vos restaurants en livraison ...
					<br>
				    tac-tac !
				</h3>
				<form action="bin/pages/restaurants.php">
					<div class="grid-x">
						<div class="cell small-6 large-11">
							<input type="text"
							       placeholder="Saisir votre adresse ou votre code postal"
							       autocomplete="off"/>
						</div>
						<div class="cell small-6 large-1">
							<button class="button full"><i class="fa fa-search"></i></button>
						</div>
					</div>
				</form>
			</div>
			<div class="large-6 cell"></div>
		</div>
	</header>
</div>
<section class="grid-container" style="margin-top:0.5rem;margin-bottom: 5rem;">
	<div class="grid-x grid-padding-x">
		<div class="cell small-6 large-3">
			<div class="text-center"><i class="fa fa-4x fa-bicycle"></i></div>
			<br>
			Compte entreprise : Entreprises soyez les premiers!
		</div>
		<div class="cell small-6 large-6 border4">
			<div class="text-center"><i class="fa fa-4x fa-bicycle"></i></div>
			<br>
			Compte entreprise : Entreprises soyez les premiers!
		</div>
		<div class="cell small-6 large-3">
			<div class="text-center"><i class="fa fa-4x fa-bicycle"></i></div>
			<br>
			Compte entreprise : Entreprises soyez les premiers!
		</div>
	</div>
</section>


<section >
	<?php include_once('./bin/templates/featured_testimonials.html'); ?>
</section>
<section class="tctc_home_best_shop">
	<div class="grid-container">
		<div class="grid-x grid-padding-x">
			<div class="large-12 cell">
				<h2 class="tctc_home_title">Vos livraisons préférées</h2>
			</div>
			<div class="large-12 cell">
				<div class="grid-x grid-margin-x tctc_cell_shop">
					<?php
						while ($arr = $rs_sh->getNext()) {
							$nom = $arr['nomShop'];
							?>
							<div class="large-4 cell tctc_shop_item animated slide-in-left  ">
								<div class="tctc_shop_item_info">
									<div class="img_holder" style="background-image: url('../images/site/JPEG/food (<?= $img ?>).jpg')"></div>
									<h3>
										<a href="restaurant/nice/riquier/<?= $nom ?>"><?= $nom ?></a>
									</h3>
									<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean fringilla velit et leo tempor gravida. </p>
								</div>
							</div>
							<?
						} ?>
				</div>
			</div>
		</div>
	</div>
</section>
<section class="grid-container">
	<div class="grid-x grid-padding-x">
		<?php
			$ARR_T1 = ['Mets', 'Repas', 'Grignotages', 'Desserts', 'Déjeuners'];
			$ARR_T2 = ['gourmets', 'sur le pouce', 'découverte', 'du monde'];

		?>
		<div class="large-12 cell">
			<h2 class="tctc_home_title">A la carte</h2>
		</div>
		<div class="large-12 cell">
			<div class="grid-x grid-margin-x tctc_cell_food">
				<?php
					$i = 0;
					for ($i = 1; $i <= 6; $i++) {
						$titre = $ARR_T1[rand(0, 3)] . ' ' . $ARR_T2[rand(0, 3)];
						?>
						<div class="large-6 cell tctc_cell_food_item ">
							<h3><?= $titre ?></h3>

							<div class="tctc_cell_food_item_info">
								<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean fringilla velit et leo tempor gravida. </p>
								<a href="/restaurants/nice/riquier">Voir <?= $titre ?></a>
							</div>
						</div>
					<?php } ?>
			</div>
		</div>
</section>
