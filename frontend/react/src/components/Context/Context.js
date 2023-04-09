import React, { Fragment } from 'react';
import { connect } from 'react-redux';
import Routes from '../Routes/Routes';
import fashionCtg from '../../assets/img/category/fashion.png';
import electronicCtg from '../../assets/img/category/electronic.png';
import householdCtg from '../../assets/img/category/household.png';
import accessoryCtg from '../../assets/img/category/accessory.png';
import bookCtg from '../../assets/img/category/book.png';
import sportCtg from '../../assets/img/category/sport.png';
import sneakerCtg from '../../assets/img/category/sneaker.png';
import decorationCtg from '../../assets/img/category/decoration.png';
import sofaCtg from '../../assets/img/category/sofa.png';
import kitchenCtg from '../../assets/img/category/kitchen.png';
import travelCtg from '../../assets/img/category/travel.png';
import beautyCtg from '../../assets/img/category/beauty.png';
import kidCtg from '../../assets/img/category/kid.png';
import cameraCtg from '../../assets/img/category/camera.png';
import waitingBg from '../../assets/img/waiting/waiting.svg';

export const context = React.createContext();

class Context extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            accountIcon: 'https://firebasestorage.googleapis.com/v0/b/vshop-4a2d5.appspot.com/o/assets%2Faccount-icon.png?alt=media&token=4fdd621f-d677-4546-9c90-d878fd21ca86',
            storeIcon: 'https://firebasestorage.googleapis.com/v0/b/vshop-4a2d5.appspot.com/o/assets%2Fstore-icon.png?alt=media&token=30613028-4fd9-40de-bfe0-7b08532ef6ba',
            geoList: [{ country: 'VIETNAM', city: ['Ho Chi Minh', 'Ha Noi', 'Da Nang'] }],
            waitingBg: waitingBg,
            categoryList: [
                {                    
                    img: fashionCtg,
                    name: 'Fashion'
                },
                {                    
                    img: electronicCtg,
                    name: 'Electronic'
                },
                {                    
                    img: householdCtg,
                    name: 'Household'
                },
                {                    
                    img: accessoryCtg,
                    name: 'Accessory'
                },
                {                    
                    img: bookCtg,
                    name: 'Book'
                },
                {                    
                    img: sportCtg,
                    name: 'Sport'
                },
                {                    
                    img: sneakerCtg,
                    name: 'Sneaker'
                },
                {                    
                    img: decorationCtg,
                    name: 'Decoration'
                },
                {                    
                    img: sofaCtg,
                    name: 'Sofa'
                },
                {                    
                    img: kitchenCtg,
                    name: 'Kitchen'
                },
                {                    
                    img: travelCtg,
                    name: 'Travel'
                },
                {                    
                    img: beautyCtg,
                    name: 'Beauty'
                },
                {                    
                    img: kidCtg,
                    name: 'Kid'
                },
                {                    
                    img: cameraCtg,
                    name: 'Camera'
                },
            ],            
        }
    }

    render() {        
        return(
            <Fragment>
                <context.Provider value={ this.state }>
                    <Routes />
                </context.Provider>
            </Fragment>
        );
    }
}

const mapStateToProps = (state) => {
    return {
        ...state
    }
}

export default connect(mapStateToProps)(Context);