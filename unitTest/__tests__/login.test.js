const puppeteer = require('puppeteer');
const axios = require('axios');

describe('Đăng nhập trên trình duyệt', () => {
    let browser;
    let page;

    beforeAll(async () => {
        browser = await puppeteer.launch();
        page = await browser.newPage();
    });

    afterAll(async () => {
        await browser.close();
    });

    it('Đăng nhập thành công', async () => {
        // Chuẩn bị dữ liệu kiểm tra
        const user = {
            email: 'nguyenducthang.it190801@gmail.com',
            password: 'Typro1908'
        };

        await page.goto("http://localhost:3000/authentication");
        const [email] = await page.$x('//input[@type="email"]');
        await email.type(user.email);
        const [password] = await page.$x('//input[@type="password"]');
        await password.type(user.password);

        const [bt] = await page.$x('//input[@type="submit"]');
        await bt.click();

        await page.waitForNavigation();

        const url = await page.url();
        expect(url).toBe('http://localhost:3000/');
    });


});
