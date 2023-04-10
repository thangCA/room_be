const axios = require('axios');
const request = require('supertest');
const cookieParser = require('cookie-parser');
const agent = request.agent('http://localhost:5001');
const user = {
    email: "nguyenducthang.it190801@gmail.com",
    password: "Typro1908"
};




describe('Send Email API', () => {
    before(async () => {
        await agent.post('http://localhost:5001/api/authentication', {
            accountEmail: user.email,
            accountPassword: user.password
        });
    });
    it('should send email a  user', async () => {
        const response = agent.post('http://localhost:5001/api/account/manage/password/change?account=1', {
            old_password: user.password,
            new_password: user.password
        });
        if (response.status === 200) {
            expect(response.status).toBe(200);
        } else if (response.status === 400) {
            expect(response.status).toBe(400);
        }
    });
});
