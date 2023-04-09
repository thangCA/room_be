import axios from 'axios';
import { axiosConfig } from '../../../App';
import { loadStoreManageInfo } from '../../../connection/load-store-manage-info';
import { editStoreValidate } from '../Modules/edit-store-validate';

export const editStoreInfo = (callThis, event, storeId) => {
    event.preventDefault();

    if (!window.confirm('Do you want to save new info')) {
        return;
    }
    else {
        const validator = editStoreValidate(callThis);

        if (validator === 'success') {
            callThis.setState({ showEditingInfoState: true });

            const postData = {
                name: callThis.state.storeNameInput,
                email: callThis.state.storeEmailInput,
                phone: callThis.state.storePhoneInput,
                address: callThis.state.storeAddressInput,
            }

            axios.post('http://localhost:5001/api/store/manage/info/edit?store=' + storeId, postData, axiosConfig)
                .then(res => {
                    if (res) {
                        callThis.setState({ showEditingInfoState: false });
                    }
                    if (res.data.protect === 'miss' || res.data.store === 'edit store info: store is not existed') {
                        alert('There are problems. Sign in again, please !');

                        callThis.props.history.push('/authentication'); return;
                    }
                    if (res.data.store === 'edit store info: email is existed') {
                        alert('This email is existed. Try again, please !'); return;
                    }
                    if (res.data.store === 'edit store info: success') {
                        loadStoreManageInfo(callThis);

                        callThis.setState({ storeNameInput: '', storeEmailInput: '', storePhoneInput: '', storeAddressInput: '' }); return;
                    }
                });

        }
    }
}