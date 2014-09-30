<?php
/**
 * Bulk delete users
 */

$guids = get_input('bulk_user_admin_guids');

if (!$guids) {
	register_error(elgg_echo('bulk_user_admin:error:nousers'));
	forward(REFERER);
}

$errors = array();
$count = 0;
$batch = new ElggBatch('elgg_get_entities', array('guids' => $guids, 'limit' => false));
$batch->setIncrementOffset(false);

foreach ($batch as $user) {
	if (!$user instanceof ElggUser) {
		$errors[] = elgg_echo('bulk_user_admin:error:wrongguid', array($user->guid));
		continue;
	}

	if ($user->delete()) {
		$count++;
	} else {
		$errors[] = elgg_echo('bulk_user_admin:error:deletefailed', array($user->name, $user->username, $user->guid));
	}
}

if ($errors) {
	foreach ($errors as $error) {
		register_error($error);
	}
} else {
	system_message(elgg_echo('bulk_user_admin:success:delete', array($count)));
}

forward(REFERER);
