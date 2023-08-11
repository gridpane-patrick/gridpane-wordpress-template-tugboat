import React from 'react';
import ProductSKU from './sku';
import ProductThumbnail from './thumbnail';
import ProductPrice from './price';
import ProductInstock from './instock';
import ProductQuantity from './quantity';
import Product from './product';
import ProductModal from './modal';

import { Table, Button, Icon, notification, List, Spin } from 'antd';
import axios from 'axios';
import './styles.scss';

interface ProductlistState {
    quantity: any,
    selectedProducts: any,
    selectedVariations: any,
    columns: any,
    showModal: boolean,
    openedProduct: any
}

interface ProductlistProps {
    products: any,
    variations: any,
    appSettings: any,
    wholesaleRole: string,
    activePage: number,
    totalPage: number,
    fetchProducts: any,
    handleAppStateUpdate: any,
    totalProducts: number,
    fetching: boolean,
    cartURL: string
}

declare var Options: any;

class Productlist extends React.Component<ProductlistProps, ProductlistState> {
    constructor(props: any) {
        super(props);
        this.state = {
            quantity: [],
            selectedProducts: [],
            selectedVariations: [],
            columns: [
                {
                    title: 'Product',
                    dataIndex: 'product',
                    key: 'product',
                    sorter: true
                },
                {
                    title: 'Price',
                    dataIndex: 'price',
                    key: 'price'
                },
                {
                    title: 'Quantity',
                    dataIndex: 'quantity',
                    key: 'quantity',
                }
            ],
            showModal: false,
            openedProduct: ''
        };
    }

    componentDidUpdate() {
        this.showSKU();
        this.showInStockColumn();
        this.showAddToCartPerRow();
        this.showThumbnail();
    }

    displayModal(product: any) {

        this.setState({ showModal: true, openedProduct: product });

    }

    updateQuantityState(product: any, quantity: any) {

        let productQuantity = this.state.quantity;

        const index = productQuantity.findIndex((data: any) => {
            return product.id === data.product_id;
        });

        if (index < 0) {
            productQuantity.push({
                product_id: product.id,
                quantity: quantity
            });
        } else {
            productQuantity[index] = {
                product_id: product.id,
                quantity: quantity
            }
        }

        this.setState({ quantity: productQuantity });

        // Update selected products
        let selectedProducts = this.state.selectedProducts;
        const product_index = selectedProducts.findIndex((data: any) => {
            return product.id === data.productID;
        });

        if (product_index >= 0) {
            selectedProducts[product_index] = {
                'productTitle': selectedProducts[product_index].productTitle,
                'productType': selectedProducts[product_index].productType,
                'productID': selectedProducts[product_index].productID,
                'variationID': selectedProducts[product_index].variationID,
                'quantity': quantity
            }
        }

        this.setState({ selectedProducts });

    }

    updateselectedVariationsState(product: any, variation_id: any) {

        let selectedVariations = this.state.selectedVariations;

        if (variation_id) {

            const index = selectedVariations.findIndex((data: any) => {
                return product.id === data.product_id;
            });

            const variations = this.props.variations[product.id].find((variation: any) => {
                return variation.id === variation_id;
            });

            const name = variations.attributes.map((attributes: any) => {
                return attributes.name + ' : ' + attributes.option;
            });

            if (index < 0) {
                selectedVariations.push({
                    product_id: product.id,
                    variation_id: variation_id,
                    name: name.join('<br/>')
                });
            } else {
                selectedVariations[index] = {
                    product_id: product.id,
                    variation_id: variation_id,
                    name: name.join('<br/>')
                }
            }

            this.setState({ selectedVariations });

            // Update selected products
            let selectedProducts = this.state.selectedProducts;
            const product_index = selectedProducts.findIndex((data: any) => {
                return product.id === data.productID;
            });

            if (product_index >= 0) {
                selectedProducts[product_index] = {
                    'productTitle': selectedProducts[product_index].productTitle,
                    'productType': selectedProducts[product_index].productType,
                    'productID': selectedProducts[product_index].productID,
                    'variationID': variation_id,
                    'quantity': selectedProducts[product_index].quantity
                }
            }

            this.setState({ selectedProducts });
        } else {
            // remove selected variation
            const index = selectedVariations.findIndex((data: any) => {
                return product.id === data.product_id;
            });
            if (index !== -1) {
                selectedVariations.splice(index, 1);
                this.setState({ selectedVariations });
            }

            // Update selected products
            let selectedProducts = this.state.selectedProducts;
            const product_index = selectedProducts.findIndex((data: any) => {
                return product.id === data.productID;
            });

            if (product_index >= 0) {
                selectedProducts[product_index] = {
                    'productTitle': selectedProducts[product_index].productTitle,
                    'productType': selectedProducts[product_index].productType,
                    'productID': selectedProducts[product_index].productID,
                    'variationID': variation_id,
                    'quantity': selectedProducts[product_index].quantity
                }
            }

        }

    }

