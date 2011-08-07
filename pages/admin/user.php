<?php
/**
 * Display a list of users to delete in bulk.
 */

// Are we performing a search
$search = get_input('s');
$limit = get_input('limit', 10);
$offset = get_input('offset', 0);

$context = get_context();

$title = elgg_view_title(elgg_echo('admin:user'));

set_context('search');

$users = elgg_get_entities(array(
	'type' => 'user',
	'limit' => $limit,
	'offset' => $offset,
	'full_view' => false
));

$form_body = '';
foreach ($users as $user) {
	$form_body .= elgg_view('bulk_user_admin/user', array('entity' => $user));
}

$delete_button .= elgg_view('input/submit', array(
	'value' => 'Delete'
));

$form_body .= elgg_view('page_elements/contentwrapper', array(
	'body' => $delete_button
));

$site = get_config('site');
$form = elgg_view('input/form', array(
	'action' =>  $site->url . 'action/bulk_user_admin/delete',
	'body' => $form_body
));

set_context('admin');

$content = $title . elgg_view('admin/user') . $form;
$body = elgg_view_layout('two_column_left_sidebar', '', $content);
page_draw(elgg_echo("admin:user"), $body);
