import axios from 'axios';
import { axiosConfig } from '../../../App';
import { loadStoreManageInfo } from '../../../connection/load-store-manage-info';

export const deleteStoreLogo = (callThis, storeId) => {
    if (!window.confirm('Do you want to delete your store logo ?')) {
        return;
    }
    else {
        callThis.setState({ showDeletingLogoState: true });

        const postData = {
            logo: ''
        }

        axios.post('http://localhost:5001/api/store/manage/logo/update?store=' + storeId, postData, axiosConfig)
            .then(res => {
                if (res) {
                    callThis.setState({ showDeletingLogoState: false });
                }
                if (res.data.protect === 'miss' || res.data.store === 'update store logo:  store is not existed') {
                    alert('There are problems. Sign in again, please !');

                    callThis.props.history.push('/authentication'); return;
                }
                if (res.data.store === 'update store logo: success') {
                    loadStoreManageInfo(callThis);

                    callThis.setState({ storeLogoInput: '' }); return;
                }
            });
    }
}