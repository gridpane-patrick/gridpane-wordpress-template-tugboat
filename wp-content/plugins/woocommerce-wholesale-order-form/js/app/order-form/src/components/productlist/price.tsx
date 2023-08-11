import React from 'react';

interface ProductPriceState { }

interface ProductPriceProps {
    product: any,
    selectedVariations: any,
    variations: any
}

class ProductPrice extends React.Component<ProductPriceProps, ProductPriceState> {

    setPriceColumn(product: any, selectedVariations: any, variations: any) {

        if (product.type === 'simple' || product.type === 'variation')
            return <div dangerouslySetInnerHTML={{ __html: product.price_html }}></div>;
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

            if (variation && variation.price !== undefined)
                return <div dangerouslySetInnerHTML={{ __html: variation.price }}></div>;
            else
                return <div dangerouslySetInnerHTML={{ __html: product.price_html }}></div>;

        }

    }

    render() {

        const { product, selectedVariations, variations } = this.props;

        return (
            <div>{this.setPriceColumn(product, selectedVariations, variations)}</div>
        );

    }

}

export default ProductPrice;