import React from 'react';
import { InputNumber } from 'antd';

interface ProductQuantityState { }

interface ProductQuantityProps {
    product: any,
    updateQuantityState: any
}

class ProductQuantity extends React.Component<ProductQuantityProps, ProductQuantityState> {

    render() {

        const { product } = this.props;

        return (
            <div><InputNumber min={1} defaultValue={1} onChange={(quantity: any) => this.props.updateQuantityState(product, quantity)} /></div>
        );

    }

}

export default ProductQuantity;