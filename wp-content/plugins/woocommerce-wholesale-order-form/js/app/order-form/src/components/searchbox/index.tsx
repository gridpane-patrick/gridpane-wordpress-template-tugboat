import React from 'react';
import { Input } from 'antd';

class SearchBox extends React.Component<any, any> {

    render() {
        return (
            <Input
                placeholder='Search Products'
                onChange={e => this.props.onInputChange(e.target.value)}
                value={this.props.inputValue} />
        );
    }
}

export default SearchBox;