    showThumbnail() {

        const show_thumbnail = this.props.appSettings.wwof_general_show_product_thumbnail;

        const columns = this.state.columns;
        const thumbnail_column = {
            title: '',
            dataIndex: 'thumbnail',
            key: 'thumbnail',
        };

        // Check if we already have sku column, to avoid error on duplicate column
        const found = columns.find(function (column: any) {
            return column.key === 'thumbnail';
        });

        if (show_thumbnail === 'yes' && typeof found === 'undefined') {
            // Insert after product column
            columns.splice(1, 0, thumbnail_column);
            this.setState({ columns });
        }

    }

    showSKU() {

        const show_sku = this.props.appSettings.wwof_general_show_product_sku;
        const columns = this.state.columns;
        const sku_column = {
            title: 'SKU',
            dataIndex: 'sku',
            key: 'sku',
        };

        // Check if we already have sku column, to avoid error on duplicate column
        const found = columns.find(function (column: any) {
            return column.key === 'sku';
        });

        if (show_sku === 'yes' && typeof found === 'undefined') {
            // Insert after product column
            columns.splice(0, 0, sku_column);
            this.setState({ columns });
        }

    }

    showInStockColumn() {

        const show_instock_col = this.props.appSettings.wwof_general_show_product_stock_quantity;
        const columns = this.state.columns;
        const in_stock_column = {
            title: 'In Stock',
            dataIndex: 'in_stock',
            key: 'in_stock',
        };

        // Check if we already have sku column, to avoid error on duplicate column
        const found = columns.find(function (column: any) {
            return column.key === 'in_stock';
        });

        if (show_instock_col === 'yes' && typeof found === 'undefined') {
            // Insert after product column
            columns.splice(3, 0, in_stock_column);
            this.setState({ columns });
        }
    }

    handleSorting(pagination: any, filters: any, sorter: any) {

        const sort_order = sorter.order === 'ascend' ? 'asc' : 'desc';
        const sort_by = sorter.field === 'product' ? 'title' : '';

        this.props.handleAppStateUpdate({
            fetching: true,
            total_products: 0,
            sort_order: sort_order,
            sort_by: sort_by,
            active_page: 1
        }, () => this.props.fetchProducts());
    }

    showAddToCartPerRow() {

        const alternate_view = this.props.appSettings.wwof_general_use_alternate_view_of_wholesale_page;
        const columns = this.state.columns;
        const add_to_cart_column = {
            title: '',
            key: 'action',
            render: (record: any) => {

                const product = record.product_data;
                const selected_variation = this.state.selectedVariations.find((data: any) => {
                    return product.id === data.product_id;
                });

                let variation = [];

                if (selected_variation) {
                    variation = this.props.variations[product.id].find((variation: any) => {
                        return variation.id === selected_variation.variation_id;
                    });
                }

                if (product.stock_quantity === 0 || variation.stock_quantity === 0)
                    return <p className="outofstock">Out of Stock</p>;
                else
                    return (<Button type='primary' onClick={(e: any) => this.addProductToCart(record)}>Add To Cart</Button>)
            }
        };

        // Check if we already have action column, to avoid error on duplicate column
        const found = columns.find(function (column: any) {
            return column.key === 'action';
        });

        if (alternate_view === 'no' && typeof found === 'undefined') {
            // Insert action column
            columns.splice(5, 0, add_to_cart_column);
            this.setState({ columns });
        }

    }

