import axios from 'axios';
import { axiosConfig } from '../App';

export const loadCartItem = (callThis) => {
    if (localStorage.getItem('accountId')) {
        axios.get('http://localhost:5001/api/cart/item/load?account=' + localStorage.getItem('accountId'), axiosConfig)
            .then(res => {
                if (res.data.protect === 'miss') {
                    return;
                }
                if (res.data.cart === 'load cart item: empty list') {
                    callThis.props.dispatch({ type: 'UPDATE_CART_LIST', payload: 'empty' }); return;
                }
                if (res.data.cart.message === 'load cart item: success') {
                    callThis.props.dispatch({ type: 'UPDATE_CART_LIST', payload: res.data.cart.result }); return;
                }
            });
    }
}