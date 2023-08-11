import React from 'react';
import { TreeSelect } from 'antd';

interface CategoryDropdownState {
    selectedCat: string
}

interface CategoryDropdownProps {
    selectedCategory: any,
    categories: any,
    onSelectChange: any,
    appSettings: any
}

class CategoryDropdown extends React.Component<CategoryDropdownProps, CategoryDropdownState>{
    constructor(props: any) {
        super(props);
        this.state = {
            selectedCat: 'Select Category'
        };
    }

    onChange(category_name: string, treeData: Array<any>) {

        let selected = category_name ? category_name : this.state.selectedCat;

        if (category_name) {
            const { categories } = this.props;

            let iterate = (cat: any, data: any) => {

                cat.children.forEach((cat2: any, index: number) => {

                    if (category_name === cat2.name) {
                        selected = cat2.id;
                        return;
                    }

                    if (cat2.children.length > 0) {
                        iterate(cat2, data.children[index]);
                    }

                });

            };

            categories.forEach((cat: any, index: number) => {
                if (category_name === cat.name) {
                    selected = cat.id;
                    return;
                }

                if (cat.children.length > 0)
                    iterate(cat, treeData[index]);
            });

            this.setState({ selectedCat: category_name });
            this.props.onSelectChange(selected);

        } else this.props.onSelectChange('Select Category');

    }

    render() {

        const { categories, appSettings, selectedCategory } = this.props;
        let treeData: any[] = [];
        let iterate = (cat: any, data: any) => {

            cat.children.forEach((cat2: any, index: number) => {

                data.children.push({
                    'title': cat2.name,
                    'value': cat2.name,
                    'key': cat2.id,
                    'children': []
                });

                if (cat2.children.length > 0) {
                    iterate(cat2, data.children[index]);
                }

            });

        };

        categories.forEach((cat: any, index: number) => {
            treeData.push({
                'title': cat.name,
                'value': cat.name,
                'key': cat.id,
                'children': []
            });
            if (cat.children.length > 0)
                iterate(cat, treeData[index]);
        });

        let selectedCat = selectedCategory;
        if (selectedCat === undefined && appSettings.wwof_general_default_product_category_search_filter !== 'none') {
            const find_default_cat = categories.find((data: any) => {
                return data.slug === appSettings.wwof_general_default_product_category_search_filter;
            });
            if (find_default_cat)
                selectedCat = find_default_cat.name;

        } else {
            if (selectedCat === 'Select Category')
                selectedCat = 'Select Category';
            else
                selectedCat = this.state.selectedCat;
        }

        return (
            <TreeSelect
                showSearch
                allowClear
                style={{ marginLeft: '10px' }}
                className='wwof-category-filter'
                value={selectedCat}
                treeData={treeData}
                placeholder="Select Category"
                treeDefaultExpandAll
                onChange={(val: string) => this.onChange(val, treeData)}
            />
        );
    }
}

export default CategoryDropdown;
