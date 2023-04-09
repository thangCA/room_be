import axios from 'axios';
import { axiosConfig } from '../../../App';
import { registrationValidate } from '../Modules/registration-validate';

export const handleRegistration = (callThis, event) => {
    event.preventDefault();

    const validator = registrationValidate(callThis);

    if (validator === 'success') {
        callThis.setState({ showLoadingState: true });

        const postData = {
            accountName: callThis.state.accountNameInput,
            accountPhone: callThis.state.accountPhoneInput,
            accountEmail: callThis.state.accountEmailInput,
            accountPassword: callThis.state.accountPasswordInput
        }

        axios.post('http://localhost:5001/api/registration', postData, axiosConfig)
            .then(res => {
                if (res) {
                    callThis.setState({ showLoadingState: false });
                }
                if (res.data.register === 'handle register: email is existed') {
                    alert('This email is existed. Try another, please !'); return;
                }
                if (res.data.register === 'handle register: phone is existed') {
                    alert('This phone number is existed. Try another, please !'); return;
                }
                if (res.data.register === 'handle register: success') {
                    alert('Register success ! Sign in, now');

                    callThis.props.history.push('/authentication'); return;
                }

            });
    }
}