<?php
	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 24/05/14
	 * Time: 18:58
	 */
	include_once($_SERVER['CONF_INC']);
	if (empty($_POST['table']) || empty($_POST['table_value'])) return;
	$table       = $_POST['table'];
	$table_value = (int)$_POST['table_value'];

	$APP = new App($table);

	$id  = 'id' . $table;
	$nom = 'nom' . ucfirst($table);
	$ARR = $APP->findOne([$id => $table_value]);

	$options = ['apply_droit'        => [$type_session,
	                                     'R'],
	            'data_mode'          => 'fiche',
	            'scheme_field_view'  => 'mini',
	            'field_draw_style'   => 'draw_html_field',
	            /* 'scheme_field_view_groupby' => 'group',*/
	            'fields_scheme_part' => 'all',
	            'field_composition'  => ['hide_field_icon'  => 1,
	                                     'hide_field_name'  => 1,
	                                     'hide_field_value' => 1]];

	$Fabric = new IdaeDataSchemeFieldDrawerFabric($table, $options);
	$Fabric->fetch_query($ARR, 'findOne');
	$tplData = $Fabric->get_templateDataHTML();

	$name  = $Fabric->AppDataSchemeModel->appscheme_name;
	$model = $Fabric->AppDataSchemeModel->scheme_model;
?>


<div class="">
    <div class="titreFor padding_more blanc  ">
        <div class="flex_h flex_align_middle">
            <div class="flex_main">
                <div class="padding borderb"><?= idioma('Suppression') ?> <?= $name ?></div>
                <div class="padding text-bold"><?= $ARR[$nom] ?></div>
            </div>
            <div class="padding_more ededed">
                <i style="color:<?= $model->colorAppscheme ?>" class="fa fa-<?= $model->iconAppscheme ?> fa-3x"></i>
            </div>
        </div>

    </div>
    <form action="<?= ACTIONMDL ?>app/actions.php"
          onsubmit="ajaxFormValidation(this);return false;" auto_close="auto_close">
        <input type="hidden"
               name="F_action"
               value="app_delete"/>
        <input type="hidden"
               name="table"
               value="<?= $table ?>"/>
        <input type="hidden"
               name="table_value"
               value="<?= $table_value ?>"/>
        <div class="flex_h flex_align_middle">
            <div class='boxshadowr padding_more' style="width:90px;text-align:center">
                <i class="fa fa-trash-o fa-5x textrouge"></i>
            </div>
            <div class="padding_more">
				<?= $tplData['scheme_field_mini'] ?>
            </div>
        </div>
        <div class="padding_more bordert ededed text-center flex_h">
            <div class="flex_main">
                <button type="button"
                        class="button" data-close=""><?= idioma('Annuler') ?></button>

            </div>
            <div class="flex_main">
                <button class="button"
                        type="submit"><?= idioma('Supprimer') ?></button>

            </div>
        </div>
    </form>
</div>
