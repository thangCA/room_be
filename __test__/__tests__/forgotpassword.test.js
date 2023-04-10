const puppeteer = require('puppeteer');
const axios = require('axios');

describe('Lấy lại trên trình duyệt', () => {
    let browser;
    let page;

    beforeAll(async () => {
        browser = await puppeteer.launch();
        page = await browser.newPage();
    });

    afterAll(async () => {
        await browser.close();
    });

    it('Lấy lại thành công', async () => {
        // Chuẩn bị dữ liệu kiểm tra
        const user = {
            email: 'nguyenducthang.it190801@gmail.com',
            password: 'Typro1908',
            code: "123456"
        };
        await page.goto("http://localhost:3000/authentication");

        const [bt] = await page.$x("//a[@class='callout-link callout-link--hover']");
        await bt.click();

        const [email] = await page.$x('//input[@type="email"]');
        await email.type(user.email);

        const [sub_bt] = await page.$x('//input[@type="submit"]');
        await sub_bt.click();

        const [code] = await page.$x('//input[@class="sign__input input input-with-border input-none-border--focus"]');
        await code.type(user.code);

        const [password] = await page.$x('//input[@type="sign__input input input-with-border input-none-border--focus"]');
        await password.type(user.password);

        const [password_again] = await page.$x('//input[@type="sign__input input input-with-border input-none-border--focus"]');
        await password_again.type(user.password);

        const [bt_sub] = await page.$x('//input[@type="submit"]');
        await bt_sub.click();



        await page.waitForNavigation();

        const url = await page.url();
        expect(url).toBe('http://localhost:3000/');
    });


});
