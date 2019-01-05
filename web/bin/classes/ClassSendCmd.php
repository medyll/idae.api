<?php

	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 26/05/2018
	 * Time: 01:56
	 */
	class SendCmd {

		static function play_sound($room) {
			AppSocket::send_cmd('act_script', ['script'    => 'play_notification',
			                                   'arguments' => [],
			                                   'options'   => []], $room);
		}

		static function remove_selector($remove_selector, $room) {
			AppSocket::send_cmd('act_remove_selector', $remove_selector, $room);
		}

		/**
		 * @param      $module
		 * @param      $table
		 * @param null $table_value
		 * @param      $target_selector
		 * @param      $room
		 *
		 * @return bool
		 */
		static function insert_mdl($module, $table, $table_value = null, $target_selector, $room) {
			if (empty($table)) return false;
			if (empty($target_selector)) return false;

			$target_selector = SendCmd::build_selector($target_selector);
			AppSocket::send_cmd('act_insert_mdl', ['table'           => $table,
			                                       'table_value'     => $table_value,
			                                       'mdl'             => $module,
			                                       'target_selector' => $target_selector], $room);
		}

		static function insert_selectors($target_selector_and_value = [], $room = null) {
			if (empty($target_selector_and_value)) return false;

			AppSocket::send_cmd('act_insert_selector', $target_selector_and_value, $room);
		}

		/**
		 * @param $target_selector
		 * @param $value
		 * @param $room
		 *
		 * @return bool
		 */
		static function insert_selector($target_selector, $value, $room) {
			if (empty($target_selector)) return false;

			AppSocket::send_cmd('act_insert_selector', [$target_selector,
			                                            $value], $room);
		}

		/**
		 * @param       $mdl
		 * @param       $table
		 * @param       $table_value
		 * @param array $extra_vars
		 * @param       $room_session_id
		 *
		 * @return bool
		 */
		static function notify_mdl($mdl, $table, $table_value, $extra_vars = [], $room_session_id) {
			if (empty($room_session_id)) return false;
			$extra_vars['table']       = $table;
			$extra_vars['table_value'] = $table_value;
			$httpvars                  = http_build_query($extra_vars);
			NotifySite::notify_mdl('idae/module/' . $mdl . '/' . $httpvars, $extra_vars, $room_session_id);
		}

		static function build_selector($target_selector) {

			if (!is_array($target_selector)) {
				return $target_selector;
			}
			$return = '';
			foreach ($target_selector as $key => $selector) {
				if (is_array($selector)) {
					switch ($key) {
						case ':not':
							//$return .= "$key(".SendCmd::build_selector($selector).")";
							break;
						default:
							break;
					}
					$return .= ' ' . SendCmd::build_selector($selector);

				} else {
					switch ($key) {
						case ':not':
							//$return .= "$key($selector)";
							break;
						default:
							$return .= "[$key=$selector]";
							break;
					}

				}
			}

			return $return;
		}

		static function sendScript($script_name,$arguments = [],$room=null) {
			AppSocket::send_cmd('act_script', ['script'    => $script_name,
			                                   'arguments' => $arguments], $room);
		}
	}