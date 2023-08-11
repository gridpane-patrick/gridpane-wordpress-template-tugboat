<?php
$textDomain = $viewParams['textDomain'] ?? 'tamara';
?>
<div class="tamara-canceled-failed-html">
    <?php echo wc_add_notice(__('We are unable to authorise your payment from Tamara. Please contact us if you need assistance.', $textDomain), 'error'); ?>
    <div class="tamara-canceled-failed-html__heading">
        <div class="tamara-canceled-failed-html__heading__logo">
        </div>
        <p class="tamara-canceled-failed-html__heading__text"><?php echo __('Payment Canceled From Tamara', $textDomain) ?></p>
    </div>
    <div class="tamara-canceled-failed-html__content">
        <h4><?php echo __('We are unable to proceed your payment via Tamara.', $textDomain) ?></h4>
        <h4><?php echo __('Please contact us if you need assistance.', $textDomain) ?></h4>
    </div>
</div>
