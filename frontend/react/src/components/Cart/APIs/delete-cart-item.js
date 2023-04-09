import axios from 'axios';
import { axiosConfig } from '../../../App';
import { loadCartItem } from '../../../connection/load-cart-item';

export const deleteCartItem = (callThis, itemId) => {
    if (!window.confirm('Do you want to delete this item ?')) {
        return;
    }
    else {
        callThis.setState({ showLoadingState: true });

        axios.get('http://localhost:5001/api/cart/item/delete?account=' + callThis.props.accountInfo._id + '&item=' + itemId, axiosConfig)
            .then(res => {
                if (res) {
                    callThis.setState({ showLoadingState: false });
                }
                if (res.data.protect === 'miss' || res.data.cart === 'delete cart item: account is not existed') {
                    alert('There are problems. Sign in again, please !');

                    callThis.props.history.push('/authentication'); return;
                }
                if (res.data.cart === 'delete cart item: success') {
                    loadCartItem(callThis); return;
                }
            });
    }
}