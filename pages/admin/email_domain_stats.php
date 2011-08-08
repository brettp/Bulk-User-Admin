<?php
/**
 * Shows a list of email domains on the site and how many users have are part of the domain.
 */

$title = 'Email domain stats';

// has to be here or the sidemenu is buggered because of pagesetup hook bs
$title_str = elgg_view_title($title);

$domain_list = elgg_view('bulk_user_admin/email_domain_stats', array(
	'domains' => bulk_user_admin_get_email_domain_stats()
));

$domain_list = elgg_view('page_elements/contentwrapper', array(
	'body' => $domain_list
));

set_context('admin');

$content = $title_str . $domain_list;
$body = elgg_view_layout('two_column_left_sidebar', '', $content);
page_draw($title, $body);
