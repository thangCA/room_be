import axios from 'axios';
import { axiosConfig } from '../App';

export const loadStoreManageInfo = (callThis) => {
    if (localStorage.getItem('storeId') && localStorage.getItem('storeId') !== '') {
        axios.get('http://localhost:5001/api/store/manage/info/load?store=' + localStorage.getItem('storeId'), axiosConfig)
            .then(res => {
                if (res.data.protect === 'miss' || res.data.info === 'load store manage info: store is not existed') {
                    return;
                }
                if (res.data.info.message === 'load store manage info: success') {
                    callThis.props.dispatch({ type: 'UPDATE_STORE_INFO', payload: res.data.info.result });
                    return;
                }
            });
    }
    else {
        return;
    }
}