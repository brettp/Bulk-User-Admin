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
	$stop_time = time() + 30;
	$s = BulkUserAdmin\DeleteService::getService();
	$s->process($stop_time);
}

function bulk_user_admin_get_sql_where_not_enqueued() {
	$db_prefix = get_config('dbprefix');
	$name_id = elgg_get_metastring_id(\BulkUserAdmin\DeleteService::PENDING_DELETE_MD);
	$value_id = elgg_get_metastring_id(true);

	return "NOT EXISTS (
			SELECT 1 FROM {$db_prefix}metadata md
			WHERE md.entity_guid = e.guid
				AND md.name_id = $name_id
				AND md.value_id = $value_id)";
}

/**
 * Get users with a few more options
 *
 * domain => Match the last part of the email address
 * only_banned => Only return banned users
 * enqueued => include|exclude|only users enqueued to be deleted (default = exclude, other values = doesn't matter)
 *
 * @param array $sent Options
 */
function bulk_user_admin_get_users(array $sent) {
	$db_prefix = elgg_get_config('dbprefix');
	$defaults = [
		'type' => 'user',
		'domain' => false,
		'only_banned' => false,
		'enqueued' => 'exclude'
	];

	$options = array_merge($defaults, $sent);

	if (!is_array($options['wheres'])) {
		$options['wheres'] = [];
	}

	if (!is_array($options['joins'])) {
		$options['joins'] = [];
	}

	// sometimes ue is joined, sometimes it's not...
	// use our own join to make sure.
	$options['joins'][]= "JOIN {$db_prefix}users_entity bua_ue on e.guid = bua_ue.guid";

	if ($options['domain']) {
		$options['wheres'][] = "bua_ue.email LIKE '%@%{$options['domain']}'";
	}

	if ($options['only_banned']) {
		$options['wheres'][] = "bua_ue.banned = 'yes'";
	}

	switch ($options['enqueued']) {
		case 'include':
			// no-op
			break;

		case 'only':
			$options['metadata_name'] = \BulkUserAdmin\DeleteService::PENDING_DELETE_MD;
			break;

		case 'exclude':
		default:
			$options['wheres'][] = bulk_user_admin_get_sql_where_not_enqueued();
			break;
	}

	return elgg_get_entities_from_metadata($options);
}