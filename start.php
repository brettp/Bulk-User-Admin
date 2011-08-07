<?php
/**
 *
 */

function bulk_user_admin_init() {
	// need to intercept the page handler to use our own page scripts
	// because the original scripts change context to search instead of admin.
	register_page_handler('admin', 'bulk_user_admin_page_handler');

	register_action('bulk_user_admin/delete', false, dirname(__FILE__) . '/actions/bulk_user_admin/delete.php', true);
}

/**
 * Serve our special users page or fall back to the core admin page handler
 *
 * @param array $page
 */
function bulk_user_admin_page_handler($page) {
	admin_gatekeeper();
	if ($page[0] == 'user') {
		include dirname(__FILE__) . '/pages/admin/user.php';
	} else {
		admin_settings_page_handler($page);
	}
}

register_elgg_event_handler('init', 'system', 'bulk_user_admin_init');