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

        const [acc_bt] = await page.$x('//a[@class="searching-inner__account-link searching-inner__account-link--hover"]');
        await acc_bt.click();

        const [change_bt] = await page.$x('///div[@class="account-info__change-password-btn btn white-btn white-btn--hover"]');
        await change_bt.click();

        const [old_password] = await page.$x('//input[@type="password"]');
        await old_password.type(user.password);

        const [new_password] = await page.$x('//input[@type="password"]');
        await new_password.type(user.password);

        const [new_password_again] = await page.$x('//input[@type="password"]');
        await new_password_again.type(user.password);

        await page.waitForNavigation();

        const url = await page.url();
        expect(url).toBe('http://localhost:3000/');
    });


});
