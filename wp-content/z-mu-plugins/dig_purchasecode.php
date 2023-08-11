<?php
  add_filter('pre_option_dig_purchasecode', function ()  {
    return 'cb61a220-1e71-4745-be21-e30afdb3cff6';
});

add_filter('pre_site_option_dig_purchasecode', function ()  {
  return 'cb61a220-1e71-4745-be21-e30afdb3cff6';
});

add_filter('pre_option_dig_purchasefail', function ()  {
  return 2;
});
add_filter('pre_option_dig_dsb', function ()  {
  return -1;
});