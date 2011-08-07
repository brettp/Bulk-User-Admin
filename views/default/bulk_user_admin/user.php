<?php
/**
 * Show a user for bulk actions. Includes a checkbox on the left.
 */

$icon = elgg_view(
		"profile/icon", array(
								'entity' => $vars['entity'],
								'size' => 'small',
							)
	);

$banned = $vars['entity']->isBanned();
$user = $vars['entity'];

$checkbox = "<input type=\"checkbox\" name=\"bulk_user_admin_guids[]\" value=\"$user->guid\">";
$first_login = elgg_view_friendly_time($user->time_created);
$last_login = elgg_view_friendly_time($user->last_login);
$last_action = elgg_view_friendly_time($user->last_action);
$objects = elgg_get_entities(array(
	'owner_guid' => $user->guid,
	'count' => true
));

$db_prefix = get_config('dbprefix');

$q = "SELECT COUNT(id) as count FROM {$db_prefix}annotations WHERE owner_guid = $user->guid";
$data = get_data($q);
$annotations = (int) $data[0]->count;

$q = "SELECT COUNT(id) as count FROM {$db_prefix}metadata WHERE owner_guid = $user->guid";
$data = get_data($q);
$metadata = (int) $data[0]->count;

// the CSS for classless <label> is really, really annoying.
$info = <<<___HTML
<label style="font-size: inherit; font-weight: inherit; color: inherit;">
<p>$checkbox $user->name | $user->username | $user->email | $user->guid </p>
<p>Last login: $last_login | First login: $first_login | Last action: $last_action</p>
<p>Objects: $objects | Annotations: $annotations | Metadata: $metadata</p>
___HTML;

if ($banned) {
	$info .= '<div id="profile_banned">';
	$info .= elgg_echo('profile:banned');
	$info .= '<br />';
	$info .= $user->ban_reason;
	$info .= '</div>';
}

$info .= '</label>';

echo elgg_view_listing($icon, $info);