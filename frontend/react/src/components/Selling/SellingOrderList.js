import React, { Fragment } from 'react';
import { connect } from 'react-redux';
import { confirmProductOrder } from './APIs/confirm-product-order';
import { refuseOrder } from './APIs/refuse-order';
import BeenhereIcon from '@material-ui/icons/Beenhere';
import Paper from '@mui/material/Paper';
import Table from '@mui/material/Table';
import TableBody from '@mui/material/TableBody';
import TableCell from '@mui/material/TableCell';
import TableContainer from '@mui/material/TableContainer';
import TableHead from '@mui/material/TableHead';
import TablePagination from '@mui/material/TablePagination';
import TableRow from '@mui/material/TableRow';
import { changePage, changeRowPerPage } from './Modules/pagination';
import CsvCreator from 'react-csv-creator';

class SellingOrderList extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            showConfirmingState: false,
            showRefusingState: false,            
            page: 0,
            rowPerPage: 10
        }

        //APIs
        this.confirmProductOrder = this.confirmProductOrder.bind(this);
        this.refuseOrder = this.refuseOrder.bind(this);
        
        //Modules
        this.handleChangePage = this.handleChangePage.bind(this);
        this.handleChangeRowsPerPage = this.handleChangeRowsPerPage.bind(this);
    }

    //APIs
    confirmProductOrder(orderId) {
        confirmProductOrder(this, orderId);
    }

    refuseOrder(orderId) {
        refuseOrder(this, this.props.accountInfo._id, orderId);
    }

    handleChangePage(event, newPage) {
        changePage(this, newPage);
    }

    handleChangeRowsPerPage(event) {
        changeRowPerPage(this, event);
    }

    render() {
        const columns = [
            { id: 'orderID', label: 'ID', minWidth: 170 },
            { id: 'confirmState', label: 'Confirm', minWidth: 170 },
            { id: 'orderTime', label: 'Order Time', minWidth: 170 },
            { id: 'receiver', label: 'Receiver', minWidth: 170 },
            { id: 'phoneNumber', label: 'Phone Number', minWidth: 170 },
            { id: 'address', label: 'Address', minWidth: 170 },
            { id: 'option', label: 'Option', minWidth: 170 },
            { id: 'quantity', label: 'Quantity', minWidth: 170 },
            { id: 'price', label: 'Price', minWidth: 170 },
            { id: 'shippingCost', label: 'Shipping Cost', minWidth: 170 },
            { id: 'totalCost', label: 'Total Cost', minWidth: 170 },
            { id: 'confirmButton', label: '', minWidth: 170 },
            { id: 'refuseButton', label: '', minWidth: 170 },
        ];

        const csvColumns = [
            { id: 'orderID', display: 'ID' },
            { id: 'confirmState', display: 'Confirm' },
            { id: 'orderTime', display: 'Order Time' },
            { id: 'receiver', display: 'Receiver' },
            { id: 'phoneNumber', display: 'Phone Number' },
            { id: 'address', display: 'Address' },
            { id: 'option', display: 'Option' },
            { id: 'quantity', display: 'Quantity' },
            { id: 'price', display: 'Price' },
            { id: 'shippingCost', display: 'Shipping Cost' },
            { id: 'totalCost', display: 'Total Cost' }
        ];

        const csvRows = [];

        const csvFilename = this.props.product._id + '_Order_List';

        const rows = [];
        let confirmedOrderNumber = 0;
        let totalRevenue = 0;

        function createData(orderID, confirmState, orderTime, receiver, phoneNumber, address, option, quantity, price, shippingCost, totalCost, confirmButton, refuseButton) {        
            return { orderID, confirmState, orderTime, receiver, phoneNumber, address, option, quantity, price, shippingCost, totalCost, confirmButton, refuseButton };
        }

        this.props.order.map(item => {
            if(item.state === 'confirmed') {
                confirmedOrderNumber++;
                totalRevenue = totalRevenue + item.price[0] + item.price[1];
            }

            csvRows.push({'orderID': item._id, 'confirmState': item.state, 'orderTime': item.time, 'receiver': item.name, 'phoneNumber': item.phone, 'address': item.address[1] + ' ' + item.address[0], 'option': item.option, 'quantity': item.quantity, 'price': item.price[0], 'shippingCost': item.price[1], 'totalCost': item.price[0] + item.price[1]});
            return rows.push(createData(item._id, item.state, item.time, item.name, item.phone, item.address[2] + ', ' + item.address[1] + ', ' + item.address[0], item.option, item.quantity, item.price[0], item.price[1], item.price[0] + item.price[1], 'confirmButton', 'refuseButton')); 
        });                        

        return(
            <Fragment>
                <div className='order-list-container'>                    
                <Paper style={{ marginTop: '0px' }} sx={{ width: '100%' }}>
                    <TableContainer sx={{ maxHeight: 440 }}>
                        <Table stickyHeader aria-label="sticky table">
                            <TableHead>
                                <TableRow>
                                    <TableCell style={{ fontSize: '17px' }} align="left" colSpan={1}>
                                        <p style={{ fontSize: '17px' }}><b>Order list</b></p>
                                    </TableCell>
                                    <TableCell style={{ fontSize: '17px' }} align="left" colSpan={1}>
                                        <b>Confirmed:</b> {confirmedOrderNumber}
                                    </TableCell> 
                                    <TableCell style={{ fontSize: '17px' }} align="left" colSpan={1}>
                                        <b>Waiting:</b> {rows.length - confirmedOrderNumber}
                                    </TableCell>   
                                    <TableCell style={{ fontSize: '17px' }} align="left" colSpan={3}>
                                        <b>Revenue (consist of shipping cost): </b> {totalRevenue}
                                    </TableCell>    
                                    <TableCell align="left" colSpan={7}>                                        
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
                            {rows
                            .slice(this.state.page * this.state.rowPerPage, this.state.page * this.state.rowPerPage + this.state.rowPerPage)
                            .map((row) => {
                                const id = row['orderID'];
                                return (
                                <TableRow  role="checkbox" tabIndex={-1} key={row.confirmState}>
                                    {columns.map((column) => {
                                    const value = row[column.id];
                                    return (                                                                                    
                                        <TableCell style={{ fontSize: '17px' }}>
                                            {
                                                value === 'confirmButton' && row['confirmState'] === 'waiting' ?
                                                <div>
                                                    {
                                                        this.state.showConfirmingState ?
                                                        <div className='btn white-btn'><p><b>Confirming...</b></p></div>:
                                                        <div className='btn white-btn white-btn--hover' onClick={ () => this.confirmProductOrder(id) }><p><b>Confirm this order</b></p></div>
                                                    }
                                                </div>:
                                                <div>
                                                    {
                                                        value === 'refuseButton' && row['confirmState'] === 'waiting' ?
                                                        <div>
                                                            {
                                                                this.state.showRefusingState ?
                                                                <div className='btn white-btn'><p><b>Refusing...</b></p></div>:
                                                                <div className='btn white-btn white-btn--hover' onClick={ () => this.refuseOrder(id) }><p><b>Refuse</b></p></div>
                                                            }
                                                        </div>:
                                                        <div>
                                                            {
                                                                value === 'refuseButton' || value === 'confirmButton' ?
                                                                null:
                                                                value
                                                            }
                                                        </div>                                                        
                                                    }
                                                </div>
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
                        count={rows.length}
                        rowsPerPage={this.state.rowPerPage}
                        page={this.state.page}
                        onPageChange={this.handleChangePage}
                        onRowsPerPageChange={this.handleChangeRowsPerPage}
                    />
                    </Paper>
                    <CsvCreator
                        filename= {csvFilename}
                        headers={csvColumns}
                        rows={csvRows}                        
                    >
                        <div style={{width: '250px', marginTop: '20px', marginBottom: '70px'}} className='btn gray-btn gray-btn--hover'>
                            <p>Export order list to CSV file</p>
                        </div>                        
                    </CsvCreator>
                </div>
            </Fragment>
        );
    }
}

const mapStateToProps = (state) => {
    return {
        ...state
    }
}

export default connect(mapStateToProps)(SellingOrderList)