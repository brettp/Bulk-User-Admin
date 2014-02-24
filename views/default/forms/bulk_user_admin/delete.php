<?php
/**
 * Form to delete users
 */

$db_prefix = elgg_get_config('dbprefix');
$users = elgg_extract('users', $vars);

// profile fields
$fields = elgg_get_config('profile_fields');
?>

<table class="elgg-table bulk-user-admin-users">
	<tr>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
		<th>Name (Username, GUID)<br />Profile Info</th>
		<th>Email</th>
		<th>Time created<br />Last login</th>
		<th>Last action</th>
		<th>Content counts</th>
	</tr>
	
<?php
	foreach ($users as $user) {
		$checkbox = elgg_view('input/checkbox', array(
			'name' => 'bulk_user_admin_guids[]',
			'value' => $user->guid,
			'default' => false,
			'id' => 'elgg-user-' . $user->guid
		));
		$icon = elgg_view_entity_icon($user, 'tiny');
		$banned = $user->isBanned();

		foreach (array('time_created', 'last_login', 'last_action') as $ts_name) {
			$ts = $user->$ts_name;
			if ($ts) {
				${$ts_name} = elgg_view_friendly_time($ts);
			} else {
				${$ts_name} = 'N/A';
			}
		}

		$object_count = elgg_get_entities(array(
			'owner_guid' => $user->guid,
			'count' => true
		));

		$q = "SELECT COUNT(id) as count FROM {$db_prefix}annotations WHERE owner_guid = $user->guid";
		$data = get_data($q);
		$annotation_count = (int) $data[0]->count;

		$q = "SELECT COUNT(id) as count FROM {$db_prefix}metadata WHERE owner_guid = $user->guid";
		$data = get_data($q);
		$metadata_count = (int) $data[0]->count;
		
		$tr_class = $user->isBanned() ? 'class="bulk-user-admin-banned"' : '';
		if ($user->isBanned()) {
			$banned = '<br />Banned: ' . $user->ban_reason;
		}

		$profile_field_tmp = array();

		foreach (array_keys($fields) as $md_name) {
			$value = $user->$md_name;
			
			if ($value) {
				$value_short = elgg_get_excerpt($value, 100);

				$profile_field_tmp[] = elgg_echo('profile:' . $md_name) . ': '
						. '<acronym title="' . strip_tags(htmlentities($value)) . '">'
						. $value_short . '</acronym>';
			}
		}

		$profile_fields = implode("<br />", $profile_field_tmp);
		if ($profile_fields) {
			$profile_fields = "<br />$profile_fields";
		}

		echo <<<___HTML
	<tr $tr_class>
		<td><label for="elgg-user-$user->guid">$checkbox</label></td>
		<td>$icon</td>
		<td><label for="elgg-user-$user->guid">$user->name ($user->username, $user->guid) $banned $profile_fields</label></td>
		<td><label for="elgg-user-$user->guid">$user->email</label></td>
		<td><label for="elgg-user-$user->guid">$time_created<br />$last_login</label></td>
		<td><label for="elgg-user-$user->guid">$last_action</label></td>
		<td><label for="elgg-user-$user->guid">Obj: $object_count<br />Ann: $annotation_count<br />MD: $metadata_count</label></td>
	</tr>
___HTML;
	}
?>
	
</table>

<?php

echo elgg_view('input/submit', array(
	'value' => 'Delete checked users',
	'class' => 'mtl pas elgg-button-submit elgg-requires-confirmation'
));