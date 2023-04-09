import React, { Fragment } from 'react';
import './Cart.css';
import { connect } from 'react-redux';
import { context } from '../Context/Context';
import { withRouter, Link } from 'react-router-dom';
import { deleteCartItem } from './APIs/delete-cart-item';
import Loading from '../Loading/Loading';
import LocalMallIcon from '@mui/icons-material/LocalMall';
import ShoppingCartOutlinedIcon from '@mui/icons-material/ShoppingCartOutlined';

class Cart extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            showLoadingState: false
        }

        this.deleteCartItem = this.deleteCartItem.bind(this);
    }

    deleteCartItem(itemId) {
        deleteCartItem(this, itemId);
    }
    
    render() {
        var totalPrice = 0;
        if(this.props.cartList !== 'empty' && this.props.cartList.length > 0) {
            this.props.cartList.map(item => {
                totalPrice = totalPrice + item.price;
            });
        }        
        
        return(
            <Fragment>
                <context.Consumer>
                    {
                        (context) =>
                        <div>
                            {
                                this.props.connectionChecking ?
                                <div className='info-manager-wrapper'>
                                    <p className='info-manager__vshop-name'><b>vShop</b><span><LocalMallIcon className='info-manager__vshop-icon' /></span></p>
                                    {
                                        this.props.accountInfo !== '' ?
                                        <div className='info-manager-inner'>
                                            <p className='info-manager-inner__title'><b>Cart&ensp;</b><span><ShoppingCartOutlinedIcon className='info-manager-inner__icon' /></span></p>                                                                                            
                                            <hr />                                                                                    
                                            {
                                                this.props.cartList !== 'empty' && this.props.cartList.length > 0 ?
                                                <div className='cart__detail'>
                                                    <p className='cart__total-item'><b>Total item:&emsp;</b><span><b className='cart__total-item-value'>{ this.props.cartList.length }</b></span></p>                                            
                                                    <p className='cart__total-price'><b>Total price:&emsp;</b><span><b className='cart__total-price-value'>${ totalPrice }</b></span></p>                                                    
                                                    {
                                                        this.props.cartList.map(item =>
                                                            <div>
                                                                <hr className='horizontal-line' />
                                                                <div className='cart__item'>                                                                    
                                                                    {
                                                                        item.file[0].type === 'image' ?
                                                                        <img alt='product-image' src={ item.file[0].url } className='cart-item__product-file' />:
                                                                        <video alt='product-video' src={ item.file[0].url } className='cart-item__product-file' />
                                                                    }                                                                                                                    
                                                                    <div className='cart-item__detail'>                                                            
                                                                        <p className='cart-item__product-name'><b>{ item.name }</b></p>                                                            
                                                                        <p className='cart-item__product-price'><b>${ item.price }</b></p>                                                                                                                            
                                                                        <div className='cart-item__option-container'>
                                                                            <Link to={ `/product/detail/view/${ item._id }` } className='cart-item__buy-btn yellow-btn btn btn--hover '><p><b>Buy</b></p></Link>                                                                
                                                                            {
                                                                                this.state.showLoadingState ?
                                                                                <div className='cart-item__del-btn gray-btn btn'><p><b>Deleting...</b></p></div>:
                                                                                <div className='cart-item__del-btn gray-btn btn btn--hover' onClick={ () => this.deleteCartItem(item._id) }><p><b>Delete</b></p></div>                                                            
                                                                            }                                                                        
                                                                        </div> 
                                                                    </div> 
                                                                </div> 
                                                            </div>                                                               
                                                        )
                                                    }
                                                </div>:
                                                <p className='cart__notification'><b><i>Your cart is empty.</i></b></p>                                            
                                            }                                                                                
                                        </div>:
                                        <div className='notification'>
                                            <p className='notification__title'><b>Notification</b></p>                                    
                                            <hr />
                                            <p className='notification__content'><b>You have not sign in.</b></p>
                                            <p>Sign in, now ? <b><Link to='/authentication' className='callout-link callout-link--hover'>Yes</Link></b></p>                                    
                                        </div>
                                    }
                                                                        
                                </div>:
                                <div>
                                    <img alt='waiting' src={ context.waitingBg } className='waiting-image' />
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

export default connect(mapStateToProps)(withRouter(Cart));