import React from 'react';
import { SearchBox, CategoryDropdown, Productlist, Pagination } from './components';
import axios from 'axios';

import { Form, Button, Row, Col, Alert } from 'antd';
import './styles.js';

interface AppState {
  products: Array<string | number>,
  categories: Array<string | number>,
  variations: Array<string | number>,
  search: string,
  category_filter: any,
  active_page: number,
  per_page: number,
  total_products: number,
  total_page: number,
  fetching: boolean,
  wwof_settings: any,
  wholesale_role: string
  subtotal: string,
  sort_order: string,
  cart_url: string,
  loading_more: boolean,
  show_all: boolean,
  fetch_error_msg: string
}

interface AppProps {
  attributes?: any
}

declare var Options: any;

class App extends React.Component<AppProps, AppState> {

  constructor(props: AppProps) {
    super(props);
    this.state = {
      products: [],
      categories: [],
      variations: [],
      search: "",
      category_filter: undefined,
      active_page: 1,
      per_page: 12,
      total_products: 0,
      total_page: 0,
      fetching: true,
      wwof_settings: [],
      wholesale_role: 'wholesale_customer',
      subtotal: '',
      sort_order: '',
      cart_url: '',
      loading_more: false,
      show_all: false,
      fetch_error_msg: ''
    };
  }

  componentDidMount() {

    this.fetchProducts('', () => {
      this.loadOnScroll(null);
      window.addEventListener('scroll', this.loadOnScroll);
    });
    this.fetchCategories();

  }

  componentWillUnmount() {

    window.removeEventListener('scroll', this.loadOnScroll);

  }

