import React, { Fragment } from 'react';
import './Store.css';
import { connect } from 'react-redux';
import { withRouter, Link } from 'react-router-dom';
import { context } from '../Context/Context';
import StoreInfoLogo from './StoreInfoLogo';
import StoreInfoBasic from './StoreInfoBasic';
import StoreInfoEdit from './StoreInfoEdit';
import StoreCreateInput from './StoreCreateInput';
import Loading from '../Loading/Loading';
import LocalMallIcon from '@mui/icons-material/LocalMall';
import StorefrontOutlinedIcon from '@mui/icons-material/StorefrontOutlined';
import InfoOutlinedIcon from '@mui/icons-material/InfoOutlined';

class Store extends React.Component {
    render() {
        const storeId = this.props.storeInfo._id;
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
                                    <Link to={ `/sell/from/store/${ storeId }` } className='btn store__render-choice-btn store__render-choice-btn--hover callout-link' >
                                        <p><b>Manage selling product</b></p>
                                    </Link>
                                    {
                                        this.props.accountInfo !== '' ?
                                        <div>
                                            {
                                                this.props.storeInfo !== '' ?                                                                                
                                                <div className='info-manager-inner'>
                                                    <p className='info-manager-inner__title'><b>Store information&ensp;</b><span><StorefrontOutlinedIcon className='info-manager-inner__icon' /></span></p> 
                                                    <hr />
                                                    <div className='store-info-container'>
                                                        <StoreInfoLogo storeIcon={ context.storeIcon } />                                                        
                                                        <StoreInfoBasic storeInfo={ this.props.storeInfo } />                                                        
                                                        <StoreInfoEdit />                                                                                              
                                                    </div>
                                                    {
                                                        this.props.storeInfo.address !== "" ?
                                                        <iframe
                                                            style={{ width: '100%', height: '700px', border: 'none' }}
                                                            referrerpolicy="no-referrer-when-downgrade"
                                                            src={"https://www.google.com/maps/embed/v1/place?key=AIzaSyCqE9f1kdH-cIcRDupATQv5uz6GELylXyg" + "&q=" + this.props.storeInfo.address + "&zoom=18"}
                                                            
                                                        >
                                                        </iframe>:null
                                                    }
                                                </div>:                                                                                        
                                                <div className='info-manager-inner'>
                                                    <p className='info-manager-inner__title'><b>Create store&ensp;</b><span><InfoOutlinedIcon className='info-manager-inner__icon' /></span></p>                                                    
                                                    <hr />                                                    
                                                    <StoreCreateInput accountInfo={ this.props.accountInfo } />
                                                </div>
                                            }
                                        </div>:
                                        <div className='notification'>                                            
                                            <p className='notification__title'><b>Notification</b></p>                                                                                
                                            <hr />                                            
                                            <p className='notification__content'><b>You have to sign in to use this feature !</b></p>                                            
                                            <p>Do you want to sell products ? <b><Link to='/authentication' className='callout-link callout-link--hover'>Sign in, now</Link></b></p>                                        
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

export default connect(mapStateToProps)(withRouter(Store));