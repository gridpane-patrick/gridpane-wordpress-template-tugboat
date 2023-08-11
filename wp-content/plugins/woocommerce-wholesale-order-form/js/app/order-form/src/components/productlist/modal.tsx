import React from 'react';
import ReactDOM from 'react-dom';
import { Avatar, Modal, Row, Col, InputNumber, Button, Icon, Select, Spin } from 'antd';
import axios from 'axios';
const { Option } = Select;

interface ProductModalState {
    variationsPerPage: any,
    variationLazyLoad: boolean,
    fetching: boolean,
    typingTimeout: any,
    searchString: string
}

interface ProductModalProps {
    openedProduct: any,
    appSettings: any,
    selectedVariations: any,
    variations: any,
    showModal: any,
    updateselectedVariationsState: any,
    handleAppStateUpdate: any,
    onCancel: any,
    updateQuantityState: any,
    addProductToCart: any
}

declare var Options: any;

class ProductModal extends React.Component<ProductModalProps, ProductModalState> {

    constructor(props: any) {
        super(props);
        this.state = {
            variationsPerPage: [],
            variationLazyLoad: false,
            fetching: false,
            typingTimeout: 0,
            searchString: ''
        };
    }

    highlightSearchedString = (attributes: any) => {

        let label = attributes.name + ': ' + attributes.option + ' ';
        let searchString = this.state.searchString;

        if (searchString) {

            let index = label.toLowerCase().indexOf(searchString.toLowerCase());

            if (index !== -1) {

                let length = searchString.length;

                let prefix = label.substring(0, index);
                let suffix = label.substring(index + length);
                let match = label.substring(index, index + length);

                return (
                    <span>
                        {prefix}<span className="searchString">{match}</span>{suffix}
                    </span>
                );
            }
        }

        return (
            <span>
                {label}
            </span>
        );
    }

    displayVariations(product: any) {

        if (product.type === 'variable' && this.props.variations) {

            if (this.props.variations[product.id]) {

                const variations = this.props.variations[product.id].map((variation: any) => {
                    const name = variation.attributes.map((attributes: any) => {
                        return <span key={attributes.id}>{this.highlightSearchedString(attributes)}</span>
                    });
                    return <Option key={variation.id} value={variation.id}>{name}</Option>;
                });

                const selected_variation = this.props.selectedVariations.find((data: any) => {
                    return product.id === data.product_id;
                });

                let value = 'Select Variation';
                if (selected_variation)
                    value = selected_variation.variation_id;

                return (
                    <div>
                        <Select
                            showSearch
                            placeholder='Select Variation'
                            value={value}
                            style={{ width: 250, marginTop: 10 }}
                            onChange={(variation_id: any) => this.props.updateselectedVariationsState(product, variation_id)}
                            onPopupScroll={(e: any) => this.variationLoadMoreOnScroll(e, product)}
                            filterOption={false}
                            onSearch={(value: string) => this.handleSearch(value, product)}
                            notFoundContent='No results found'
                            dropdownRender={(menu: any) => this.isSearching(menu)}
                            onSelect={() => this.setState({ searchString: '' })}
                            allowClear={true}>
                            {variations}
                        </Select>
                    </div>
                )
            }

        }

    }

    variationLoadMoreOnScroll = async (e: any, product: any) => {

        // https://github.com/ant-design/ant-design/issues/12406#issuecomment-424968753
        e.persist();
        let target = e.target;

        if (product.type === 'variable' && this.props.variations) {

            if (this.state.variationLazyLoad === false && target.scrollTop + target.offsetHeight === target.scrollHeight) {

                this.setState({ variationLazyLoad: true });
                // scrollToEnd, do something!!!
                let variationsPerPage = this.state.variationsPerPage;

                const s2 = variationsPerPage.findIndex((data: any) => {
                    return product.id === data.productID;
                });

                // Insert Loading more variations
                if (variationsPerPage.length === 0 || (variationsPerPage.length > 0 && variationsPerPage[s2].page < variationsPerPage[s2].total_pages)) {
                    const elements = target.getElementsByClassName("loadmore");
                    if (elements.length <= 0) {
                        const node = document.createElement("li");
                        ReactDOM.render(<span style={{ padding: '10px' }}><Icon type="loading" /> Loading more variations</span>, node);
                        node.setAttribute('class', 'loadmore');
                        target.appendChild(node);
                    }
                }

                const qs = require('qs');
                const res = await axios.post(Options.ajax, qs.stringify({
                    'action': 'wwof_api_get_variations',
                    'product_id': product.id,
                    'page': s2 >= 0 ? variationsPerPage[s2].page + 1 : 2,
                    'search': this.state.searchString
                }));

                if (res.data.status === 'success' && res.data.variations.length !== 0) {

                    const elements = target.getElementsByClassName("loadmore");

                    while (elements.length > 0) elements[0].remove();

                    let variations = this.props.variations;

                    variations[product.id] = [
                        ...variations[product.id],
                        ...res.data.variations[product.id]
                    ];

                    if (s2 >= 0) {
                        variationsPerPage[s2] = {
                            'productID': product.id,
                            'page': variationsPerPage[s2].page + 1,
                            'total_pages': res.data.total_pages,
                            'total_variations': res.data.total_variations
                        }
                    } else {
                        variationsPerPage.push({
                            'productID': product.id,
                            'page': 2,
                            'total_pages': res.data.total_pages,
                            'total_variations': res.data.total_variations
                        });
                    }

                    this.setState({ variationsPerPage });
                    this.props.handleAppStateUpdate({ variations }, () => {
                        this.setState({ variationLazyLoad: false });
                    });

                }

            }

        }

    }

