import axios from 'axios';
import { axiosConfig } from '../../../App';

export const loadProductDetail = (callThis, productId) => {
    axios.get('http://localhost:5001/api/product/detail/load?product=' + productId, axiosConfig)
        .then(res => {
            if (res.data.product === 'load product detail: product is not existed') {
                alert('Product is not existed. Try others, please !');

                callThis.props.history.push('/'); return;
            }
            console.log(res.data.product.message);
            if (res.data.product.message === 'load product detail: success') {
                callThis.setState({
                    productStore: res.data.product.store,
                    productDetail: res.data.product.detail,
                    productCommentList: res.data.product.comment,
                    productFileViewType: res.data.product.detail.file[0].type,
                    productFileViewUrl: res.data.product.detail.file[0].url,
                    renderDetail: true
                });



                const relatedProductList = [];

                const categoryList = res.data.product.detail.category;

                function getProductListOnCategory(category) {
                    axios.get('http://localhost:5001/api/product/list/category/load?category=' + category, axiosConfig)
                        .then(res => {
                            if (res.data.product.message === 'success') {
                                relatedProductList.push(...res.data.product.result)
                                callThis.setState({ relatedProductList: relatedProductList });
                            }
                        });
                }

                categoryList.map(item => getProductListOnCategory(item));
            }
        });
}