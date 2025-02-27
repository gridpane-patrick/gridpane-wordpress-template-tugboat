<?php

namespace YayMail;

use stdClass;
use YayMail\Helper\Helper;
use YayMail\Helper\LogHelper;
use YayMail\MailBuilder\Shortcodes;
use YayMail\Page\Source\CustomPostType;
use YayMail\Page\Source\DefaultElement;
use YayMail\Page\Source\UpdateElement;
use YayMail\Templates\Templates;

defined( 'ABSPATH' ) || exit;

class Ajax {

	protected static $instance = null;

	public static function getInstance() {

		if ( null == self::$instance ) {
			self::$instance = new self();
			self::$instance->doHooks();
		}

		return self::$instance;
	}
	private function doHooks() {
		add_action( 'wp_ajax_yaymail_send_mail', array( $this, 'sendTestMail' ) );
		add_action( 'wp_ajax_yaymail_install_plugin', array( $this, 'ajax_install_plugin' ) );
		add_action( 'wp_ajax_yaymail_save_mail', array( $this, 'saveTemplate' ) );
		add_action( 'wp_ajax_yaymail_parse_template', array( new Shortcodes(), 'templateParser' ) );
		add_action( 'wp_ajax_yaymail_copy_mail', array( $this, 'copyTemplate' ) );
		add_action( 'wp_ajax_yaymail_reset_template', array( $this, 'resetTemplate' ) );
		add_action( 'wp_ajax_yaymail_review', array( $this, 'reviewYayMail' ) );
		add_action( 'wp_ajax_yaymail_export_all_template', array( $this, 'exportAllTemplate' ) );
		add_action( 'wp_ajax_yaymail_import_template', array( $this, 'importAllTemplate' ) );
		add_action( 'wp_ajax_yaymail_enable_disable_template', array( $this, 'enableDisableTempalte' ) );
		add_action( 'wp_ajax_yaymail_general_setting', array( $this, 'generalSettings' ) );
		add_action( 'wp_ajax_yaymail_get_coupons', array( $this, 'yaymail_get_coupons' ) );
		add_action( 'wp_ajax_yaymail_get_products', array( $this, 'yaymail_get_products' ) );
		add_action( 'wp_ajax_yaymail_get_product_skus', array( $this, 'yaymail_get_product_skus' ) );
		add_action( 'wp_ajax_yaymail_get_products_by_ids', array( $this, 'yaymail_get_products_by_ids' ) );
		add_action( 'wp_ajax_yaymail_get_product_skus_by_ids', array( $this, 'yaymail_get_product_skus_by_ids' ) );
	}

	private function __construct() {}

	public function exportAllTemplate() {
		try {
			// 1. check nonce
			Helper::checkNonce();
			// 2. download
			$template_export = CustomPostType::getTemplateExport();
			$fileName        = 'yaymail_all-customize-email-templates_' . gmdate( 'm-d-Y' ) . '.json';
			header( 'Content-Type: application/json' );
			header( 'Content-Disposition: attachment; filename="' . $fileName . '";' );
			$response_object = array(
				'result'   => $template_export,
				'fileName' => $fileName,
				'mess'     => __( 'Export successfully.', 'yaymail' ),
			);
			wp_send_json_success( $response_object );
		} catch ( \Exception $ex ) {
			LogHelper::getMessageException( $ex, true );
		} catch ( \Error $ex ) {
			LogHelper::getMessageException( $ex, true );
		}

	}

