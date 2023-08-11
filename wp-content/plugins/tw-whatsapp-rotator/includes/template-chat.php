<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="id-ID" prefix="og: http://ogp.me/ns#">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php esc_html_e('Redirect...', 'twwr'); ?></title>
<meta name="robots" content="noindex, nofollow">
<?php
$settings = !empty( get_option('twwr-whatsapp-chat-setting') ) ? get_option('twwr-whatsapp-chat-setting') : array();
$loading_text = (isset($settings['loading-text'])) ? $settings['loading-text'] : __('Please wait, you will immediately connected to one of our agents.', 'twwr');?>
<style>
    body {
        background-color: <?php echo (isset($settings['page-bg']) && !empty($settings['page-bg']) ) ? $settings['page-bg'] : '#f1f1f1'; ?>;
        font: 16px/160% Arial, Helvetica, sans-serif;
        color: #7e7e7e;
        margin: 10% auto;
    }

    .twwr-whatsapp-chat-loading-connect{
        color : <?php echo (isset($settings['sub-heading-color']) && !empty($settings['sub-heading-color']) ) ? $settings['sub-heading-color'] : '#7e7e7e'; ?>;
    }

    .agent-forward {
        width: 90%;
        margin: 0 auto;
        max-width: 640px;
        text-align: center;
        position: fixed;
        top: 45%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
    
    .agent-avatar {
        margin: 0 auto;
    }

    .agent-avatar img.avatar {
        width: 60px;
        height: 60px;
        border-radius: 100px;
    }
    
    .number {
        margin-bottom: 20px;
    }

    .number label {
        font-size: 20px;
        font-weight: 400;
        color: <?php echo (isset($settings['sub-heading-color']) && !empty($settings['sub-heading-color']) ) ? $settings['sub-heading-color'] : '#bbb'; ?>;
        letter-spacing: 1px;
        margin: 0;
        padding: 0;
    }

    .number img.wa-icon {
        position: relative;
        top: 3px;
    }

    .agent-avatar span.wa-icon {
        position: absolute;
        top: 0;
        right: 0;
        width: 34px;
        padding: 8px;
        background: #03cc0b;
        line-height: 17px;
        border-radius: 17px;
        border: solid 2px #ffffff;
        height: 34px;
    }

    .agent-avatar span.wa-icon img {
        display: block;
    }

    .agent-forward h1 {
        font-size: 32px;
        font-weight: 400;
        color: <?php echo (isset($settings['heading-color']) && !empty($settings['heading-color']) ) ? $settings['heading-color'] : '#333'; ?>;
        margin: 10px 0 5px 0;
        padding: 0;
    }

    .wa-progress-bar {
        background: #dfe5ea;
        position: relative;
    }

    .wa-progress-bar:after {
        content: '';
        display: block;
        width: 100%;
        height: 5px;
    }

    span.wapb-span {
        position: absolute;
        left: 0;
        bottom: 0;
        top: 0;
        background: #03cc0b;
        box-shadow: 0 0 8px rgba(3, 204, 11, 0.41);
    }

    .agent-forward.notfound {
        box-shadow: none;
    }

    .agent-forward.notfound h3 {
        font-weight: 400;
        margin: 10px 0 30px 0;
        color: <?php echo (isset($settings['sub-heading-color']) && !empty($settings['sub-heading-color']) ) ? $settings['sub-heading-color'] : '#7e7e7e'; ?>;
    }

    .agent-forward.notfound h3:after {
        background: #e24848;
    }

    a.twwr-wa-button {
        color: #bbbbbb;
        display: flex;
        padding: 15px;
        flex-wrap: wrap;
        position: relative;
        text-decoration: none;
        align-items: center;
    }

    a.twwr-wa-button span.chat {
        position: absolute;
        top: 50%;
        right: 15px;
        transform: translateY(-50%);
        background-color: <?php echo (isset($settings['chat-button-color']) && !empty($settings['chat-button-color']) ) ? $settings['chat-button-color'] : '#fff'; ?>;
        font-size: 12px;
        font-weight: 700;
        color: <?php echo (isset($settings['chat-button-text-color']) && !empty($settings['chat-button-text-color']) ) ? $settings['chat-button-text-color'] : '#03cc0b'; ?>;
        text-decoration: none;
        padding: 3px 17px;
        border-radius: 20px;
    }

    a.twwr-wa-button span.agent-avatar-fig {
        flex: 0 0 40px;
        display: flex;
        width: 40px;
        height: 40px;
        border: solid 2px #dddddd;
        border-radius: 30px;
        overflow: hidden;
    }
    
    a.twwr-wa-button.Online  span.agent-avatar-fig {
        border-color: #3cd609;
    }

    a.twwr-wa-button span.agent-avatar-fig img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        filter: grayscale(1);
    }

    a.twwr-wa-button.Online  span.agent-avatar-fig img {
        filter: grayscale(0);
    }

    span.agent-detail-fig {
        flex: 0 0 calc(100% - 135px);
        text-align: left;
        padding: 0 15px;
        font-size: 12px;
    }

    span.agent-message {
        display: none;
    }

    span.agent-number {
        display: flex;
        font-size: 11px;
        line-height: 16px;
        letter-spacing: 1px;
    }

    span.agent-label {
        display: flex;
        margin: 0 -7px;
        flex-wrap: wrap;
        line-height: 16px;
        align-items: center;
    }

    span.agent-label span {
        padding: 0 7px;
    }

    span.agent-label span.agent-name {
        flex: 0 0 100%;
        order: 3;
        font-size: 16px;
        font-weight: 700;
        line-height: 24px;
    }

    .Online span.agent-label span.agent-name {
        color: #333333;
    }

    span.agent-label span.status:before {
        content: '';
        display: inline-block;
        vertical-align: middle;
        width: 10px;
        height: 10px;
        background: #bbbbbb;
        margin: -2px 5px 0 0;
        border-radius: 5px;
    }

    .Online span.agent-label span.status:before {
        background: #3cd609;
    }

    span.agent-label span.status {
        border-left: solid 1px #eeeeee;
    }

    .Online span.agent-label span.status {
        color: #3cd609;
    }

        .lds-spinner {
            color: official;
            display: inline-block;
            position: relative;
            width: 64px;
            height: 64px;
        }
        
        .lds-spinner div {
            transform-origin: 32px 32px;
            animation: lds-spinner 1.2s linear infinite;
        }

        .lds-spinner div:after {
            content: " ";
            display: block;
            position: absolute;
            top: 3px;
            left: 29px;
            width: 5px;
            height: 14px;
            border-radius: 20%;
            background: #92D1C3;
        }

        .lds-spinner div:nth-child(1) {
            transform: rotate(0deg);
            animation-delay: -1.1s;
        }

        .lds-spinner div:nth-child(2) {
            transform: rotate(30deg);
            animation-delay: -1s;
        }

        .lds-spinner div:nth-child(3) {
            transform: rotate(60deg);
            animation-delay: -0.9s;
        }

        .lds-spinner div:nth-child(4) {
            transform: rotate(90deg);
            animation-delay: -0.8s;
        }

        .lds-spinner div:nth-child(5) {
            transform: rotate(120deg);
            animation-delay: -0.7s;
        }

        .lds-spinner div:nth-child(6) {
            transform: rotate(150deg);
            animation-delay: -0.6s;
        }

        .lds-spinner div:nth-child(7) {
            transform: rotate(180deg);
            animation-delay: -0.5s;
        }

        .lds-spinner div:nth-child(8) {
            transform: rotate(210deg);
            animation-delay: -0.4s;
        }

        .lds-spinner div:nth-child(9) {
            transform: rotate(240deg);
            animation-delay: -0.3s;
        }

        .lds-spinner div:nth-child(10) {
            transform: rotate(270deg);
            animation-delay: -0.2s;
        }

        .lds-spinner div:nth-child(11) {
            transform: rotate(300deg);
            animation-delay: -0.1s;
        }

        .lds-spinner div:nth-child(12) {
            transform: rotate(330deg);
            animation-delay: 0s;
        }

        @keyframes lds-spinner {
            0% {
                opacity: 1;
            }
            100% {
                opacity: 0;
            }
        }

        .agent-list-item {
            display: block;
            background: <?php echo (isset($settings['agent-box-color']) && !empty($settings['agent-box-color']) ) ? $settings['agent-box-color'] : '#fff'; ?>;
            max-width: 350px;
            margin: 0 auto;
            box-shadow: 0 10px 20px <?php echo (isset($settings['agent-box-shadow']) && !empty($settings['agent-box-shadow']) ) ? $settings['agent-box-shadow'] : '#f1f1f1'; ?>;
            transition: transform ease-in-out .2s;
            border-radius: 7px;
            position: relative;
        }

        .agent-list-item:hover {
            transform: translateY(3px) scale(1);
        }

        .agent-list-item+.agent-list-item {
            margin-top: 20px;
        }
        

    p.contact-message {
        font-size: 12px;
        opacity: .6;
    }

    @media screen and (max-width: 320px) {
        a.twwr-wa-button {
            align-items: start;
        }

        a.twwr-wa-button span.agent-avatar-fig {
            width: 40px;
            height: 40px;
            flex: 0 0 40px;
        }

        span.agent-detail-fig {
            flex: 0 0 calc(100% - 120px);
        }

        a.twwr-wa-button span.chat {
            padding: 3px 13px;
            font-size: 12px;
        }

        .agent-forward h1 {
            font-size: 24px;
        }

        .agent-forward h3 {
            font-size: 14px ;
            line-height: 18px;
        }
    }
