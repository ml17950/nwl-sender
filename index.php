<?php
	session_start();
	date_default_timezone_set(MY_TIMEZONE);
	
	if (file_exists('config/config.php'))
		include_once('config/config.php');
	else
		exit("config/config.php not found");
	include_once('includes/lang_'.MY_LANGUAGE.'.php');
	include_once('includes/defines.php');
	include_once('includes/common.php');
	include_once('includes/class.core.php');
	$core = new clsCore();
	
	$core->initialize();
	define('BID', $_SESSION['current-zone']);
	
	$view = param('view', 'home');
	define('VIEW', $view);
	
	$core->ui->html_head('NWL-Sender');
	
	if ($core->user->is_loggedin($view)) {
		$core->ui->header();
		$core->ui->sidebar();
		
		echo "<div class='content'>";
		
		switch ($view) {
			case 'home':
			case 'login':
				$core->ui->home_overview();
				break;
			
			case 'zone-select':
				$id = param_int('id');
				$core->zone->select($id);
				break;
			
			case 'zone-list':
				$core->zone->list_all();
				break;
			
			case 'zone-edit':
				$core->zone->edit();
				break;
			
			case 'zone-create':
				$core->zone->create();
				break;
			
			case 'zone-delete':
				$id = param_int('id');
				$core->zone->delete($id);
				break;
			
			case 'nwl-create':
				$core->nwl->create();
				break;
			
			case 'nwl-edit':
				$core->nwl->edit();
				break;
			
			case 'nwl-send':
				$core->nwl->send();
				break;
			
			case 'nwl-history':
				$core->nwl->history();
				break;
			
			case 'nwl-preview':
				$id = param_int('id');
				$core->nwl->preview($id);
				break;
			
			case 'nwl-delete':
				$id = param_int('id');
				$core->nwl->delete($id);
				break;
			
			case 'nwl-copy':
				$id = param_int('id');
				$core->nwl->copy($id);
				break;
			
			case 'sub-list':
				$set = param('set');
				if (!empty($set)) {
					$status = param_int('status');
					if ($status == ABO_ACTIVE) {
						$core->nwl->opt_validate(BID, $set, '!!adm!!');
					}
					elseif ($status == ABO_INACTIVE) {
						$core->nwl->opt_out(BID, $set, false);
					}
				}
				$core->sub->list_all();
				break;
			
			case 'sub-import':
				$core->sub->import();
				break;
			
			case 'sub-export':
				$core->sub->export();
				break;
			
			case 'sub-remove':
				$core->sub->remove();
				break;
			
			case 'sub-delete':
				$core->sub->delete();
				break;
			
			case 'stats-all':
				$core->stats->display();
				break;
			
			case 'stats-cur':
				$core->stats->display(BID);
				break;
			
			case 'config':
				$core->config();
				break;
			
			case 'password':
				$core->user->set_password();
				break;
			
			case 'debug':
				$core->user->renew_session();
				break;
			
			case 'logout':
				$core->user->logout();
				break;
			
			default:
				msg(LNG_SYS_UNKNOWN_VIEW.' ['.$view.']', 'error');
		}
		
		echo "</div>\n"; // .content
		
		$core->ui->footer();
	}
	else
		$core->user->login_form();
	
	$core->ui->html_foot();
?>