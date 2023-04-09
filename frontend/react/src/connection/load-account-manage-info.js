import axios from 'axios';
import { axiosConfig } from '../App';

export const loadAccountManageInfo = (callThis) => {
    if (localStorage.getItem('accountId')) {
        axios.get('http://localhost:5001/api/account/manage/info/load?account=' + localStorage.getItem('accountId'), axiosConfig)
            .then(res => {
                if (res.data.protect === 'miss' || res.data.info === 'load account manage info: account is not existed') {
                    return;
                }
                if (res.data.info.message === 'load account manage info: success') {
                    callThis.props.dispatch({ type: 'UPDATE_ACCOUNT_INFO', payload: res.data.info.result }); return;
                }
            });
    }
}