import axios from 'axios';
import { axiosConfig } from '../../../App';
import { loadSellingManageProductList } from '../../../connection/load-selling-manage-product-list';

export const deleteProduct = (callThis, storeId, productId) => {
    if (!window.confirm('Do you want to delete this product ?')) {
        return;
    }
    else {
        callThis.setState({ showLoadingState: true });

        axios.get('http://localhost:5001/api/product/manage/delete?store=' + storeId + '&product=' + productId, axiosConfig)
            .then(res => {
                if (res) {
                    callThis.setState({ showLoadingState: false });
                }
                if (res.data.protect === 'miss' || res.data.product === 'delete product: product is not existed' || res.data.product === 'delete product: store is not existed') {
                    alert('There are problems. Sign in again, please !');

                    callThis.props.history.push('/authentication'); return;
                }
                if (res.data.product === 'delete product: success') {
                    loadSellingManageProductList(callThis); return;
                }
            });
    }
}