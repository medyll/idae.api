<?php

	namespace Idae\Data\Scheme\Field\Element;

	class IdaeDataSchemeFieldElementCast {

		public function __construct() {

		}

		private function get_typeFromFielCode() {

		}

		/**
		 * @param \IdaeDataSchemeFieldElement $fieldElement
		 *
		 * @return float|string
		 */
		public static function cast_field($fieldElement) {
			$value                    = $fieldElement->field_value;
			$codeAppscheme_field_type = $fieldElement->field_model_type;

			if ($fieldElement->field_model['is_image'] == true) {
				$codeAppscheme_field_type = 'custom_image';
			};
			// var_dump($fieldElement);
			switch (strtolower($codeAppscheme_field_type)):
				case 'distance':
					$value = round($value / 1000, 2);
					break;
				case 'minutes':
					if (empty($value)) break;
					$value = ceil($value / 60);
					break;
				case 'valeur':
					$value = (is_int($value)) ? maskNbre($value, 0) : $value;
					break;
				case 'prix':
					if (empty($value)) break;
					$value = maskNbre((float)$value, 2);
					break;
				case 'prix_precis':
					if (empty($value)) break;
					$value = maskNbre((float)$value, 6);
					break;
				case 'pourcentage':
					$value = (float)$value;
					break;
				case 'date':
					if (empty($value)) break;
					$value = date_fr($value);
					break;
				case 'heure':
					if (empty($value)) break;
					$value = maskHeure($value);
					break;
				case 'phone':
					if (empty($value)) break;
					$value = maskTel($value);
					break;
				case 'textelibre':
					if (empty($value)) break;
					$value = nl2br(stripslashes($value));
					break;
				case 'texteformate':
					if (empty($value)) break;
					break;
				case 'bool':
					$value = !empty($value);
					break;
				case 'icon':
					if (empty($value)) break;
					$value = "fa fa-$value";
					break;
				case 'password':
					if (empty($value)) break;
					$value = "***********";
					break;
				case 'color':
					if (empty($value)) break;
					break;
				case 'custom_image':
					$url             = $fieldElement->field_model['urlImage'];
					$appscheme_value = $fieldElement->appscheme_value;
					//$value = IdaeDataSchemeImage::get_image_url($fieldElement->field_model_scheme_code,$fieldElement->appscheme_value,'small');
					$value = "https://tac-tac.lan/images_base/tactac/$fieldElement->field_model_scheme_code/$url-$appscheme_value.jpg";
					break;
			endswitch;

			return $value;
		}

		/**
		 * @param \IdaeDataSchemeFieldElement $fieldElement
		 *
		 * @return float|string
		 */
		public static function field_json_template($fieldElement) {
			$out = get_object_vars($fieldElement);
			unset($out['field_model']);

			return (json_encode($out, JSON_FORCE_OBJECT));
		}

		/**
		 * @param \IdaeDataSchemeFieldElement $fieldElement
		 *
		 * @return float|string
		 */
		public static function field_html_template($fieldElement) {

			$value                    = $fieldElement->field_value_casted;
			$codeAppscheme            = $fieldElement->field_model_scheme_code;
			$iconAppscheme_field      = $fieldElement->field_model_icon;
			$codeAppscheme_field_type = $fieldElement->field_model_type;
			$codeAppscheme_field      = $fieldElement->field_model_code;
			$codeAppscheme_has_field  = $fieldElement->field_code;
			$appscheme_value          = $fieldElement->appscheme_value;

			switch ($codeAppscheme_field_type):
				case 'distance':
					$value .= ' kms';
					break;
				case 'minutes':
					if (empty($value)) break;
					$value .= ' minutes';
					break;
				case 'valeur':
				case 'date':
				case 'heure':
					break;
				case 'prix':
				case 'prix_precis':
					if (empty($value)) break;
					$value .= ' €';
					break;
				case 'pourcentage':
					$value .= ' % ';
					break;
				case 'phone':
					if (empty($value)) break;
					$value = maskTel($value);
					break;
				case 'textelibre':
					if (empty($value)) break;
					$value = "<div style='max-height:200px;overflow:auto;'>" . $value . "</div>";
					break;
				case 'texteformate':
					if (empty($value)) break;
					break;
				case 'bool':
					$class_color = ($value == 1) ? 'textvert' : 'textgris';
					$icon        = empty($iconAppscheme_field) ? 'check-circle' : $iconAppscheme_field;
					$value       = "<i class = 'fa fa-$icon $class_color'  ></i >";
					break;
				case 'icon':
					if (empty($value)) break;
					$value = "<i class= 'fa fa-$value'></i>";
					break;
				case 'password':
					break;
				case 'color':
					$value = "<i class='fa fa-circle' style='color:$value ;'  ></i>";
					break;
				case 'custom_image':
					$code = $fieldElement->field_model['codeTailleImage'];

					$value = "<img data-codeImage='$code'  src='$value'  />";
					break;
			endswitch;

			$str = "<span data-appscheme='$codeAppscheme' data-appscheme_value='$appscheme_value' data-field_name='$codeAppscheme_has_field'  data-field_name_raw='$codeAppscheme_field'  >$value </span>";

			return $str;

		}

		/**
		 * @param \IdaeDataSchemeFieldElement $fieldElement
		 *
		 * @return float|string
		 */
		public static function field_input_template($fieldElement) {

			$value                    = $fieldElement->field_value_casted;
			$iconAppscheme_field      = $fieldElement->field_model_icon;
			$codeAppscheme_field_type = $fieldElement->field_model_type;
			$codeAppscheme_field      = $fieldElement->field_model_code;
			$codeAppscheme_has_field  = $fieldElement->field_code;
			$nomAppscheme_has_field   = $fieldElement->field_name;

			$var_name = 'vars';
			$class    = "";
			$type     = 'text';

			$required      = empty($fieldElement->field_required) ? '' : 'required="required"';
			$required_hash = empty($fieldElement->field_required) ? '' : ' <i class="fa fa-circle textrouge"></i> ';

			$var_name_cast = $var_name . '[' . $codeAppscheme_has_field . ']';

			switch ($codeAppscheme_field_type):
				case "icon":
					$attr  = 'act_defer mdl="app/app_select_icon_fa" vars="' . http_build_query([]) . '"';
					$class = "fauxInput";
					$tag   = 'div';
					break;
				case "date":
					$class = "validate-date-au";
					$type  = "date";
					break;
				case "heure":
					$type  = "time";
					$class = "heure inputSmall";
					break;
				case "minutes":
					$class = " inputSmall";
					break;
				case "email":
					$class = "email";
					$type  = 'email';
					break;
				case "identification":
					$class = "inputLarge";
					break;
				case "codification":
					$class = "inputSmall";
					break;
				case "localisation":
					$class = "inputLarge";
					break;
				case "bool":
					$class = "inputLarge";
					$tag   = 'checkbox';
					break;
				case "textelibre":
					$class = "inputFull";
					$tag   = 'textarea';
					break;
				case "texteformate":
					$class = "inputFull";
					$tag   = 'textarea';
					$attr  = 'ext_mce_textarea';
					break;
				case "color":
					$class = "inputTiny";
					$type  = 'color';
					if (empty($value)) $value = '_';
					break;
				case "valeur":
					$class = "inputTiny";
					break;
				case "texte":
					$class = "inputLarge";
					break;
				case "prix_precis":
					$value = maskNbre((float)$value, 6);
					$class = "inputSmall";
					break;
				case "prix":
					$value = maskNbre((float)$value, 2);
					$class = "inputSmall";
					break;
				case "password":
					$class = "inputMedium";
					$type  = 'password';
					$value = '';
					break;
				case 'phone':
					$value = maskTel($value);
					break;
				case 'custom_image':
					$tag   = 'div';
					$code  = $fieldElement->field_model['codeTailleImage'];
					$value = "<div class='flex_h flex_align_middle'> <img data-codeImage='$code'  src='$value'  /><input type='file'></div>";
					break;
				default:
					$class = "";
					$type  = 'text';
					break;
			endswitch;

			switch ($tag):
				case 'div':
					$str = '<div ' . $required . ' class="' . $class . '" ' . $attr . '  >' . $value . '</div>';
					break;
				case 'textarea':
					$str = '<textarea  ' . $attr . ' ' . $required . ' class="' . $class . '" name="' . $var_name_cast . '">' . $value . '</textarea>';
					break;
				case 'checkbox':
					$str = chkSch($codeAppscheme_has_field, $value);
					break;
				default:
					$placeholder = 'placeholder="' . $nomAppscheme_has_field . '"';
					$str         = '<input ' . $attr . ' ' . $required . ' ' . $placeholder . ' class="' . $class . '" type="' . $type . '" name="' . $var_name_cast . '" value="' . $value . '" >';
					break;
			endswitch;

			if ($fieldElement->mode == 'external') {
				$str = "$value <a class='textbleu'>...</a>";
			}

			return "<div class='flex_h flex_align_middle'><div class='  padding'>$required_hash</div><div class='flex_main'>$str</div></div>";
		}
	}
