<?php

defined('ABSPATH') || exit;

function taager_country_selection_page()
{
	$ta_available_countries = get_option('ta_available_countries');
?>
	<style>
		p.submit {
			text-align: center;
		}
	</style>
	<div class="content">
		<div class="taager_country_selection_section" style="text-align: center;">
			<h1 class="cs_pro_setting_heading">اختيار البلد</h1>
			<h4 class="cs_pro_setting_subheading">اختار البلد لربط الموقع بها</h4>
			<div class="country_notice notice-warning">
				<p>برجاء العلم انه باختيار البلد لا يمكن تغيير هذا الاختيار مره اخري</p>
			</div>
			<form id="ta_country_selection_form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
				<input type="hidden" name="action" value="ta_country_selection" />
				<table class="form-table" style="text-align: center;width: min-content;margin: auto;">
					<tr>
						<th>البلد:</th>
						<td>
							<select name="country_selection" required id="country_selection_select_field">
								<option value="">اختر البلد</option>
								<?php
								foreach ($ta_available_countries as $key => $value) {
									echo '<option value="' . $value->countryIsoCode3 . '">' . $value->name . '</option>';
								}
								?>
							</select>
						</td>
					</tr>
				</table>
				<?php submit_button('تحديد', 'primary btn-country-selection') ?>
			</form>
		</div>
	</div>

<?php
}

add_action('admin_post_ta_country_selection', 'ta_country_selection');
function ta_country_selection()
{
	$ta_initial_status = get_option('ta_initial_status');

	if (isset($_POST['country_selection'])) {
		$ta_selected_country = $_POST['country_selection'];
		update_option('ta_selected_country', $ta_selected_country);
		ta_initialize();
	}
}
