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
$title = '';

if ($domain) {
	$title = "Users in the domain $domain";
}

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
	'base_url' => current_page_url(),
	'offset' => $offset,
	'count' => $users_count
));

$form_vars = array(
	'users' => $users,
);

$form = elgg_view_form('bulk_user_admin/delete', array(), $form_vars);

$domain_form = '';

if ($domain) {
	$delete_button = "<br /><br />" . elgg_view('input/submit', array(
		'value' => 'Delete all in domain',
	));

	$hidden = elgg_view('input/hidden', array(
		'name' => 'domain',
		'value' => $domain
	));

	$form_body = $delete_button . $hidden;

	$domain_form = elgg_view('input/form', array(
		'action' =>  $site->url . 'action/bulk_user_admin/delete_by_domain',
		'body' => $form_body
	));

}

$summary = "<div>$users_count user(s) found</div>";

if ($domain) {
	$summary .= '<br />';
	$summary .= elgg_view('output/url', array(
		'href' => elgg_http_remove_url_query_element(current_page_url(), 'domain'),
		'text' => 'All users'
	));
}

elgg_set_context('admin');

echo $title . $summary . $pagination . $form . $domain_form . $pagination;