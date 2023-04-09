import axios from 'axios';
import { axiosConfig } from '../../../App';
import { loadSellingManageProductList } from '../../../connection/load-selling-manage-product-list';

export const confirmProductOrder = (callThis, orderId) => {
    if (!window.confirm('Do you want to confirm this order ?')) {
        return;
    }
    else {
        callThis.setState({ showConfirmingState: true });
        callThis.props.dispatch({ type: 'UPDATE_CONFIRMING_STATE', payload: true });
        axios.get('http://localhost:5001/api/order/confirm?order=' + orderId, axiosConfig)
            .then(res => {
                if (res) {
                    callThis.setState({ showConfirmingState: false });
                }
                if (res.data.protect === 'miss' || res.data.order === 'confirm order: order is not existed') {
                    alert('There are problems. Sign in again, please !');

                    callThis.props.history.push('/authentication'); return;
                }
                if (res.data.order === 'confirm order: success') {
                    loadSellingManageProductList(callThis, true); return;
                }
            });
    }
}