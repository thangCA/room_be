import React, { Fragment } from 'react';
import { loadDiscountList } from './APIs/load-discount-list';
import { Link, withRouter } from 'react-router-dom';

const date = new Date();
const month = date.getMonth() + 1;
const now = date.getFullYear() + '/' + month;

class FlashSale extends React.Component {
    constructor(props) {
        super(props);

        this.state = {            
            discountList: []
        }
    }

    componentDidMount() {
        loadDiscountList(this, date.getFullYear() + '-' + month + '-');
    }

    render() {
        return(
            <Fragment>
                <div className='home-discount-container' style={{ width: 'calc(100% - 200px)', padding: '50px 100px' }}>
                    <p className='home-part__title' style={{ fontSize: '20px' }}><b>Discount in {now}</b></p>                                
                    <div className='discount-wrapper' style={{ boxShadow: 'none' }}>                                    
                        {
                            this.state.discountList.map(item => 
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
            </Fragment>
        );
    }
}

export default withRouter(FlashSale);