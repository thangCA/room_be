import axios from "axios";
import { axiosConfig } from "../../../../App";

export const loadDiscountList = (callThis, time) => {
    axios.get('http://localhost:5001/api/product/list/discount?time=' + time, axiosConfig)
        .then(res => {
            if (res.data.product.message === 'success') {
                callThis.setState({ discountList: res.data.product.result }); return;
            }
        });
}