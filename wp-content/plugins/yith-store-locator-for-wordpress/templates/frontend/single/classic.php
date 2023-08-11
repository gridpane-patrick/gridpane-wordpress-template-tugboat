<?php extract( $args ) ?>
<div id="yith-sl-main-wrapper">
    <div class="wrap-header">
        <h1>
            <?php echo esc_html( $name ); ?>
        </h1>
    </div>
    <div class="main-section">
        <div class="wrap-image">
            <?php echo wp_kses_post( $image ) ?>
        </div>
        <div class="wrap-description">
            <p>
                <?php echo wp_kses_post( $description ); ?>
            </p>
        </div>
    </div>
    <div class="contact-info">
        <div class="wrap-single-info location ">
            <div class="wrap-icon">
                <img src="<?php echo esc_url( $address_icon ); ?>">
            </div>
            <div class="info">
                <h4><?php echo esc_html( $address_title ); ?></h4>
                <p>
                    <?php echo wp_kses_post( $address ); ?>
                </p>
            </div>
            <?php if( !empty($custom_direction_link) ): ?>
                <a class="custom-link" href="<?php echo esc_url( $custom_direction_link ); ?>" title="<?php esc_html_e( 'Open page', 'yith-store-locator' ) ?>">
                    <?php echo esc_html( $custom_direction_label ); ?>
                </a>
            <?php endif; ?>
        </div>
        <div class="wrap-single-info contact">
            <div class="wrap-icon">
                <img src="<?php echo esc_url( $contact_info_icon ); ?>">
            </div>
            <div class="info">
                <h4><?php echo esc_html( $contact_info_title ); ?></h4>
                <?php
                $phone_label = apply_filters( 'yith_sl_contact_phone_label', esc_html__( 'Phone:','yith-store-locator' ) );
                $mobile_label = apply_filters( 'yith_sl_contact_mobile_label', esc_html__( 'Mobile Phone:','yith-store-locator' ) );
                $email_label = apply_filters( 'yith_sl_contact_email_label', esc_html__( 'E-mail:','yith-store-locator' ) );
                $website_label = apply_filters( 'yith_sl_contact_website_label', esc_html__( 'Website','yith-store-locator' ) );
                ?>
                <ul>
                    <li>
                        <b><?php echo esc_html( $phone_label ); ?></b>
                        <?php echo esc_html( $phone ); ?>
                    </li>
                    <li>
                        <b><?php echo esc_html( $mobile_label ) ?></b>
                        <?php echo esc_html( $mobile_phone ); ?>
                    </li>
                    <li>
                        <b><?php echo esc_html( $email_label ) ; ?></b>
                        <?php echo esc_html( $email ); ?>
                    </li>
                    <li>
                        <b><?php echo esc_html( $website_label ); ?></b>
                        <?php echo esc_html( $website ); ?>
                    </li>
                </ul>
            </div>
            <?php if( !empty($custom_contact_link) ): ?>
                <a class="custom-link" href="<?php echo esc_url( $custom_contact_link ); ?>" title="<?php esc_attr_e( 'Click here','yith-store-locator' ) ?>">
                    <?php echo esc_html( $custom_contact_label ); ?>
                </a>
            <?php endif; ?>
        </div>
        <?php if( !empty( $opening_hours_text ) ): ?>
            <div class="wrap-single-info opening-hours">
                <div class="wrap-icon">
                    <img src="<?php echo esc_url( $opening_hours_icon ); ?>">
                </div>
                <div class="info">
                    <h4><?php echo esc_html( $opening_hours_title ); ?></h4>
                    <p>
                        <?php echo wp_kses_post( $opening_hours_text ); ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>


