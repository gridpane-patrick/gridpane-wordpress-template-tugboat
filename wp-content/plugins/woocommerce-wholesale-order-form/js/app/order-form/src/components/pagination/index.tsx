import React from 'react';
import { Pagination } from 'antd';

interface AppPaginationState { }

interface AppPaginationProps {
    totalProducts: number
    activePage: number,
    totalPage: number
    activePageUpdate: any,
    perPage: number
}

class AppPagination extends React.Component<AppPaginationProps, AppPaginationState> {

    render() {
        const { totalProducts, activePage, perPage } = this.props;

        return (
            <Pagination
                current={activePage}
                total={totalProducts}
                onChange={(activePage: number) => this.props.activePageUpdate(activePage)}
                pageSize={perPage}
                style={{ marginTop: '10px' }} />

        );
    }
}

export default AppPagination;

