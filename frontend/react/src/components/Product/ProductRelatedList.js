import React, { Fragment } from 'react';
import { Link, withRouter } from 'react-router-dom';

class ProductRelatedList extends React.Component {
    constructor(props) {
        super(props);

        this.reloadPage = this.reloadPage.bind(this);
    }

    reloadPage(id) {
        this.props.history.push('/product/detail/view/' + id);
        window.location.reload();
    }

    render() {
        return(
            <Fragment>
                <div className='home-best-selling-container' style={{ boxShadow: 'none', width: 'calc(100% - 400px)', margin: '0px 200px', padding: '0 0' }}>
                    <p className='home-part__title' style={{ fontSize: '20px' }}><b>Related</b></p>                                
                    <div className='best-selling-wrapper'>                                    
                        {
                            this.props.relatedProductList.map(item => 
                                <div style={{ minWidth: '250px', maxWidth: '250px' }} className='shopping__item'>
                                    <div className='shopping-item__img'>
                                        <img alt='product-img' src={ item.file[0].url } />
                                    </div>
                                    <div className='shopping-item__detail'>
                                        {
                                            item.name.length > 50 ?
                                            <p><b><Link className='shopping-item__product-link shopping-item__product-link--hover' onClick={ () => this.reloadPage(item._id) }>{ item.name.slice(0, 50) }...</Link></b></p>:
                                            <p><b><Link className='shopping-item__product-link shopping-item__product-link--hover' onClick={ () => this.reloadPage(item._id) }>{ item.name }</Link></b></p>
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

export default withRouter(ProductRelatedList);