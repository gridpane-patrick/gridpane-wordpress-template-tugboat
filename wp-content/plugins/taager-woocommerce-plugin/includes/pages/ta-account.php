<?php

/**
 * Account Page
 */

defined('ABSPATH') || exit;

function taager_account_page()
{

	$ta_selected_country = get_option('ta_selected_country');
	// $ta_free_shipping = get_option('taager_enable_free_shipping');
	$ta_user = get_option('ta_user');

?>
	<div style="text-align: center;" class="content">
		<h1>ربط متجرك بحسابك على منصة تاجر</h1>

		<style>
			p.submit {
				text-align: center;
			}
		</style>
		<?php if (isset($ta_user) && get_option('ta_api_username') && get_option('ta_api_password')) : ?>
			<!-- <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
				<input type="hidden" name="action" value="ta_account" /> -->
				<!-- <input type="hidden" name="ta_free_shipping" value="ta_free_shipping" /> -->

				<!-- <input type="checkbox" name="taager_enable_free_shipping"  value="yes"> -->
				
				<!-- <label for="apple">شحن مجاني</label>
				<?= submit_button('حفظ') ?>
			</form> -->
		<?php endif; ?> 
		<form id="ta_login_form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
			<input type="hidden" name="action" value="ta_account" />
			<?php
			if (isset($ta_user) && get_option('ta_api_username') && get_option('ta_api_password')) {
			?>
				<input type="hidden" name="taager_logout" value="logout" />
				<p>تسجيل الدخول بواسطة: <b><?php echo $ta_user->username; ?></b></p>
				<?php if ($ta_selected_country) {
					echo "<p>الدولة : <b>" . $ta_selected_country . "</b></p>";
				}

				submit_button('تسجيل الخروج');
			} else {
				?>
				<style>
					.shahbandr .colm-form .form-container:hover {
						transform: scale(1.05) !important;

					}

					.text-align {
						text-align: right;

					}
				</style>
				<section id="custom-login-form">
					<main>
						<div class="row-login">
							<h4>من فضلك ادخل بيانات الدخول الخاصة بحساب منصة تاجر</h4>

							<div class="colm-form" style="margin-top: 1rem;">
								<div class="form-container">
									<h2 class="header-form">تسجيل الدخول </h2>
									<?php
									if (isset($_GET['login']) and ('failed' == $_GET['login'])) {

										echo '<div class="error notice" style="display: block !important;">
												  <p>تسجيل الدخول غير صالح . يرجى التحقق من اسم المستخدم / كلمة المرور الخاصة بك وحاول مرة أخرى.</p>
											  </div>';
									}
									?>
									<input type="text" class="text-align" name="ta_api_username" required placeholder="ادخل بريدك الالكتروني او اسم المستخدم">
									<input type="password" class="text-align" name="ta_api_password" required placeholder="كلمة المرور">
									<?= submit_button('تسجيل الدخول', 'btn-login'); ?>
								</div>
							</div>
						</div>
					</main>
				</section>



			<?php
			}
			?>
		</form>
	</div>
<?php
}

/**
 * Import Category, Provinces after save Username and Password
 */
add_action('admin_post_ta_account', 'ta_account');
function ta_account()
{

	if (isset($_POST['ta_free_shipping'])) {
		// if (isset($_POST['taager_enable_free_shipping']) and $_POST['taager_enable_free_shipping'] == 'yes') {
		// 	update_option('taager_enable_free_shipping', 'yes');
		// } else {
		// 	update_option('taager_enable_free_shipping', 'no');
		// }
		wp_redirect(admin_url('admin.php?page=taager_setting&5'));
	}
	if (isset($_POST['ta_api_username']) && isset($_POST['ta_api_password'])) {
		$ta_api_username   = $_POST['ta_api_username'];
		$ta_api_password   = $_POST['ta_api_password'];
		$ta_initial_status = get_option('ta_initial_status');
		$ta_selected_country = get_option('ta_selected_country');

		//authorize credentials
		$is_authorized = ta_login($ta_api_username, $ta_api_password);
		if ('authorized' != $is_authorized) {
			wp_redirect(admin_url('admin.php?page=taager_setting&login=failed'));
			exit;
		}

		$ta_user = get_option('ta_user');
		$ta_user_features = $ta_user->features;

		if (!$ta_initial_status || $ta_initial_status == 'done') {
			if (!$ta_selected_country) {
				/* Check if user has multitenancy or select EGY as country */
				if (in_array('multitenancy', $ta_user_features, true)) {
					$ta_initial_status = 'country_selection';
					update_option('ta_initial_status', $ta_initial_status);
					$ta_available_countries = get_available_countries();
					update_option('ta_available_countries', $ta_available_countries);
					wp_redirect(admin_url('admin.php?page=taager_country_selection'));
					$redirect_url = admin_url('admin.php?page=taager_country_selection');
					exit;
				} else {
					$ta_selected_country = 'EGY';
					update_option('ta_selected_country', $ta_selected_country);
					ta_initialize();
				}
			} else {
				ta_initialize();
			}
		} elseif ($ta_initial_status == 'running') {
			wp_redirect(admin_url('admin.php?page=taager_setting'));
		}

		exit;
	} else if (isset($_POST['taager_logout']) && ('logout' == $_POST['taager_logout'])) {
		delete_option('ta_user');
		delete_option('ta_api_username');
		delete_option('ta_api_password');
		delete_option('ta_initial_status');
		// wp_clear_scheduled_hook('ta_hourly_update_hook');
		clear_cron_event();
		wp_redirect(admin_url('admin.php?page=taager_setting'));
		exit;
	}
}

/**
 * Attempt login
 */

function ta_login($ta_api_username, $ta_api_password)
{
	$login_data = array(
		'username' => $ta_api_username,
		'password' => $ta_api_password
	);

	$login_response = callAPI('POST', TAAGER_URL . '/auth/login', json_encode($login_data));
	if (isset($login_response->data) && isset($login_response->user)) {
		update_option('ta_api_username', $login_response->user->username);
		update_option('ta_api_password', $ta_api_password);
		update_option('ta_user', $login_response->user);
		return 'authorized';
	}
	return '';
}

function get_available_countries()
{
	$response = callAPI('GET', TAAGER_URL . '/countries');
	$ta_available_countries = $response->data;
	return $ta_available_countries;
}
