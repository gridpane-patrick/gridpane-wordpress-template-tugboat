import React from 'react';
import ReactDOM from 'react-dom';
import { Row, Col, Select, Icon, Spin } from 'antd';
import axios from 'axios';
const { Option } = Select;

interface ProductState {
    variationsPerPage: any,
    variationLazyLoad: boolean,
    fetching: boolean,
    typingTimeout: any,
    searchString: string
}

interface ProductProps {
    product: any,
    appSettings: any,
    displayModal: any,
    selectedVariations: any,
    variations: any,
    updateselectedVariationsState: any,
    handleAppStateUpdate: any
}

declare var Options: any;

class Product extends React.Component<ProductProps, ProductState> {

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

    displayPopup() {
        const show_popup = this.props.appSettings.wwof_general_display_product_details_on_popup;
        return show_popup === 'yes' ? true : false;
    }

    setProductColumn(product: any) {
        return (
            <div>
                <Row>
                    <Col xs={24} sm={24} md={24} lg={24} xl={24}>
                        {this.displayPopup() ? <a href="/#" rel="noopener noreferrer" onClick={(e: any) => { e.preventDefault(); this.props.displayModal(product) }}>{product.name}</a> : <a href={product.permalink} target="_blank" rel="noopener noreferrer">{product.name}</a>}
                        {this.displayVariations(product)}
                    </Col>
                </Row>
            </div>
        );
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

    render() {

        const { product } = this.props;

        return (
            <div>{this.setProductColumn(product)}</div>
        );

    }

}

export default Product;