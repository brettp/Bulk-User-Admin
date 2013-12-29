<?php
/**
 * Bulk delete users by email
 */

$domain = get_input('domain');

$errors = array();
$count = 0;

$options = array(
	'limit' => 50,
	'offset' => 0,
);

$users = bulk_user_admin_get_users_by_email_domain($domain, $options);

while ($users) {
	foreach ($users as $user) {
		if ($user->delete()) {
			$count++;
		} else {
			$errors[] = "Could not delete $user->name ($user->username, $user->guid).";
		}
	}

	$options['offset'] = $options['offset'] + $options['limit'];
	$users = bulk_user_admin_get_users_by_email_domain($domain, $options);
}

if ($errors) {
	foreach ($errors as $error) {
		register_error($error);
	}
} else {
	system_message("Users deleted: $count");
}

forward(REFERER);
