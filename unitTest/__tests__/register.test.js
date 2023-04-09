const puppeteer = require('puppeteer');
const axios = require('axios');
function generateRandomName() {
    const firstNames = ['Alice', 'Bob', 'Charlie', 'David', 'Emily', 'Frank', 'Grace', 'Henry', 'Isabelle', 'Jack', 'Kate', 'Liam', 'Mia', 'Nora', 'Oliver', 'Penny', 'Quentin', 'Riley', 'Samantha', 'Tom', 'Violet', 'William', 'Xander', 'Yvonne', 'Zachary'];
    const lastName = ['Smith', 'Johnson', 'Brown', 'Lee', 'Davis', 'Wilson', 'Miller', 'Garcia', 'Jones', 'Taylor', 'Clark', 'Martinez', 'Anderson', 'Thomas', 'Murphy', 'Moore', 'Martin', 'Jackson', 'Lee', 'White', 'Harris', 'Young', 'King', 'Green', 'Baker'];

    const randomFirstName = firstNames[Math.floor(Math.random() * firstNames.length)];
    const randomLastName = lastName[Math.floor(Math.random() * lastName.length)];

    return randomFirstName + ' ' + randomLastName;
}
function generateRandomPhoneNumber() {
    const areaCodes = ['415', '510', '650', '408', '707', '831', '925', '559', '805', '916'];
    const firstDigits = Math.floor(Math.random() * 899) + 100;
    const lastDigits = Math.floor(Math.random() * 8999) + 1000;
    const areaCode = areaCodes[Math.floor(Math.random() * areaCodes.length)];
    return '(' + areaCode + ') ' + firstDigits + '-' + lastDigits;
}
function generateRandomEmail() {
    let result = '';
    const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    for (let i = 0; i < 10; i++) {
        result += characters.charAt(Math.floor(Math.random() * characters.length));
    }

    result += '@gmail.com';
    return result;
}
describe('Đăng ký trên trình duyệt', () => {
    let browser;
    let page;

    beforeAll(async () => {
        browser = await puppeteer.launch();
        page = await browser.newPage();
    });

    afterAll(async () => {
        await browser.close();
    });

    it('Đăng ký thành công', async () => {
        // Chuẩn bị dữ liệu kiểm tra

        const user = {
            email_: generateRandomEmail(),
            name: generateRandomName(),
            phone: generateRandomPhoneNumber(),
            password: 'Typro1908'
        };
        await page.goto("http://localhost:3000/registration");
        const [email_re] = await page.$x('//input[@id="email"]');
        await email_re.type(user.email_);
        const [po] = await page.$x('//input[@id="phone"]');
        await po.type(user.phone);
        const [name] = await page.$x('//input[@id="username"]');
        await name.type(user.name);
        const [password] = await page.$x('//input[@id="password"]');
        await password.type(user.password);
        const [password_re] = await page.$x('//input[@id="password-again"]');
        await password_re.type(user.password);


        const [bt] = await page.$x('//input[@type="submit"]');
        await bt.click();

        await page.waitForNavigation();

        const url = await page.url();
        console.log(url);
    });
});