	public static function getHtmlByElements( $postID, $args = array() ) {
		$updateElement        = new UpdateElement();
		$yaymail_elements     = get_post_meta( $postID, '_yaymail_elements', true );
		$yaymail_elements     = $updateElement->merge_new_props_to_elements( $yaymail_elements );
		$yaymail_settings     = get_option( 'yaymail_settings' );
		$emailBackgroundColor = get_post_meta( $postID, '_email_backgroundColor_settings', true ) ? get_post_meta( $postID, '_email_backgroundColor_settings', true ) : '#ECECEC';
		$general_attrs        = array( 'tableWidth' => str_replace( 'px', '', $yaymail_settings['container_width'] ) );
		$yaymail_template     = get_post_meta( $postID, '_yaymail_template', true );
		$html                 = '<!DOCTYPE html>
			<html lang="en">
			<head>
				<meta charset="UTF-8">
				<meta http-equiv="X-UA-Compatible" content="IE=edge">
				<meta name="viewport" content="width=device-width, initial-scale=1"/>
				<style>
				h1{ font-family:inherit;text-shadow:unset;text-align:inherit;}
				h2,h3{ font-family:inherit;color:inherit;text-align:inherit;}
				</style>
			</head><body>';
			$html            .= '<table
			style="background:' . esc_attr( $emailBackgroundColor ) . '"
			border="0"
			cellpadding="0"
			cellspacing="0"
			height="100%"
			width="100%"
			class="' . esc_attr( 'yaymail-template-' . $yaymail_template ) . '"
		  >';
		foreach ( $yaymail_elements as $key => $element ) {
			// add shortcode params
			$reg_pattern = '/\[([a-z0-9A-Z_]+)\]/';
			if ( isset( $element['settingRow']['content'] ) ) {
				$content      = $element['settingRow']['content'];
				$contentTitle = isset( $element['settingRow']['contentTitle'] ) ? $element['settingRow']['contentTitle'] : '';

				// Add $atts for content if has shortcode
				preg_match_all( $reg_pattern, $content, $result );
				if ( ! empty( $result[0] ) ) {
					foreach ( $result[0] as $key => $shortcode ) {
						$textcolor     = isset( $element['settingRow']['textColor'] ) ? ' textcolor=' . $element['settingRow']['textColor'] : '';
						$bordercolor   = isset( $element['settingRow']['borderColor'] ) ? ' bordercolor=' . $element['settingRow']['borderColor'] : '';
						$titlecolor    = isset( $element['settingRow']['titleColor'] ) ? ' titlecolor=' . $element['settingRow']['titleColor'] : '';
						$fontfamily    = isset( $element['settingRow']['family'] ) ? ' fontfamily=' . str_replace( ' ', '', str_replace( array( '\'', '"' ), '', $element['settingRow']['family'] ) ) : '';
						$newshortcode  = substr( $shortcode, 0, -1 );
						$newshortcode .= $textcolor . $bordercolor . $titlecolor . $fontfamily . ']';
						$content       = str_replace( $shortcode, $newshortcode, $content );
					}
					$element['settingRow']['content'] = $content;
				}
				// Add $atts for contentTitle if has shortcode
				if ( $contentTitle ) {
					preg_match_all( $reg_pattern, $contentTitle, $result );
					if ( ! empty( $result[0] ) ) {
						foreach ( $result[0] as $key => $shortcode ) {
							$textcolor     = isset( $element['settingRow']['textColor'] ) ? ' textcolor=' . $element['settingRow']['textColor'] : '';
							$bordercolor   = isset( $element['settingRow']['borderColor'] ) ? ' bordercolor=' . $element['settingRow']['borderColor'] : '';
							$titlecolor    = isset( $element['settingRow']['titleColor'] ) ? ' titlecolor=' . $element['settingRow']['titleColor'] : '';
							$fontfamily    = isset( $element['settingRow']['family'] ) ? ' fontfamily=' . str_replace( ' ', '', str_replace( array( '\'', '"' ), '', $element['settingRow']['family'] ) ) : '';
							$newshortcode  = substr( $shortcode, 0, -1 );
							$newshortcode .= $textcolor . $bordercolor . $titlecolor . $fontfamily . ']';
							$contentTitle  = str_replace( $shortcode, $newshortcode, $contentTitle );
						}
						$element['settingRow']['contentTitle'] = $contentTitle;
					}
				}

				// Add $atts for content of shipment tracking if has shortcode
				if ( '[yaymail_order_meta:_wc_shipment_tracking_items]' === $content ) {
					$shortcode                        = $content;
					$textcolor                        = isset( $element['settingRow']['textColor'] ) ? ' textcolor=' . $element['settingRow']['textColor'] : '';
					$bordercolor                      = isset( $element['settingRow']['borderColor'] ) ? ' bordercolor=' . $element['settingRow']['borderColor'] : '';
					$titlecolor                       = isset( $element['settingRow']['titleColor'] ) ? ' titlecolor=' . $element['settingRow']['titleColor'] : '';
					$fontfamily                       = isset( $element['settingRow']['family'] ) ? ' fontfamily=' . str_replace( ' ', '', str_replace( array( '\'', '"' ), '', $element['settingRow']['family'] ) ) : '';
					$newshortcode                     = substr( $shortcode, 0, -1 );
					$newshortcode                    .= $textcolor . $bordercolor . $titlecolor . $fontfamily . ']';
					$content                          = str_replace( $shortcode, $newshortcode, $content );
					$element['settingRow']['content'] = $content;
				}
			}
			ob_start();
			if ( isset( $element['settingRow']['arrConditionLogic'] ) ) {
				if ( ! empty( $element['settingRow']['arrConditionLogic'] ) ) {
					$conditional_Logic = apply_filters( 'yaymail_addon_for_conditional_logic', false, $args, $element['settingRow'] );
					if ( $conditional_Logic ) {
						do_action( 'Yaymail' . $element['type'], $args, $element['settingRow'], $general_attrs, $element['id'], $postID, $isInColumns = false );
					}
				} else {
					if ( 'OneColumn' === $element['type'] || 'TwoColumns' === $element['type'] || 'ThreeColumns' === $element['type'] || 'FourColumns' === $element['type'] ) {
						for ( $column = 1; $column <= 4; $column++ ) {
							if ( isset( $element['settingRow'][ 'column' . $column ] ) ) {
								foreach ( $element['settingRow'][ 'column' . $column ] as $column_key => $column_element ) {
									if ( isset( $column_element['settingRow']['arrConditionLogic'] ) && ! empty( $column_element['settingRow']['arrConditionLogic'] ) ) {
										$conditional_Logic = apply_filters( 'yaymail_addon_for_conditional_logic', false, $args, $column_element['settingRow'] );
										if ( ! $conditional_Logic ) {
											unset( $element['settingRow'][ 'column' . $column ][ $column_key ] );
										}
									}
								}
							}
						}
					}
					do_action( 'Yaymail' . $element['type'], $args, $element['settingRow'], $general_attrs, $element['id'], $postID, $isInColumns = false );
				}
			} else {
				do_action( 'Yaymail' . $element['type'], $args, $element['settingRow'], $general_attrs, $element['id'], $postID, $isInColumns = false );
			}
			$el_html = ob_get_clean();
			if ( '' !== $el_html ) {
				$html    .= '<tr><td>';
				$el_html .= '</tr></td>';
			}
			$html .= $el_html;
		}
		$html .= '</table></body></html>';
		return $html;
	}
	public function ajax_install_plugin() {
		try {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
			if ( ! wp_verify_nonce( $nonce, 'email-nonce' ) ) {
				wp_send_json_error( array( 'mess' => __( 'Nonce is invalid', 'yaymail' ) ) );
			} else {
				$installed = $this->pluginInstaller( 'yaysmtp' );
				if ( false === $installed ) {
					wp_send_json_error( array( 'message' => $installed ) );
				}

				try {
					$result = activate_plugin( 'yaysmtp/yay-smtp.php' );

					if ( is_wp_error( $result ) ) {
						throw new \Exception( $result->get_error_message() );
					}
					wp_send_json_success(
						array(
							'sendMailSucc' => $result,
							'mess'         => __(
								'Plugin installation successful.',
								'yaymail'
							),
						)
					);
				} catch ( \Exception $e ) {
					throw new \Exception( $e->getMessage() );
				}
			}
		} catch ( \Exception $ex ) {
			LogHelper::getMessageException( $ex, true );
		} catch ( \Error $ex ) {
			LogHelper::getMessageException( $ex, true );
		}
	}
	public function pluginInstaller( $slug ) {
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-ajax-upgrader-skin.php';
		require_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';

		$api      = plugins_api(
			'plugin_information',
			array(
				'slug'   => $slug,
				'fields' => array(
					'short_description' => false,
					'sections'          => false,
					'requires'          => false,
					'rating'            => false,
					'ratings'           => false,
					'downloaded'        => false,
					'last_updated'      => false,
					'added'             => false,
					'tags'              => false,
					'compatibility'     => false,
					'homepage'          => false,
					'donate_link'       => false,
				),
			)
		);
		$skin     = new \WP_Ajax_Upgrader_Skin();
		$upgrader = new \Plugin_Upgrader( $skin );
		try {
			$result = $upgrader->install( $api->download_link );

			if ( is_wp_error( $result ) ) {
				throw new \Exception( $result->get_error_message() );
			}

			return true;
		} catch ( \Exception $e ) {
			throw new \Exception( $e->getMessage() );
		}

		return false;
	}
	// html output of mail must to map with html output in single-mail-template.php
	public function sendTestMail() {
		try {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
			if ( ! wp_verify_nonce( $nonce, 'email-nonce' ) ) {
				wp_send_json_error( array( 'mess' => __( 'Nonce is invalid', 'yaymail' ) ) );
			} else {
				if ( isset( $_POST['order_id'] ) && isset( $_POST['template'] ) && isset( $_POST['email_address'] ) ) {
					// Helper::checkNonce();
					$template      = sanitize_text_field( $_POST['template'] );
					$email_address = sanitize_email( $_POST['email_address'] );
					// check email
					if ( ! is_email( $email_address ) ) {
						wp_send_json_error( array( 'mess' => __( 'Invalid email format!', 'yaymail' ) ) );
					}
					if ( CustomPostType::postIDByTemplate( $template ) ) {
						update_user_meta( get_current_user_id(), 'yaymail_default_email_test', $email_address );
						$customShortcode = new Shortcodes( $template, sanitize_text_field( $_POST['order_id'] ), false );
						if ( sanitize_text_field( $_POST['order_id'] ) !== 'sampleOrder' ) {
							$order_id = intval( sanitize_text_field( $_POST['order_id'] ) );
							$WC_order = new \WC_Order( $order_id );
						}
						$postID = CustomPostType::postIDByTemplate( $template );

						if ( in_array( $template, array( 'new_order', 'cancelled_order', 'failed_order' ) ) ) {
							$customShortcode->setOrderId( $order_id, true );
						} else {
							$customShortcode->setOrderId( $order_id, false );
						}

						$customShortcode->shortCodesOrderDefined();
						if ( isset( $WC_order ) ) {
							$html = self::getHtmlByElements( $postID, array( 'order' => $WC_order ) );
						} else {
							$html = self::getHtmlByElements( $postID, array( 'order' => 'SampleOrder' ) );
						}

						$html         = html_entity_decode( $html, ENT_QUOTES, 'UTF-8' );
						$headers      = "Content-Type: text/html\r\n";
						$sendMail     = \WC_Emails::instance();
						$subjectEmail = $this->getSubjectEmail( $sendMail, $template );
						// $email_admin = get_bloginfo('admin_email');
						if ( ! empty( $email_address ) ) {
							$sendMailSucc = $sendMail->send( $email_address, $subjectEmail, $html, $headers, array() );
							wp_send_json_success(
								array(
									'sendMailSucc' => $sendMailSucc,
									'mess'         => __(
										'Email has been sent.',
										'yaymail'
									),
								)
							);
						}
					} else {
						wp_send_json_error( array( 'mess' => __( 'Template not Exists!.', 'yaymail' ) ) );
					}
				}
			}
			wp_send_json_error( array( 'mess' => __( 'Error send mail!', 'yaymail' ) ) );
		} catch ( \Exception $ex ) {
			LogHelper::getMessageException( $ex, true );
		} catch ( \Error $ex ) {
			LogHelper::getMessageException( $ex, true );
		}

	}

