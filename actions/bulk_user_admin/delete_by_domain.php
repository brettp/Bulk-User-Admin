<?php
/**
 * Bulk delete users by email
 */

$domain = get_input('domain');
$banned = get_input('banned');
$include_enqueued = get_input('include_enqueued');

$s = BulkUserAdmin\DeleteService::getService();

$errors = array();
$count = 0;
$batch = new ElggBatch('bulk_user_admin_get_users', array(
	'domain' => $domain,
	'limit' => false,
	'enqueued' => 'exclude'
));
$batch->setIncrementOffset(false);

foreach ($batch as $user) {
	if (!$user instanceof ElggUser) {
		$errors[] = elgg_echo('bulk_user_admin:error:wrongguid', array($user->guid));
		continue;
	}

	if (!$s->enqueue($user)) {
		$errors[] = elgg_echo('bulk_user_admin:error:enqueue_failed',
				array($user->name, $user->username, $user->guid));
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

forward('/admin/users/bulk_user_admin');
