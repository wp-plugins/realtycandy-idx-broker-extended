<?php
/**
 *
 * Contains functions and markup for creating a quicksearch scrollspy
 */
function selected_city_list_city_options($_idx) {
	//$cities = $_idx->city_list(get_theme_mod('scrollspy_city_list'));
	$cities = $_idx->city_list('combinedActiveMLS');
	if ( !$cities ) {
		return;
	}
	foreach ($cities as $city_object) {
		echo '<option value="', $city_object->id, '">', $city_object->name, '</option>';
	}
}
function city_list_data_source($_idx) {
	$cities = $_idx->city_list('combinedActiveMLS');
	if ( ! $cities ) {
		return;
	}
	$output = '';
	foreach ($cities as $city) {
		$count++;
		if ( '' == $city->name ) {
			continue;
			
		}
		// Clean city names of single quotes which break the form
		$clean_city_name = str_replace("'", "", $city->name);
		$output .= '"' . $clean_city_name . ' (' . $city->id . ')"';
		if ( $count != count($cities) ) {
			$output .= ',';
		}
	}
	return '[' . $output . ']';
}
add_action( 'genesis_before_header', 'turnkey_quicksearch_scrollspy' );
function turnkey_quicksearch_scrollspy() {
if ( get_theme_mod('disable_scrollspy') ) {
	return;
}
$_idx = new RC_Idx_Api;
?>
<div id="quicksearch-scrollspy" class="clearfix">
	<div class="wrap">
		<h4 class="widgettitle">Property Search</h4>
		<form class="clearfix" method="POST" action="<?php echo get_template_directory_uri() . '/lib/idx/search.php'; ?>">
			<input type="text" id="qs-city-select" class="bqf-city-select" name="city" data-provide="typeahead" data-source='<?php echo city_list_data_source($_idx); ?>' placeholder="Start typing a city name" value="Start typing a city name" onblur="if (this.value == '') {this.value = 'Start typing a city name';}" onfocus="if (this.value == 'Start typing a city name') {this.value = '';}" autocomplete="off">
			<input type="text" id="qs-price-min" class="bqf-price-min-input input" placeholder="Price Min" name="lp" />
			<input type="text" id="qs-price-max" class="bqf-price-max-input input" placeholder="Price Max" name="hp" />
			<input type="text" id="qs-beds" class="bqf-beds-input input" placeholder="Beds" name="bd" />
			<input type="text" id="qs-baths" class="bqf-baths-input input" placeholder="Baths" name="ba" />
			<input type="hidden" name="results_url" value="<?php echo $_idx->system_results_url(); ?>" />
			<div class="bqf-form-bottom">
				<div class="bqf-submit">
					<input class="bqf-submit-button btn btn-alt" type="submit" name="submit" value="Search Now" />
					<i class="icon icon-nav-search"></i>
				</div>
			</div><!-- .bqf-form-bottom -->
		</form>
		<div class="top-link">
			<a href="#">
				&uarr;
				<span>TOP</span>
			</a>
		</div>
	</div>
</div>
<?php
}