  loadOnScroll = (e: any) => {
    let disable_pagination = typeof this.state.wwof_settings.wwof_general_disable_pagination !== 'undefined' ? this.state.wwof_settings.wwof_general_disable_pagination : 'no';

    if (disable_pagination === 'yes' && !this.state.loading_more) {

      if (this.state.active_page < this.state.total_page) {

        var el: any = document.getElementById('wwof-order-form');

        var rect = el.getBoundingClientRect();

        var isAtEnd = (
          // rect.top >= 0 &&
          // rect.left >= 0 &&
          rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) && /*or $(window).height() */
          rect.right <= (window.innerWidth || document.documentElement.clientWidth) /*or $(window).width() */
        );

        // User at the end of content. load more content
        if (isAtEnd) {

          this.setState({
            active_page: this.state.active_page + 1,
            loading_more: true
          }, () => {
            this.fetchProducts('', () => this.loadOnScroll(null));
          });

        }
      }
    }
  }

  fetchProducts = async (searching?: string, callback?: any) => {

    let { attributes } = this.props;

    const qs = require('qs');
    const res = await axios.post(Options.ajax, qs.stringify({
      'action': 'wwof_api_get_products',
      'search': this.state.search,
      'category': this.state.category_filter,
      'page': this.state.active_page,
      'searching': searching || 'no',
      'sort_order': this.state.sort_order,
      'products': attributes.products,
      'categories': attributes.categories,
      'show_all': this.state.show_all
    }));

    if (res.data.status === 'success') {

      let selectedCat = '';
      if (searching !== 'yes' && this.state.category_filter !== 'Select Category' && this.state.categories && res.data.settings.wwof_general_default_product_category_search_filter !== 'none') {
        const find_default_cat: any = this.state.categories.find((data: any) => {
          return data.slug === res.data.settings.wwof_general_default_product_category_search_filter;
        });
        if (find_default_cat)
          selectedCat = find_default_cat.id;
      } else {
        selectedCat = this.state.category_filter;
      }

      this.setState({
        wwof_settings: res.data.settings,
        per_page: parseInt(res.data.settings.wwof_general_products_per_page) || 12,
        total_page: parseInt(res.data.total_page),
        total_products: parseInt(res.data.total_products),
        fetching: false,
        subtotal: res.data.cart_subtotal,
        cart_url: res.data.cart_url,
        loading_more: false,
        category_filter: selectedCat ? selectedCat : 'Select Category'
      });

      if (res.data.settings.wwof_general_disable_pagination === 'yes' && this.state.active_page > 1) {
        const products = [
          ...this.state.products,
          ...res.data.products
        ];
        const variations = {
          ...this.state.variations,
          ...res.data.variations,
        };
        this.setState({ products, variations });
      } else {
        this.setState({
          products: res.data.products,
          variations: res.data.variations,
        });
      }

      if (typeof callback === 'function')
        callback();

    } else { // error
      const fetch_error_msg = res.data.message;
      this.setState({ fetch_error_msg });
      this.setState({ fetching: false });
    }
  }

  fetchCategories = async () => {

    const qs = require('qs');
    let { attributes } = this.props;

    const res = await axios.post(Options.ajax, qs.stringify({
      'action': 'wwof_api_get_categories',
      'categories': attributes.categories
    }));

    if (res.data.status === 'success') {
      this.setState({
        categories: res.data.categories
      });
    } else {
      console.log(res.data.message);
    }
  }

  onFormSubmit(e: any) {
    e.preventDefault();
    this.setState({
      fetching: true,
      total_products: 0,
      show_all: false,
      active_page: 1
    }, () => {
      this.fetchProducts('yes');
    });
  }

  showAll(e: any) {
    e.preventDefault();
    this.setState({
      search: '',
      category_filter: 'Select Category',
      active_page: 1,
      fetching: true,
      show_all: true
    }, () => {
      this.fetchProducts();
    });
  }

  activePageUpdate(active_page: number) {
    this.setState({
      active_page,
      fetching: true
    }, () => {
      this.fetchProducts();
    });
  }

  handleAppStateUpdate(states: any, callback: any) {
    this.setState(states, () => callback());
  }

  render() {

    let { attributes } = this.props;

    const productListProps = {
      products: this.state.products,
      variations: this.state.variations,
      appSettings: this.state.wwof_settings,
      wholesaleRole: this.state.wholesale_role,
      activePage: this.state.active_page,
      totalPage: this.state.total_page,
      fetchProducts: this.fetchProducts,
      handleAppStateUpdate: this.handleAppStateUpdate.bind(this),
      totalProducts: this.state.total_products,
      fetching: this.state.fetching,
      cartURL: this.state.cart_url
    };

    return (

      <div>
        {this.state.fetch_error_msg ?
          <div className="wwof-alert-message">
            <Alert
              message="Error"
              description={this.state.fetch_error_msg}
              type="error"
              closable
            /></div> : ''
        }

        {(attributes.show_search >= 1) ?
          <Form onSubmit={(e: any) => this.onFormSubmit(e)}>
            <Row>
              <Col md={24} lg={8} xl={8}>
                <SearchBox
                  onInputChange={(search: string) => this.setState({ search })}
                  inputValue={this.state.search} />
              </Col>
              <Col md={24} lg={8} xl={8}>
                <CategoryDropdown
                  selectedCategory={this.state.category_filter}
                  categories={this.state.categories}
                  onSelectChange={(category_filter: number) => this.setState({ category_filter: category_filter, show_all: true })}
                  appSettings={this.state.wwof_settings} />

              </Col>
              <Col md={24} lg={8} xl={8}>
                <Button type="primary" onClick={(e: any) => this.onFormSubmit(e)} style={{ marginLeft: '20px' }} className="wwof-search-btn" >Search</Button>
                <Button type="primary" onClick={(e: any) => this.showAll(e)} style={{ marginLeft: '10px' }} className="wwof-show-all-btn">Show All Products</Button>
              </Col>
            </Row>
          </Form> : ''
        }
        <Productlist {...productListProps}
        />
        {
          this.state.wwof_settings.wwof_general_disable_pagination !== 'yes' ?
            <Pagination
              totalProducts={this.state.total_products}
              activePage={this.state.active_page}
              totalPage={this.state.total_page}
              perPage={this.state.per_page}
              activePageUpdate={(active_page: number) => this.activePageUpdate(active_page)} />
            : ''
        }
        {
          this.state.wwof_settings.wwof_general_display_cart_subtotal === 'yes' && this.state.subtotal ?
            <Row>
              <Col><div dangerouslySetInnerHTML={{ __html: this.state.subtotal }}></div></Col>
            </Row> : ''
        }
        <Row>
          <Col><p>{this.state.total_products} Product(s) Found</p></Col>
        </Row>
      </div>
    );
  }
}

export default App;