    handleSearch = async (value: string, product: any) => {

        if (value) {

            const { typingTimeout } = this.state;

            // Clear timeout
            if (typingTimeout)
                clearTimeout(this.state.typingTimeout);

            this.setState({
                searchString: value,
                fetching: true,
                typingTimeout: setTimeout(async () => {

                    const qs = require('qs');
                    const res = await axios.post(Options.ajax, qs.stringify({
                        'action': 'wwof_api_get_variations',
                        'product_id': product.id,
                        'search': value,
                        'page': 1
                    }));

                    if (res.data.status === 'success') {

                        // Enable lazy load
                        this.setState({ fetching: false, variationsPerPage: [], variationLazyLoad: false });

                        let variations = this.props.variations;

                        if (res.data.variations.length !== 0) {
                            variations[product.id] = [
                                ...res.data.variations[product.id]
                            ];
                        } else {
                            variations[product.id] = []
                        }

                        this.props.handleAppStateUpdate({ variations }, () => { });

                    } else this.props.handleAppStateUpdate({ variations: [] }, () => { });

                }, 500)
            });

        } else {

            // Set back to default initial value, enable lazy load
            this.setState({ searchString: '', variationsPerPage: [], variationLazyLoad: false });

            const qs = require('qs');
            const res = await axios.post(Options.ajax, qs.stringify({
                'action': 'wwof_api_get_variations',
                'product_id': product.id,
                'page': 1
            }));

            if (res.data.status === 'success') {

                let variations = this.props.variations;

                if (res.data.variations.length !== 0) {
                    variations[product.id] = [
                        ...res.data.variations[product.id]
                    ];
                }

                this.props.handleAppStateUpdate({ variations }, () => { });

            }

        }

    }

    isSearching = (menu: any) => {
        return this.state.fetching ? (
            <div style={{ padding: 10 }}><Spin size="small" /> Searching</div>
        ) : menu;
    }

    productModal() {

        const placeholder = Options.site_url + '/wp-content/uploads/woocommerce-placeholder-300x300.png';
        const product = this.props.openedProduct;
        const show_instock_col = this.props.appSettings.wwof_general_show_product_stock_quantity;

        let variation = [];

        const selected_variation = this.props.selectedVariations.find((data: any) => {
            return product.id === data.product_id;
        });

        if (selected_variation) {
            variation = this.props.variations[product.id].find((variation: any) => {
                return variation.id === selected_variation.variation_id;
            });
        }

        if (product && this.props.showModal === true) {
            return (
                <Modal
                    title=""
                    visible={this.props.showModal}
                    onCancel={() => {
                        this.props.onCancel()
                    }}
                    width="650px"
                    footer={null}>
                    <Row>
                        <Col xs={12} sm={12} md={12} lg={12} xl={12}>
                            {typeof product.images[0] !== 'undefined' ? <Avatar src={product.images[0]['src']} shape="square" size={200} /> : <Avatar src={placeholder} shape="square" size={200} />}
                        </Col>
                        <Col xs={12} sm={12} md={12} lg={12} xl={12}>
                            <h2>{product.name}</h2>
                            <p style={{ fontSize: "16px" }} dangerouslySetInnerHTML={{ __html: product.price_html }}></p>
                            <p style={{ fontSize: "16px" }} dangerouslySetInnerHTML={{ __html: product.short_description }}></p>
                            <p style={{ fontSize: "16px" }} dangerouslySetInnerHTML={{ __html: product.description }}></p>
                            <div>
                                <div style={{ marginBottom: '10px' }}>
                                    {this.displayVariations(product)}
                                </div>

                                {variation && variation.description ?
                                    <div style={{ marginBottom: "10px" }}>
                                        <p style={{ fontSize: "16px" }} dangerouslySetInnerHTML={{ __html: variation.description }}></p>
                                    </div> : ''}

                                {variation && variation.price ?
                                    <div style={{ marginBottom: "10px" }}>
                                        <p style={{ fontSize: "16px" }} dangerouslySetInnerHTML={{ __html: variation.price }}></p>
                                    </div> : ''}

                                {show_instock_col === 'yes' && product.type === 'variable' && (variation && variation.stock_quantity != null && variation.stock_quantity >= 0) ?
                                    <div style={{ marginBottom: "10px" }}>
                                        <Icon type="inbox" style={{ fontSize: '22px', color: '#08c', marginRight: '10px' }} />{variation.stock_quantity}
                                    </div> : ''}

                                {show_instock_col === 'yes' && (product.type === 'simple' || product.type === 'variation') && product.stock_quantity != null && product.stock_quantity >= 0 ?
                                    <div style={{ marginBottom: "10px" }}>
                                        <Icon type="inbox" style={{ fontSize: '22px', color: '#08c', marginRight: '10px' }} />{product.stock_quantity}
                                    </div> : ''}

                                <div style={{ marginBottom: '10px' }}>
                                    {product.stock_quantity === 0 || (variation && variation.stock_quantity === 0) ?
                                        <div>
                                            <p className="outofstock">Out of Stock</p>
                                            <InputNumber min={1} defaultValue={1} style={{ marginRight: '10px' }} />
                                            <Button type='primary' disabled>Add To Cart</Button>
                                        </div>
                                        :
                                        <div>
                                            <InputNumber min={1} defaultValue={1} onChange={(quantity: any) => this.props.updateQuantityState(product, quantity)} style={{ marginRight: '10px' }} />
                                            <Button type='primary' onClick={(e: any) => this.props.addProductToCart(product)}>Add To Cart</Button>
                                        </div>
                                    }
                                </div>
                            </div>
                        </Col>
                    </Row>
                </Modal>
            );
        }
    }

    render() {

        return (
            <div>{this.productModal()}</div>
        );

    }

}

export default ProductModal;