</style>

<!-- Facebook Pixel Code -->
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
    document,'script','https://connect.facebook.net/en_US/fbevents.js');
</script>
<!-- End Facebook Pixel Code -->

<?php
$display = get_post_meta(get_the_ID(), '_twwr_whatsapp_display', true);

if( (!isset($_GET['agent']) || !isset($_GET['number'])) && $display == 'random' ){
    $button = twwr_whatsapp_chat_random_agent();
    if( is_array($button) && count($button) > 0 ){
        $_GET['agent'] = $button[0]['agent'];
        $_GET['number'] = $button[0]['number'];
    }
}

if ( isset($_GET['agent']) && !empty($_GET['agent']) && isset($_GET['number']) && !empty($_GET['agent']) ) {
    $agent = get_post($_GET['agent']);
    $number = $_GET['number'];
    $ref = isset($_GET['ref']) ? url_to_postid($_GET['ref']) : false;
    $link = twwr_whatsapp_chat_generate_link( get_the_ID(), $number, $_GET['agent'], $ref);
    $fb_pix_ids = get_post_meta( get_the_ID(), '_twwr_whatsapp_fb_id', true );
    $fb_pix = get_post_meta(get_the_ID(), '_twwr_whatsapp_pixel_events', true);

    if ( $fb_pix == 'Custom' ) {
        $fb_pix = get_post_meta(get_the_ID(), '_twwr_cr_pixel_events_custom', true);
    }

    if ( empty($fb_pix) ) {
        $fb_pix = 'ViewContent';
    }
}