	public function getSubjectEmail( $wc_emails, $template ) {
		$subject = __( 'Email Test', 'yaymail' );
		foreach ( $wc_emails->emails as $email => $item ) {
			if ( $item->id == $template ) {
				if ( 'customer_invoice' == $template ) {
					$subject = Helper::getCustomerInvoiceSubject( $wc_emails->emails[ $email ] );
					if ( ! empty( $subject ) ) {
						return $subject;
					}
				} elseif ( 'new_booking' == $template ) {
					$subject = Helper::getNewBookingSubject( $wc_emails->emails[ $email ] );
					if ( ! empty( $subject ) ) {
						return $subject;
					}
				} elseif ( 'customer_payment_retry' == $template ) {
					$subject = Helper::getNewBookingSubject( $wc_emails->emails[ $email ] );
					if ( ! empty( $subject ) ) {
						return $subject;
					}
				} elseif ( 'Dokan_Email_Booking_New' == $template ) {
					$subject = $wc_emails->emails[ $email ]->subject;
					if ( ! empty( $subject ) ) {
						return $subject;
					}
				} else {
					if ( ! empty( $wc_emails->emails[ $email ]->subject ) ) {
						$subject = $wc_emails->emails[ $email ]->subject;
						if ( ! empty( $subject ) ) {
							return $subject;
						}
					}
				}
			}
		}
		return $subject;
	}

	public function sanitize( $var ) {
		// Prevent XSS
		$list_elements = Helper::preventXSS( $var['emailContents'] );
		return wp_kses_post_deep( $list_elements );
	}

