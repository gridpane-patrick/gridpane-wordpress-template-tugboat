<?php

defined('ABSPATH') || exit;

/**
 * Product setting page
 */
function taager_product_setting_page()
{



	// ta_UpdateTable($db);
	// $res = $db->query("SELECT  * from 'products' limit 5");
	// echo "<pre>";
	// while ($row = $res->fetchArray()) {
	// 	echo "{$row['id']} \n";
	// }

	// die;
	// $db = new SQLite3(WP_CONTENT_DIR . '/ta.db');
	// $res = $db->query("SELECT  * from categories");
	// echo "<pre>";
	// while ($row = $res->fetchArray()) {
	// 	echo "{$row['id']} \n";
	// }

	// die;

	//import categoies first
	if (class_exists('SQLite3')) {
		if (!file_exists(WP_CONTENT_DIR . '/ta.db')) {
			new SQLite3(WP_CONTENT_DIR . '/ta.db');
		} 
		try {
			$db = new PDO("sqlite:" . WP_CONTENT_DIR . '/ta.db');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (Exception $e) {
			echo "Unable to connect to database";
			echo $e->getMessage();
			exit;
		}

		import_categories($db);

		$sql = "SELECT * from categories";
	} else {
		import_categories();
	}
	$taager_categories_names = get_option('taager_categories_name_lits');

	$ta_initial_status = get_option('ta_selected_country');
	$args               = array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => false,
	);
	$product_categories = get_terms($args);


?>
	<div class="content">
		<style>
			p.submit {
				padding: 0;
				margin: 0;
			}
		</style>
		<!-- category and search section-->
		<section class="category_and_search" style="direction: rtl;" id="category_and_search">
			<div class="container">
				<form class="row" id="ta_product_setting_form" method="post">
					<input type="hidden" name="action" value="list_taager_products" />

					<div class="col-md-4">
						<div class="input-group">
							<input class="form-control border" name="product_name" type="search" id="form-search" placeholder="ابحث عن منتج" id="example-search-input">
						</div>
					</div>
					<div class="col-md-4">
						<select class="form-select" id="form-cat" name="product_category" aria-label="Default select example">
							<option value="">اختار القسم</option>
							<?php
							if (class_exists('SQLite3')) {
								foreach ($db->query($sql)->fetchall(PDO::FETCH_ASSOC) as $value) {
							?>
									<option value="<?= $value['_id'] ?>">
										<?= $value['text'] ?>
									</option>
									<?php
								}
							} else {
								foreach ($product_categories as $value) {
									if ($value->name == 'Uncategorized') {
										continue;
									}
									if (in_array($value->name, $taager_categories_names)) {
									?>
										<option value="<?php echo $value->name; ?>">
											<?php echo $value->name; ?>
										</option>
							<?php }
								}
							}
							?>
						</select>
					</div>
					<div class="col-md-2 part">
						<select class="form-select" name="product_sort" aria-label="Default select example">
							<option selected value="">ترتيب حسب </option>
							<option value="max_price">الاكثر سعر</option>
							<option value="lowest_price">الاقل سعر</option>
							<option value="max_profit">الاكثر ربح </option>
							<option value="lowest_profit">الاقل ربح </option>
						</select>
					</div>
					<div class="col-md-2 fdiv">
						<?php submit_button('بحث', 'primary  export-btn'); ?>

					</div>

				</form>
			</div>
		</section>


		<!-- sub header section-->
		<section class="sub_header" style="direction: rtl;" id="sub_header">
			<div class="container">
				<div class="row">
					<div class="col-md-4 menu_head part">
						<ul>
							<li onclick="jQuery('.product_cart:not(.selected_product)',document).click()">تحديد الكل</li>
							<li onclick="jQuery('.product_cart.selected_product').click()">الغاء</li>
							<li id="import-products" class="export-btn">استيراد</li>
						</ul>
					</div>
					<div class="col-md-2">
					</div>
					<div class="col-md-3 part">
						<p class="text_head">عرض <b id="pageSize">0</b> من <b id="total_prod">0</b> منتج </p>
					</div>
					<div class="col-md-3 menu_head part">
						<ul>
							<li id="import-cat" style="text-align: center;margin-right: auto !important;" class="export-btn">استيراد القسم كامل</li>
						</ul>
					</div>
					<div class="col-md-6" style="margin-top: 1rem;">
						<span for="">زيادة السعر أثناء عملية الاستيراد</span>
						<td>
							<input type="radio" style="width: auto;" name="enable_increase_price" value="1"> <label class="increase_price_label">نعم</label>
							<input type="radio" style="width: auto;" name="enable_increase_price" value="0" checked=""> <label class="increase_price_label">لا</label>
							<div class="increase_price_section" style="display: none;">
								<input type="text" name="increase_price" style="width: auto;" id="increase_price">
								<select name="increase_price_by">
									<option value="by_price">قيمة ثابته</option>
									<option value="by_percentage">نسبة مئوية %</option>
								</select>
							</div>
					</div>

				</div>
			</div>
		</section>


		<!-- products section-->
		<section class="products_section" style="direction: rtl;" id="products_section">
			<div class="container">
				<div class="row products_show">

				</div>
			</div>
		</section>

		<!--pagination section-->
		<section class="paginnation">
			<div class="">
				<div class="">
					<div class="paged" style="padding-top: 1.8rem;">
					</div>
				</div>
			</div>
		</section>
	</div>
	<script>
		jQuery(function($) {

			$('[name="enable_increase_price"]').change(function() {
				console.log($(this).val())
				if ($('[name="enable_increase_price"]:checked').val() == 1) {
					$('.increase_price_section').show()
				} else {
					$('.increase_price_section').hide()

				}
			})
			$('#import-products').addClass('disabled');
			$('#import-cat').addClass('disabled');

			$(document).on('click', '.product_cart', function() {
				console.log()
				if ($(this).find('.product_checkbox').prop('checked')) {
					$(this).removeClass('selected_product')
					$(this).find('.product_checkbox').prop('checked', false);

				} else {
					$(this).addClass('selected_product')
					$(this).find('.product_checkbox').prop('checked', true)

				}
				if (jQuery(".product_checkbox:checked", document).length) {
					$('#import-products').removeClass('disabled');

				} else {
					$('#import-products').addClass('disabled');
					// jQuery('.product_cart',document).removeClass('selected_product');
					// jQuery('.product_checkbox',document).removeAttr('checked');
				}


			});

			$('#import-cat').click(function() {

				jQuery('body').waitMe({
					effect: 'bounce',
					text: 'جاري استيراد المنتجات',
					maxSize: '',
					waitTime: -1,
					textPos: 'vertical',
					fontSize: '',
					source: '',
				});
				$.ajax({
					type: "POST",
					url: ta_admin.ajaxURL,
					data: {
						product_cat: $('#form-cat').val(),
						action: 'taager_products_import',
						increase_price: $('[name="increase_price"]').val(),
						increase_price_by: $('[name="increase_price_by"]').val(),
						enable_increase_price: $('[name="enable_increase_price"]').val(),
					},
					success: function(data) {
						jQuery('body').waitMe('hide');
						ta_pagination(jQuery('form#ta_product_setting_form').serialize());

					},
				});

			});
			$('#import-products').click(function() {
				var selected = new Array();
				$(".product_checkbox:checked", document).each(function() {
					selected.push($(this).val());
				});
				jQuery('body').waitMe({
					effect: 'bounce',
					text: 'جاري استيراد المنتجات',
					maxSize: '',
					waitTime: -1,
					textPos: 'vertical',
					fontSize: '',
					source: '',
				});
				$.ajax({
					type: "POST",
					url: ta_admin.ajaxURL,
					data: {
						'product_name': selected,
						action: 'taager_products_import',
						increase_price: $('[name="increase_price"]').val(),
						increase_price_by: $('[name="increase_price_by"]').val(),
						enable_increase_price: $('[name="enable_increase_price"]').val(),
					},
					success: function(data) {
						jQuery('body').waitMe('hide');
						ta_pagination(jQuery('form#ta_product_setting_form').serialize());

					},
				});

			});
			$('form#ta_product_setting_form').on('submit', function(e) {
				e.preventDefault();

				var product_category = jQuery("select[name='product_category'] option:selected").val();
				var product_name = jQuery("input[name='product_name']").val();
				var product_ids = jQuery("input[name='product_ids']").val();


				var formData = $(this).serialize();

				ta_pagination(formData)

				if (jQuery('#form-cat').val()) {
					$('#import-cat').removeClass('disabled');

				} else {
					$('#import-cat').addClass('disabled');
				}

			});
			ta_pagination(jQuery('form#ta_product_setting_form').serialize());


		})

		function ta_pagination(formData = '') {

			jQuery('.paged').pagination({
				autoHidePrevious: true,
				autoHideNext: true,
				dataSource: ta_admin.ajaxURL + "?" + formData,
				locator: 'items',
				pageSize: 16,
				totalNumberLocator: function(response) {
					return response.products;
				},

				ajax: {
					beforeSend: function() {
						jQuery('body').waitMe({
							effect: 'bounce',
							text: 'الرجاء الانتظار',
							maxSize: '',
							waitTime: -1,
							textPos: 'vertical',
							fontSize: '',
							source: '',
						});
						// dataContainer.html('Loading data from flickr.com ...');
					}
				},
				callback: function(data, pagination) {
					jQuery('body').waitMe('hide');
					jQuery('.products_show').html(' ');
					jQuery('#total_prod').text(pagination.totalNumber)
					jQuery('#pageSize').text(pagination.totalNumber > pagination.pageSize ? pagination.pageSize : pagination.totalNumber)
					data.forEach(prod => {

						jQuery('.products_show').append(`
					<div class="col-md-3">
						<div class="product_cart" data-product-id="${prod.id}">
							<div class="pimg">
								<p> <img src="${prod.image}" alt="${prod.name}"></p>
							</div>
							<div class="product_data">
								<h5>${prod.cat}</h3>
									<h4>${prod.name}</h2>
										<div class="row flex">
											<p class="col-md-6"> السعر <span>${prod.price}</span> <?= $ta_initial_status == 'EGY' ? 'جنية' : 'ريال' ?></p>
											<p class="col-md-6"> الربح <span>${prod.profit}</span> <?= $ta_initial_status == 'EGY' ? 'جنية' : 'ريال' ?></p>
										</div>
							</div>
							<div class="choose_product">
								<input type="checkbox" class="product_checkbox" value="${prod.name}" id="product_id_${prod.id}">
								<span>اختيار المنتج</span>
							</div>
						</div>
					</div>
					`);
					});


				}
			})
		}
	</script>
<?php
}
