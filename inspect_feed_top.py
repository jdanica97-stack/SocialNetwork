import time
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.options import Options

options = Options()
options.add_argument('--headless=new')
options.add_argument('--no-sandbox')
options.add_argument('--disable-dev-shm-usage')
options.add_argument('--window-size=1280,1000')

driver = webdriver.Chrome(options=options)

try:
    driver.get('http://localhost/SocialNetwork/public/?url=auth/login')
    driver.find_element(By.NAME, 'username').send_keys('alice_wonder')
    driver.find_element(By.NAME, 'password').send_keys('password123')
    btn = driver.find_element(By.CSS_SELECTOR, 'button[type="submit"]')
    driver.execute_script("arguments[0].click();", btn)
    time.sleep(1.5)

    posts = driver.find_elements(By.CSS_SELECTOR, '.post-card')
    print(f"Total posts on feed: {len(posts)}")
    for i, p in enumerate(posts[:3]):
        pid = p.get_attribute('id')
        rect = driver.execute_script("return arguments[0].getBoundingClientRect();", p)
        tbtn = p.find_elements(By.CSS_SELECTOR, '.comments-toggle-btn')
        mbtn = p.find_elements(By.CSS_SELECTOR, '.comment-menu-btn')
        print(f"Post {i} ({pid}): top={rect['top']}, height={rect['height']}")
        print(f"  Toggle buttons: {len(tbtn)}, Menu buttons: {len(mbtn)}")

finally:
    driver.quit()
