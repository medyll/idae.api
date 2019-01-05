<?
	include_once($_SERVER['CONF_INC']);
	ini_set('display_errors' , 55);

	$table = $_POST['table'];
	$vars  = empty($_POST['vars']) ? [ ] : function_prod::cleanPostMongo($_POST['vars'] , 1);

	$Table          = ucfirst($table);
	$table_value    = empty($_POST['table_value']) ? '' : (int)$_POST['table_value'];
	$act_chrome_gui = empty($_POST['act_chrome_gui']) ? '' : 'act_chrome_gui=' . $_POST['act_chrome_gui'];
	//
	$APP = new App($table);
	//
	/** @var  $EXTRACTS_VARS */
	//   VAR BANG :$NAME_APP ,  $ARR_GROUP_FIELD ,  $APP_TABLE, $GRILLE_FK, $R_FK, $HTTP_VARS;
	$EXTRACTS_VARS = $APP->extract_vars($table_value , $vars);
	extract($EXTRACTS_VARS , EXTR_OVERWRITE);

	$BASE_APP = $APP_TABLE['codeAppscheme_base'];
	//
	$id = 'id' . $table;
	if ( !empty($table_value) ) {
		$vars[$id] = (int)$table_value;
	}

	$ARR = $APP->query_one($vars);
	//
	//

	if ( sizeof($R_FK) != 0 ):
		foreach ($R_FK as $arr_fk):
			$final_rfk[$arr_fk['scope']][] = $arr_fk;
		endforeach;

		//if (sizeof($final_rfk) == 0) return;
		?>
		<div class = " ">
			<?
				foreach ($final_rfk as $key => $arr_final):
					?>
					<div class = "  blanc ">
						<div class = "blanc    grid-x grid-padding-x align-middle     ">
							<?
								foreach ($arr_final as $arr_fk):
									if ( empty($arr_fk['count']) ) {
										continue;
									}
									if ( str_find($arr_fk['table'] , '_ligne') ) {
										continue;
									}
                                    /** todo apply droits  */
                                    if($arr_fk['table']=='commande_proposition'){
                                        continue;
                                    }
									$APP_TMP          = new App($arr_fk['table']);
									$Idae_tmp         = new Idae($arr_fk['table']);
									$table_fk         = $arr_fk['table'];
									$name_id_fk       = 'id' . $table_fk;
									$vars_rfk['vars'] = [ 'id' . $table => (int)$table_value ];
									//$vars_rfk['table_value']        = $ARR['id' . $arr_fk['table']];
									$vars_rfk['table'] = $arr_fk['table'];
									$count             = $arr_fk['count'];
									$html_list_link    = http_build_query($vars_rfk);
									//
									$sortByGB        = $APP_TMP->app_table_one['sortFieldName'];
									$sortDirGB       = (int)$APP_TMP->app_table_one['sortFieldOrder'];
									$sortBySecondGB  = $APP_TMP->app_table_one['sortFieldSecondName'];
									$sortDirSecondGB = (int)$APP_TMP->app_table_one['sortFieldSecondOrder'];

									$arr_sort_groupby = array_filter([ $sortByGB => $sortDirGB , $sortBySecondGB => $sortDirSecondGB ]);

									$RS_TMP            = $APP_TMP->find($vars_rfk['vars'])->sort($arr_sort_groupby)->limit(3);

									?>
									<div class = "cell small-5 medium-4 large-3">
										<div class="padding_more align-center">
											<div class = "cursor  ">
												<div class = "text-center padding_more" >
													<div class="padding_more inline borderb">
														<a data-module_link="app_liste/app_liste" data-vars="<?= $html_list_link ?>" class="paddingg_more" <?= $act_chrome_gui ?> data-link data-table="<?= $vars_rfk['table'] ?>" data-vars="<?= http_build_query($vars_rfk); ?>">
															<i class = "fa fa-<?= $APP_TMP->iconAppscheme ?> fa-3x" style = "color: <?= $APP_TMP->colorAppscheme ?>;"></i>
														</a>
													</div>
													<div class="padding_more">
														<a data-module_link="app_liste/app_liste" data-vars="<?= $html_list_link ?>" class="paddingg_more" <?= $act_chrome_gui ?> data-link data-table="<?= $vars_rfk['table'] ?>" data-vars="<?= http_build_query($vars_rfk); ?>">
														<span data-count = "data-count" data-table = "<?= $arr_fk['table'] ?>" data-vars = "<?= http_build_query($vars_rfk); ?>"><?= $count ?></span>
														<br>
														<?= $arr_fk['nomAppscheme']  ?>
														</a></div>
												</div>
												<div class = " ">
													<div class="blanc">
														<?
															while ($arr_app = $RS_TMP->getNext()) {
																$value = $arr_app[$name_id_fk];
																// echo $Idae_tmp->module('fiche_fields' , "table=$table_fk&table_value=$value");
															}
														?>
													</div>
												</div>
											</div>
										</div>
									</div>
									<?
								endforeach; ?>
						</div>
					</div>
				<? endforeach; ?>
		</div>
		<?
	endif; ?>


