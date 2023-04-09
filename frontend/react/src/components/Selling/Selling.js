import React, { Fragment } from 'react';
import './Selling.css';
import { connect } from 'react-redux';
import { context } from '../Context/Context';
import { withRouter, Link } from 'react-router-dom';
import { changeRenderChoice } from './Modules/change-render-choice';
import { postProductDetail } from './APIs/post-product-detail';
import { changePage, changeRowPerPage } from './Modules/pagination';
import SellingProductList from './SellingProductList';
import SellingInput from './SellingInput';
import Loading from '../Loading/Loading';
import LocalMallIcon from '@mui/icons-material/LocalMall';
import SellOutlinedIcon from '@mui/icons-material/SellOutlined';
import InfoOutlinedIcon from '@mui/icons-material/InfoOutlined';
import Paper from '@mui/material/Paper';
import Table from '@mui/material/Table';
import TableBody from '@mui/material/TableBody';
import TableCell from '@mui/material/TableCell';
import TableContainer from '@mui/material/TableContainer';
import TableHead from '@mui/material/TableHead';
import TablePagination from '@mui/material/TablePagination';
import TableRow from '@mui/material/TableRow';
import { Chart } from "react-google-charts";

class Selling extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            isShowTable: true,
            page: 0,
            rowPerPage: 10,
            productDetailIndex: 0,
            renderChoice: 'list',
            sellingList: [],
            uploadProgress: '',            
        }

        //APIs
        this.postProductDetail = this.postProductDetail.bind(this);        
        
        //Modules
        this.changeRenderChoice = this.changeRenderChoice.bind(this);     
        this.handleChangePage = this.handleChangePage.bind(this);
        this.handleChangeRowsPerPage = this.handleChangeRowsPerPage.bind(this);      
        this.showProductDetail = this.showProductDetail.bind(this);
        this.showTable = this.showTable.bind(this);
    }

    //APIs
    postProductDetail(event) {
        postProductDetail(this, event);
    }

    //Modules
    changeRenderChoice(choice) {
        changeRenderChoice(this, choice);
    }  

    handleChangePage(event, newPage) {
        changePage(this, newPage);
    }

    handleChangeRowsPerPage(event) {
        changeRowPerPage(this, event);
    }

    showProductDetail(index) {
        this.setState({ isShowTable: false, productDetailIndex: index });
    }
    
    showTable() {
        this.setState({ isShowTable: true });
    }

    render() {
        const columns = [
            { id: 'id', label: 'ID', minWidth: 170 },
            { id: 'name', label: 'Name', minWidth: 300 },            
            { id: 'price', label: 'Price', minWidth: 170 },
            { id: 'quantity', label: 'Quantity', minWidth: 170 },      
            { id: 'order', label: 'Order', minWidth: 170 },      
            { id: 'viewDetailButton', label: '', minWidth: 170 },
        ];                

        const revenueChartOption = {
            title: "",
            sliceVisibilityThreshold: 0.01, // 20%
        };

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
                                        <div>
                                            {
                                                this.props.storeInfo !== '' ?
                                                <div>
                                                    {
                                                        this.state.renderChoice === 'list' ?
                                                        <div className='btn selling__render-choice-btn selling__render-choice-btn--hover ' onClick={ () => this.changeRenderChoice('form') }>
                                                            <p><b>Sell product, now &gt;&gt;</b></p>
                                                        </div>:
                                                        <div className='btn selling__render-choice-btn selling__render-choice-btn--hover' onClick={ () => this.changeRenderChoice('list') }>
                                                            <p><b>Manage product &gt;&gt;</b></p>
                                                        </div>                                                        
                                                    }
                                                    {
                                                        this.state.renderChoice === 'list' ?
                                                        <div className='info-manager-inner'>
                                                            <p className='info-manager-inner__title'><b>Selling list&ensp;</b><span><SellOutlinedIcon className='info-manager-inner__icon' /></span></p>                                            
                                                            <hr />                                                            
                                                            {
                                                                this.props.sellingList.length === 0 ?
                                                                <p className='selling-list__notification'><b><i>You have not sold any products yet.</i></b></p>:
                                                                <div>
                                                                    {
                                                                        this.state.isShowTable ?
                                                                        <div>
                                                                            {
                                                                                this.props.sellingListRows.length > 0  ?
                                                                                <div>
                                                                                    {
                                                                                        this.props.revenueChartData.length > 1 ?
                                                                                        <div>
                                                                                            <Chart
                                                                                                chartType="PieChart"
                                                                                                data={this.props.revenueChartData}
                                                                                                options={revenueChartOption}
                                                                                                width={"100%"}
                                                                                                height={"400px"}
                                                                                            />
                                                                                            <p style={{ textAlign: 'center' }}><b><i>Total revenue (consist of shipping cost)</i></b></p>
                                                                                            <hr />
                                                                                        </div>:                                                                                        
                                                                                        null
                                                                                    }                                                                                                                                                                                                                                                            
                                                                                    <Paper style={{ marginTop: '80px' }} sx={{ width: '100%' }}>
                                                                                        <TableContainer sx={{ maxHeight: 440 }}>
                                                                                            <Table stickyHeader aria-label="sticky table">
                                                                                                <TableHead>    
                                                                                                    <TableRow>
                                                                                                        <TableCell style={{ fontSize: '17px' }} align="left" colSpan={6}>
                                                                                                            <p style={{ fontSize: '17px' }}><b>Selling product information</b></p>
                                                                                                        </TableCell>                                                                                                                                                                                   
                                                                                                    </TableRow>                                                                                               
                                                                                                    <TableRow>
                                                                                                    {columns.map((column) => (
                                                                                                        <TableCell
                                                                                                        key={column.id}
                                                                                                        align={column.align}
                                                                                                        style={{ top: 57, minWidth: column.minWidth, fontSize: '17px' }}
                                                                                                        >
                                                                                                        <b>{column.label}</b>
                                                                                                        </TableCell>
                                                                                                    ))}
                                                                                                    </TableRow>
                                                                                                </TableHead>
                                                                                            <TableBody>
                                                                                                {this.props.sellingListRows
                                                                                                .slice(this.state.page * this.state.rowPerPage, this.state.page * this.state.rowPerPage + this.state.rowPerPage)
                                                                                                .map((row, index) => {                                                                                        
                                                                                                    return (
                                                                                                    <TableRow  role="checkbox" tabIndex={-1} key={row.name}>
                                                                                                        {columns.map((column) => {
                                                                                                        const value = row[column.id];
                                                                                                        return (                                                                                    
                                                                                                            <TableCell style={{ fontSize: '17px' }}>
                                                                                                                {
                                                                                                                    value === 'viewDetailButton' ?
                                                                                                                    <div className='btn white-btn white-btn--hover' onClick={ () => this.showProductDetail(index) }><p><b>View detail</b></p></div>:
                                                                                                                    value
                                                                                                                }                                            
                                                                                                            </TableCell>                                                                                
                                                                                                        );
                                                                                                        })}
                                                                                                    </TableRow>
                                                                                                    );
                                                                                                })}
                                                                                            </TableBody>
                                                                                            </Table>
                                                                                        </TableContainer>
                                                                                        <TablePagination
                                                                                            rowsPerPageOptions={[10, 25, 100]}
                                                                                            component="div"
                                                                                            count={this.props.sellingListRows.length}
                                                                                            rowsPerPage={this.state.rowPerPage}
                                                                                            page={this.state.page}
                                                                                            onPageChange={this.handleChangePage}
                                                                                            onRowsPerPageChange={this.handleChangeRowsPerPage}
                                                                                        />
                                                                                    </Paper>
                                                                                </div>:                                                                                    
                                                                                <Loading />
                                                                            }
                                                                        </div>:                                                                        
                                                                        <div>                                                                            
                                                                            <SellingProductList product={ this.props.sellingList[this.state.productDetailIndex].product } order={ this.props.sellingList[this.state.productDetailIndex].order } showTable={ this.showTable } />
                                                                        </div>
                                                                    }                                                                    
                                                                </div>
                                                                
                                                            }
                                                        </div>:
                                                        <div className='info-manager-inner'>
                                                            <p className='info-manager-inner__title'><b>Product information&ensp;</b><span><InfoOutlinedIcon className='info-manager-inner__icon' /></span></p>                                            
                                                            <hr />
                                                            <SellingInput 
                                                                categoryList={ context.categoryList } 
                                                                storeInfo={ this.props.storeInfo } 
                                                                geoList={ context.geoList } 
                                                                submitChoice='post' 
                                                            />
                                                        </div>
                                                    }
                                                </div>:                                        
                                                <div className='notification'>
                                                    <p className='notification__title'><b>Notification</b></p>                                                                                                
                                                    <hr />                                                    
                                                    <p className='notification__content'><b>You have to create a store to use this feature !</b></p>                                                    
                                                    <p>Do you want to sell products ? <b><Link to='/store' className='callout-link callout-link--hover'>Create a store, now</Link></b></p>
                                                
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

export default connect(mapStateToProps)(withRouter(Selling));