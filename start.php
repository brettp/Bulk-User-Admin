<?php
/**
 * Allow bulk delete operations
 */

/**
 * Init
 */
function bulk_user_admin_init() {
	// need to intercept the page handler to use our own page scripts
	// because the original scripts change context to search instead of admin.
	elgg_register_page_handler('admin', 'bulk_user_admin_page_handler');

	elgg_register_event_handler('pagesetup', 'system', 'bulk_user_admin_admin_page_setup');

	elgg_extend_view('admin/user_opt/search', 'bulk_user_admin/search_by_domain');
	elgg_extend_view('css/elgg', 'bulk_user_admin/css');

	elgg_register_action('bulk_user_admin/delete', dirname(__FILE__) . '/actions/bulk_user_admin/delete.php', 'admin');
	elgg_register_action('bulk_user_admin/delete_by_domain', dirname(__FILE__) . '/actions/bulk_user_admin/delete_by_domain.php', 'admin');

}

/**
 * Serve our special users page or fall back to the core admin page handler
 *
 * @param array $page
 */
function bulk_user_admin_page_handler($page) {
	admin_gatekeeper();
	if ($page[0] == 'user') {
		if (isset($page[1]) && $page[1] == 'email_domain_stats') {
			if(include(dirname(__FILE__) . '/pages/admin/email_domain_stats.php')){
			  return TRUE;
			}
		} else {
			if(include(dirname(__FILE__) . '/pages/admin/user.php')){
			  return TRUE;
			}
		}
	} else {
		admin_settings_page_handler($page);
	}
	
	return FALSE;
}

function bulk_user_admin_get_users_by_email_domain($domain, $options = array()) {
	$domain = sanitise_string($domain);
	$db_prefix = elgg_get_config('dbprefix');
	
	$where = "ue.email LIKE '%@$domain'";
	if (!isset($options['wheres'])) {
		$options['wheres'] = array($where);
	} else {
		if (!is_array($options['wheres'])) {
			$options['wheres'] = array($options['wheres']);
		}
		$options['wheres'][] = $where;
	}

	$join = "JOIN {$db_prefix}users_entity ue on e.guid = ue.guid";
	if (!isset($options['joins'])) {
		$options['joins'] = array($join);
	} else {
		if (!is_array($options['joins'])) {
			$options['joins'] = array($options['joins']);
		}
		$options['joins'][] = $join;
	}

	$options['type'] = 'user';

	return elgg_get_entities($options);
}

/**
 * Sets up admin menu. Triggered on pagesetup.
 */
function bulk_user_admin_admin_page_setup() {
	if (elgg_get_context() == 'admin' && elgg_is_admin_logged_in()) {
	    $item = new ElggMenuItem('bulk_user_admin_domain_stats', 'Email domain stats', elgg_get_site_url() . "admin/user/email_domain_stats");
	    $item->setParent('users');
	    elgg_register_menu_item('page', $item);
	}
}

function bulk_user_admin_get_email_domain_stats() {
	$db_prefix = elgg_get_config('dbprefix');
	$q = "SELECT email, substring_index(email, '@', -1) as domain, count(*) as count
		FROM {$db_prefix}users_entity ue
		JOIN {$db_prefix}entities e ON ue.guid = e.guid
		WHERE e.enabled = 'yes'
		group by domain order by count desc, domain asc;";
		
	return get_data($q);
}

elgg_register_event_handler('init', 'system', 'bulk_user_admin_init');