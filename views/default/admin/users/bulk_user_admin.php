<?php
/**
 * Display a list of users to delete in bulk.
 *
 * Also used to show the search by domain results
 */

// Are we performing a search
$limit = get_input('limit', 30);
$offset = get_input('offset', 0);
$domain = get_input('domain');
$banned = get_input('banned');
$include_enqueued = get_input('include_enqueued', false);

$title = 'Users';

if ($domain) {
	$title = elgg_echo('bulk_user_admin:title:domains', array($domain));
}

if ($banned) {
	$title = "Banned " + $title;
}

$options = array(
	'type' => 'user',
	'limit' => $limit,
	'offset' => $offset,
	'full_view' => false,
);

if ($banned) {
	$db_prefix = elgg_get_config("dbprefix");
	$options['joins'] = array("JOIN {$db_prefix}users_entity ue ON ue.guid = e.guid");
	$options['wheres'] = array("ue.banned = 'yes'");
}

if (!$include_enqueued) {
	$db_prefix = get_config('dbprefix');
	$name_id = elgg_get_metastring_id('bulk_user_admin_delete_queued');
	$value_id = elgg_get_metastring_id(true);
	$options['wheres'][] = "NOT EXISTS (
			SELECT 1 FROM {$db_prefix}metadata md
			WHERE md.entity_guid = e.guid
				AND md.name_id = $name_id
				AND md.value_id = $value_id)";
}

if ($domain) {
	$options['domain'] = $domain;
	$users = bulk_user_admin_get_users_by_email_domain($options);
	$options['count'] = true;
	$users_count = bulk_user_admin_get_users_by_email_domain($options);
} else {
	$users = elgg_get_entities($options);
	$options['count'] = true;
	$users_count = elgg_get_entities($options);
}

$pagination = elgg_view('navigation/pagination', array(
	'base_url' => current_page_url(),
	'offset' => $offset,
	'count' => $users_count,
	'limit' => $limit
));

$form_vars = array(
	'users' => $users,
);

$form = elgg_view_form('bulk_user_admin/delete', array('class' => 'pvl'), $form_vars);

$domain_form = '';

if ($domain) {
	$delete_button = elgg_view('input/submit', array(
		'value' => elgg_echo('bulk_user_admin:delete:domainall', [$domain]),
		'class' => 'mtm elgg-button elgg-button-submit',
		'data-confirm' => elgg_echo('bulk_user_admin:delete:domainall?', [$domain])
	));

	$hidden = elgg_view('input/hidden', array(
		'name' => 'domain',
		'value' => $domain
	));

	$form_body = $delete_button . $hidden;

	$domain_form = elgg_view('input/form', array(
		'action' =>  elgg_get_site_url() . 'action/bulk_user_admin/delete_by_domain',
		'body' => $form_body
	));
}

$summary = "<div>" . elgg_echo('bulk_user_admin:usersfound', array($users_count)) . "</div>";
$options = array(
	'name' => 'banned',
	'value' => 1
);
if ($banned) {
	$options['checked'] = 'checked';
}
$banned_form_body = '<p><label>' . elgg_view('input/checkbox', $options)
		. elgg_echo('bulk_user_admin:banned_only') . '</label></p>';

$options = array(
	'name' => 'include_enqueued',
	'value' => 1
);
if ($include_enqueued) {
	$options['checked'] = 'checked';
}
$banned_form_body .= '<p><label>' . elgg_view('input/checkbox', $options)
		. elgg_echo('bulk_user_admin:include_enqueued') . '</label></p>';
$banned_form_body .= elgg_view('input/submit', array(
	'value' => elgg_echo('update'),
	'class' => 'elgg-button elgg-button-action mhm'
));

$banned_form = elgg_view('input/form', array(
	'body' => $banned_form_body,
	'action' => 'admin/users/bulk_user_admin',
	'method' => 'get',
	'disable_security' => true
));


if ($domain) {
	$summary .= '<br />';
	$summary .= elgg_view('output/url', array(
		'href' => elgg_http_remove_url_query_element(current_page_url(), 'domain'),
		'text' => elgg_echo('bulk_user_admin:allusers')
	));
}

elgg_set_context('admin');

echo <<<HTML
$title
$summary
$banned_form

$pagination
$form
$domain_form
$pagination
HTML;


