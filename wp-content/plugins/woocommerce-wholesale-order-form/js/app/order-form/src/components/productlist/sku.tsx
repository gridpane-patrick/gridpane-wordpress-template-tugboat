import React from 'react';

interface ProductSKUState { }

interface ProductSKUProps {
    product: any,
    selectedVariations: any,
    variations: any
}

class ProductSKU extends React.Component<ProductSKUProps, ProductSKUState> {

    setSKUColumn(product: any, selectedVariations: any, variations: any) {

        if (product.type === 'simple' || product.type === 'variation')
            return product.sku;
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

            if (variation && typeof variation.sku !== 'undefined')
                return variation.sku;
            else if (typeof product.sku !== 'undefined')
                return product.sku;

        }

    }

    render() {

        const { product, selectedVariations, variations } = this.props;

        return (
            <div>{this.setSKUColumn(product, selectedVariations, variations)}</div>
        );

    }

}

export default ProductSKU;