?>

<?php $gtm_id = get_post_meta(get_the_ID(), '_twwr_whatsapp_gtm_id', true); ?>

<?php if ( !empty( $gtm_id ) ) : ?>
  <!-- Google Tag Manager -->
  <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
      new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
  j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
  'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
  })(window,document,'script','dataLayer','<?php esc_attr_e( $gtm_id ); ?>');</script>
  <!-- End Google Tag Manager -->
<?php endif; ?>

</head>


<body>

<?php if ( !empty( $gtm_id ) ) : ?>
  <!-- Google Tag Manager (noscript) -->
  <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php esc_attr_e( $gtm_id ); ?>"
  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
  <!-- End Google Tag Manager (noscript) -->
<?php endif; ?>

    <?php if ( isset($_GET['agent']) && !empty($_GET['agent']) && isset($_GET['number']) && !empty($_GET['agent']) ) : ?>

        <div class="agent-forward">

            <p class="twwr-whatsapp-chat-loading-connect"><?php echo nl2br($loading_text); ?></p>

            <div class="agent-avatar">
                <?php echo get_the_post_thumbnail($_GET['agent'], 'thumbnail', array('class' => 'avatar')); ?>
            </div>
            <h1><?php esc_html_e ( $agent->post_title ); ?></h1>
            <div class="number">
                <img class="wa-icon" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyRpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoTWFjaW50b3NoKSIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDpFQTg3NTY5QUI4MTExMUU5QkFCMzk5NDA2Q0I1MkVFMSIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDpFQTg3NTY5QkI4MTExMUU5QkFCMzk5NDA2Q0I1MkVFMSI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOkVBODc1Njk4QjgxMTExRTlCQUIzOTk0MDZDQjUyRUUxIiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOkVBODc1Njk5QjgxMTExRTlCQUIzOTk0MDZDQjUyRUUxIi8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+1GuVWgAAArdJREFUeNqMlV9ozlEYx5+z/YQaoeFtmrZIuCA0ZRdmUXMhf278uVBDImUXQloSSS6Wf1cos5upV3KxFpJiF9Jqa0qpaSz5mz8lTa2wHd/nnO/enfe39/f+nPq8599znvM8z3l+z2syrdOlULPAiFSj2wHWgOVgGjYGsNGHcQd4KAnNJCiuhuJ2I7bWqc+70GJlbM0OYX8/BrfjCkoKKG0Sawep9CXmjeItnwRKoXQW+g3gjvNAJAsepFl8DjSDv2ALuC/F22xwD9SA52AVo5hncSOVfgPz/kOpUHY1aAcrwN14KDJwv81HUENgv3J9BohS1VvZDZ5gtA3soWJn+Qkx+iCmyVh5Telm7Pxwe2nNHZW9nJ1XnRpjfZRhTEaxOxVrI5hXQPA9PfoI5oPRxKQcb20M6UY9WKuvDYFb6EcEJoOlQZg03utDVfmm2pAsNzZp/BZz8jg48SoYP8ORR6GqiXHItRfsl6lVVbTjk7/UKBqGVgr1m1wYU9tnelWpin/TyVIfhpyGw0CzQx/lQLGUKBCgP6p4gPsLvUxOaFhjxaPX8Hs0OHsKaHpVxm6pYj9YglP97g4jDS7lTJ7DPbin3ri7TAvf4Tg4iYvWoX8HDgbyNey7I7jfi0OIqdmMhbngS8yvLtYKrQf1JIi4mRyk3j6OO5lS9hLjdCwhkG/BErALPLW+lmi7DK7wgjr8NKi1GPdF1rqbu433d0HKq2ueZnGijCYPcb3MVztntTMuokcr6VpH0Swdb7+CMcqj7YEkKp05rR65WsHs2kqhLi/oyucFjou17axwi8B1cGZsQ7+8KayjLgtAeVAFjtB9LYdvuJwBdczvOfRLM6UlvDFiwGdyXs7Xv2F8Tp4FO8nEkiPSidVDWP0QdyUiV8FN0BvbvwjWsphXGC/7XT9z/pH+TIrRPwEGAE3xuAKVUJ65AAAAAElFTkSuQmCC" />
                <label>+<?php esc_html_e ( $number ); ?></label>
            </div>

            <div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>

        </div>
    <?php else: ?>
        <div class="agent-forward notfound" data-type="<?php esc_attr_e( $display ); ?>">
            <h1 class="page-title"><?php echo (isset($settings['heading-text']) && !empty($settings['heading-text']) ) ? $settings['heading-text'] : __('Choose an Agent', 'twwr'); ?></h1>
            <h3><?php echo (isset($settings['sub-heading-text']) && !empty($settings['sub-heading-text']) ) ? $settings['sub-heading-text'] : __('Choose one of of the agent to initiate chat in WhatsApp.', 'twwr'); ?></h3>
            <?php echo twwr_whatsapp_chat_listing_agent(); ?>
        </div>
    <?php endif; ?>

    <script type="text/javascript">
        <?php if( isset($link) ) : ?>
            <?php if (isset($fb_pix_ids) && !empty($fb_pix_ids) ) : ?>
                <?php foreach ($fb_pix_ids as $pixel_id) : ?>
                    <?php if ( !empty($pixel_id) ) : ?>
                        fbq('set', 'autoConfig', 'false', '<?php echo esc_attr($pixel_id); ?>');
                        fbq('init', '<?php echo esc_attr($pixel_id); ?>');
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>

            fbq('track', 'PageView', {
                'source': 'themewarrior-wa-chat',
                'version': '1.0.0'
            });
            fbq('track', '<?php esc_attr_e( $fb_pix ); ?>', {});
            var time = Math.floor(Math.random() * (3000 - 2000)) + 2000; // random time from 2-3 second

            var geo = {};
                
            function callback(data){
                geo = data;
                const rot_data = "action=twwr-whatsapp-chat-count-click&chat=<?php echo get_the_ID(); ?>&number=<?php esc_attr_e( $_GET['number'] ); ?>&geo="+JSON.stringify(geo)+"&type=click&ref=<?php esc_attr_e( $_GET['ref'] ); ?>";
        
                const datas = "action=twwr-whatsapp-chat-agent-count-click&chat=<?php echo get_the_ID(); ?>&agent=<?php esc_attr_e( $_GET['agent'] ); ?>&number=<?php esc_attr_e( $_GET['number'] ); ?>&geo="+JSON.stringify(geo)+"&type=click&ref=<?php esc_attr_e( $_GET['ref'] ); ?>";

                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    },
                    body: datas,
                });
            }

            var script = document.createElement('script');
            script.type = 'text/javascript';
            script.src = 'https://geolocation-db.com/jsonp/?callback=callback';
            var h = document.getElementsByTagName('script')[0];
            h.parentNode.insertBefore(script, h);

            var FBAction = '<?php esc_attr_e( $fb_pix ); ?>';

            if ( typeof fbq !== 'undefined' && FBAction ) {
                if( FBAction != 'noevent' ){
                    fbq('track', FBAction, {});
                }
            }

            setTimeout(function(){
                window.location.replace( "<?php echo $link; ?>" );
            }, time);
        <?php endif; ?>
    </script>
</body>
</html>
