<?php
/**
 * Bulk delete users
 */

$guids = get_input('bulk_user_admin_guids');

if (!$guids) {
	register_error('Nothing to delete.');
	forward(REFERER);
}

$errors = array();
$count = 0;
$batch = new ElggBatch('elgg_get_entities', array('guids' => $guids, 'limit' => 0));
$batch->setIncrementOffset(false);

foreach ($batch as $user) {
	if (!$user instanceof ElggUser) {
		$errors[] = "$user->guid is not a user.";
		continue;
	}

	if ($user->delete()) {
		$count++;
	} else {
		$errors[] = "Could not delete $user->name ($user->username, $user->guid).";
	}
}

if ($errors) {
	foreach ($errors as $error) {
		register_error($error);
	}
} else {
	system_message("Users deleted: $count");
}

forward(REFERER);
