<?php extract( $args ); ?>
<svg id="yith-sl-loader" style="margin:auto;background:transparent;display:block;" width="<?php echo $loader_size. 'px' ?>" height="<?php echo $loader_size. 'px' ?>" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
    <rect x="17.5" y="28.9481" width="15" height="42.1038" fill="<?php echo $loader_color; ?>">
        <animate attributeName="y" repeatCount="indefinite" dur="1s" calcMode="spline" keyTimes="0;0.5;1" values="18;30;30" keySplines="0 0.5 0.5 1;0 0.5 0.5 1" begin="-0.2s"></animate>
        <animate attributeName="height" repeatCount="indefinite" dur="1s" calcMode="spline" keyTimes="0;0.5;1" values="64;40;40" keySplines="0 0.5 0.5 1;0 0.5 0.5 1" begin="-0.2s"></animate>
    </rect>
    <rect x="42.5" y="28.1084" width="15" height="43.7833" fill="<?php echo $loader_color; ?>">
        <animate attributeName="y" repeatCount="indefinite" dur="1s" calcMode="spline" keyTimes="0;0.5;1" values="20.999999999999996;30;30" keySplines="0 0.5 0.5 1;0 0.5 0.5 1" begin="-0.1s"></animate>
        <animate attributeName="height" repeatCount="indefinite" dur="1s" calcMode="spline" keyTimes="0;0.5;1" values="58.00000000000001;40;40" keySplines="0 0.5 0.5 1;0 0.5 0.5 1" begin="-0.1s"></animate>
    </rect>
    <rect x="67.5" y="26.3543" width="15" height="47.2914" fill="<?php echo $loader_color; ?>">
        <animate attributeName="y" repeatCount="indefinite" dur="1s" calcMode="spline" keyTimes="0;0.5;1" values="20.999999999999996;30;30" keySplines="0 0.5 0.5 1;0 0.5 0.5 1"></animate>
        <animate attributeName="height" repeatCount="indefinite" dur="1s" calcMode="spline" keyTimes="0;0.5;1" values="58.00000000000001;40;40" keySplines="0 0.5 0.5 1;0 0.5 0.5 1"></animate>
    </rect>
</svg>