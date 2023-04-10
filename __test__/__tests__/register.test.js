function randomString(length) {
    let result = '';
    const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    for (let i = 0; i < length; i++) {
        result += characters.charAt(Math.floor(Math.random() * characters.length));
    }
    return result;
}
const axios = require('axios');
const phone = '098' + randomString(7);
const email = randomString(10) + '@example.com';
const name = 'Test User';
const password = 'password' + randomString(6);

const user = {
    "accountName": name,
    "accountEmail": email,
    "accountPhone": phone,
    "accountPassword": password
};

describe('Registration API', () => {
    it('should register a new user', async () => {
        const response = await axios.post('http://localhost:5001/api/registration', user);
        expect(response.status).toBe(201);
    });
});

