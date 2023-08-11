<?php

/**
 * Dyanmically apply user CSS.
 */
namespace Hurrytimer;

use Hurrytimer\Utils\Helpers;

$campaigns = Helpers::getCampaigns( [ 'post_status' => 'publish' ] );

foreach ( $campaigns as $post ):
$campaign = new Campaign( $post->ID );
$campaign->loadSettings();

// Identifier class of the current campaign.
$campaignClass = ".hurrytimer-campaign-{$campaign->get_id()}";
?>

<?php
// ------------------------------------------------
// Campaign container
// ------------------------------------------------
?>
<?php echo $campaignClass ?>{
    text-align: <?php echo $campaign->campaignAlign ?>;
    display: <?php echo $campaign->campaignDisplay ?>;
}
<?php
// ------------------------------------------------
// Digit and seperator
// ------------------------------------------------
?>

<?php echo "$campaignClass .hurrytimer-timer-digit" ?>,
<?php echo "$campaignClass .hurrytimer-timer-sep" ?>{
    color: <?php echo $campaign->digitColor ?>;
    display: <?php echo $campaign->blockDisplay ?>;
    font-size: <?php echo $campaign->digitSize ?>px;
}

<?php echo "$campaignClass .hurrytimer-timer" ?>{
<?php if ( $campaign->campaignDisplay === "inline" ): ?>
    display: inline-flex;
    vertical-align: middle;
<?php else: ?>
<?php switch ( $campaign->campaignAlign ) {
case  'left':
    echo 'justify-content:flex-start';
break;
case  'right':
    echo 'justify-content:flex-end';
break;
default:
    echo 'justify-content:center';
break;
} endif; 
echo '}';

// ------------------------------------------------
// Label
// ------------------------------------------------
?>
<?php echo "$campaignClass .hurrytimer-timer-label" ?>{
    font-size: <?php echo $campaign->labelSize ?>px;
    color: <?php echo $campaign->labelColor ?>;
    text-transform: <?php echo $campaign->labelCase ?>;
    display: <?php echo $campaign->blockDisplay ?>;
}

<?php if ( $campaign->enableSticky === C::YES ): ?>
    .hurrytimer-sticky-<?php echo $campaign->get_id() ?>{
    background-color: <?php echo $campaign->stickyBarBgColor; ?>;
    padding-top: <?php echo $campaign->stickyBarPadding ?>px;
    padding-bottom: <?php echo $campaign->stickyBarPadding ?>px;
    <?php echo $campaign->stickyBarPosition; ?>: 0;
}
.hurrytimer-sticky-<?php echo $campaign->get_id() ?> .hurrytimer-sticky-close svg{
    fill: <?php echo $campaign->stickyBarCloseBtnColor; ?>
}
<?php endif; ?>

<?php //removeIf(!pro)
?>

<?php
// ------------------------------------------------
// Timer Block
// ------------------------------------------------
?>
<?php echo "$campaignClass .hurrytimer-timer-block" ?>{
    border: <?php echo $campaign->blockBorderWidth ?>px solid <?php echo $campaign->blockBorderColor
    ?: 'transparent' ?>;
    border-radius: <?php echo $campaign->blockBorderRadius ?>px;
    background-color: <?php echo $campaign->blockBgColor ?: 'transparent' ?>;
    padding: <?php echo $campaign->blockPadding ?>px;
    margin-left: <?php echo $campaign->blockSpacing ?>px;
    margin-right: <?php echo $campaign->blockSpacing ?>px;
    <?php if ( $campaign->blockDisplay === "block" ): ?>
    width: <?php echo $campaign->blockSize === 'auto' ? 'auto' : $campaign->blockSize . 'px' ?>;
    height: <?php echo $campaign->blockSize === 'auto' ? 'auto' : $campaign->blockSize . 'px' ?>;
    <?php endif; ?>
    <?php if ( $campaign->blockDisplay === "inline" ): ?>
    display: inline-block;
    margin-bottom:0;
<?php endif; ?>
}
<?php echo "$campaignClass .hurrytimer-timer-block:last-child" ?>
{
    margin-right: 0;
}
<?php echo "$campaignClass .hurrytimer-timer-block:first-child" ?>
{
    margin-left: 0;
}


<?php //endRemoveIf(!pro)
?>

<?php
// ------------------------------------------------
// Headline
// ------------------------------------------------
?>
<?php echo "$campaignClass .hurrytimer-headline"; ?>
{
    font-size: <?php echo $campaign->headlineSize ?>px;
    color: <?php echo $campaign->headlineColor ?>;
<?php if ( $campaign->headlinePosition === C::HEADLINE_POSITION_BELOW_TIMER ): ?>
    margin-<?php echo $campaign->campaignDisplay === "inline" ? 'left'
: 'top' ?>: <?php echo $campaign->headlineSpacing; ?>px;
<?php else: ?>
    margin-<?php echo $campaign->campaignDisplay === "inline" ? 'right' : 'bottom' ?>: <?php echo $campaign->headlineSpacing; ?>px;
<?php endif; ?>

<?php if ( $campaign->campaignDisplay === "inline" ): ?>
    display:inline-block;
    vertical-align:middle;
<?php endif; ?>
}

<?php echo "$campaignClass .hurrytimer-button-wrap" ?>{
<?php if ( $campaign->campaignDisplay === 'inline' ): ?>
    margin-left: <?php echo $campaign->callToAction[ 'spacing' ] ?>px;
<?php else: ?>
    margin-top: <?php echo $campaign->callToAction[ 'spacing' ] ?>px;
<?php endif; ?>

<?php if ( $campaign->campaignDisplay === "inline" ): ?>
    display:inline-block;
    vertical-align:middle;
<?php endif; ?>
}
<?php echo "$campaignClass .hurrytimer-button" ?>{
    font-size: <?php echo $campaign->callToAction[ 'text_size' ] ?>px;
    color: <?php echo $campaign->callToAction[ 'text_color' ] ?>;
    background-color: <?php echo $campaign->callToAction[ 'bg_color' ] ?>;
    border-radius: <?php echo $campaign->callToAction[ 'border_radius' ] ?>px;
    padding: <?php echo $campaign->callToAction[ 'y_padding' ] . 'px '. $campaign->callToAction[ 'x_padding' ] ?>px;
}

@media(max-width:425px) {
<?php echo $campaignClass ?> .hurrytimer-button-wrap,
<?php echo $campaignClass ?> .hurrytimer-headline
{
    margin-left: 0;
    margin-right: 0;
}
}
<?php
// ------------------------------------------------
// Append Custom CSS
// ------------------------------------------------

//removeIf(!pro)
$css = str_replace( '.hurrytimer-campaign', ".hurrytimer-campaign-{$campaign->get_id()}",
$campaign->customCss );
echo $css;
//endRemoveIf(!pro)
endforeach;
?>





