import React, { Fragment } from 'react';
import { BrowserRouter, Route } from 'react-router-dom';
import ChatIcon from '@mui/icons-material/Chat';
import Header from '../Header/Header';
import Registration from '../Registration/Registration';
import Authentication from '../Authentication/Authentication';
import Searching from '../Searching/Searching';
import Home from '../Home/Home';
import Selling from '../Selling/Selling';
import Store from '../Store/Store';
import Product from '../Product/Product';
import Shopping from '../Shopping/Shopping';
import FlashSale from '../Promotion/FlashSale/FlashSale';
import Cart from '../Cart/Cart';
import Account from '../Account/Account';
import Footer from '../Footer/Footer';
import Chatbox from '../Chatbox/Chatbox';

class Routes extends React.Component {    
    constructor(props) {
        super(props);

        this.state = {
            isShowChatbox: false,
        };

        this.toggleChatbox = this.toggleChatbox.bind(this);
    }

    toggleChatbox() {
        this.setState({ isShowChatbox: !this.state.isShowChatbox });
    }

    render() {        
        return(
            <Fragment>    
                {
                    /**
                     * <div className="chatbot-btn chatbot-btn--hover" onClick={this.toggleChatbox}><ChatIcon /></div>    
                {
                    this.state.isShowChatbox ? <Chatbox /> : null
                }
                     */
                }
                        
                <BrowserRouter>
                    <Searching />                    
                    <Header />
                    <Route exact path = '/registration'>
                        <Registration />
                    </Route>
                    <Route exact path = '/authentication'>
                        <Authentication />
                    </Route>
                    <Route exact path = '/'>
                        <Home />
                    </Route>
                    <Route exact path = { ['/sell', '/sell/', '/sell/from/store', '/sell/from/store/', '/sell/from/store/:id'] }>
                        <Selling />
                    </Route>
                    <Route exact path = { ['/account', '/account/', '/account/manage', '/account/manage/', '/account/manage/:id'] }>
                        <Account />
                    </Route>
                    <Route exact path = { ['/store', '/store/', '/store/manage', '/store/manage/', '/store/manage/:id'] }>
                        <Store />
                    </Route>                     
                    <Route exact path = { ['/cart', '/cart/', '/cart/manage', '/cart/manage/', '/cart/manage/of', '/cart/manage/of/', '/cart/manage/of/:id'] }>
                        <Cart />
                    </Route>
                    <Route exact path={ ['/shopping/load/category/:category', '/shopping/load/name/:name'] }>
                        <Shopping />
                    </Route>
                    <Route exact path={ '/promotion/flash-sale' }>
                        <FlashSale />
                    </Route>
                    <Route exact path = { '/product/detail/view/:id' }>
                        <Product />
                    </Route>                                                                             
                    <Footer />
                </BrowserRouter>
            </Fragment>
        );
    }
}

export default Routes;