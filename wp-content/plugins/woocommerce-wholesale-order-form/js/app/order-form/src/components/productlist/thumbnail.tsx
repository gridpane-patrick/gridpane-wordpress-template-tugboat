import React from 'react';
import { Avatar } from 'antd';

interface ProductThumbnailState { }

interface ProductThumbnailProps {
    product: any,
    appSettings: any,
    displayModal: any
}

declare var Options: any;

class ProductThumbnail extends React.Component<ProductThumbnailProps, ProductThumbnailState> {

    displayPopup() {
        const show_popup = this.props.appSettings.wwof_general_display_product_details_on_popup;
        return show_popup === 'yes' ? true : false;
    }

    productImage(product: any, appSettings: any) {

        const placeholder = Options.site_url + '/wp-content/uploads/woocommerce-placeholder-300x300.png';
        const thumbnail_size = this.props.appSettings.wwof_general_product_thumbnail_image_size;
        const images = product.images;

        if (this.displayPopup()) {
            if (typeof images[0] !== 'undefined')
                return <a href="/#" rel="noopener noreferrer" onClick={(e: any) => { e.preventDefault(); this.props.displayModal(product) }}><Avatar src={images[0]['src']} shape="square" style={{ width: thumbnail_size.width + 'px', height: thumbnail_size.height + 'px' }} /></a>;
            else
                return <a href="/#" rel="noopener noreferrer" onClick={(e: any) => { e.preventDefault(); this.props.displayModal(product) }}><Avatar src={placeholder} shape="square" style={{ width: thumbnail_size.width + 'px', height: thumbnail_size.height + 'px' }} /></a>;
        } else {
            if (typeof images[0] !== 'undefined')
                return <a href={product.permalink} target="_blank" rel="noopener noreferrer"><Avatar src={images[0]['src']} shape="square" style={{ width: thumbnail_size.width + 'px', height: thumbnail_size.height + 'px' }} /></a>;
            else
                return <a href={product.permalink} target="_blank" rel="noopener noreferrer"><Avatar src={placeholder} shape="square" style={{ width: thumbnail_size.width + 'px', height: thumbnail_size.height + 'px' }} /></a>;
        }

    }

    render() {

        const { product, appSettings } = this.props;

        return (
            <div>{this.productImage(product, appSettings)}</div>
        );

    }

}

export default ProductThumbnail;