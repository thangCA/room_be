import axios from 'axios';
import { axiosConfig } from '../../../App';
import { loadSellingManageProductList } from '../../../connection/load-selling-manage-product-list';

export const refuseOrder = (callThis, accountId, orderId) => {
    if (!window.confirm('Do you want to refuse this order ?')) {
        return;
    }
    else {
        callThis.setState({ showRefusingState: true });

        axios.get('http://localhost:5001/api/order/refuse?account=' + accountId + '&order=' + orderId, axiosConfig)
            .then(res => {
                if (res) {
                    callThis.setState({ showRefusingState: false });
                }
                if (res.data.protect === 'miss' || res.data.order === 'refuse order: account is not existed' || res.data.order === 'refuse order: order is not existed') {
                    alert('There are problems. Sign in again, please !');

                    callThis.props.history.push('/authentication'); return;
                }
                if (res.data.order === 'refuse order: success') {
                    loadSellingManageProductList(callThis); return;
                }
            });
    }
}