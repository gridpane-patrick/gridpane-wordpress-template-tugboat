<div id="hurrytimer-customcss-tab" class="hurrytimer-subtabcontent">
<?php //removeIf(!pro) ?>
    <textarea name="custom_css"
              id="hurrytimer-customcss-textarea"
              ><?php echo $campaign->customCss ?></textarea>

              <p class="hurryt-note">
                  <b>Note:</b> add <code>.hurrytimer-campaign</code> before the classes and tags you want to target, e.g.: <code>.hurrytimer-campaign .hurrytimer-timer-digit{font-size:20px;}</code>
              </p>
    <?php //endRemoveIf(!pro) ?>

    <?php ?>
</div>

