<?php
/**
 * Filter the users
 */

$db_prefix = elgg_get_config('dbprefix');
$users = elgg_extract('users', $vars);
$domain = elgg_extract('domain', $vars);
$banned = elgg_extract('banned', $vars);
$include_enqueued = elgg_extract('include_enqueued', $vars);
$options = elgg_extract('options', $vars);
$options['count'] = true;

$banned_input_options = array(
	'name' => 'banned',
	'value' => 1
);
if ($banned) {
	$banned_input_options['checked'] = 'checked';
}

$banned_count = bulk_user_admin_get_users(array_merge($options, ['only_banned' => true]));
$filter_body = '<p><label>' . elgg_view('input/checkbox', $banned_input_options)
		. elgg_echo('bulk_user_admin:banned_only', [$banned_count]) . '</label></p>';

$enqueued_input_options = array(
	'name' => 'include_enqueued',
	'value' => 1
);
if ($include_enqueued) {
	$enqueued_input_options['checked'] = 'checked';
	$enqueued_count = '0';
} else {
	$enqueued_count = bulk_user_admin_get_users(array_merge($options, ['enqueued' => 'only']));
}

$filter_body .= '<p><label>' . elgg_view('input/checkbox', $enqueued_input_options)
		. elgg_echo('bulk_user_admin:include_enqueued', [$enqueued_count]) . '</label></p>';

$domain_input_options = array(
	'name' => 'domain',
	'value' => $domain,
	'class' => 'elgg-input-thin'
);

$input = elgg_view('input/text', $domain_input_options);
$label = elgg_echo('bulk_user_admin:domain');
$help = elgg_echo('bulk_user_admin:domain:help');
$domain_count = '';
if ($domain) {
	$domain_count = bulk_user_admin_get_users(array_merge($options, ['domain' => $domain]));
	$domain_count_txt = elgg_echo('bulk_user_admin:domain_count', [$domain_count]);
}

$filter_body .=<<<HTML
<p>
	<label>
	$label
	$input
	$domain_count_txt
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
	'class' => 'elgg-button elgg-button-action mhm',
	'href' => '/admin/users/bulk_user_admin'
));

$filter_form = elgg_view('input/form', array(
	'body' => $filter_body,
	'action' => 'admin/users/bulk_user_admin',
	'method' => 'get',
	'disable_security' => true
));

$filter_class = 'hidden';

if ($banned || $domain || $include_enqueued) {
	$filter_class = '';
}

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

$legend = elgg_echo('bulk_user_admin:filters');

echo <<<HTML
$filter_toggle
<fieldset id="bulk-user-admin-filter" class="elgg-fieldset mtm $filter_class">
	<legend>$legend</legend>
	$filter_form
</fieldset>
HTML;
