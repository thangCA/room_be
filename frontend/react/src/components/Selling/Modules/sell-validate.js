import { whitespaceValidate } from '../../../modules/validate/whitespace-validate';

export const sellValidate = (callThis) => {
    const country = callThis.state.countryInput;
    const city = callThis.state.cityInput;
    const address = callThis.state.addressInput;
    const productName = callThis.state.productNameInput;
    const productImage = callThis.state.productImage;
    const productOption = callThis.state.productOptionInput;
    const productPrice = callThis.state.productPriceInput;
    const productQuantity = callThis.state.productQuantityInput;
    const productCategory = callThis.state.productCategory;
    const productDescription = callThis.state.productDescriptionInput;

    if(!country || !city || !address || !productName || !productImage || !productPrice || !productQuantity || !productCategory) {
        alert('Address, name, image, price, category are compulsory. Try again, please !'); return;        
    }
    if(whitespaceValidate(address) === false || whitespaceValidate(productName) === false || whitespaceValidate(productOption) === false || whitespaceValidate(productDescription) === false) {
        alert('Do not use only whitespace or linebreak. Try again, please !'); return;
    }
    if(productOption) {
        if(productOption.length < 1 || productOption.length > 20) {            
            alert('Just use 1 - 20 characters for option. Try again, please !'); return;
        }
    }
    if(productDescription) {        
        if(productDescription.length < 2 || productDescription.length > 10000) {            
            alert('Just use 2 - 10000 characters for description. Try again, please !'); return;
        }        
    }    
    if(productPrice > 100000) {
        alert('Just set price from 0 - 100000$. Try again, please !'); return;        
    }
    if(address.length > 250) {
        alert('Just use 1 - 250 characters for address. Try again, please !'); return;
    }   
    else {
        return 'success';
    }
}