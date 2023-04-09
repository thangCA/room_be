import axios from 'axios';
import { axiosConfig } from '../App';

export const loadSellingManageProductList = (callThis, isConfirming) => {
    if (localStorage.getItem('storeId') && localStorage.getItem('storeId') !== '') {
        axios.get('http://localhost:5001/api/selling/manage/product/list/load?store=' + localStorage.getItem('storeId'), axiosConfig)
            .then(res => {
                if (res.data.protect === 'miss' || res.data.product === 'load selling manage product list: store is not existed') {
                    return;
                }
                if (res.data.product.message === 'load selling manage product list: success') {
                    const result = res.data.product.result;
                    callThis.props.dispatch({ type: 'UPDATE_SELLING_LIST', payload: result });
                    const rows = [];

                    function createData(id, name, price, quantity, order, viewDetailButton) {
                        return { id, name, price, quantity, order, viewDetailButton };
                    }

                    let totalRevenue = 0;
                    const revenueChartData = [['Product', 'Revenue']];

                    for (let i = 0; i <= result.length - 1; i++) {
                        if (result[i].order.length === 0) {
                            rows.push(createData(result[i].product._id, result[i].product.name, result[i].product.price + ' $', result[i].product.quantity, 0, 'viewDetailButton'));
                        }
                        else {
                            for (let j = 0; j <= result[i].order.length - 1; j++) {

                                if (result[i].order[j].state === 'confirmed') {
                                    totalRevenue = totalRevenue + result[i].order[j].price[0] + result[i].order[j].price[1];
                                }

                                if (j === result[i].order.length - 1) {
                                    rows.push(createData(result[i].product._id, result[i].product.name, result[i].product.price + ' $', result[i].product.quantity, result[i].order.length, 'viewDetailButton'));
                                }
                            }
                        }
                    }

                    for (let j = 0; j <= result.length - 1; j++) {
                        if (result[j].order[0] && result[j].order[0].state === 'confirmed') {
                            const revenueChartDataItem = [result[j].product.name, ((result[j].order[0].price[0] + result[j].order[0].price[1]) * 100) / totalRevenue];
                            revenueChartData.push(revenueChartDataItem);
                        }

                        if (j === result.length - 1) {
                            callThis.props.dispatch({ type: 'UPDATE_SELLING_LIST_ROWS', payload: rows });
                            callThis.props.dispatch({ type: 'UPDATE_REVENUE_CHART_DATA', payload: revenueChartData });
                            if (isConfirming && isConfirming === true) {
                                callThis.props.dispatch({ type: 'UPDATE_CONFIRMING_STATE', payload: false });
                                alert('confirm order success !');
                            }
                        }
                    }
                }
            });
    }
    else {
        return;
    }
}