    addProductToCart(columns: any) {

        const product = columns.hasOwnProperty('product_data') ? columns.product_data : columns;

        let quantity = this.state.quantity.find((data: any) => {
            return data.product_id === product.id;
        });

        quantity = quantity !== undefined ? quantity.quantity : 1;

        const variation = this.state.selectedVariations.find((data: any) => {
            return data.product_id === product.id;
        });

        let variation_name: string = '';

        if (product.type === 'variable' && variation !== undefined)
            variation_name = variation.name;

        const qs = require('qs');
        axios.post(Options.ajax, qs.stringify({
            'action': 'wwof_add_product_to_cart',
            'product_type': product.type,
            'product_id': product.id,
            'variation_id': variation !== undefined ? variation.variation_id : 0,
            'quantity': quantity
        }))
            .then((res: any) => {

                if (res.data.status === 'success') {

                    notification['success']({
                        message: 'Succesfully Added:',
                        description:
                            <div>
                                <div dangerouslySetInnerHTML={{ __html: '<b>' + product.name + '</b> x ' + quantity + '<br/>' + variation_name }} />
                                <a href={this.props.cartURL}><Button style={{ marginTop: '10px' }} >View Cart<Icon type="shopping-cart" /></Button></a>
                            </div>,
                        duration: 10
                    });

                    // Update cart total by triggering the added_to_cart custom event of wc.
                    const fragments: any = res.data.fragments;
                    const cart_hash: any = res.data.cart_hash;

                    const event = new CustomEvent('added_to_cart', { detail: { fragments: fragments, cart_hash: cart_hash } });
                    document.body.dispatchEvent(event);

                    // Update subtotal below the order form.
                    this.props.handleAppStateUpdate({ subtotal: res.data.cart_subtotal_markup }, () => { });

                } else if (res.data.status === 'failed')
                    notification['error']({
                        message: 'Add to Cart Failed:',
                        description: <div dangerouslySetInnerHTML={{ __html: res.data.error_message }} />,
                        duration: 10
                    });

            })
            .catch((error: any) => {
                notification['error']({
                    message: 'Add to Cart',
                    description: 'Add to cart failed.',
                    duration: 10
                });
            });

    }

    addProductsToCart() {

        const products = this.state.selectedProducts;

        const qs = require('qs');
        axios.post(Options.ajax, qs.stringify({
            'action': 'wwof_add_products_to_cart',
            'products': products
        }))
            .then((res: any) => {
                if (res.data.status === 'success') {

                    let added: any = [];
                    const successfully_added = res.data.successfully_added;

                    Object.keys(successfully_added).forEach(product_id => {

                        const product = products.find((product: any) => {
                            return parseInt(product.productID) === parseInt(product_id) || parseInt(product.variationID) === parseInt(product_id);
                        });

                        if (product) {
                            if (['simple', 'variation'].includes(product.productType))
                                added.push(<div dangerouslySetInnerHTML={{ __html: '<b>' + product.productTitle + '</b> x ' + successfully_added[product_id] }} />);
                            else if (product.productType === 'variable') {
                                const index = this.state.selectedVariations.findIndex((data: any) => {
                                    return parseInt(product_id) === parseInt(data.variation_id);
                                });
                                if (index !== -1)
                                    added.push(<div dangerouslySetInnerHTML={{ __html: '<b>' + product.productTitle + '</b> x ' + successfully_added[product_id] + '<br/>' + this.state.selectedVariations[index].name }} />);
                            }

                        }

                    });

                    let failed: any = [];
                    const failed_to_add = res.data.failed_to_add;
                    failed_to_add.forEach((data: any, index: number) => {

                        const product = products.find((product: any) => {
                            return parseInt(product.productID) === parseInt(data.product_id);
                        });
                        if (product)
                            failed.push(<div dangerouslySetInnerHTML={{ __html: '<b>' + product.productTitle + '</b> x ' + data.quantity + '<br/>' + data.error_message }} />);
                    });
                    if (added.length > 0) {
                        notification['success']({
                            message: 'Succesfully Added:',
                            description:
                                <div>
                                    <List
                                        size="small"
                                        bordered
                                        dataSource={added}
                                        renderItem={(item: any) => <List.Item>{item}</List.Item>}
                                    />
                                    <a href={this.props.cartURL}><Button style={{ marginTop: '10px' }} >View Cart<Icon type="shopping-cart" /></Button></a>
                                </div>,
                            duration: 10
                        });
                    }
                    if (failed.length > 0) {
                        notification['error']({
                            message: 'Add to Cart Failed:',
                            description:
                                <List
                                    size="small"
                                    bordered
                                    dataSource={failed}
                                    renderItem={(item: any) => <List.Item>{item}</List.Item>}
                                />,
                            duration: 10
                        });
                    }

                    // Update cart total by triggering the added_to_cart custom event of wc.
                    const fragments: any = res.data.fragments;
                    const cart_hash: any = res.data.cart_hash;

                    const event = new CustomEvent('added_to_cart', { detail: { fragments: fragments, cart_hash: cart_hash } });
                    document.body.dispatchEvent(event);

                    // Update subtotal below the order form.
                    this.props.handleAppStateUpdate({ subtotal: res.data.cart_subtotal_markup }, () => { });

                } else {
                    notification['error']({
                        message: 'Add to Cart Failed:',
                        description: 'error'
                    });
                }

            })
            .catch((error: any) => {
                console.log(error);
                notification['error']({
                    message: 'Add to Cart Failed',
                    description: 'error'
                });
            });
    }

