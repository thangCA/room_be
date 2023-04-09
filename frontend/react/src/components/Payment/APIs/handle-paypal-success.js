import axios from 'axios';
import { axiosConfig } from '../../../App';

export const handlePaypalSuccess = (callThis, orderId) => {
    axios.get('http://localhost:5001/api/order/paid?order=' + orderId, axiosConfig)
        .then(res => {
            if (res.data.protect === 'miss' || res.data.order === 'paid order: order is not existed') {
                alert('There are problems. Sign in again, please !');

                callThis.props.history.push('/authentication'); return;
            }
            if (res.data.order === 'paid order: success') {
                alert('Paying with PayPal success !');
                window.location.reload();
            }
        });
}