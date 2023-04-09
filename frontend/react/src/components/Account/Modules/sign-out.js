import Cookies from "js-cookie"

export const signOut = (callThis) => {
    Cookies.remove('access_token');
    
    localStorage.clear();

    callThis.props.history.push('/authentication');

    window.location.reload(); return;    
}