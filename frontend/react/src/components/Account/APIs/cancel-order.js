import axios from 'axios';
import { axiosConfig } from '../../../App';
import { loadOrderList } from '../../../connection/load-order-list';

export const cancelOrder = (callThis, accountId, orderId) => {
    if (!window.confirm('Do you want to cancel this order ?')) {
        return;
    }
    else {
        callThis.setState({ showLoadingState: true });

        axios.get('http://localhost:5001/api/order/cancel?account=' + accountId + '&order=' + orderId, axiosConfig)
            .then(res => {
                if (res) {
                    callThis.setState({ showLoadingState: false });
                }
                if (res.data.protect === 'miss' || res.data.order === 'cancel order: account is not existed' || res.data.order === 'cancel order: order is not existed') {
                    alert('There are problems. Sign in again, please !');

                    callThis.props.history.push('/authentication'); return;
                }
                if (res.data.order === 'cancel order: success') {
                    loadOrderList(callThis); return;
                }
            });
    }
}