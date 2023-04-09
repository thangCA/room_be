import React, { useEffect, useRef, useState } from 'react'
import './PayPal.css';
import { withRouter } from 'react-router-dom';
import { connect } from "react-redux";
import { handlePaypalSuccess } from './APIs/handle-paypal-success';

function PayPal (props) {
    const paypal = useRef();   
    
    //const [paidFor, setPaidFor] = useState(false);

    /*const handleApprove = (orderId) => {
        // Call backend function to fulfill order
    
        // if response is success
        //setPaidFor(true);
        // Refresh user's account or subscription status
    
        // if response is error
        // alert("Your payment was processed successfully. However, we are unable to fulfill your purchase. Please contact us at support@designcode.io for assistance.");
    };*/

    useEffect(() =>{
        window.paypal.Buttons({
            createOrder: (data,actions,err) =>{
                return actions.order.create({
                    intent: "CAPTURE",
                    purchase_units:[
                        {
                        description:props.product.name,
                        amount: {
                            currency_code: "USD",
                            value:props.product.price
                            }
                        }
                    ]
                })                                
            },
            onApprove: async (data,actions) =>{
                const order = await actions.order.capture();    
                handlePaypalSuccess(this, props.order._id);          
            },
            onError: (err) =>{
                console.log("here" + err);
            }
        }).render(paypal.current)
        
    },[])
  return (
    <div className='payment'>
        <div ref={paypal}></div>
    </div>
  )
}

const   mapStateToProps = (state) => {
    return {
      ...state,
    };
  };

  export default connect(mapStateToProps)(withRouter(PayPal));