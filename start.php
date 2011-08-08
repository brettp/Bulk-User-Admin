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
	register_page_handler('admin', 'bulk_user_admin_page_handler');

	register_elgg_event_handler('pagesetup', 'system', 'bulk_user_admin_admin_page_setup');

	elgg_extend_view('admin/user_opt/search', 'bulk_user_admin/search_by_domain');
	elgg_extend_view('css', 'bulk_user_admin/css');

	register_action('bulk_user_admin/delete', false, dirname(__FILE__) . '/actions/bulk_user_admin/delete.php', true);
	register_action('bulk_user_admin/delete_by_domain', false, dirname(__FILE__) . '/actions/bulk_user_admin/delete_by_domain.php', true);


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
			include dirname(__FILE__) . '/pages/admin/email_domain_stats.php';
		} else {
			include dirname(__FILE__) . '/pages/admin/user.php';
		}
	} else {
		admin_settings_page_handler($page);
	}
}

function bulk_user_admin_get_users_by_email_domain($domain, $options = array()) {
	$domain = sanitise_string($domain);
	$db_prefix = get_config('dbprefix');
	
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
 * Sets up tidypics admin menu. Triggered on pagesetup.
 */
function bulk_user_admin_admin_page_setup() {
	global $CONFIG;

	if (get_context() == 'admin' && isadminloggedin()) {
		add_submenu_item('Find/delete users by email', $CONFIG->url . "pg/admin/user/delete_by_email");
		add_submenu_item('Email domain stats', $CONFIG->url . "pg/admin/user/email_domain_stats");
	}
}

function bulk_user_admin_get_email_domain_stats() {
	$db_prefix = get_config('dbprefix');
	$q = "SELECT email, substring_index(email, '@', -1) as domain, count(*) as count
		FROM {$db_prefix}users_entity ue
		JOIN {$db_prefix}entities e ON ue.guid = e.guid
		WHERE e.enabled = 'yes'
		group by domain order by count desc, domain asc;";
		
	return get_data($q);
}

register_elgg_event_handler('init', 'system', 'bulk_user_admin_init');