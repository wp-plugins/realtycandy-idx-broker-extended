<?php
/**
 *
 * Redirects to the appropriate search page after quicksearch form submit
 * 
 * This file is responsible for building the query string out of the form field values
 * submitted via the Agentevo Quicksearch Widget or the Search Scrollspy
 *
 * After building the query string, the user is redirected to the IDX results page.
 *
 * The URI of the IDX results page relies on the value of RC_Idx_Api::system_results_url()
 */

// do nothing if the form was not submitted
if ( ! isset($_POST['submit']) ) {
	exit;
}
$data = $_POST;
$location = $data['results_url'];
$query_string = '';
// dont want these to end up in the query string
unset($data['submit']);
unset($data['results_url']);
// unset any empty data so it doesn't end up in the query string
foreach( $data as $key => $value ) {
	if ( empty($data[$key]) ) {
		unset($data[$key]);
	}
}
// build the query string if there is any remaining $data
if ( !empty($data) ) {
	$query_string = http_build_query($data);
}
header('Location: ' . $location . '?' . $query_string);
exit;