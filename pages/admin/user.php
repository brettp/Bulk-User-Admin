<?php
/**
 * Display a list of users to delete in bulk.
 *
 * Also used to show the search by domain results
 */

// Are we performing a search
$limit = get_input('limit', 10);
$offset = get_input('offset', 0);
$domain = get_input('domain');

$context = elgg_get_context();

if (!$domain) {
	$title = elgg_echo('admin:user');
} else {
	$title = "Users in domain $domain";
}

// has to be here or the sidemenu is buggered because of pagesetup hook bs
$title_str = elgg_view_title($title);

elgg_set_context('search');

$options = array(
	'type' => 'user',
	'limit' => $limit,
	'offset' => $offset,
	'full_view' => false
);

if ($domain) {
	$users = bulk_user_admin_get_users_by_email_domain($domain, $options);
	$options['count'] = true;
	$users_count = bulk_user_admin_get_users_by_email_domain($domain, $options);
} else {
	$users = elgg_get_entities($options);
	$options['count'] = true;
	$users_count = elgg_get_entities($options);
}

$pagination = elgg_view('navigation/pagination', array(
	'baseurl' => current_page_url(),
	'offset' => $offset,
	'count' => $users_count
));

$form_body = '';
foreach ($users as $user) {
	$form_body .= elgg_view('bulk_user_admin/user', array('entity' => $user));
}

$delete_button = elgg_view('input/submit', array(
	'value' => 'Delete checked'
));

$form_body .= elgg_view('page_elements/contentwrapper', array(
	'body' => $delete_button
));

$site = elgg_get_config('site');
$checked_form = elgg_view('input/form', array(
	'action' =>  $site->url . 'action/bulk_user_admin/delete',
	'body' => $form_body
));

$domain_form = '';

if ($domain) {
	$delete_button = elgg_view('input/submit', array(
		'value' => 'Delete all in domain'
	));

	$hidden = elgg_view('input/hidden', array(
		'name' => 'domain',
		'value' => $domain
	));

	$form_body = elgg_view('page_elements/contentwrapper', array(
		'body' => $delete_button . $hidden
	));

	$domain_form = elgg_view('input/form', array(
		'action' =>  $site->url . 'action/bulk_user_admin/delete_by_domain',
		'body' => $form_body
	));
}

$summary = "$users_count users found";

if ($domain) {
	$summary .= " in domain $domain";
	$summary .= '<br />';
	$summary .= elgg_view('output/url', array(
		'href' => elgg_http_remove_url_query_element(current_page_url(), 'domain'),
		'text' => 'All users'
	));
}

$summary = elgg_view('page_elements/contentwrapper', array(
	'body' => $summary
));

elgg_set_context('admin');

$content = $title_str . elgg_view('admin/user') . $summary . $pagination . $checked_form . $domain_form . $pagination;
$body = elgg_view_layout('one_sidebar', array('content' => $content));

echo elgg_view_page($title, $body);