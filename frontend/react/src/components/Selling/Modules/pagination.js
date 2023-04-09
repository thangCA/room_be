export const changePage = (callThis, newPage) => {
    callThis.setState({ page: newPage });
}

export const changeRowPerPage = (callThis, event) => {
    callThis.setState({ rowPerPage: +event.target.value });
    changePage(callThis, 0);
}