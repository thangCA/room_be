import axios from 'axios';
import { axiosConfig } from '../../../App';
import { loadStoreManageInfo } from '../../../connection/load-store-manage-info';
import { simpleUploadFirebase } from '../../../modules/firebase/upload';

export const updateStoreLogo = (callThis, storeId, storeEmail) => {
    callThis.setState({ showUploadingLogoState: true });

    const update = () => {
        const postData = {
            logo: callThis.state.logoImg
        }

        axios.post('http://localhost:5001/api/store/manage/logo/update?store=' + storeId, postData, axiosConfig)
            .then(res => {
                if (res) {
                    callThis.setState({ showUploadingLogoState: false });
                }
                if (res.data.protect === 'miss' || res.data.store === 'update store logo: store is not existed') {
                    alert('There are problems. Sign in again, please !');

                    callThis.props.history.push('/authentication'); return;
                }
                if (res.data.store === 'update store logo: success') {
                    loadStoreManageInfo(callThis);

                    callThis.setState({ storeLogoInput: '' }); return;
                }
            });
    }

    const storageRef = 'store/';
    const storageChild = storeEmail + '/' + 'logo/';

    simpleUploadFirebase(callThis, storageRef, storageChild, callThis.state.logoImg, 'logoImg', update);
}