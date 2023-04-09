import React, { Fragment } from 'react';
import SendIcon from '@mui/icons-material/Send';
import './Chatbox.css';
import { handleChange } from '../../modules/input/handle-change';
import { handleChat } from './APIs/handle-chat';

class Chatbox extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            messageList: [],
            messageInput: '',
        }

        this.handleChangeMessageInput = this.handleChangeMessageInput.bind(this);
        this.handleChat = this.handleChat.bind(this);
    }

    handleChangeMessageInput(event) {
        handleChange(this, event, 'messageInput');
    }

    handleChat(event) {
        handleChat(this, event);
    }

    render() {
        return (
            <Fragment>
                <div className="chatbox">
                    <div className="chatbox-header">Vshop AI Chatbot</div>
                    <div className="chatbox-message-list hidden-scrollbar">
                        {
                            this.state.messageList.map((message, index) => <div className={message.from === 0 ? "chatbox-message display-left" : "chatbox-message display-right"}>{message.content}</div>)
                        }
                    </div>
                    <form onSubmit={this.handleChat} className="chatbox-controller">
                        <input type="text" value={this.state.messageInput} placeholder="Type something ..." onChange={this.handleChangeMessageInput} className="chatbox-message-input" />
                        <input type="submit" id="chat-submit" />
                        <label htmlFor="chat-submit"><SendIcon className="chatbox-message-submit-btn chatbox-message-submit-btn--hover" /></label>
                    </form>
                </div>
            </Fragment>
        );
    }
}

export default Chatbox;

