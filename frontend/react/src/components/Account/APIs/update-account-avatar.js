import axios from 'axios';
import { axiosConfig } from '../../../App';
import { loadAccountManageInfo } from '../../../connection/load-account-manage-info';
import { simpleUploadFirebase } from '../../../modules/firebase/upload';

export const updateAccountAvatar = (callThis, accountId, accountEmail) => {
    callThis.setState({ showUploadingAvatarState: true });

    const update = () => {
        const postData = {
            avatar: callThis.state.avatarImg
        }

        axios.post('http://localhost:5001/api/account/manage/avatar/update?account=' + accountId, postData, axiosConfig)
            .then(res => {
                if (res) {
                    callThis.setState({ showUploadingAvatarState: false });
                }
                if (res.data.protect === 'miss' || res.data.account === 'update account avatar: account is not existed') {
                    alert('There are problems. Sign in again, please !');

                    callThis.props.history.push('/authentication'); return;
                }
                if (res.data.account === 'update account avatar: success') {
                    loadAccountManageInfo(callThis);

                    callThis.setState({ accountAvatarInput: '' }); return;
                }
            });
    }

    const storageRef = 'account/';
    const storageChild = accountEmail + '/' + 'avatar/';

    simpleUploadFirebase(callThis, storageRef, storageChild, callThis.state.avatarImg, 'avatarImg', update);
}