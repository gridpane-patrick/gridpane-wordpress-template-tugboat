jQuery(document).ready(function($){
    // alert('Hello World!');
    $(".cancel-button").on('click', function(e) {
        e.preventDefault();
        Swal.fire({
            title: "Please Wait While Send Your Cancel Request",
            icon: 'info',
            showConfirmButton: false,
        })
        let target = $(this).attr('data-key');
        let token = $(this).attr('bearerToken');
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Authorization': `${token}`,
                },
            url:`https://portal.eg.sideup.co/api/cancelWooCommerceOrder`,
            data: {
                woocommerce_id: target,
            },
            method: "POST",
            success:function(response){
                return true;
            },
            error:function(error){
                return false;
            },
        }).then(response => {
            location.reload();
        }).catch(e => {
            Swal.fire({
                title: 'It seems you missed something, please contact with your account manager to help you and solve this issue.',
                confirmButtonText: `OK`,
            })
        })
    });
    $(".shipping-button").on('click', function(e) {
        e.preventDefault();
        let city = $(this).attr('data-city');
        let item_cost = Number($(this).attr('data-total'));
        let target = $(this).attr('data-key');
        let token = $(this).attr('bearerToken');

        $result = $.ajax({
                        url: "https://portal.eg.sideup.co/api/localPrices",
                        data: { drop_area_name: city },
                        type: "GET",
                        beforeSend: function(xhr){xhr.setRequestHeader('Authorization', token);},
                        success: function(response) {
                            var drop_main_city_id = city;
                            var pickupZoneId = response['pickup zone'];
                            var mylerzNotPlaces = ["Benisuif", "Fayoum", "Minya", "Asyut", "Souhag", "Quena", "Luxor", "Aswan", "Sharm el-sheikh", "Hurghada", "Marsa Matrouh", "elwadi elgedid", "Elwahat", "oases"];
                            var fetchrNotPlaces = ["Benisuif", "Fayoum", "Minya", "Asyut", "Souhag", "Quena", "Luxor", "Aswan", "Sharm el-sheikh", "Hurghada", "Marsa Matrouh", "elwadi elgedid", "Elwahat", "oases"];
                            
                            var mylerzNotexist = mylerzNotPlaces.includes(drop_main_city_id);
                            var hideMylerz = (mylerzNotexist || pickupZoneId!=93 ) && (mylerzNotexist || pickupZoneId!=45) && (mylerzNotexist || pickupZoneId!=92);
                            
                            var fetchrNotexist = fetchrNotPlaces.includes(drop_main_city_id);
                            var hideFetchr = fetchrNotexist;
                            mylerzFees = Number(response.data.Fedex) - 5;
                            console.log(response.data.Fedex, response.data.Aramex, Math.ceil(((item_cost) - 2500) / 1000) * 7);
                            $fedex_total_fees = (item_cost > 2500) ?  Number(response.data.Fedex) + Math.ceil((((item_cost) - 2500)) * 7 / 1000) : Number(response.data.Fedex);
                            $mylerz_total_fees = (item_cost > 2500) ?  Number(mylerzFees) + Math.ceil((((item_cost) - 2500)) * 7 / 1000) : Number(mylerzFees);
                            $fetchr_total_fees = (item_cost > 2500) ?  Number(response.data.Fetchr) + Math.ceil((((item_cost) - 2500)) * 7 / 1000) : Number(response.data.Fetchr);
                            $jt_total_fees = (item_cost > 2500) ?  Number(response.data.Jt) + Math.ceil((((item_cost) - 2500)) * 7 / 1000) : Number(response.data.Jt);
                            $aramex_total_fees = (item_cost > 2500) ?  Number(response.data.Aramex) + Math.ceil((((item_cost) - 2500)) * 7 / 1000) : Number(response.data.Aramex);
                            $(".fedex-total-fees").val($fedex_total_fees);
                            $(".fetchr-total-fees").val($fetchr_total_fees);
                            $(".jt-total-fees").val($jt_total_fees);
                            $(".aramex-total-fees").val($aramex_total_fees);
                            $(".mylerz-total-fees").val($mylerz_total_fees);
                            console.log(item_cost, $fedex_total_fees, $mylerz_total_fees, $fetchr_total_fees, $jt_total_fees,$aramex_total_fees);
                            target = target;
                            Swal.fire({
                                title: 'Choose Courier and Payment Method',
                                html:
                                    `
                                    <div class="" style="text-align:center"><h3>Add receiver backup Phone Number (OPTIONAL)</h3></div>
                                    <input type="text" id="backup-mobile" target=${target} value="" placeholder="01xxxxxxxxx">
                                    <br>
                                    <div class="" style="text-align:center"><h2>Total Cash Collection</h2></div>
                                    <input type="number" id="item-cost" target=${target} value=${item_cost}>
                                    <br>
                                    <span class="badge badge-primary">This is the cost that will be collected from your client if you choose Cash OnDlivery or Online Payment.</span>
                                    <br>
                                    <span class="badge badge-primary">This will be 0 automatically if you chooce Zero Cash Collection.</span>
                                    <div class="" style="text-align:center"><h2>Select Payment Way</h2></div>
                                    <div class="" style="text-align:initial">
                                    <div class="mb-3 form-check item-details">
                                    <input class="form-check-input" type="radio" name="paymentWay" id="COD" value="4" checked target=${target}>
                                    <label class="form-check-label ml-4" for="COD">
                                        Cash On Delivery
                                    </label>
                                    <br>
                                    <span class="badge badge-primary">COD amount exceeds 2500 LE, 7 EGP for each 1000 EGP will apply.</span>
                                    <br>
                                    <span class="badge badge-primary">Weight increments are rounded up to the higher weight by 1 Kg unit and each 1 KG = 6 LE.</span>
                                    </div>
                                    <div class="mb-3 form-check item-details">
                                    <input class="form-check-input" type="radio" name="paymentWay" id="credit" value="1" target=${target}>
                                    <label class="form-check-label ml-4" for="credit">
                                        Credit Card (Visa or Mastercard)
                                    </label>
                                    <br>
                                    <span class="badge badge-primary">There are 3% of total amount will be added as Credit Card fees.</span>
                                    </div>
                                    <div class="mb-3 form-check item-details">
                                    <input class="form-check-input" type="radio" name="paymentWay" id="fawry" value="2" target=${target}>
                                    <label class="form-check-label ml-4" for="fawry">
                                        Fawry
                                    </label>
                                    <br>
                                    <span class="badge badge-primary">There are 3% of total amount will be added as Fawry fees.</span>
                                    </div>
                                    <div class="mb-3 form-check item-details">
                                    <input class="form-check-input" type="radio" name="paymentWay" id="zeroCash" value="3" target=${target}>
                                    <label class="form-check-label ml-4" for="zeroCash">
                                        Zero Cash Collection
                                    </label>
                                    <br>
                                    <span class="badge badge-primary">There are no cash will collected from the cosignee and I will pay the delivery fees.</span>
                                    </div>
                                    </div>
                                    </div>
                                    <div class="" style="text-align:center"><h2>Select Courier</h2></div>
                                    <div class="row">
                                    <input type="hidden" id="payment-way" value="4">
                                    
                                    <div class="item-details">
                                        <div class="top2">
                                            <img class="img-fluid" src="https://portal.eg.sideup.co/assets/icons/fedex.svg" style="width: 120px;">
                                        </div>
                                        <span class="font-md regular text-capitalize "></span>
                                        <div class="delivery-fees">
                                            <span class="font-sm">Delivery Fees</span>
                                            <input class="form-control input-back fedex-total-fees" style="margin : 0 auto; width: 80% !important;height: auto;top: 100px;opacity:1;" name="delivery_fees" disabled="" value="${$fedex_total_fees}">
                                            <button class="btn btn-primary ship-order" target=${target} courier="fedex">Ship</button>
                                            <input class="base-fedex-fees" hidden type="text" value="${(Number(response.data.Fedex))}">
                                        </div>
                                    </div>
                                    
                                    <div class="item-details">
                                        <div class="top2">
                                            <img class="img-fluid" src="https://portal.eg.sideup.co/assets/icons/jt.svg" style="width: 120px;">
                                        </div>
                                        <span class="font-md regular text-capitalize "></span>
                                        <div class="delivery-fees">
                                            <span class="font-sm">Delivery Fees</span>
                                            <input class="form-control input-back jt-total-fees" style="margin : 0 auto; width: 80% !important;height: auto;top: 100px;opacity:1;" name="delivery_fees" disabled="" value="${$jt_total_fees}">
                                            <button class="btn btn-primary ship-order" target=${target} courier="Jt">Ship</button>
                                            <input class="base-jt-fees" hidden type="text" value="${(Number(response.data.Jt))}">
                                        </div>
                                    </div>

                                    <div class="item-details">
                                        <div class="top2">
                                            <img class="img-fluid" src="https://portal.eg.sideup.co/assets/icons/aramex.svg" style="width: 120px;">
                                        </div>
                                        <span class="font-md regular text-capitalize "></span>
                                        <div class="delivery-fees">
                                            <span class="font-sm">Delivery Fees</span>
                                            <input class="form-control input-back aramex-total-fees" style="margin : 0 auto; width: 80% !important;height: auto;top: 100px;opacity:1;" name="delivery_fees" disabled="" value="${$aramex_total_fees}">
                                            <button class="btn btn-primary ship-order" target=${target} courier="aramex">Ship</button>
                                            <input class="base-aramex-fees" hidden type="text" value="${(Number(response.data.Aramex))}">
                                        </div>
                                    </div>
                                    <div ${hideMylerz ? 'style=display:none;' : 'class="item-details"'}>
                                        <div class="top2">
                                            <img class="img-fluid" src="https://portal.eg.sideup.co/assets/icons/mylerz.png" style="width: 120px;">
                                        </div>
                                        <span class="font-md regular text-capitalize "></span>
                                        <div class="delivery-fees">
                                            <span class="font-sm">Delivery Fees</span>
                                            <input class="form-control input-back mylerz-total-fees" style="margin : 0 auto; width: 80% !important;height: auto;top: 100px;opacity:1;" name="delivery_fees" disabled="" value="${$mylerz_total_fees}">
                                            <button class="btn btn-primary ship-order" target=${target} courier="mylerz">Ship</button>
                                            <input class="base-mylerz-fees" hidden type="text" value="${(Number(mylerzFees))}">
                                            <div class="hint>
                                                <small style="color: black;padding-left: 5px;font-size: 14px;bottom: -40%;z-index: 99999;font-weight:bold">* No Pickup Available Only Dropoff, <br> Find <strong>Mylerz</strong> Branchs <a href="https://portal.eg.sideup.co/samples/mylerz_branchs.pdf" target="_blank" style="color: #F25A29;font-weight: bold;">Here</a></small>
                                            </div>
                                        </div>
                                    </div>
                                    </div>`,
                                focusConfirm: false,
                                showConfirmButton: false,
                            })
                        }
                    });
        // swal.fire('HELLO');
    });

    $(document).on('change', 'input[type=radio][name=paymentWay]', function() {
        id = $(this).attr('target');
        $(`.payment-way`).val($(this).val());
        row = $(`#${$(this).attr('target')}`);
        item_cost = Number($("#item-cost").val());
        base_fedex_fees = $(".base-fedex-fees").val();
        base_fetchr_fees = $(".base-fetchr-fees").val();
        base_jt_fees = $(".base-jt-fees").val();
        base_aramex_fees = $(".base-aramex-fees").val();
        base_mylerz_fees = $(".base-mylerz-fees").val();
        console.log($(this).val(), 'HELLO', item_cost);
        if($(this).val() == '4') {
            $(".fedex-total-fees").val(((item_cost) > 2500) ?  Number(base_fedex_fees) + Math.ceil((((Number(base_fedex_fees) + item_cost) - 2500)) * 7 / 1000) : Number(base_fedex_fees));
            $(".fetchr-total-fees").val(((item_cost) > 2500) ?  Number(base_fetchr_fees) + Math.ceil((((Number(base_fetchr_fees) + item_cost) - 2500)) * 7 / 1000) : Number(base_fetchr_fees));
            $(".jt-total-fees").val(((item_cost) > 2500) ?  Number(base_jt_fees) + Math.ceil((((Number(base_jt_fees) + item_cost) - 2500)) * 7 / 1000) : Number(base_jt_fees));
            $(".aramex-total-fees").val(((item_cost) > 2500) ?  Number(base_aramex_fees) + Math.ceil((((Number(base_aramex_fees) + item_cost) - 2500)) * 7 / 1000) : Number(base_aramex_fees));
            $(".mylerz-total-fees").val(((item_cost) > 2500) ?  Number(base_mylerz_fees) + Math.ceil((((Number(base_mylerz_fees) + item_cost) - 2500)) * 7 / 1000) : Number(base_mylerz_fees));
        } else if($(this).val() == '1') {
            $(".fedex-total-fees").val(Math.ceil((Number(base_fedex_fees)) + (0.03 * (item_cost))));
            $(".fetchr-total-fees").val(Math.ceil((Number(base_fetchr_fees)) + (0.03 * (item_cost))));
            $(".jt-total-fees").val(Math.ceil((Number(base_jt_fees)) + (0.03 * (item_cost))));
            $(".aramex-total-fees").val(Math.ceil((Number(base_aramex_fees)) + (0.03 * (item_cost))));
            $(".mylerz-total-fees").val(Math.ceil((Number(base_mylerz_fees)) + (0.03 * (item_cost))));
        } else if($(this).val() == '2') {
            $(".fedex-total-fees").val(Math.ceil((Number(base_fedex_fees)) + (0.03 * (item_cost))));
            $(".fetchr-total-fees").val(Math.ceil((Number(base_fetchr_fees)) + (0.03 * (item_cost))));
            $(".jt-total-fees").val(Math.ceil((Number(base_jt_fees)) + (0.03 * (item_cost))));
            $(".aramex-total-fees").val(Math.ceil((Number(base_aramex_fees)) + (0.03 * (item_cost))));
            $(".mylerz-total-fees").val(Math.ceil((Number(base_mylerz_fees)) + (0.03 * (item_cost))));
        } else if($(this).val() == '3') {
            $(".fedex-total-fees").val(base_fedex_fees);
            $(".fetchr-total-fees").val(base_fetchr_fees);
            $(".jt-total-fees").val(base_jt_fees);
            $(".aramex-total-fees").val(base_aramex_fees);
            $(".mylerz-total-fees").val(base_mylerz_fees);
            $("#item-cost").val(0);
        } else {
            return;
        }
    });

    $(document).on('keyup', '#backup-mobile', function() {
        id = $(this).attr('target');
        // $(`.payment-way`).val();
        row = $(`#${id}`);
        row.attr('data-backup-phone', $(this).val());
    });

    $(document).on('change', '#item-cost', function() {
        id = $(this).attr('target');
        // $(`.payment-way`).val();
        row = $(`#${id}`);
        console.log(id, row);
        // target = $(`#${row}`);
        item_cost = Number($(this).val());
        row.attr('data-total', item_cost);
        console.log(row);
        base_fedex_fees = $(".base-fedex-fees").val();
        base_fetchr_fees = $(".base-fetchr-fees").val();
        base_jt_fees = $(".base-jt-fees").val();
        base_aramex_fees = $(".base-aramex-fees").val();
        base_mylerz_fees = $(".base-mylerz-fees").val();
        console.log($(this).val(), 'HELLO', item_cost, $('input[type=radio][name="paymentWay"]:checked').val());
        if($('input[type=radio][name="paymentWay"]:checked').val() == '4') {
            $(".fedex-total-fees").val(((item_cost) > 2500) ?  Number(base_fedex_fees) + Math.ceil((((Number(base_fedex_fees) + item_cost) - 2500)) * 7 / 1000) : Number(base_fedex_fees));
            $(".fetchr-total-fees").val(((item_cost) > 2500) ?  Number(base_fetchr_fees) + Math.ceil((((Number(base_fetchr_fees) + item_cost) - 2500)) * 7 / 1000) : Number(base_fetchr_fees));
            $(".jt-total-fees").val(((item_cost) > 2500) ?  Number(base_jt_fees) + Math.ceil((((Number(base_jt_fees) + item_cost) - 2500)) * 7 / 1000) : Number(base_jt_fees));
            $(".aramex-total-fees").val(((item_cost) > 2500) ?  Number(base_aramex_fees) + Math.ceil((((Number(base_aramex_fees) + item_cost) - 2500)) * 7 / 1000) : Number(base_aramex_fees));
            $(".mylerz-total-fees").val(((item_cost) > 2500) ?  Number(base_mylerz_fees) + Math.ceil((((Number(base_mylerz_fees) + item_cost) - 2500)) * 7 / 1000) : Number(base_mylerz_fees));
        } else if($(`.payment-way`).val() == '1') {
            $(".fedex-total-fees").val(Math.ceil((Number(base_fedex_fees)) + (0.03 * (item_cost))));
            $(".fetchr-total-fees").val(Math.ceil((Number(base_fetchr_fees)) + (0.03 * (item_cost))));
            $(".jt-total-fees").val(Math.ceil((Number(base_jt_fees)) + (0.03 * (item_cost))));
            $(".aramex-total-fees").val(Math.ceil((Number(base_aramex_fees)) + (0.03 * (item_cost))));
            $(".mylerz-total-fees").val(Math.ceil((Number(base_mylerz_fees)) + (0.03 * (item_cost))));
        } else if($(`.payment-way`).val() == '2') {
            $(".fedex-total-fees").val(Math.ceil((Number(base_fedex_fees)) + (0.03 * (item_cost))));
            $(".fetchr-total-fees").val(Math.ceil((Number(base_fetchr_fees)) + (0.03 * (item_cost))));
            $(".jt-total-fees").val(Math.ceil((Number(base_jt_fees)) + (0.03 * (item_cost))));
            $(".aramex-total-fees").val(Math.ceil((Number(base_aramex_fees)) + (0.03 * (item_cost))));
            $(".mylerz-total-fees").val(Math.ceil((Number(base_mylerz_fees)) + (0.03 * (item_cost))));
        } else if($(`.payment-way`).val() == '3') {
            $(".fedex-total-fees").val(base_fedex_fees);
            $(".fetchr-total-fees").val(base_fetchr_fees);
            $(".jt-total-fees").val(base_jt_fees);
            $(".aramex-total-fees").val(base_aramex_fees);
            $(".mylerz-total-fees").val(base_mylerz_fees);
            $("#item-cost").val(0);
        } else {
            return;
        }
    });

    $(document).on('click', '.ship-order', function() {
        Swal.fire({
            title: "Please Wait While Send Your Order",
            icon: 'info',
            showConfirmButton: false,
        })
        id = $(this).attr('target');
        target = $(`#${id}`);
        bearerToken      = target.attr("bearerToken");
        woocommerce_id   = target.attr('id');
        shipment_code    = Math.floor(1000000 + Math.random() * 9000000);
        name             = target.attr('data-name');
        phone            = target.attr('data-mobile');
        area_id          = target.attr('data-city');
        address          = target.attr('data-address');
        item_description = target.attr('data-description');
        item_cost        = target.attr('data-total');
        backup_mobile    = target.attr('data-backup-phone');
        courier          = $(this).attr('courier');
        landmark         = 'N/A';
        notes            = `WooCommerce Order`;
        payment_way      = $(`.payment-way`).val();
        wordpress_id     = target.attr('data-wordpress-id');
        zero_cash_collection = 0;
        total_cash_collection = Number(item_cost);
        if(payment_way == 1) {
            online_payment = 'online_payment';
        } else if(payment_way == 2) {
            online_payment = 'fawry_payment';
        } else if(payment_way == 3) {
            online_payment = null;
            zero_cash_collection = 1;
            total_cash_collection = 0;
        } else if(payment_way == 4) {
            online_payment = null;
        } else {
            online_payment = null;
        }

        console.log(shipment_code    ,
                    payment_way      ,
                    name             ,
                    phone            ,
                    area_id          ,
                    address          ,
                    item_description ,
                    item_cost        ,
                    courier          ,
                    landmark         ,
                    notes            ,
                    zero_cash_collection,
                    online_payment,
                    total_cash_collection,
                    backup_mobile,
                    Number($(`.${courier}-total-fees`).val()));

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Authorization': `${bearerToken}`,
                },
            url:`https://portal.eg.sideup.co/api/orders`,
            data: {
                shipment_code    : shipment_code   ,
                name             : name            ,
                phone            : phone           ,
                area_id          : area_id         ,
                address          : address         ,
                item_description : item_description,
                // item_cost        : item_cost       ,
                courier          : courier         ,
                landmark         : landmark        ,
                notes            : notes           ,
                isWooCommerce    : true            ,
                woocommerce_id   : woocommerce_id  ,
                zero_cash_collection : zero_cash_collection,
                online_payment   : online_payment  ,
                total_cash_collection : total_cash_collection,
                backup_mobile    : backup_mobile,
            },
            method: "POST",
            success:function(response){
                return true;
            },
            error:function(error){
                return false;
            },
        }).then(response => {
            location.reload();
        }).catch(e => {
            Swal.fire({
                title: 'It seems you missed something, please contact with your account manager to help you and solve this issue.',
                confirmButtonText: `OK`,
            })
        })
    });
});