	public function saveTemplate() {
		try {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
			if ( ! wp_verify_nonce( $nonce, 'email-nonce' ) ) {
				wp_send_json_error( array( 'mess' => __( 'Nonce is invalid', 'yaymail' ) ) );
			} else {
				if ( isset( $_POST['template'] ) ) {
					// Helper::checkNonce();
					$emailBackgroundColor    = isset( $_POST['emailBackgroundColor'] ) ? sanitize_text_field( $_POST['emailBackgroundColor'] ) : 'rgb(236, 236, 236)';
					$emailTextLinkColor      = isset( $_POST['emailTextLinkColor'] ) ? sanitize_text_field( $_POST['emailTextLinkColor'] ) : '#7f54b3';
					$titleShipping           = isset( $_POST['titleShipping'] ) ? sanitize_text_field( $_POST['titleShipping'] ) : 'Shipping Address';
					$titleBilling            = isset( $_POST['titleBilling'] ) ? sanitize_text_field( $_POST['titleBilling'] ) : 'Billing Address';
					$orderTitle              = ( isset( $_POST['orderTitle'] ) && is_array( $_POST['orderTitle'] ) ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['orderTitle'] ) ) : array();
					$orderItemsDownloadTitle = ( isset( $_POST['orderItemsDownloadTitle'] ) && is_array( $_POST['orderItemsDownloadTitle'] ) ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['orderItemsDownloadTitle'] ) ) : array();
					$template                = sanitize_text_field( $_POST['template'] );
					$updateElement           = new UpdateElement();
					$setDefaultLogo          = isset( $_POST['setDefaultLogo'] ) ? 'true' == sanitize_text_field( $_POST['setDefaultLogo'] ) ? true : false : false;
					$setDefaultFooter        = isset( $_POST['setDefaultFooter'] ) ? 'true' == sanitize_text_field( $_POST['setDefaultFooter'] ) ? true : false : false;
					if ( isset( $_POST['emailContents'] ) ) {
						$emailContents = $this->sanitize( $_POST );
						$emailContents = $updateElement->merge_new_props_to_elements( $emailContents );
					} else {
						$emailContents = array();
					}
					foreach ( $emailContents as $key => $value ) {
						if ( 'TwoColumns' == $value['type'] || 'ThreeColumns' == $value['type'] || 'FourColumns' == $value['type'] ) {
							if ( ! array_key_exists( 'column1', $emailContents[ $key ]['settingRow'] ) ) {
								$emailContents[ $key ]['settingRow']['column1'] = array();
							}
							if ( ! array_key_exists( 'column2', $emailContents[ $key ]['settingRow'] ) ) {
								$emailContents[ $key ]['settingRow']['column2'] = array();
							}
							if ( ( 'ThreeColumns' == $value['type'] || 'FourColumns' == $value['type'] ) && ! array_key_exists( 'column3', $emailContents[ $key ]['settingRow'] ) ) {
								$emailContents[ $key ]['settingRow']['column3'] = array();
							}
							if ( 'FourColumns' == $value['type'] && ! array_key_exists( 'column4', $emailContents[ $key ]['settingRow'] ) ) {
								$emailContents[ $key ]['settingRow']['column4'] = array();
							}
						}
					}
					if ( CustomPostType::postIDByTemplate( $template ) ) {
						$postID = CustomPostType::postIDByTemplate( $template );

						if ( empty( $orderTitle ) ) {
							$orderTitle = Helper::OrderItemsTitle();
						}

						if ( empty( $orderItemsDownloadTitle ) ) {
							$orderItemsDownloadTitle = Helper::OrderItemsDownloadsTitle();
						}

						update_post_meta( $postID, '_yaymail_elements', $emailContents );
						update_post_meta( $postID, '_email_backgroundColor_settings', $emailBackgroundColor );
						update_post_meta( $postID, '_yaymail_email_textLinkColor_settings', $emailTextLinkColor );
						update_post_meta( $postID, '_email_title_shipping', $titleShipping );
						update_post_meta( $postID, '_email_title_billing', $titleBilling );
						update_post_meta( $postID, '_yaymail_email_order_item_title', $orderTitle );
						update_post_meta( $postID, '_yaymail_email_order_item_download_title', $orderItemsDownloadTitle );
						// Change default logo
						$default_logo = array(
							'set_default' => (bool) $setDefaultLogo,
						);
						if ( 'true' == $setDefaultLogo ) {
							$posts = CustomPostType::getListPostTemplate();
							foreach ( $emailContents as $key => $element ) {
								if ( 'Logo' == $element['type'] ) {
									$logoDefault = $element['settingRow'];
									break;
								}
							}

							if ( count( $posts ) > 0 && isset( $logoDefault ) ) {
								foreach ( $posts as $post ) {
									$yaymail_elements = get_post_meta( $post->ID, '_yaymail_elements', true );
									foreach ( $yaymail_elements as $key => $element ) {
										if ( 'Logo' == $element['type'] ) {
											$yaymail_elements[ $key ]['settingRow'] = wp_parse_args( $logoDefault, $yaymail_elements[ $key ]['settingRow'] );
										}
									}
									update_post_meta( $post->ID, '_yaymail_elements', $yaymail_elements );
								}
							}
						}
						update_option( 'yaymail_settings_default_logo', $default_logo );
						// Change default footer
						$default_footer = array(
							'set_default' => (bool) $setDefaultFooter,
						);
						if ( 'true' == $setDefaultFooter ) {
							$posts = CustomPostType::getListPostTemplate();
							foreach ( $emailContents as $key => $element ) {
								if ( 'ElementText' == $element['type'] && 'Footer' == $element['nameElement'] ) {
									$footerDefault = $element['settingRow'];
									break;
								}
							}

							if ( count( $posts ) > 0 && isset( $footerDefault ) ) {
								foreach ( $posts as $post ) {
									$yaymail_elements = get_post_meta( $post->ID, '_yaymail_elements', true );
									foreach ( $yaymail_elements as $key => $element ) {
										if ( 'ElementText' == $element['type'] && 'Footer' == $element['nameElement'] ) {
											$yaymail_elements[ $key ]['settingRow'] = wp_parse_args( $footerDefault, $yaymail_elements[ $key ]['settingRow'] );
										}
									}
									update_post_meta( $post->ID, '_yaymail_elements', $yaymail_elements );
								}
							}
						}
						update_option( 'yaymail_settings_default_footer', $default_footer );

						wp_send_json_success(
							array(
								'mess'                    => __( 'Email has been saved.', 'yaymail' ),
								'orderTitle'              => $orderTitle,
								'orderItemsDownloadTitle' => $orderItemsDownloadTitle,
							)
						);
					} else {
						wp_send_json_error( array( 'mess' => __( 'Template not Exists!.', 'yaymail' ) ) );
					}
				}
				wp_send_json_error( array( 'mess' => __( 'Error save data.', 'yaymail' ) ) );
			}
		} catch ( \Exception $ex ) {
			LogHelper::getMessageException( $ex, true );
		} catch ( \Error $ex ) {
			LogHelper::getMessageException( $ex, true );
		}

	}
	public function copyTemplate() {
		try {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
			if ( ! wp_verify_nonce( $nonce, 'email-nonce' ) ) {
				wp_send_json_error( array( 'mess' => __( 'Nonce is invalid', 'yaymail' ) ) );
			} else {
				if ( isset( $_POST['copy_to'] ) && isset( $_POST['copy_from'] ) ) {
					Helper::checkNonce();
					$copyTo   = sanitize_text_field( $_POST['copy_to'] );
					$copyFrom = sanitize_text_field( $_POST['copy_from'] );
					if ( CustomPostType::postIDByTemplate( $copyFrom ) ) {
						$postID                  = CustomPostType::postIDByTemplate( $copyFrom );
						$emailContentsFrom       = get_post_meta( $postID, '_yaymail_elements', true );
						$emailBackgroundColor    = get_post_meta( $postID, '_email_backgroundColor_settings', true ) ? get_post_meta( $postID, '_email_backgroundColor_settings', true ) : 'rgb(236, 236, 236)';
						$emailTextLinkColor      = get_post_meta( $postID, '_yaymail_email_textLinkColor_settings', true ) ? get_post_meta( $postID, '_yaymail_email_textLinkColor_settings', true ) : '#7f54b3';
						$titleShipping           = isset( $_POST['titleShipping'] ) ? sanitize_text_field( $_POST['titleShipping'] ) : 'Shipping Address';
						$titleBilling            = isset( $_POST['titleBilling'] ) ? sanitize_text_field( $_POST['titleBilling'] ) : 'Billing Address';
						$orderTitle              = isset( $_POST['orderTitle'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['orderTitle'] ) ) : array();
						$orderItemsDownloadTitle = ( isset( $_POST['orderItemsDownloadTitle'] ) && is_array( $_POST['orderItemsDownloadTitle'] ) ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['orderItemsDownloadTitle'] ) ) : array();
						if ( CustomPostType::postIDByTemplate( $copyTo ) ) {
							$idTo = CustomPostType::postIDByTemplate( $copyTo );
							update_post_meta( $idTo, '_yaymail_elements', $emailContentsFrom );
							update_post_meta( $idTo, '_email_backgroundColor_settings', $emailBackgroundColor );
							update_post_meta( $idTo, '_yaymail_email_textLinkColor_settings', $emailTextLinkColor );
							update_post_meta( $idTo, '_email_title_shipping', $titleShipping );
							update_post_meta( $idTo, '_email_title_billing', $titleBilling );
							update_post_meta( $idTo, '_yaymail_email_order_item_title', $orderTitle );
							update_post_meta( $idTo, '_yaymail_email_order_item_download_title', $orderItemsDownloadTitle );
							wp_send_json_success(
								array(
									'mess' => __( 'Copied Template successfully.', 'yaymail' ),
									'data' => $emailContentsFrom,
								)
							);
						} else {
							wp_send_json_error( array( 'mess' => __( 'Template not Exists!.', 'yaymail' ) ) );
						}
					} else {
						wp_send_json_error( array( 'mess' => __( 'Template not Exists!.', 'yaymail' ) ) );
					}
				}
				wp_send_json_error( array( 'mess' => __( 'Error save data.', 'yaymail' ) ) );
			}
		} catch ( \Exception $ex ) {
			LogHelper::getMessageException( $ex, true );
		} catch ( \Error $ex ) {
			LogHelper::getMessageException( $ex, true );
		}

	}

	public function reviewYayMail() {
		try {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
			if ( ! wp_verify_nonce( $nonce, 'email-nonce' ) ) {
				wp_send_json_error( array( 'mess' => __( 'Nonce is invalid', 'yaymail' ) ) );
			} else {
				if ( isset( $_POST['review'] ) ) {
					$yaymail_review = update_option( 'yaymail_review', true );
					wp_send_json_success(
						array(
							'value' => $yaymail_review,
						)
					);
				}
				wp_send_json_error( array( 'mess' => __( 'Error Reset Template!', 'yaymail' ) ) );
			}
		} catch ( \Exception $ex ) {
			LogHelper::getMessageException( $ex, true );
		} catch ( \Error $ex ) {
			LogHelper::getMessageException( $ex, true );
		}
	}

	public function resetTemplate() {
		try {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
			if ( ! wp_verify_nonce( $nonce, 'email-nonce' ) ) {
				wp_send_json_error( array( 'mess' => __( 'Nonce is invalid', 'yaymail' ) ) );
			} else {
				if ( isset( $_POST['template'] ) ) {
					// Helper::checkNonce();
					$reset                    = sanitize_text_field( $_POST['template'] );
					$templateEmail            = \YayMail\Templates\Templates::getInstance();
					$templates                = $templateEmail::getList();
					$orderItemsTitle          = Helper::OrderItemsTitle();
					$orderItemsDownloadsTitle = Helper::OrderItemsDownloadsTitle();
					if ( 'all' == $reset ) {
						foreach ( $templates as $key => $template ) {
							if ( CustomPostType::postIDByTemplate( $key ) ) {
								$postID = CustomPostType::postIDByTemplate( $key );
								update_post_meta( $postID, '_yaymail_elements', json_decode( $template['elements'], true ) );
								update_post_meta( $postID, '_email_backgroundColor_settings', 'rgb(236, 236, 236)' );
								update_post_meta( $postID, '_yaymail_email_textLinkColor_settings', '#7f54b3' );
								update_post_meta( $postID, '_email_title_shipping', __( 'Shipping Address', 'yaymail' ) );
								update_post_meta( $postID, '_email_title_billing', __( 'Billing Address', 'yaymail' ) );
								update_post_meta( $postID, '_yaymail_email_order_item_title', $orderItemsTitle );
								update_post_meta( $postID, '_yaymail_email_order_item_download_title', $orderItemsDownloadsTitle );
							}
						}

						if ( get_option( 'yaymail_settings' ) ) {
							$yaymail_settings                    = get_option( 'yaymail_settings' );
							$yaymail_settings['container_width'] = '605px';
							$yaymail_settings['direction_rtl']   = 'ltr';
							update_option( 'yaymail_settings', $yaymail_settings );
						}

						wp_send_json_success( array( 'mess' => __( 'Template reset successfully.', 'yaymail' ) ) );
					} else {
						if ( CustomPostType::postIDByTemplate( $reset ) && isset( $templates[ $reset ] ) ) {
							$postID = CustomPostType::postIDByTemplate( $reset );
							update_post_meta( $postID, '_yaymail_elements', json_decode( $templates[ $reset ]['elements'], true ) );
							update_post_meta( $postID, '_email_backgroundColor_settings', 'rgb(236, 236, 236)' );
							update_post_meta( $postID, '_yaymail_email_textLinkColor_settings', '#7f54b3' );
							update_post_meta( $postID, '_email_title_shipping', __( 'Shipping Address', 'yaymail' ) );
							update_post_meta( $postID, '_email_title_billing', __( 'Billing Address', 'yaymail' ) );
							update_post_meta( $postID, '_yaymail_email_order_item_title', $orderItemsTitle );
							update_post_meta( $postID, '_yaymail_email_order_item_download_title', $orderItemsDownloadsTitle );
							wp_send_json_success( array( 'mess' => __( 'Template reset successfully.', 'yaymail' ) ) );
						} else {
							wp_send_json_error( array( 'mess' => __( 'Template not Exists!.', 'yaymail' ) ) );
						}
					}
				}
				wp_send_json_error( array( 'mess' => __( 'Error Reset Template!', 'yaymail' ) ) );
			}
		} catch ( \Exception $ex ) {
			LogHelper::getMessageException( $ex, true );
		} catch ( \Error $ex ) {
			LogHelper::getMessageException( $ex, true );
		}

	}

	public function importAllTemplate() {
		try {
			Helper::checkNonce();
			if ( isset( $_FILES['file']['type'] ) ) {
				if ( 'application/json' == $_FILES['file']['type'] ) {
					if ( ! empty( $_FILES['file']['tmp_name'] ) ) {
						$fileJson = sanitize_text_field( $_FILES['file']['tmp_name'] );
						global $wp_filesystem;

						if ( empty( $wp_filesystem ) ) {
							require_once ABSPATH . '/wp-admin/includes/file.php';
							WP_Filesystem();
						}
						$data        = $wp_filesystem->get_contents( $fileJson );
						$data        = json_decode( $data, true );
						$dataImports = $data['yaymailTemplateExport'];

						$versionOld     = $data['yaymail_version'];
						$versionCurrent = YAYMAIL_VERSION;

						/*
						check key in settingRow whether or not it exists.
						note: case when add setting row for element.
						 */
						if ( $versionOld != $versionCurrent ) {
							$element             = new DefaultElement();
							$defaultDataElements = $element->defaultDataElement;

							foreach ( $defaultDataElements as $defaultelement ) {

								foreach ( $dataImports as $keyTemplate => $templateImport ) {
									foreach ( $templateImport['_yaymail_elements'] as $keyElem => $elemImport ) {
										if ( $defaultelement['type'] == $elemImport['type'] ) {
											/*
											@@@ add key default for element
											*/
											$keyEleDefaus = array();
											$keyEleDefaus = array_diff_key( $defaultelement, $elemImport );
											if ( count( $keyEleDefaus ) > 0 ) {
														$dataImports[ $keyTemplate ]['_yaymail_elements'][ $keyElem ] = array_merge( $elemImport, $keyEleDefaus );
											}

											/*
											add key default for setting row
											note: when add a field in setting row
											*/
											$propSettings    = array();
											$propSettings    = array_diff_key( $defaultelement['settingRow'], $elemImport['settingRow'] );
											$lenPropSettings = count( $propSettings );
											if ( $lenPropSettings > 0 ) {
												$result = array();
												$result = array_merge( $elemImport['settingRow'], $propSettings );
												$dataImports[ $keyTemplate ]['_yaymail_elements'][ $keyElem ]['settingRow'] = $result;
											}

											/*
											remove Key not needed for setting row
											note: when deleting a field in setting row
											*/
										}
									}
								}
							}
						}
						$flag = false;
						if ( count( $dataImports ) > 0 ) {
							foreach ( $dataImports as $key => $value ) {
								if ( isset( $value['_yaymail_elements'] ) ) {
										$template          = $value['_yaymail_template'];
										$template_language = $value['_yaymail_template_language'];
									if ( CustomPostType::postIDByTemplateLanguage( $template, $template_language ) ) {
										$pID = CustomPostType::postIDByTemplateLanguage( $template, $template_language );
										update_post_meta( $pID, '_yaymail_elements', $value['_yaymail_elements'] );
									} else {
										$array = array(
											'mess'        => '',
											'post_date'   => current_time( 'Y-m-d H:i:s' ),
											'post_type'   => 'yaymail_template',
											'post_status' => 'publish',
											'_yaymail_template' => $template,
											'_yaymail_elements' => $value['_yaymail_elements'],

										);
										if ( '' !== $value['_yaymail_template_language'] ) {
											$array['_yaymail_template_language'] = $value['_yaymail_template_language'];
										}
										$insert = CustomPostType::insert( $array );
									}
									$flag = true;
								}
							}
						}
						if ( ! $flag ) {
							  wp_send_json_error( array( 'mess' => __( 'Import Failed.', 'yaymail' ) ) );
						}
						wp_send_json_success( array( 'mess' => __( 'Imported successfully.', 'yaymail' ) ) );
					} else {
						wp_send_json_error( array( 'mess' => __( 'File not found.', 'yaymail' ) ) );
					}
				} else {
					wp_send_json_error( array( 'mess' => __( 'File not correct format.', 'yaymail' ) ) );
				}
			}
			wp_send_json_error( array( 'mess' => __( 'Please upload 1 file to import.', 'yaymail' ) ) );
		} catch ( \Exception $ex ) {
			LogHelper::getMessageException( $ex, true );
		} catch ( \Error $ex ) {
			LogHelper::getMessageException( $ex, true );
		}

	}
	public function enableDisableTempalte() {
		try {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
			if ( ! wp_verify_nonce( $nonce, 'email-nonce' ) ) {
				wp_send_json_error( array( 'mess' => __( 'Nonce is invalid', 'yaymail' ) ) );
			} else {
				if ( isset( $_POST['settings'] ) ) {
					// Helper::checkNonce();
					$settingDefault = CustomPostType::templateEnableDisable();
					$listTemplates  = ! empty( $settingDefault ) ? array_keys( $settingDefault ) : array();
					$settingCurrent = array_map( 'sanitize_text_field', wp_unslash( $_POST['settings'] ) );

					if ( ! empty( $listTemplates ) ) {
						foreach ( $settingCurrent as $key => $value ) {
							if ( in_array( $key, $listTemplates ) ) {
								update_post_meta( $settingDefault[ $key ]['post_id'], '_yaymail_status', $value );
							}
						}
					}
					wp_send_json_success( array( 'mess' => __( 'Settings saved.', 'yaymail' ) ) );
				}
				wp_send_json_error( array( 'mess' => __( 'Settings Failed!.', 'yaymail' ) ) );
			}
		} catch ( \Exception $ex ) {
			LogHelper::getMessageException( $ex, true );
		} catch ( \Error $ex ) {
			LogHelper::getMessageException( $ex, true );
		}

	}

	public function generalSettings() {
		try {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
			if ( ! wp_verify_nonce( $nonce, 'email-nonce' ) ) {
				wp_send_json_error( array( 'mess' => __( 'Nonce is invalid', 'yaymail' ) ) );
			} else {
				if ( isset( $_POST['settings'] ) ) {
					$setting           = array_map( 'sanitize_text_field', wp_unslash( $_POST['settings'] ) );
					$yaymail_direction = $setting['direction_rtl'];
					isset( $yaymail_direction ) ? update_option( 'yaymail_direction', $yaymail_direction ) : update_option( 'yaymail_direction', 'ltr' );
					$setting['custom_css'] = wp_kses_post( isset( $_POST['settings']['custom_css'] ) ? $_POST['settings']['custom_css'] : '' );
					update_option( 'yaymail_settings', $setting );
					wp_send_json_success( array( 'mess' => __( 'Settings saved.', 'yaymail' ) ) );
				}
				wp_send_json_error( array( 'mess' => __( 'Settings Failed!.', 'yaymail' ) ) );
			}
		} catch ( \Exception $ex ) {
			LogHelper::getMessageException( $ex, true );
		} catch ( \Error $ex ) {
			LogHelper::getMessageException( $ex, true );
		}

	}

	public function yaymail_get_coupons() {
		try {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
			if ( ! wp_verify_nonce( $nonce, 'email-nonce' ) ) {
				wp_send_json_error( array( 'mess' => __( 'Nonce is invalid', 'yaymail' ) ) );
			} else {
				$size          = 20;
				$num           = isset( $_POST['num'] ) ? sanitize_text_field( $_POST['num'] ) : 1;
				$offset        = ( +$num - 1 ) * $size;
				$limit         = $size + 1;
				$search_string = isset( $_POST['searchString'] ) ? sanitize_text_field( $_POST['searchString'] ) : null;
				$args          = array(
					'post_type'      => 'shop_coupon',
					'post_status'    => 'publish',
					'posts_per_page' => $limit,
					'orderby'        => 'post_title',
					'order'          => 'ASC',
					'offset'         => $offset,
				);

				if ( $search_string ) {
					$args['s'] = $search_string;
				}

				$query        = new \WP_Query( $args );
				$coupon_codes = wp_list_pluck( $query->posts, 'post_title' );

				$has_more = false;
				if ( count( $coupon_codes ) == ( $limit ) ) {
					$has_more = true;
					array_splice( $coupon_codes, -1 );
				}

				$response_object = array(
					'mess'         => __( 'get successfully.', 'yaymail' ),
					'couponCodes'  => str_replace( '-', ' ', $coupon_codes ),
					'hasMore'      => $has_more,
					'num'          => $num,
					'searchString' => $search_string,
				);
				wp_send_json_success( $response_object );
			}
		} catch ( \Exception $ex ) {
			LogHelper::getMessageException( $ex, true );
		} catch ( \Error $ex ) {
			LogHelper::getMessageException( $ex, true );
		}
	}
	public function yaymail_get_products() {
		try {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
			if ( ! wp_verify_nonce( $nonce, 'email-nonce' ) ) {
				wp_send_json_error( array( 'mess' => __( 'Nonce is invalid', 'yaymail' ) ) );
			} else {
				$size          = 20;
				$num           = isset( $_POST['num'] ) ? sanitize_text_field( $_POST['num'] ) : 1;
				$offset        = ( +$num - 1 ) * $size;
				$search_string = isset( $_POST['searchString'] ) ? sanitize_text_field( $_POST['searchString'] ) : null;

				$args = array(
					'post_type'      => 'product',
					'post_status'    => 'publish',
					'posts_per_page' => $size + 1,
					'offset'         => $offset,
					's'              => $search_string,
					'orderby'        => 'post_title',
					'order'          => 'ASC',
				);

				$products_query = new \WP_Query( $args );
				$products       = array();
				foreach ( $products_query->posts as $post ) {
					$product       = new stdClass();
					$product->id   = (string) $post->ID;
					$product->name = get_the_title( $post );
					$products[]    = $product;
				}

				$has_more = false;
				if ( count( $products ) == ( $size + 1 ) ) {
					$has_more = true;
					array_splice( $products, -1 );
				}

				$response_object = array(
					'mess'         => __( 'get successfully.', 'yaymail' ),
					'products'     => $products,
					'hasMore'      => $has_more,
					'num'          => $num,
					'searchString' => $search_string,
				);
				wp_send_json_success( $response_object );
			}
		} catch ( \Exception $ex ) {
			LogHelper::getMessageException( $ex, true );
		} catch ( \Error $ex ) {
			LogHelper::getMessageException( $ex, true );
		}
	}

	public function yaymail_get_product_skus() {
		try {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
			if ( ! wp_verify_nonce( $nonce, 'email-nonce' ) ) {
				wp_send_json_error( array( 'mess' => __( 'Nonce is invalid', 'yaymail' ) ) );
			} else {
				$size          = 20;
				$num           = isset( $_POST['num'] ) ? sanitize_text_field( $_POST['num'] ) : 1;
				$offset        = ( +$num - 1 ) * $size;
				$search_string = isset( $_POST['searchString'] ) ? sanitize_text_field( $_POST['searchString'] ) : null;

				$args           = array(
					'post_type'      => array( 'product', 'product_variation' ),
					'post_status'    => 'publish',
					'meta_key'       => '_sku',
					'orderby'        => 'meta_value',
					'order'          => 'ASC',
					'posts_per_page' => $size + 1,
					'offset'         => $offset,

					// this is a custom argument, used to search for either meta_value or post_title
					'_meta_or_title' => $search_string,
					'meta_query'     => array(
						array(
							'key'     => '_sku',
							'value'   => $search_string,
							'compare' => 'LIKE',
						),
						array(
							'key'     => '_sku',
							'compare' => 'DISTINCT',
						),
					),
				);
				$products_query = new \WP_Query( $args );

				$product_skus = array();

				if ( $products_query->have_posts() ) {
					while ( $products_query->have_posts() ) {
						$products_query->the_post();
						$id = get_the_ID();

						$sku_value = get_post_meta( $id, '_sku', true );
						$name      = get_the_title();
						if ( ! empty( $sku_value ) ) {
							$product_skus[] = array(
								'id'    => (string) $id,
								'name'  => $name,
								'sku'   => $sku_value,
							);
						}
					}
					wp_reset_postdata();
				}

				$has_more = false;
				if ( count( $product_skus ) == ( $size + 1 ) ) {
					$has_more = true;
					array_splice( $product_skus, -1 );
				}

				$response_object = array(
					'mess'         => __( 'get successfully.', 'yaymail' ),
					'productSkus'  => $product_skus,
					'hasMore'      => $has_more,
					'num'          => $num,
					'searchString' => $search_string,
				);
				wp_send_json_success( $response_object );
			}
		} catch ( \Exception $ex ) {
			LogHelper::getMessageException( $ex, true );
		} catch ( \Error $ex ) {
			LogHelper::getMessageException( $ex, true );
		}
	}

	public function yaymail_get_products_by_ids() {
		try {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
			if ( ! wp_verify_nonce( $nonce, 'email-nonce' ) ) {
				wp_send_json_error( array( 'mess' => __( 'Nonce is invalid', 'yaymail' ) ) );
			} else {
				$ids = isset( $_POST['ids'] ) ? array_map( 'sanitize_text_field', $_POST['ids'] ) : null;

				if ( null == $ids ) {
					wp_send_json_error(
						array(
							'mess' => 'ID list is empty',
						)
					);
					return;
				}

				$args = array(
					'post_type'      => 'product',
					'post_status'    => 'publish',
					'post__in'       => $ids,
					'posts_per_page' => -1, // Retrieve all matching products
				);

				$products_query = new \WP_Query( $args );
				$products       = array();
				foreach ( $products_query->posts as $post ) {
					$product       = new stdClass();
					$product->id   = (string) $post->ID;
					$product->name = get_the_title( $post );
					$products[]    = $product;
				}

				$response_object = array(
					'mess'     => __( 'get successfully.', 'yaymail' ),
					'products' => $products,
				);
				wp_send_json_success( $response_object );
			}
		} catch ( \Exception $ex ) {
			LogHelper::getMessageException( $ex, true );
		} catch ( \Error $ex ) {
			LogHelper::getMessageException( $ex, true );
		}
	}

	public function yaymail_get_product_skus_by_ids() {
		try {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
			if ( ! wp_verify_nonce( $nonce, 'email-nonce' ) ) {
				wp_send_json_error( array( 'mess' => __( 'Nonce is invalid', 'yaymail' ) ) );
			} else {
				$ids = isset( $_POST['ids'] ) ? array_map( 'sanitize_text_field', $_POST['ids'] ) : null;

				if ( null == $ids ) {
					wp_send_json_error(
						array(
							'mess' => 'ID list is empty',
						)
					);
					return;
				}

				$args = array(
					'post_type'      => array( 'product', 'product_variation' ),
					'post_status'    => 'publish',
					'meta_query'     => array(
						array(
							'key'     => '_sku',
							'compare' => 'DISTINCT',
						),
					),
					// 'meta_key'       => '_sku',
					'post__in'       => $ids,
					'posts_per_page' => -1, // Retrieve all matching products
				);

				$products_query = new \WP_Query( $args );
				$product_skus   = array();
				if ( $products_query->have_posts() ) {
					while ( $products_query->have_posts() ) {
						$products_query->the_post();
						$id = get_the_ID();

						$sku_value = get_post_meta( $id, '_sku', true );
						$name      = get_the_title();
						if ( ! empty( $sku_value ) ) {
							$product_skus[] = array(
								'id'    => (string) $id,
								'name'  => $name,
								'sku'   => $sku_value,
							);
						}
					}
					wp_reset_postdata();
				}

				$response_object = array(
					'mess'     => __( 'get successfully.', 'yaymail' ),
					'productSkus' => $product_skus,
				);
				wp_send_json_success( $response_object );
			}
		} catch ( \Exception $ex ) {
			LogHelper::getMessageException( $ex, true );
		} catch ( \Error $ex ) {
			LogHelper::getMessageException( $ex, true );
		}
	}

}
