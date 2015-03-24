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

$options = array(
	'type' => 'user',
	'limit' => $limit,
	'offset' => $offset,
	'full_view' => false,
);

$filter_class = 'hidden';

if ($banned) {
	$db_prefix = elgg_get_config("dbprefix");
	$options['joins'] = array("JOIN {$db_prefix}users_entity ue ON ue.guid = e.guid");
	$options['wheres'] = array("ue.banned = 'yes'");
	$filter_class = '';
}

if (!$include_enqueued) {
	$options['wheres'][] = bulk_user_admin_get_sql_where_not_enqueued();
} else {
	$filter_class = '';
}

if ($domain) {
	$options['domain'] = $domain;
	$users = bulk_user_admin_get_users_by_email_domain($options);
	$options['count'] = true;
	$users_count = bulk_user_admin_get_users_by_email_domain($options);
	$filter_class = '';
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

$options = array(
	'name' => 'banned',
	'value' => 1
);
if ($banned) {
	$options['checked'] = 'checked';
}
$filter_body = '<p><label>' . elgg_view('input/checkbox', $options)
		. elgg_echo('bulk_user_admin:banned_only') . '</label></p>';

$options = array(
	'name' => 'include_enqueued',
	'value' => 1
);
if ($include_enqueued) {
	$options['checked'] = 'checked';
}
$filter_body .= '<p><label>' . elgg_view('input/checkbox', $options)
		. elgg_echo('bulk_user_admin:include_enqueued') . '</label></p>';

$options = array(
	'name' => 'domain',
	'value' => $domain,
	'class' => 'elgg-input-thin'
);

$input = elgg_view('input/text', $options);
$label = elgg_echo('bulk_user_admin:domain');
$help = elgg_echo('bulk_user_admin:domain:help');

$filter_body .=<<<HTML
<p>
	<label>
	$label
	$input
	</label>
	<span class='elgg-text-help'>$help</span>
</p>
HTML;

$filter_body .= elgg_view('input/submit', array(
	'value' => elgg_echo('update'),
	'class' => 'elgg-button elgg-button-action mhm'
));

$filter_body .= elgg_view('output/url', array(
	'text' => elgg_echo('bulk_user_admin:clear'),
	'class' => 'bulk-user-admin-clear elgg-button elgg-button-action mhm',
	'href' => '/admin/users/bulk_user_admin'
));

$filter_form = elgg_view('input/form', array(
	'body' => $filter_body,
	'action' => 'admin/users/bulk_user_admin',
	'method' => 'get',
	'disable_security' => true
));

$filter_toggle = '';
if ($filter_class == 'hidden') {
	$filter_toggle = elgg_view('output/url', [
		'text' => elgg_echo('bulk_user_admin:add_filters'),
		'href' => '#bulk-user-admin-filter',
		'rel' => 'toggle',
		'is_trusted' => true,
		'class' => 'elgg-button elgg-button-action'
	]);
}

elgg_set_context('admin');

$legend = elgg_echo('bulk_user_admin:filters');

echo <<<HTML
$filter_toggle
<fieldset id="bulk-user-admin-filter" class="elgg-fieldset mtm $filter_class">
	<legend>$legend</legend>
	$filter_form
</fieldset>

$pagination
$form
$domain_form
$pagination
HTML;


