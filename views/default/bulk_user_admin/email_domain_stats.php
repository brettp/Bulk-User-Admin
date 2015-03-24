<?php

$domains = $vars['domains'];

?>
<table class="elgg-table">
	<tr>
		<th><?php echo elgg_echo('bulk_user_admin:domain');?></th>
		<th><?php echo elgg_echo('bulk_user_admin:users');?></th>
	</tr>
<?php

foreach ($domains as $domain_info) {
	if (!$domain_info->domain) {
		continue;
	}

	$domain = elgg_view('output/url', array(
		'text' => $domain_info->domain,
		'href' => $domain_info->domain
	));

	$url = elgg_http_add_url_query_elements(elgg_normalize_url('/admin/users/bulk_user_admin'),
		[
			'domain' => $domain_info->domain,
			'include_enqueued' => true
		]
	);

	$users = elgg_view('output/url', array(
		'text' => $domain_info->count,
		'href' => $url
	));

	echo <<<___HTML
	<tr>
		<td>$domain</td>
		<td>$users</td>
	</tr>
___HTML;
}

?>
</table>