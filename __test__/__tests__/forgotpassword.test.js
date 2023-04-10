
const axios = require('axios');
const user = {
    email: "nguyenducthang.it190801@gmail.com",
    code: "123456",
    password: "Typro1908"
};


describe('Send Email API', () => {
    it('should send email a  user', async () => {
        const response = await axios.post('http://localhost:5001/api/authentication/password/forgot', {
            email: user.email
        });
        expect(response.status).toBe(200);
    });
});

describe('Change Password API', () => {
    it('should send email a  user', async () => {
        const response = await axios.post('http://localhost:5001/api/authentication/password/reset', {
            email: user.email,
            code: user.code,
            password: user.password
        });
        expect(response.status).toBe(200);
    });
});
