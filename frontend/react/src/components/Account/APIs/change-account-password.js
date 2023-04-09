import axios from 'axios';
import { axiosConfig } from '../../../App';
import { changeAccountPasswordValidate } from '../Modules/change-account-password-validate';

export const changeAccountPassword = (callThis) => {
    if (!window.confirm('Do you want to save new password ?')) {
        return;
    }
    else {
        const validator = changeAccountPasswordValidate(callThis);

        if (validator === 'success') {
            callThis.setState({ showChangingPasswordState: true });

            const postData = {
                oldPassword: callThis.state.accountOldPasswordInput,
                newPassword: callThis.state.accountNewPasswordInput
            }

            axios.post('http://localhost:5001/api/account/manage/password/change?account=' + callThis.props.accountInfo._id, postData, axiosConfig)
                .then(res => {
                    if (res) {
                        callThis.setState({ showChangingPasswordState: false, showUploadingAvatar: true });
                    }
                    if (res.data.protect === 'miss' || res.data.account === 'change password: account is not existed') {
                        alert('There are problems. Sign in again, please !');

                        callThis.props.history.push('/authentication'); return;
                    }
                    if (res.data.account === 'change password: old password is wrong') {
                        alert('Old password is wrong. Try again, please !'); return;
                    }
                    if (res.data.account === 'change password: success') {
                        callThis.props.history.push('/authentication'); return;
                    }
                });
        }
    }
}