import axios from 'axios';
import { axiosConfig } from '../../../App';
import { loadAccountManageInfo } from '../../../connection/load-account-manage-info';

export const deleteAccountAvatar = (callThis) => {
    if (!window.confirm('Do you want to delete your account avatar ?')) {
        return;
    }
    else {
        callThis.setState({ showDeletingAvatarState: true });

        const postData = {
            avatar: ''
        }

        axios.post('http://localhost:5001/api/account/manage/avatar/update?account=' + callThis.props.accountInfo._id, postData, axiosConfig)
            .then(res => {
                if (res) {
                    callThis.setState({ showDeletingAvatarState: false, showEditingInfo: true });
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

}