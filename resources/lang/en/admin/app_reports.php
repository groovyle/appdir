<?php

return [

'page_title'		=> [
	'index'			=> 'Product Moderation',
	'review'		=> 'Review Product Reports',
	'review_x'		=> 'Review Reports for :x',
	'verdicts'		=> 'Product Verdicts History',
	'verdicts_x'	=> 'Verdicts History for :x',

	'view_mode'			=> [
		'all'	=> 'Viewing all reported products',
		'prodi'	=> 'Viewing reported products in your study program - :x',
	],
],

'apps_list'						=> 'Products List',
'no_app_reports_yet'			=> 'No products reported yet!',
'app_versions'					=> 'Product Versions',
'is_current_app_version'		=> 'This is the product\'s current version',
'version_x_app_information'		=> 'Version :x product details',
'reports_on_version_x'			=> 'Reports on Version :x',
'reports_on_x'					=> 'Reports on :x',
'reports_list'					=> 'Reports List',
'close_this_version'			=> 'Close this version\'s report form',
'report_#x'						=> 'Report #:x',
'report_categories'				=> 'Violation type(s)',
'was_this_a_valid_report?'		=> 'Was this a valid report?',
'label_report_is_valid'			=> 'Valid',
'mark_as_valid'					=> 'Mark as valid',
'mark_report_as_valid'			=> 'Mark this report as valid',
'this_report_was_valid'			=> 'This report was deemed valid',
'label_report_is_invalid'		=> 'Invalid',
'mark_as_invalid'				=> 'Mark as invalid',
'mark_report_as_invalid'		=> 'Mark this report as invalid/irrelevant',
'this_report_was_invalid'		=> 'This report was deemed invalid/irrelevant',
'clear_mark'					=> 'Clear marks',
'select_some_reports_to_use_this_feature'	=> 'Nothing selected! Please select 1 or more reports to use this feature.',
'settle_review'					=> 'Settle reports review',
'all_categories'				=> 'All categories',
'all_app_versions'				=> 'All product versions',
'version__none'					=> 'No associated version',
'take_care_when_reviewing_older_version_reports'	=> 'Take care when reviewing older version reports, as it might have become irrelevant due to the product\'s later changes',
'this_report_was_reported_on_an_older_version'	=> 'This report was submitted for an older version of the product',
'go_to_verdict_section'			=> 'Go to verdicts section below',
'report_from_registered_user'	=> 'Report from a registered user',
'report_from_anonymous_user'	=> 'Report from an anonymous/public visitor',
'x_of_y_reports_have_been_validated'	=> ':x of :y reports have been validated',
'this_app_is_clean'				=> 'This product is spotless!',
'no_reports_for_this_app'		=> 'There are no reports to this product',
'review_anyway?'				=> 'I want to review this product anyway',
'verdicts_list'					=> 'Verdicts List',
'review'						=> 'Review',
'review_reports'				=> 'Review Reports',
'history'						=> 'History',
'verdicts_history'				=> 'Verdicts History',
'judgement_was_made_when_the_app_was_at_version_x'	=> 'Judgement was made when the product was at Version :x',
'no_reports_in_this_verdict'	=> 'This verdict did not review any reports...',
'verdict_by'					=> 'Verdict by',
'no_verdicts'					=> 'No verdicts yet for this product',
'status_unresolved'				=> 'Unresolved',
'status_resolved'				=> 'Reviewed',
'reported_x_times'				=> 'Reported :x times',
'x_reports'						=> ':x reports',
'x_unresolved_reports'			=> ':x unresolved reports',
'x_past_verdicts'				=> ':x past verdicts',
'view_verdicts'					=> 'View verdicts',

'fields'			=> [
	'reported_versions'	=> 'Reported Versions',
	'violation_types'	=> 'Violation Types',
	'categories'		=> 'Categories',
	'reports'			=> 'Reports',
	'report'			=> 'Report',
	'report_status'		=> 'Report status',
	'reason'			=> 'Reason',
	'comments'			=> 'Comments',
	'final_comments'	=> 'Final Comments',
	'final_comments_hint'	=> 'The product owner won\'t be able to see the report submissions (they only see the violation/report categories), so make sure to summarize below about which parts of the content are inappropriate. A clear explanation will help the product owner to spot and correct their mistakes.',
	'final_comments_placeholder'	=> 'A brief summary to tell the product owner about which parts of the content are inappropriate, and additional details related to the reports...',
	'verdict'			=> 'Verdict',
	'verdict_notes'		=> 'Verdict Notes',
	'ban_reasons'		=> 'Ban Reasons',
],

'verdicts'			=> [
	// Reflect changes to the _past versions below
	'innocent'				=> 'Innocent',
	'innocent_explanation'	=> 'If the reports had been proven to be false, nothing will be done to this product.',
	'guilty'				=> 'Guilty',
	'guilty_explanation'	=> 'This product will be unlisted from the public space until the offending contents are removed, and the product will have to go through verification processes again.',
	'guilty_block_user'		=> 'Block user',
	'guilty_block_user_explanation'	=> 'Block the product owner as well. A blocked user won\'t be able to log into the system, and thus cannot modify their products anymore. All their products will also be publicly unavailable.',
],

'verdicts_past'		=> [
	// Reflect changes to the present versions above
	'innocent'				=> 'Innocent',
	'innocent_explanation'	=> 'The product was unaffected - the allegations had been found to be false.',
	'guilty'				=> 'Guilty',
	'guilty_explanation'	=> 'The product was unlisted from the public space. The product will have to go through verification processes again with the offending contents removed for it to be published again.',
	'guilty_block_user'		=> 'User blocked',
	'guilty_block_user_explanation'	=> 'The product owner was blocked as a result of the verdict.',
],

'messages'			=> [
	'user_app_x_has_inappropriate_content_y'	=> 'This user\'s product, :x, had been found to have inappropriate content: :y',
	'if_verdict_innocent_all_reports_must_be_invalid'	=> 'If the verdict is innocent, all the reports must be invalid.',
	'if_verdict_guilty_one_report_must_be_valid'	=> 'If the verdict is guilty, at least one of the reports must be valid.',
],

];
