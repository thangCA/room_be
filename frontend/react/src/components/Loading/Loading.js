import React, { Fragment } from 'react';
import './Loading.css';
import RotateRightIcon from '@mui/icons-material/RotateRight';

class Loading extends React.Component {
    render() {
        return(
            <Fragment>
                <div className='loading-wrapper'>                                                   
                    <div className='loading-popup-container'>
                        <RotateRightIcon className='loading-icon loading-popup__icon' />
                        <p><b>Loading...</b></p>
                    </div>
                </div>
            </Fragment>
        );
    }
}

export default Loading;