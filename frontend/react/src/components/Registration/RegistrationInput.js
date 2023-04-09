import React, { Fragment } from 'react';
import { withRouter } from 'react-router-dom';
import { handleRegistration } from './APIs/handle-registration';
import { handleChange } from '../../modules/input/handle-change';
import { hidePassword } from '../../modules/password/hide-password';
import RegistrationOption from './RegistrationOption';

class RegistrationInput extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            accountNameInput: '',
            accountPhoneInput: '',
            accountEmailInput: '',
            accountPasswordInput: '',
            passwordAgainInput: '',
            hidePassword: true,
            showLoadingState: false
        }

        //APIs
        this.handleRegistration = this.handleRegistration.bind(this);

        //Modules
        this.handleChangeUsername = this.handleChangeUsername.bind(this);
        this.handleChangePhone = this.handleChangePhone.bind(this);
        this.handleChangeEmail = this.handleChangeEmail.bind(this);
        this.handleChangePassword = this.handleChangePassword.bind(this);
        this.handleChangePasswordAgain = this.handleChangePasswordAgain.bind(this);
        this.hidePassword = this.hidePassword.bind(this);

    }


    //APIs
    handleRegistration(event) {
        handleRegistration(this, event);                        
    }

    //Modules
    handleChangeUsername(event) {
        handleChange(this, event, 'accountNameInput');
    }

    handleChangePhone(event) {
        handleChange(this, event, 'accountPhoneInput');
    }

    handleChangeEmail(event) {
        handleChange(this, event, 'accountEmailInput');
    }

    handleChangePassword(event) {
        handleChange(this, event, 'accountPasswordInput');

    }

    handleChangePasswordAgain(event) {
        handleChange(this, event, 'passwordAgainInput');
    }

    hidePassword() {
        hidePassword(this);
    }

    render() {
        return(
            <Fragment>
                <form onSubmit={ this.handleRegistration }>
                    <label>
                        <input
                            type='text'
                            id='username'
                            placeholder='Username'                                     
                            value={ this.state.accountNameInput }                                     
                            onChange={ this.handleChangeUsername }
                            className='sign__input input input-with-border input-none-border--focus' 
                        />
                    </label>
                    <label>
                        <input
                            type='tel'
                            id='phone'
                            placeholder='Phone'                                     
                            value={ this.state.accountPhoneInput }                                     
                            onChange={ this.handleChangePhone }
                            className='sign__input input input-with-border input-none-border--focus' 
                        />
                    </label>
                    <label>
                        <input
                            type='email'
                            id='email'
                            placeholder='Email'                                     
                            value={ this.state.accountEmailInput }
                            onChange={ this.handleChangeEmail }
                            className='sign__input input input-with-border input-none-border--focus' 
                        />
                    </label>
                    <label>
                        <input
                            type={ this.state.hidePassword ? 'password' : 'text' }
                            placeholder='Password'
                            id='password'
                            value={ this.state.accountPasswordInput }
                            onChange={ this.handleChangePassword }
                            className='sign__input input input-with-border input-none-border--focus' 
                        />
                    </label>
                    <label>
                        <input
                            type={ this.state.hidePassword ? 'password' : 'text' }
                            placeholder='Password again'
                            id='password-again'
                            value={ this.setState.passwordAgainInput }                                     
                            onChange={ this.handleChangePasswordAgain }
                            className='sign__input input input-with-border input-none-border--focus' 
                        />
                    </label>
                    <label className='show-password-container'>
                        <input 
                            type='checkbox'
                            onClick={ this.hidePassword }
                            className='show-password__checkbox'
                        />
                        <p><b>Show password</b></p>              
                    </label>

                    <RegistrationOption showLoadingState={ this.state.showLoadingState } />
                </form>                
            </Fragment>
        );
    }
}

export default withRouter(RegistrationInput);