import axios from 'axios';
import { axiosConfig } from '../../../App';
import { loadSellingManageProductList } from '../../../connection/load-selling-manage-product-list';

export const deleteProductDetail = (callThis, productId, field, where) => {
    if (!window.confirm('Do you want to delete this product detail ?')) {
        return;
    }
    else {
        axios.get('http://localhost:5001/api/product/manage/detail/delete?product=' + productId + '&field=' + field + '&where=' + where, axiosConfig)
            .then(res => {
                if (res.data.protect === 'miss' || res.data.product === 'delete product detail: product is not existed') {
                    alert('There are problems. Sign in again, please !');

                    callThis.props.history.push('/authentication'); return;
                }
                if (res.data.product === 'delete product detail: success') {
                    loadSellingManageProductList(callThis); return;
                }
            });
    }
}