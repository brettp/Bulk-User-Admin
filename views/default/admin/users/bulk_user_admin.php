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
$include_enqueued = get_input('include_enqueued');

$db_prefix = elgg_get_config("dbprefix");
$options = array(
	'type' => 'user',
	'limit' => $limit,
	'offset' => $offset,
	'full_view' => false,
	'only_banned' => $banned,
	'domain' => $domain,
	'enqueued' => $include_enqueued ? 'include' : 'exclude'
);

$users = bulk_user_admin_get_users($options);

$options['count'] = true;
$count = bulk_user_admin_get_users($options);

$pagination = elgg_view('navigation/pagination', array(
	'base_url' => current_page_url(),
	'offset' => $offset,
	'count' => $count,
	'limit' => $limit
));

$form_vars = [
	'users' => $users,
	'banned' => $banned,
	'domain' => $domain,
	'include_enqueued' => $include_enqueued,
	'options' => $options
];

$form_filter = elgg_view('bulk_user_admin/form_filter', array_merge($vars, $form_vars));
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

elgg_set_context('admin');

echo <<<HTML
$form_filter

$pagination
$form
$domain_form
$pagination
HTML;