    insertFooter() {
        return <div style={{ textAlign: 'center' }}><Spin tip={"Loading " + this.props.products.length + " out of " + this.props.totalProducts + " products"}></Spin></div >;
    }

    render() {

        const { products, appSettings, fetching } = this.props;

        const alternate_view = appSettings.wwof_general_use_alternate_view_of_wholesale_page;
        const disable_pagination = appSettings.wwof_general_disable_pagination;

        const data = products.length > 0 ? products.map((product: any) => {
            return {
                key: product.id,
                product_data: product,
                product:
                    <Product
                        product={product}
                        appSettings={this.props.appSettings}
                        displayModal={this.displayModal.bind(this)}
                        selectedVariations={this.state.selectedVariations}
                        variations={this.props.variations}
                        updateselectedVariationsState={this.updateselectedVariationsState.bind(this)}
                        handleAppStateUpdate={this.props.handleAppStateUpdate} />,
                sku:
                    <ProductSKU
                        product={product}
                        selectedVariations={this.state.selectedVariations}
                        variations={this.props.variations} />,
                thumbnail:
                    <ProductThumbnail
                        product={product}
                        appSettings={this.props.appSettings}
                        displayModal={this.displayModal.bind(this)} />,
                price:
                    <ProductPrice
                        product={product}
                        selectedVariations={this.state.selectedVariations}
                        variations={this.props.variations} />,
                in_stock:
                    <ProductInstock
                        product={product}
                        selectedVariations={this.state.selectedVariations}
                        variations={this.props.variations} />,
                quantity:
                    <ProductQuantity
                        product={product}
                        updateQuantityState={this.updateQuantityState.bind(this)} />,
            };
        }) : [];

        const rowSelection = {

            onChange: (selectedRowKeys: any, selectedRows: any) => {

                let selectedProducts = selectedRowKeys.length === 0 ? [] : this.state.selectedProducts;

                // handles unselection
                selectedProducts.forEach((product: any, index: number) => {
                    if (selectedRowKeys.includes(product.productID) === false)
                        selectedProducts.splice(index, 1);
                });

                // handles selection
                selectedRowKeys.forEach((selectedRowKey: number, index: number) => {

                    const productRow = selectedRows.find((product: any) => {
                        return product.key === selectedRowKey;
                    });

                    const quantity = this.state.quantity.find((data: any) => {
                        return data.product_id === selectedRowKey;
                    });

                    const variation = this.state.selectedVariations.find((data: any) => {
                        return data.product_id === selectedRowKey;
                    });

                    if (productRow) {

                        const productRowCheck = selectedProducts.find((product: any) => {
                            return product.productID === selectedRowKey;
                        });

                        if (productRowCheck === undefined) {
                            selectedProducts.push({
                                'productTitle': productRow.product_data.name,
                                'productType': productRow.product_data.type,
                                'productID': selectedRowKey,
                                'variationID': variation !== undefined ? variation.variation_id : 0,
                                'quantity': quantity !== undefined ? quantity.quantity : 1
                            });
                        }

                    }

                });

                this.setState({ selectedProducts });

            }

        };

        let addToCart: any = '';
        if (alternate_view === 'yes') {
            if (this.state.selectedProducts.length > 0) {
                addToCart = <Button type="primary" onClick={this.addProductsToCart.bind(this)}>Add Selected Products To Cart</Button>;
            } else {
                addToCart = <Button type="primary" disabled>Add Selected Products To Cart</Button>;
            }
        }

        let table_props: any = {
            dataSource: data,
            columns: this.state.columns,
            loading: fetching,
            pagination: false,
            style: { marginTop: '10px' },
            onChange: this.handleSorting.bind(this)
        }

        if (alternate_view === 'yes') {
            table_props['rowSelection'] = rowSelection;
        }

        if (disable_pagination === 'yes' && this.props.products.length < this.props.totalProducts) {
            table_props['footer'] = this.insertFooter.bind(this);
        }

        return (
            <div>
                <div>{addToCart}</div>
                <ProductModal
                    openedProduct={this.state.openedProduct}
                    appSettings={this.props.appSettings}
                    selectedVariations={this.state.selectedVariations}
                    variations={this.props.variations}
                    showModal={this.state.showModal}
                    updateselectedVariationsState={this.updateselectedVariationsState.bind(this)}
                    handleAppStateUpdate={this.props.handleAppStateUpdate}
                    onCancel={() => {
                        this.setState({ showModal: false })
                    }}
                    updateQuantityState={this.updateQuantityState}
                    addProductToCart={this.addProductToCart.bind(this)} />
                <Table
                    id="wwof-order-form"
                    {...table_props} />
                <div>{addToCart}</div>
            </div>
        );

    }

}

export default Productlist;