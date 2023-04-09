import React, { createRef, Fragment } from 'react';
import './Product.css';
import { connect } from 'react-redux';
import { context } from '../Context/Context';
import { withRouter } from 'react-router-dom';
import { loadProductDetail } from './APIs/load-product-detail';
import { showOrderForm } from './Modules/show-order-form';  
import { changeFileView } from './Modules/change-file-view';
import { productFilePopup } from './Modules/product-file-popup';
import { handleChange } from '../../modules/input/handle-change';
import { postProductComment } from './APIs/post-product-comment';
import { addCartItem } from './APIs/add-cart-item';
import { showEmojiList } from './Modules/show-emoji-list';
import { insertEmoji } from './Modules/insert-emoji';
import Order from '../Order/Order';
import ProductFile from './ProductFile';
import ProductDetail from './ProductDetail';
import ProductDescription from './ProductDescription';
import ProductComment from './ProductComment';
import Loading from '../Loading/Loading';
import ProductRelatedList from './ProductRelatedList';

class Product extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            emojiList: ['😀', '😅', '🤣', '😉', '😘', '😎', '😥', '😠', '😏', '😮'],
            renderDetail: false,
            showOrderForm: false,
            productStore: '',
            productDetail: '',
            productFileViewType: '',           
            productFileViewUrl: '',            
            productCommentInput: '',
            productCommentList: [],
            showEmojiList: false,
            relatedProductList: [],
            productFilePopup: false,
            showLoadingState: false,            
        }

        //APIs
        this.addCartItem = this.addCartItem.bind(this);
        this.postProductComment = this.postProductComment.bind(this);

        //Modules
        this.showOrderForm = this.showOrderForm.bind(this);
        this.changeFileView = this.changeFileView.bind(this);
        this.productFilePopup = this.productFilePopup.bind(this);
        this.handleChangeComment = this.handleChangeComment.bind(this);                
        this.showEmojiList = this.showEmojiList.bind(this);
        this.insertEmoji = this.insertEmoji.bind(this);
        this.commentEndRef = createRef();
    }
    

    componentDidMount() {
        const { location } = this.props;
        loadProductDetail(this, location.pathname.split('/product/detail/view/')[1]);
    }

    //APIs
    addCartItem() {
        addCartItem(this);
    }

    postProductComment() {
        postProductComment(this);
    }

    //Modules
    showOrderForm() {
        showOrderForm(this);
    }

    changeFileView(type, url) {
        changeFileView(this, type, url);
    }

    productFilePopup() {
        productFilePopup(this);
    }

    handleChangeComment(event) {
        handleChange(this, event, 'productCommentInput');
    }        

    showEmojiList() {
        showEmojiList(this);
    }

    insertEmoji(index) {
        insertEmoji(this, this.state.emojiList, index);
    }

    render() {
        return(
            <Fragment>
                <context.Consumer>
                    {
                        (context) =>
                        <div className='product-viewer-wrapper'>
                            {
                                this.state.renderDetail ?
                                <div>
                                    <div className='product-viewer-inner'>
                                        <ProductFile
                                            changeFileView={ this.changeFileView }
                                            productFileViewType={ this.state.productFileViewType }
                                            productFileViewUrl={ this.state.productFileViewUrl } 
                                            productDetailFile={ this.state.productDetail.file }                                            
                                        />                                       
                                        <ProductDetail
                                            addCartItem={ this.addCartItem }
                                            showOrderForm={ this.showOrderForm }
                                            productDetail={ this.state.productDetail }
                                            productStore={ this.state.productStore }
                                            showLoadingState={ this.state.showLoadingState }
                                        />                                        
                                    </div>
                                    {
                                        this.state.productStore.address !== "" ?
                                        <iframe
                                            style={{ width: 'calc(100% - 400px)', height: '700px', margin: '0 200px', border: 'none' }}
                                            referrerpolicy="no-referrer-when-downgrade"
                                            src={"https://www.google.com/maps/embed/v1/place?key=AIzaSyCqE9f1kdH-cIcRDupATQv5uz6GELylXyg" + "&q=" + this.state.productStore.address + "&zoom=18"}
                                            
                                        >
                                        </iframe>:null
                                    }
                                    <ProductDescription productDetail={ this.state.productDetail } />
                                    <br />
                                    <hr className='horizontal-line' style={{ width: 'calc(100% - 400px)', margin: '150px 200px 0 200px' }} /> 
                                    {
                                        this.state.relatedProductList.length > 0 ?
                                        <ProductRelatedList relatedProductList={ this.state.relatedProductList }/>:null
                                    }                                   
                                    <ProductComment 
                                        productCommentList={ this.state.productCommentList }
                                        accountIcon={ context.accountIcon }
                                        accountInfo={ this.props.accountInfo }
                                        storeInfo={ this.state.productStore }
                                        productInfo={ this.state.productDetail }                                                                                                                                                                                                                                                                                                                                                                   
                                    />
                                    {
                                        this.state.showOrderForm ?
                                        <Order showOrderForm={ this.showOrderForm } productDetail={ this.state.productDetail } geoList={ context.geoList } />:
                                        null
                                    }
                                </div>:
                                <div>
                                    <img alt='waiting-background' src={ context.waitingBg } className='waiting-image' />                                
                                    <Loading />
                                </div>                                
                            }                            
                        </div>
                    }
                </context.Consumer>                
            </Fragment>
        );
    }
}

const mapStateToProps = (state) => {
    return {
        ...state
    }
}

export default connect(mapStateToProps)(withRouter(Product));