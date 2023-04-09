import React, { Fragment } from 'react';
import './Home.css';
import { Link, withRouter } from 'react-router-dom';
import { connect } from 'react-redux';
import { context } from '../Context/Context';
import flashSaleBannerWhite from '../../assets/img/promotion/flash-sale-banner-white.jpg'
import { loadBestSellingList } from './APIs/load-best-selling-list';
import { loadNewProductList } from './APIs/load-new-product-list';

const date = new Date();
const month = date.getMonth() + 1;
const now = date.getFullYear() + '/' + month;

class Home extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            bestSellingList: [],
            newProductList: []
        }
    }

    componentDidMount() {
        loadBestSellingList(this, date.getFullYear() + '-' + month);
        loadNewProductList(this, date.getFullYear() + '-' + month);        
    }

    render() {              
        return(
            <Fragment>
                <context.Consumer>
                    {
                        (context) => 
                        <div className='home-wrapper'>                                                                                                    
                            <div className='home-welcome-container'>
                                <p>Welcome to<b> vShop</b></p>
                                <p><b>-- <i>"Buy more. Spend less"</i> --</b></p>                       
                            </div>
                            <div className='home-category-container'>
                                {
                                    context.categoryList.map(item =>
                                        <div className='home-category__item'>
                                            
                                            <img alt='vshop-catagory-item' src={ item.img } />
                                            <p className='home-category-item__name'><b><Link to={ `/shopping/load/category/${ item.name }` } className='shopping-on-category-link shopping-on-category-link--hover'>{ item.name }</Link></b></p>                                                                                        
                                        </div>
                                    )
                                }
                            </div>
                            <div className='home-best-selling-container'>
                                <p className='home-part__title' style={{ fontSize: '20px' }}><b>Best selling in {now}</b></p>                                
                                <div className='best-selling-wrapper'>                                    
                                    {
                                        this.state.bestSellingList.map(item => 
                                            <div style={{ minWidth: '250px', maxWidth: '250px' }} className='shopping__item'>
                                                <div className='shopping-item__img'>
                                                    <img alt='product-img' src={ item.file[0].url } />
                                                </div>
                                                <div className='shopping-item__detail'>
                                                    {
                                                        item.name.length > 50 ?
                                                        <p><b><Link to={ `/product/detail/view/${ item._id }` } className='shopping-item__product-link shopping-item__product-link--hover'>{ item.name.slice(0, 50) }...</Link></b></p>:
                                                        <p><b><Link to={ `/product/detail/view/${ item._id }` } className='shopping-item__product-link shopping-item__product-link--hover'>{ item.name }</Link></b></p>
                                                    }
                                                </div>
                                                <div className='shopping-item__detail'>
                                                    {
                                                        item.discount > 0 ?
                                                        <p><b className='shopping-item__old-price'>${ item.price + item.discount }</b>&emsp;--&emsp;<b className='shopping-item__price'>${ item.price }</b></p>:
                                                        <p className='shopping-item__price'><b>${ item.price }</b></p>
                                                    }
                                                </div>  
                                                <div className='shopping-item__detail'>
                                                    <p>{ item.quantity } item(s) more</p>
                                                </div>  
                                                <div className='shopping-item__detail'>
                                                    <p className='shopping-item__address'><b>{ item.address[1] }, { item.address[0] }</b></p>
                                                </div>                                                                   
                                            </div>
                                        )
                                    }
                                </div>                              
                            </div>
                            <Link to={'/promotion/flash-sale'}><img alt='flash-sale-banner' src={ flashSaleBannerWhite } className='flash-sale-banner' /></Link>
                            <div className='home-new-product-container'>
                                <p className='home-part__title' style={{ fontSize: '20px' }}><b>New product in {now}</b></p>
                                
                                <div className='new-product-wrapper'>                                    
                                    {
                                        this.state.newProductList.map(item => 
                                            <div style={{ minWidth: '250px', maxWidth: '250px' }} className='shopping__item'>
                                                <div className='shopping-item__img'>
                                                    <img alt='product-img' src={ item.file[0].url } />
                                                </div>
                                                <div className='shopping-item__detail'>
                                                    {
                                                        item.name.length > 50 ?
                                                        <p><b><Link to={ `/product/detail/view/${ item._id }` } className='shopping-item__product-link shopping-item__product-link--hover'>{ item.name.slice(0, 50) }...</Link></b></p>:
                                                        <p><b><Link to={ `/product/detail/view/${ item._id }` } className='shopping-item__product-link shopping-item__product-link--hover'>{ item.name }</Link></b></p>
                                                    }
                                                </div>
                                                <div className='shopping-item__detail'>
                                                    {
                                                        item.discount > 0 ?
                                                        <p><b className='shopping-item__old-price'>${ item.price + item.discount }</b>&emsp;--&emsp;<b className='shopping-item__price'>${ item.price }</b></p>:
                                                        <p className='shopping-item__price'><b>${ item.price }</b></p>
                                                    }
                                                </div>  
                                                <div className='shopping-item__detail'>
                                                    <p>{ item.quantity } item(s) more</p>
                                                </div>  
                                                <div className='shopping-item__detail'>
                                                    <p className='shopping-item__address'><b>{ item.address[1] }, { item.address[0] }</b></p>
                                                </div>                                                                   
                                            </div>
                                        )
                                    }
                                </div>                              
                            </div>                                                                                  
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

export default connect(mapStateToProps)(withRouter(Home));