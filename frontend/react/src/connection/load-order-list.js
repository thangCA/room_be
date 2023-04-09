import axios from 'axios';
import { axiosConfig } from '../App';

export const loadOrderList = (callThis) => {
    if (localStorage.getItem('accountId')) {
        axios.get('http://localhost:5001/api/order/list/load?account=' + localStorage.getItem('accountId'), axiosConfig)
            .then(res => {
                if (res.data.protect === 'miss') {
                    return;
                }
                console.log(res.data);
                if (res.data.order.message === 'load order list: empty') {
                    callThis.props.dispatch({ type: 'UPDATE_ORDER_LIST', payload: 'empty' }); return;
                }
                if (res.data.order.message === 'load order list: success') {
                    callThis.props.dispatch({ type: 'UPDATE_ORDER_LIST', payload: res.data.order.result }); return;
                }
            });
    }
    else {
        return;
    }
}