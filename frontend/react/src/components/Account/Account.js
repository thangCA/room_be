import React, { Fragment } from 'react';
import './Account.css';
import { connect } from 'react-redux';
import { context } from '../Context/Context';
import { withRouter, Link } from 'react-router-dom';
import AccountInfoAvatar from './AccountInfoAvatar';
import AccountInfoBasic from './AccountInfoBasic';
import AccountInfoEdit from './AccountInfoEdit';
import AccountOrderList from './AccountOrderList';
import Loading from '../Loading/Loading';
import LocalMallIcon from '@mui/icons-material/LocalMall';
import AccountCircleOutlinedIcon from '@mui/icons-material/AccountCircleOutlined';

class Account extends React.Component {
    render() {
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
                                            <p className='info-manager-inner__title'><b>Account information&ensp;</b><span><AccountCircleOutlinedIcon className='info-manager-inner__icon' /></span></p>                                                                                        
                                            <hr />
                                            <div className='account-info-container'>
                                                <AccountInfoAvatar accountIcon={ context.accountIcon } />
                                                <AccountInfoBasic accountInfo={ this.props.accountInfo } />                                                
                                                <AccountInfoEdit />                                                                                                
                                            </div>
                                            {
                                                this.props.orderList !== 'empty' && this.props.orderList.length > 0 ?
                                                <div>
                                                    <hr />
                                                    <AccountOrderList accountInfo={ this.props.accountInfo } orderList={ this.props.orderList } />
                                                </div>:
                                                null
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

export default connect(mapStateToProps)(withRouter(Account));