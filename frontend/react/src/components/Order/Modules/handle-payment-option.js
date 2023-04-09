export const handlePaymentOption = (callThis, event) => {
    callThis.setState({ paymentOption: event.target.value });
}