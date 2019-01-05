<?php
	include_once($_SERVER['CONF_INC']);

	$nom = $_GET['nomShop'];
?>
<?php include_once('menu_run.php') ?>
<div data-sticky-container>
	<div class="grid-container">
		<div class="grid-x">
			<div class="cell small-8">
				<h3><?=$nom?></h3>
				<p>Exclu
				   Français
				   Rôtisserie
				   Poulet
					<br>
				   10 Boulevard Carnot, Nice, 06300
				   +33493898391<br>
				   Ouvert jusqu'à 22:00
				   Qualité - Tradition - Bio
				</p>
			</div>
			<div class="cell small-4">
				<div class="padding">
					<img src="../images/site/JPEG/food (1).jpg"  class="thumbnail" >
					Livrer à
					<br>
					06300 Nice France

				</div>
			</div>
		</div>
	</div>
</div>
<div class="grid-container" data-sticky-container>
	<div class="grid-x"  data-sticky data-margin-top="0" >
		<div class="cell small-8">
			<div class="grid-x">
				<ul class="menu">
					<li>
						<a href="#ancre">Categorie</a>
					</li>
					<li>
						<a href="#">Dessrts</a>
					</li>
					<li>
						<a href="#">Boissons</a>
					</li>
					<li>
						<a href="#">Bieres et vins</a>
					</li>
					<li>
						<a href="#">Accompagnements supplémentaires</a>
					</li>
				</ul>
				<ul class="dropdown menu" data-dropdown-menu>
					<li class="is-dropdown-submenu-parent">
						<a href="#ancre">Voir tout</a>
						<ul class="menu">
							<li>
								<a>test</a>
							</li>
							<li>
								<a>test</a>
							</li>
							<li>
								<a>test</a>
							</li>
							<li>
								<a>test</a>
							</li>
						</ul>
					</li>
				</ul>
			</div>
			<div class="grid-x grid-margin-x">
				<div class="callout alert cell small-8" data-closable>
					<div>Pas de commandes pour le moment, à bientôt !</div>
					<button class="close-button" aria-label="Dismiss alert" type="button" data-close>
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
			</div>
		</div>
		<div class="cell small-4">
			<div class="padding">
				<a href="" class="button expanded">Valider mon panier</a>
			</div>
		</div>
	</div>
</div>
<div  >
	<div class="grid-container">
		<div class="grid-x  ">
			<div class="cell large-8  ">
				<?php for ($v = 0; $v <= 7; $v++) { ?>
					<h3>Catégorie 1</h3>
					<p>Elevé en plein air, en liberté et agriculture biologique pendant 81 jours minimum - 1,8kg - Provenance France</p>
					<div class="grid-x grid-margin-x">
						<?php for ($i = 0; $i < 2; $i++) { ?>
							<div class="card cell large-1">
								<div class="card-divider">
									<div><h4>Le poulet bio </h4></div>
									<div class="align-right"><h4>25€</h4></div>
								</div>
								<div class="card-section">
									<div><p>Un accompagnement est offert Un accompagnement est offert Un accompagnement est offert </p></div>
								</div>
								<div class="card-image">
									<img src="../images/site/JPEG/food (1).jpg">
								</div>
							</div>
						<?php } ?>
					</div>
				<?php } ?>
			</div>
			<div class="cell large-4">
			</div>
		</div>
	</div>
</div>