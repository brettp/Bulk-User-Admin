<?php
/**
 * Allow bulk delete operations
 */

elgg_register_event_handler('init', 'system', 'bulk_user_admin_init');

/**
 * Init
 */
function bulk_user_admin_init() {
	elgg_extend_view('css/admin', 'bulk_user_admin/css');

	elgg_register_admin_menu_item('administer', 'email_domain_stats', 'users');
	elgg_register_admin_menu_item('administer', 'bulk_user_admin', 'users');

	$base_dir = elgg_get_plugins_path() . 'bulk_user_admin/actions/bulk_user_admin';
	elgg_register_action('bulk_user_admin/delete', $base_dir . '/delete.php', 'admin');
	elgg_register_action('bulk_user_admin/delete_by_domain', $base_dir . '/delete_by_domain.php', 'admin');

	elgg_register_plugin_hook_handler('cron', 'minute', 'bulk_user_admin_cron');
}

/**
 * Return users by email domain
 *
 * @param type $options
 * @return array
 */
function bulk_user_admin_get_users_by_email_domain($options = array()) {
	$domain = sanitise_string($options['domain']);
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
 * Get number of users per email domain
 *
 * @return array
 */
function bulk_user_admin_get_email_domain_stats() {
	$db_prefix = elgg_get_config('dbprefix');
	$q = "SELECT email, substring_index(email, '@', -1) as domain, count(*) as count
		FROM {$db_prefix}users_entity ue
		JOIN {$db_prefix}entities e ON ue.guid = e.guid
		WHERE e.enabled = 'yes'
		group by domain order by count desc, domain asc;";

	return get_data($q);
}

/**
 * @access private
 */
function bulk_user_admin_cron() {
	$stop_time = time() + 45;
	$s = BulkUserAdmin\DeleteService::getService();
	$s->process($stop_time);
}

function bulk_user_admin_get_sql_where() {
	$db_prefix = get_config('dbprefix');
	$name_id = elgg_get_metastring_id('bulk_user_admin_delete_queued');
	$value_id = elgg_get_metastring_id(true);

	return "NOT EXISTS (
			SELECT 1 FROM {$db_prefix}metadata md
			WHERE md.entity_guid = e.guid
				AND md.name_id = $name_id
				AND md.value_id = $value_id)";
}