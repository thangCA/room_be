import axios from 'axios';
import { axiosConfig } from '../../../App';
import { resetPasswordValidate } from '../Modules/reset-password-validate';

export const handleResetPassword = (callThis, event) => {
    event.preventDefault();

    const validator = resetPasswordValidate(callThis);

    if (validator === 'success') {
        callThis.setState({ showLoadingState: true });

        const postData = {
            email: localStorage.getItem('reset-password'),
            code: callThis.state.verifyCodeInput,
            password: callThis.state.resetPasswordInput
        }

        axios.post('http://localhost:5001/api/authentication/password/reset', postData, axiosConfig)
            .then(res => {
                if (res) {
                    callThis.setState({ showLoadingState: false });
                }
                if (res.data.authentication === 'reset authentication password: verify code is wrong') {
                    alert('Verify code is wrong. Try again, please !'); return;
                }
                if (res.data.authentication === 'reset authentication password: success') {
                    alert('Reset password success !');

                    localStorage.removeItem('reset-password');

                    window.location.reload(); return;
                }
            });
    }
}