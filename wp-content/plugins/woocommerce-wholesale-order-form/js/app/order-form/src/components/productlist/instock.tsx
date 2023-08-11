import React from 'react';
import { Icon } from 'antd';

interface ProductInstockState { }

interface ProductInstockProps {
    product: any,
    selectedVariations: any,
    variations: any
}

class ProductInstock extends React.Component<ProductInstockProps, ProductInstockState> {

    setInstockColumn(product: any, selectedVariations: any, variations: any) {

        if (product.type === 'simple' || product.type === 'variation')
            return product.stock_quantity !== null ? <p><Icon type="inbox" style={{ fontSize: '20px', color: '#08c', marginRight: '10px' }} />{product.stock_quantity}</p> : '';
        else if (product.type === 'variable') {

            const selected_variation = selectedVariations.find((data: any) => {
                return product.id === data.product_id;
            });

            let variation = [];

            if (selected_variation) {
                variation = variations[product.id].find((variation: any) => {
                    return variation.id === selected_variation.variation_id;
                });
            }

            if (variation && variation.stock_quantity !== undefined && variation.stock_quantity !== null)
                return <p><Icon type="inbox" style={{ fontSize: '20px', color: '#08c', marginRight: '10px' }} />{variation.stock_quantity}</p>;
            else
                return product.stock_quantity !== null ? <p><Icon type="inbox" style={{ fontSize: '20px', color: '#08c', marginRight: '10px' }} />{product.stock_quantity}</p> : '';

        }

    }

    render() {

        const { product, selectedVariations, variations } = this.props;

        return (
            <div>{this.setInstockColumn(product, selectedVariations, variations)}</div>
        );

    }

}

export default ProductInstock;