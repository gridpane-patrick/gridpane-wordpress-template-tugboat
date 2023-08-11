<?php
$textDomain = $viewParams['textDomain'] ?? 'tamara';
?>
<br>
<?php echo __('Thank you for choosing Tamara! We will inform you once the merchant ships your order.', $textDomain) ?>
<div class="tamara-view-and-pay-button">
    <div class="tamara-view-and-pay-button__text">
        <a href="<?php echo __('https://app.tamara.co/orders',
            $textDomain) ?>" class="tamara-view-and-pay-button__text--up"
           target="_blank"><?php echo __('View Your Orders',
                $textDomain) ?></a>
        <a href="<?php echo __('https://app.tamara.co',
            $textDomain) ?>" class="tamara-view-and-pay-button__text--down"
           target="_blank"><?php echo __('Go to Tamara and pay',
                $textDomain) ?></a>
    </div>
</div>
