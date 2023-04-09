import axios from "axios";
import { axiosConfig } from "../../../App";

export const loadBestSellingList = (callThis, time) => {
    axios.get('http://localhost:5001/api/product/list/best-selling?time=' + time, axiosConfig)
        .then(res => {
            console.log(res.data.product);
            if (res.data.product.message === 'success') {
                callThis.setState({ bestSellingList: res.data.product.result }); return;
            }
            else {
                alert('Load best selling list: failed !');
            }
        });
}