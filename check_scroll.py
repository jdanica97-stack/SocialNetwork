import time
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.options import Options

options = Options()
options.add_argument('--headless=new')
options.add_argument('--no-sandbox')
options.add_argument('--disable-dev-shm-usage')
options.add_argument('--window-size=1280,900')

driver = webdriver.Chrome(options=options)

try:
    driver.get('http://localhost/SocialNetwork/public/?url=auth/login')
    driver.find_element(By.NAME, 'username').send_keys('alice_wonder')
    driver.find_element(By.NAME, 'password').send_keys('password123')
    btn = driver.find_element(By.CSS_SELECTOR, 'button[type="submit"]')
    driver.execute_script("arguments[0].click();", btn)
    time.sleep(1.5)

    scroll_info = driver.execute_script("""
        var bodyStyle = window.getComputedStyle(document.body);
        var htmlStyle = window.getComputedStyle(document.documentElement);
        var mainStyle = window.getComputedStyle(document.getElementById('main-content'));
        
        window.scrollTo(0, 500);

        return {
            bodyClass: document.body.className,
            bodyOverflow: bodyStyle.overflow,
            bodyOverflowX: bodyStyle.overflowX,
            bodyOverflowY: bodyStyle.overflowY,
            htmlOverflow: htmlStyle.overflow,
            htmlOverflowX: htmlStyle.overflowX,
            htmlOverflowY: htmlStyle.overflowY,
            mainOverflow: mainStyle.overflow,
            scrollYAfterScrollTo: window.scrollY,
            pageHeight: document.body.scrollHeight,
            windowHeight: window.innerHeight
        };
    """)
    print("Scroll info:", scroll_info)

finally:
    driver.quit()
