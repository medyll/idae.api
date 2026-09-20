<?php

	/**
	 * Created by PhpStorm.
	 * User: Mydde
	 * Date: 03/10/2017
	 * Time: 01:02
	 */
	class NotifySite extends App {

		/**
		 * @param string|null $table Table to work against
		 */
		function __construct($table = null) {
			parent::__construct($table);
		}

		/**
		 * Opens a modal in the browser over the socket.
		 *
		 * @param string      $msg
		 * @param string      $type Modal style
		 * @param array       $args Extra options; `mdl_vars` is passed to the template
		 * @param string|null $id   Session to target; the current one when null
		 * @return void
		 */
		static function notify_modal( $msg,$type = 'alert', $args=[], $id = null) {
			$id       = $id ?: session_id();
			$mdl_vars = $args['mdl_vars'] ?: [];
			switch ($type):
				case 'loading' :
					$args['icon']  = 'spinner fa-spin';
					$args['color'] = 'white';
					break;
				case 'alert' :
					$args['icon']  = 'exclamation-triangle';
					$args['color'] = 'orange';
					break;
				case 'success' :
					$args['icon']  = 'check-circle';
					$args['color'] = 'green';
					break;
				case 'error' :
					$args['icon']  = 'ban';
					$args['color'] = 'red';
					break;
				default :
					$args['icon']  = 'exclamation-triangle';
					$args['color'] = 'orange';
					break;
			endswitch;
			AppSocket::send_cmd('act_notify_reveal', array_merge(['msg' => $msg, 'mdl' => '/fragment/notify', 'type' => $type, 'mdl_vars' => $mdl_vars],$args), $id);
		}
		/**
		 * Shows a back-office notification.
		 *
		 * @param string      $msg
		 * @param string      $type
		 * @param array       $args
		 * @param string|null $id Session to target
		 * @return void
		 */
		static function notify_idae( $msg,$type = 'alert', $args=[], $id = null) {
			$id       = $id ?: session_id();
			$args['type'] = $type;

			switch ($type):
				case 'alert' :
					$args['icon']  = 'exclamation-triangle';
					$args['color'] = 'orange';
					break;
				case 'success' :
					$args['icon']  = 'check-circle';
					$args['color'] = 'green';
					break;
				case 'error' :
					$args['icon']  = 'ban';
					$args['color'] = 'red';
					break;
				default :
					$args['icon']  = 'exclamation-triangle';
					$args['color'] = 'orange';
					break;
			endswitch;
			AppSocket::send_cmd('act_notify',['msg' => $msg,  'options'=>['vars' => $args,'type' => $type,'mdl' => '/fragment/notify']], $id);
			//	AppSocket::send_cmd('act_notify', array_merge(['msg' => $msg,  'type' => $type, 'options'=>['vars' => $mdl_vars,'mdl' => '/fragment/notify']],$args), $id);
		}
		/**
		 * Shows a notification whose body is a rendered module.
		 *
		 * @param string      $mdl  Module path
		 * @param array       $args Module variables
		 * @param string|null $id   Session to target
		 * @return void
		 */
		static function notify_mdl($mdl, $args=[], $id = null) {
			$id       = $id ?: session_id();
			AppSocket::send_cmd('act_notify',['msg' => '',  'options'=>['vars' => $args,'mdl' => $mdl]], $id);
			//	AppSocket::send_cmd('act_notify', array_merge(['msg' => $msg,  'type' => $type, 'options'=>['vars' => $mdl_vars,'mdl' => '/fragment/notify']],$args), $id);
		}
		/**
		 * Raises a native browser notification, rather than an in-page one.
		 *
		 * @param string      $title
		 * @param string      $body
		 * @param string      $type
		 * @param array       $args
		 * @param string|null $id Session to target
		 * @return void
		 */
		static function notify_notification( $title,$body='',$type = 'alert', $args=[], $id = null) { // html5
			$id       = $id ?: session_id();
			$mdl_vars = $args['mdl_vars'] ?: [];
			AppSocket::send_cmd('act_notify_notification', array_merge(['title' => $title,'body'=> $body, 'mdl' => '/fragment/notify', 'type' => $type, 'mdl_vars' => $mdl_vars],$args), $id);
		}
	}