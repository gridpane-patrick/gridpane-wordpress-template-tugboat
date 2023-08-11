<?php
$textDomain = $viewParams['textDomain'] ?? 'tamara';
$authoriseSuccessUrl = $viewParams['authoriseSuccessUrl'] ?? null;
get_header();
?>

<div id="tamara-success-overlay"></div>
<div class="tamara-success-default">
    <div class="tamara-success-default__heading">
        <div class="tamara-success-default__heading__logo">
        </div>
        <p class="tamara-success-default__heading__text"><?php echo __('Order Received by Tamara', $textDomain) ?></p>
    </div>
    <div class="tamara-success-default__content">
        <h4><?php echo __("Please don't close this window.", $textDomain) ?></h4>
        <h4><?php echo __('Your order paying with Tamara is still under process.', $textDomain) ?></h4>
        <h4><?php echo sprintf(__("Please %s if you are not redirected automatically after 30 seconds.", $textDomain), '<a href="'.$authoriseSuccessUrl.'">'.__('click here', $textDomain).'</a>') ?></h4>
    </div>
</div>

<?php
get_footer();
