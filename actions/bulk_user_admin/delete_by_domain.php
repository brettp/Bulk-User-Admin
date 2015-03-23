<?php
/**
 * Bulk delete users by email
 */

$domain = get_input('domain');
$s = BulkUserAdmin\DeleteService::getService();

$errors = array();
$count = 0;
$batch = new ElggBatch('bulk_user_admin_get_users_by_email_domain', array(
	'domain' => $domain,
	'limit' => false
));

foreach ($batch as $user) {
	if (!$user instanceof ElggUser) {
		$errors[] = elgg_echo('bulk_user_admin:error:wrongguid', array($user->guid));
		continue;
	}

	if (!$s->enqueue($user)) {
		$errors[] = elgg_echo('bulk_user_admin:error:enqueue_failed', array($user->name, $user->username, $user->guid));
	}
	$count++;
}

if ($errors) {
	foreach ($errors as $error) {
		register_error($error);
	}
} else {
	system_message(elgg_echo('bulk_user_admin:enqueue:delete', array($count)));
}

forward(REFERER);
