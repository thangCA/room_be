import axios from 'axios';
import { axiosConfig } from '../../../App';

export const addCartItem = (callThis) => {
    callThis.setState({ showLoadingState: true });

    axios.get('http://localhost:5001/api/cart/item/add?account=' + callThis.props.accountInfo._id + '&product=' + callThis.state.productDetail._id, axiosConfig)
        .then(res => {
            console.log("res" + res.data)
            if (res) {
                callThis.setState({ showLoadingState: false });
            }
            if (res.data.protect === 'miss' || res.data.cart === 'add cart item: account is not existed' || res.data.cart === 'add cart item: product is not existed') {
                alert('There are problems. Sign in again, please !');

                callThis.props.history.push('/authentication'); return;
            }
            if (res.data.cart === 'add cart item: existed item') {
                alert('This item is in cart, now'); return;
            }
            if (res.data.cart === 'add cart item: success') {
                alert('Add item to cart success !');

                callThis.props.history.push('/cart/manage/of/' + callThis.props.accountInfo._id);

                window.location.reload(); return;
            }
        });
}