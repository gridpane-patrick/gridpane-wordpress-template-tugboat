<?php extract( $args ); ?>

<svg id="yith-sl-loader" style="margin:auto;background:transparent;display:block;" width="<?php echo $loader_size. 'px' ?>" height="<?php echo $loader_size. 'px' ?>" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
    <g transform="rotate(35.0927 50 50)">
        <path d="M50 15A35 35 0 1 0 74.74873734152916 25.251262658470843" fill="none" stroke="<?php echo $loader_color; ?>" stroke-width="12"></path>
        <path d="M49 3L49 27L61 15L49 3" fill="<?php echo $loader_color; ?>"></path>
        <animateTransform attributeName="transform" type="rotate" repeatCount="indefinite" dur="1.7857142857142856s" values="0 50 50;360 50 50" keyTimes="0;1"></animateTransform>
    </g>
</svg>
