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
		<th><?php echo elgg_echo('bulk_user_admin:usericon');?></th>
		<th><?php echo elgg_echo('bulk_user_admin:userinfo');?><br /><?php echo elgg_echo('bulk_user_admin:profileinfo');?></th>
		<th><?php echo elgg_echo('bulk_user_admin:email');?></th>
		<th><?php echo elgg_echo('bulk_user_admin:timecreated');?><br /><?php echo elgg_echo('bulk_user_admin:lastlogin');?></th>
		<th><?php echo elgg_echo('bulk_user_admin:lastaction');?></th>
		<th><?php echo elgg_echo('bulk_user_admin:contentcounts');?></th>
	</tr>

<?php
	foreach ($users as $user) {
		$checkbox = elgg_view('input/checkbox', array(
			'name' => 'bulk_user_admin_guids[]',
			'value' => $user->guid,
			'default' => false,
			'id' => 'elgg-user-' . $user->guid
		));

		$spacer_url = elgg_get_site_url() . '_graphics/spacer.gif';
		$name = htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8', false);
		$username = $user->username;
		$icon_url = elgg_format_url($user->getIconURL('tiny'));
		$icon = elgg_view('output/img', array(
			'src' => $spacer_url,
			'alt' => $name,
			'title' => $name,
			'class' => '',
			'style' => "background: url($icon_url) no-repeat;",
		));
		$user_icon = "<div class='elgg-avatar elgg-avatar-tiny'>";
		$user_icon .= elgg_view('output/url', array(
			'href' => $user->getURL(),
			'text' => $icon,
			'is_trusted' => true,
			'class' => "elgg-avatar elgg-avatar-tiny bulk-user-admin-icon",
		));
		$user_icon .= elgg_view_icon('hover-menu');
		$user_icon .= elgg_view_menu('user_hover', array('entity' => $user, 'username' => $username, 'name' => $name));
		$user_icon .= "</div>";

		foreach (array('time_created', 'last_login', 'last_action') as $ts_name) {
			$ts = $user->$ts_name;
			if ($ts) {
				${$ts_name} = elgg_view_friendly_time($ts);
			} else {
				${$ts_name} = elgg_echo('bulk_user_admin:notavailable');
			}
		}

		$object_count = elgg_get_entities(array(
			'owner_guid' => $user->guid,
			'count' => true
		));
		$object_count = elgg_echo('bulk_user_admin:objectcounts') .  $object_count;

		$q = "SELECT COUNT(id) as count FROM {$db_prefix}annotations WHERE owner_guid = $user->guid";
		$data = get_data($q);
		$annotation_count = (int) $data[0]->count;
		$annotation_count = elgg_echo('bulk_user_admin:annotationscounts') . $annotation_count;

		$q = "SELECT COUNT(id) as count FROM {$db_prefix}metadata WHERE owner_guid = $user->guid";
		$data = get_data($q);
		$metadata_count = (int) $data[0]->count;
		$metadata_count = elgg_echo('bulk_user_admin:metadatacounts') . $metadata_count;
		
		$tr_class = '';

		$banned = '';
		if ($user->isBanned()) {
			$tr_class .= 'bulk-user-admin-banned';
			$banned = '<br />' . elgg_echo('bulk_user_admin:banned') . $user->ban_reason;
		}

		$enqueued = '';
		if ($user->bulk_user_admin_delete_queued) {
			$tr_class .= ' bulk-user-admin-enqueued';
			$enqueued = '<br />' . elgg_echo('bulk_user_admin:enqueued');
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
	<tr class="$tr_class">
		<td><label for="elgg-user-$user->guid">$checkbox</label></td>
		<td><label for="elgg-user-$user->guid">$user_icon</label></td>
		<td><label for="elgg-user-$user->guid">$user->name ($user->username, $user->guid) $enqueued $banned $profile_fields</label></td>
		<td><label for="elgg-user-$user->guid">$user->email</label></td>
		<td><label for="elgg-user-$user->guid">$time_created<br />$last_login</label></td>
		<td><label for="elgg-user-$user->guid">$last_action</label></td>
		<td><label for="elgg-user-$user->guid">$object_count<br />$annotation_count<br />$metadata_count</label></td>
	</tr>
___HTML;
	}
?>
	
</table>

<?php

echo elgg_view('input/submit', array(
	'value' => elgg_echo('bulk_user_admin:delete:checked'),
	'class' => 'mtm elgg-button elgg-button-submit',
	'data-confirm' => elgg_echo('bulk_user_admin:delete:checked?')
));
