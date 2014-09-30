<?php
/**
 * Shows a list of email domains on the site and how many users have are part of the domain.
 */

$domain_list = elgg_view('bulk_user_admin/email_domain_stats', array(
	'domains' => bulk_user_admin_get_email_domain_stats()
));

echo $domain_list;
