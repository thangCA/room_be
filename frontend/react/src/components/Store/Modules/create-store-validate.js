import { whitespaceValidate } from '../../../modules/validate/whitespace-validate';

export const createStoreValidate = (callThis) => {    
    const storeName = callThis.state.storeNameInput;
    const storeEmail = callThis.state.storeEmailInput;
    const storePhone = callThis.state.storePhoneInput;
    const storeAddress = callThis.state.storeAddressInput;

    if(!storeName || !storeEmail || !storePhone) {
        alert('Name, email, phone are compulsory. Try again, please !'); return;        
    }
    if(whitespaceValidate(storeName) === false || whitespaceValidate(storeEmail) === false || whitespaceValidate(storePhone) === false || whitespaceValidate(storeAddress) === false) {
        alert('Do not use only whitespace or linebreak. Try again, please !'); return;
    }
    if(!(storeEmail.match(/^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/)) || storeEmail.length < 3 || storeEmail.length > 255) {
        alert('This email has wrong format. Try again, please !'); return;            
    }     
    if(!(storeName.match(/^[a-zA-Z0-9 ]*$/))) {            
        alert('Just use these character for name: [a -> z; A -> Z; 0 -> 9]. Try again, please !'); return;            
    }
    if(storeName.length === 0 || storeName.length > 50) {            
        alert('Just use 1 - 50 characters for name. Try again, please !'); return;            
    }
    if(!(storePhone.match(/^(?:(?:\(?(?:00|\+)([1-4]\d\d|[1-9]\d?)\)?)?[\-\.\ \\\/]?)?((?:\(?\d{1,}\)?[\-\.\ \\\/]?){0,})(?:[\-\.\ \\\/]?(?:#|ext\.?|extension|x)[\-\.\ \\\/]?(\d+))?$/i)) || storePhone.length < 3 || storePhone.length > 12) {            
        alert('This phone number has wrong format. Try again, please !'); return;
    }
    else {
        return 'success';
    }
}