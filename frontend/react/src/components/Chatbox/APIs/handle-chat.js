import axios from "axios";
import { axiosConfig } from "../../../App";

export const handleChat = (callThis, event) => {
    event.preventDefault();

    const postData = {
        content: callThis.state.messageInput,
    };

    axios.post('http://localhost:5001/api/chat', postData, axiosConfig)
        .then(res => {
            if (res.data.chat.message === "handle chat: success") {
                const messageList = [...callThis.state.messageList];

                messageList.push({
                    from: 1,
                    content: postData.content
                });
                if (res.data.chat.response) {
                    messageList.push({
                        from: 0,
                        content: res.data.chat.response
                    });
                }
                callThis.setState({ messageInput: '' });
                callThis.setState({ messageList: messageList });
            }
        });
}