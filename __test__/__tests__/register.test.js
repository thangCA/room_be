const faker = require('faker');

const puppeteer = require('puppeteer');

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
            email_: faker.internet.email(),
            name: faker.name.findName(),
            phone: faker.phone.phoneNumber(),
            password: faker.internet.password()
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
