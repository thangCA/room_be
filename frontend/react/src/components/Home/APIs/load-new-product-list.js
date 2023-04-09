import axios from "axios";
import { axiosConfig } from "../../../App";

export const loadNewProductList = (callThis, time) => {
    axios.get('http://localhost:5001/api/product/list/new?time=' + time, axiosConfig)
        .then(res => {
            if (res.data.product.message === 'success') {
                callThis.setState({ newProductList: res.data.product.result }); return;
            }
        });
}