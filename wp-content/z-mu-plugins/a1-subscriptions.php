<?php

if (!defined('ABSPATH')) { //this is for secuirty
    exit;
}

//do not run if user is not in back-end
if (!is_admin()) {
    return;
} //this is for performance


//if the call is from "wp-cli" don't run the code below
if (defined('WP_CLI') && WP_CLI) {
    return;
} //this is important that controlpanel can see all the plugins



//do not run if the call is ajax
if (defined('DOING_AJAX') && DOING_AJAX) {
    return;
} //we do not need to run this in javascript ajax call = This is for perforamnce
$shah_consumer_key = get_option('shah_consumer_key', null);
$shah_consumer_secret = get_option('shah_consumer_secret');
$shah_unique_order_id = get_option('shah_unique_order_id');




require_once __DIR__ . '/subscriptions/update_key.php';

define('shah_consumer_key', $shah_consumer_key);
define('shah_consumer_secret', $shah_consumer_secret);
define('shah_unique_order_id', $shah_unique_order_id + 1);

if (!in_array(WAAS1_RESTRICTION_GROUP_ID,[1,6])) {

    add_action('admin_menu', function () {
        add_menu_page(' الحساب - النطاق', ' الحساب - النطاق', 'manage_options', 'my_account', 'my_account_page', 'dashicons-admin-users', 65);
    });
    function my_account_page()
    {
        $subscriptions = json_decode(wp_remote_retrieve_body(wp_remote_post(
            'https://myshahbandr.com/wp-json/auth/subscriptions/' . shah_unique_order_id,
            array(
                'method'      => 'GET',
                'body'        => array(
                    'consumer_key' => shah_consumer_key,
                    'consumer_secret' => shah_consumer_secret,
                ),
            )
        )));
      
        if (!isset($subscriptions)) {
            delete_option('shah_consumer_key');
            delete_option('shah_consumer_secret');
            delete_option('shah_unique_order_id');
            header("location: " . $_SERVER['REQUEST_URI']);
        }
      
?>
        <style>
            .my_account_card {
                margin-bottom: 2rem;
                box-shadow: 0 4px 24px 0 rgb(34 41 47 / 10%);
                -webkit-transition: all 0.3s ease-in-out, background 0s, color 0s, border-color 0s;
                transition: all 0.3s ease-in-out, background 0s, color 0s, border-color 0s;
                display: inline-block;
                width: 30%;
                margin: 1rem;
                /* padding: 0.6rem; */
                text-align: center;
            }

            .card-body {
                -webkit-box-flex: 1;
                -webkit-flex: 1 1 auto;
                -ms-flex: 1 1 auto;
                flex: 1 1 auto;
                padding: 1.5rem 1.5rem;
                font-weight: 600;
            }

            .my_account_pagetable {
                border: 1px solid #ccc;
                border-collapse: collapse;
                margin: 0;
                padding: 0;
                width: 100%;
                table-layout: fixed;
            }

            .my_account_pagetable caption {
                font-size: 1.5em;
                margin: .5em 0 .75em;
            }

            .my_account_pagetable tr {
                background-color: #f8f8f8;
                border: 1px solid #ddd;
                padding: .35em;
            }

            .my_account_pagetable th,
            .my_account_pagetable td {
                padding: .625em;
                text-align: center;
            }

            .my_account_pagetable th {
                font-size: .85em;
                letter-spacing: .1em;
                text-transform: uppercase;
            }

            @media screen and (max-width: 600px) {
                .my_account_card {
                    width: 92%;

                }

                .my_account_pagetable {
                    border: 0;
                }

                .my_account_pagetable caption {
                    font-size: 1.3em;
                }

                .my_account_pagetable thead {
                    border: none;
                    clip: rect(0 0 0 0);
                    height: 1px;
                    margin: -1px;
                    overflow: hidden;
                    padding: 0;
                    position: absolute;
                    width: 1px;
                }

                .my_account_pagetable tr {
                    border-bottom: 3px solid #ddd;
                    display: block;
                    margin-bottom: .625em;
                }

                .my_account_pagetable td {
                    border-bottom: 1px solid #ddd;
                    display: block;
                    font-size: .8em;
                    text-align: right;
                }

                .my_account_pagetable td::before {

                    content: attr(data-label);
                    float: left;
                    font-weight: bold;
                    text-transform: uppercase;
                }

                .my_account_pagetable td:last-child {
                    border-bottom: 0;
                }
            }
        </style>
        <div class="row">
            <div class="my_account_card">
                <div class="card-body"><?= $subscriptions->meta_data[0]->display_key . '  ' . $subscriptions->meta_data[0]->display_value?></div>
            </div>
            <div class="my_account_card">
            <div class="card-body"><?= $subscriptions->meta_data[1]->display_key . ' ' . $subscriptions->meta_data[1]->display_value?></div>

            </div>
            <div class="my_account_card">
            <div class="card-body"> <?= date_diff( date_create(date('Y-m-d')),date_create($subscriptions->trial_end))->format('%R%a') >0 ? 'نهاية الفترة التجريبية <p> '.date('Y-m-d', strtotime($subscriptions->trial_end)) : 'نهاية الاشتراك <p>'. date('Y-m-d', strtotime($subscriptions->next_payment)) ?></p></div>

            </div>
        </div>

        <table class="my_account_pagetable">
            <caption>الفواتير</caption>
            <thead>
                <tr>
                    <th scope="col">رقم الفاتورة</th>
                    <th scope="col">التاريخ</th>
                    <th scope="col">الحالة</th>
                    <th scope="col">الاجمالي</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subscriptions->orders as $key => $order) : ?>
                    <tr>
                        <td data-label="رقم الطلب"><?= $order->id ?></td>
                        <td data-label="التاريخ"><?= date('Y-m-d', strtotime($order->date_created)) ?></td>
                        <td data-label="الحالة"><?= $order->status ?></td>
                        <td data-label="الاجمالي"><?= $order->total ?></td>
                    </tr>
                <?php endforeach; ?>


            </tbody>

        </table>

        <div style="width: 100%;text-align: center;">
            <a href="https://myshahbandr.com/" target="_blank" style="text-decoration: none;font-size: 1.1rem;padding: 1rem;display: block;width: fit-content;margin: auto;background: #004eee;margin-top: 1rem;color: #fff;border-radius: 1rem;">ادارة الحساب و النطاق</a>
        </div>
<?php

    }
}
