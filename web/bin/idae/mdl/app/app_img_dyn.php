<?
	include_once($_SERVER['CONF_INC']);

	global $buildArr;
	global $IMG_SIZE_ARR;

	ini_set('display_errors', 55);

	$table_value     = (int)$this->HTTP_VARS['table_value'];
	$table           = !empty($this->table) ? $this->table : $this->HTTP_VARS['table'];
	$codeTailleImage = $this->HTTP_VARS['codeTailleImage'];
	//
	$data_attr = empty($this->HTTP_VARS['edit']) ? '' : ' data-button_image_uploader = ""';

	$src = AppSite::imgApp($table, $table_value, $codeTailleImage);
?>
<div class="padding_more flex_h ">
    <div class="padding_more">
        <div class=" text-center ">
            <img data-image="<?= $codeTailleImage ?>" data-table="<?= $table ?>" data-table_value="<?= $table_value ?>"
                 class="boxshadowb " style="width:<?= $IMG_SIZE_ARR[$codeTailleImage][0] ?>px;max-height:<?= $IMG_SIZE_ARR[$codeTailleImage][1] ?>px;max-width:100%"
                 src="<?= $src ?>?time=<?= time() ?>">
        </div>
    </div>
    <div class="flex_main">
        <div class="padding_more inline text-bold borderb">
		    <?= $codeTailleImage ?>
        </div>
        <div class="padding_more">
			<?= $src ?>
        </div>
        <div class=" bordert flex_h ">
            <div class="flex_main"></div>
            <div class="fileinput-button padding_more borderr">
                <div data-size="<?= $codeTailleImage ?>" data-table="<?= $table ?>"
                     data-table_value="<?= $table_value ?>" <?= $data_attr ?> ><i class="fa fa-refresh"></i> </div> Modifier
            </div>
            <div class="   ">
                <div data-module_link="app_crud/app_delete_image" data-vars="table=<?= $table ?>&table_value=<?= $table_value ?>&codeTailleImage=<?= $codeTailleImage ?>&fileName=<?= $table . '-' . $codeTailleImage . '-' . $table_value ?>"
                     data-target_flyout="true"
                     class="text-center   cursor flex_main" data-link
                     data-table="<?= $table ?>" >
                    <div class="item_icon_more item_icon_shadow  inline">
                        <i class="fa fa-times fa-2x textrouge"  ></i>
                    </div>
                </div>
                <div class="none" data-action="app_img_delete" data-table="<?= $table ?>" data-table_value="<?= $table_value ?>"
                     data-vars="fileName=<?= $table . '-' . $codeTailleImage . '-' . $table_value ?>">
                    <i class="fa fa-times"></i>
                </div>
            </div>

        </div>
    </div>
</div>
