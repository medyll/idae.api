<?
	include_once($_SERVER['CONF_INC']);

	$vars        = empty($_POST['vars']) ? [ ] : function_prod::cleanPostMongo($_POST['vars'] , 1);
	$vars        = array_filter($vars);
	$table       = $_POST['table'];
	$table_value = empty($_POST['table_value']) ? null : (int)$_POST['table_value'];

	$id     = 'id' . $_POST['table'];
	$Table  = ucfirst($table);
	$nom    = 'nom' . ucfirst($table);
	$prenom = 'prenom' . ucfirst($table);
	$code   = 'code' . ucfirst($table);
	//
	//
	$APP = new App($table);
	if ( !empty($_POST['vars_in']) ) {
		foreach ($_POST['vars_in'] as $key_vars => $value_vars):
			$value_vars['$in'] = json_decode($value_vars['$in']);
			$vars[$key_vars]   = $value_vars;
		endforeach;
	}
	//  vardump($vars);
	$HTTP_VARS  = $APP->translate_vars($vars);
	$APP_TABLE  = $APP->app_table_one;
	$ARR_FIELDS = $APP->get_field_list();

	$add = [ ];

    $sortBy        = empty($APP->app_table_one['sortFieldName']) ? $nom : $APP->app_table_one['sortFieldName'];
    $sortDir       = empty($APP->app_table_one['sortFieldOrder']) ? 1 : (int)$APP->app_table_one['sortFieldOrder'];
    $sortBySecond  = empty($APP->app_table_one['sortFieldSecondName']) ? null : $APP->app_table_one['sortFieldSecondName'];
    $sortDirSecond = empty($APP->app_table_one['sortFieldSecondOrder']) ? 1 : (int)$APP->app_table_one['sortFieldSecondOrder'];
    $sortByThird   = empty($APP->app_table_one['sortFieldThirdName']) ? null : $APP->app_table_one['sortFieldThirdName'];
    $sortDirThird  = empty($APP->app_table_one['sortFieldThirdOrder']) ? 1 : (int)$APP->app_table_one['sortFieldThirdOrder'];

    $sort_fields = [$sortBy => $sortDir];
    if ($sortBySecond) {
        $sort_fields = array_merge($sort_fields,[$sortBySecond => $sortDirSecond]);
    }
	//
	if ( !empty($_POST['search']) ):
		$search    = trim($_POST['search']);
		$arrSearch = explode(' ' , trim($search));
		foreach ($arrSearch as $key => $value) {
			// $out[] = new MongoRegex("/.*" . (string)$arrSearch[$key] . "*./i");
		}
		$out[] = new MongoRegex("/" . (string)$search . "/i");
		if ( sizeof($out) == 1 ) {
			$add = [ '$or' => [ [ $nom => [ '$all' => $out ] ] , [ $id => (int)$_POST['search'] ] , [ 'code' . $Table => [ '$in' => $out ] ] , [ $prenom => [ '$in' => $out ] ] ] ];
		}
		if ( is_int($_POST['search']) ):
			$add['$or'][] = [ $id => (int)$_POST['search'] ];
		endif;
		$rs = $APP->find($vars + $add)->sort([ 'nom' . $Table => 1 , 'ordre' . $Table => - 1 ])->limit(250);
	// vardump_async(array_merge( $vars , $add),true);
	else:
		$rs = $APP->find($vars)->sort($sort_fields)->limit(250);
	endif;



?>
<select data-select name = "vars[<?= $id ?>]">
	<? while ($arr = $rs->getNext()) { ?>
		<option value = "<?= $arr[$id] ?>" <?=($table_value==$arr[$id])? 'selected' : '' ?>>
			<?= $arr[$nom] ?> <?= htmlspecialchars(empty($arr[$prenom]) ? '' : ' ' . $arr[$prenom]); ?>
			<? if ( array_key_exists('codePostal' . $Table , $ARR_FIELDS) && $arr['codePostal' . $Table] ) { ?>
				<?= $arr['codePostal' . $Table] ?><? } ?>
		</option>
	<? } ?>
</select>
