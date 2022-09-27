<?php

return [

'app_versions'					=> 'App Versions',
'is_current_app_version'		=> 'This is the app\'s current version',
'version_x_app_information'		=> 'Version :x app details',
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
'label_report_is_invalid'		=> 'Invalid',
'mark_as_invalid'				=> 'Mark as invalid',
'mark_report_as_invalid'		=> 'Mark this report as invalid/irrelevant',
'clear_mark'					=> 'Clear marks',
'select_some_reports_to_use_this_feature'	=> 'Nothing selected! Please select 1 or more reports to use this feature.',
'settle_review'					=> 'Settle reports review',
'all_categories'				=> 'All categories',
'all_app_versions'				=> 'All app versions',
'version__none'					=> 'No associated version',
'take_care_when_reviewing_older_version_reports'	=> 'Take care when reviewing older version reports, as it might have become irrelevant due to the app\'s later changes',
'go_to_verdict_section'			=> 'Go to verdicts section below',
'report_from_registered_user'	=> 'Report from a registered user',
'report_from_anonymous_user'	=> 'Report from an anonymous/public viewer',
'x_of_y_reports_have_been_validated'	=> ':x of :y reports have been validated',
'this_app_is_clean'				=> 'This app is spotless!',
'no_reports_for_this_app'		=> 'There are no reports to this app',
'review_anyway?'				=> 'I want to review this app anyway',
'verdicts_list'					=> 'Verdicts List',
'history'						=> 'History',

'fields'			=> [
	'reported_versions'	=> 'Reported Versions',
	'categories'		=> 'Categories',
	'reason'			=> 'Reason',
	'final_comments'	=> 'Final Comments',
	'final_comments_hint'	=> 'The app owner won\'t be able to see the report texts (they only see the violation/report categories), so make sure to summarize below about which parts of the content are inappropriate. A clear explanation will help the app owner to spot and correct their mistakes.',
	'final_comments_placeholder'	=> 'A brief summary to tell the app owner about which parts of the content are inappropriate, and additional details related to the reports...',
	'verdict'			=> 'Verdict',
],

'verdicts'			=> [
	'innocent'				=> 'Innocent',
	'innocent_explanation'	=> 'If the reports had been proven to be false, nothing will be done to this app.',
	'guilty'				=> 'Guilty',
	'guilty_explanation'	=> 'This app will be unlisted from the public space until the offending contents are removed, and the app will have to go through verification processes again.',
	'guilty_block_user'		=> 'Block user',
	'guilty_block_user_explanation'	=> 'Block the app owner as well. A blocked user won\'t be able to log into the system, and thus cannot modify their apps anymore.',
],

'messages'			=> [
	'user_app_x_has_inappropriate_content_y'	=> 'This user\'s app, :x, had been found to have inappropriate content: :y',
],

];
