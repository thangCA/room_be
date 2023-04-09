import React, { Fragment } from 'react';
import './Footer.css';
import FacebookIcon from '@mui/icons-material/Facebook';
import InstagramIcon from '@mui/icons-material/Instagram';
import WhatsAppIcon from '@mui/icons-material/WhatsApp';

class Footer extends React.Component {
    render() {
        return(
            <Fragment>
                <div className='footer-wrapper'>
                    <div className='footer-body--top'></div>
                    <div className='footer-body--middle'>
                        <div className='footer-content-column'>
                            <b style={{ fontSize: '17px' }}>Team</b>
                            <p style={{ marginTop: '20px' }}>Name:&emsp;Vtekapt<br /><br />Phone:&emsp;0909090909<br /><br />Email:&emsp;abc@gmail.com</p>
                            <p style={{ marginTop: '20px' }}>Name:&emsp;Vtekapt<br /><br />Phone:&emsp;0808080808<br /><br />Email:&emsp;abc@gmail.com</p>
                        </div>
                        <div className='footer-content-column'>
                            <b style={{ fontSize: '17px' }}>Contact</b>
                            <p style={{ marginTop: '20px' }}>Phone:&emsp;010101010101<br /><br />Email:&emsp;vshop@gmail.com<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /></p>
                        </div>
                        <div className='footer-content-column'>
                            <b style={{ fontSize: '17px' }}>Social</b>
                            <div style={{ marginTop: '20px', display: 'flex', flexDirection: 'row' }}>
                                <FacebookIcon />
                                <InstagramIcon style={{ margin: '0 5px' }} />
                                <WhatsAppIcon />
                            </div>
                            <br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
                        </div>                        
                    </div>
                    <div className='footer-body--bottom'><b>Copyright &copy; 2023 vShop. All right reserved</b></div>
                </div>
            </Fragment>
        );
    }
}

export default Footer;