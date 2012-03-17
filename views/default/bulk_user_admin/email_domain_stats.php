<?php

$domains = $vars['domains'];

?>
<table class="bulk_user_admin_email_domains">
	<tr>
		<th>Domain</th>
		<th>Registered users</th>
	</tr>
<?php


$i = 0;
foreach ($domains as $domain_info) {
	if (!$domain_info->domain) {
		continue;
	}

	$domain = elgg_view('output/url', array(
		'text' => $domain_info->domain,
		'href' => $domain_info->domain
	));

	$url = elgg_http_add_url_query_elements($vars['url'] . 'admin/user', array('domain' => $domain_info->domain));
	$users = elgg_view('output/url', array(
		'text' => $domain_info->count,
		'href' => $url
	));

	$class = ($i % 2) ? 'odd' : 'even';

	echo <<<___HTML
	<tr class="$class">
		<td>$domain</td>
		<td class="center">$users</td>
	</tr>
___HTML;
	
	$i++;
}

?>
</table>