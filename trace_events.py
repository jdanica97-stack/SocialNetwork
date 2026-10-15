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

    toggle_btn = driver.find_element(By.CSS_SELECTOR, '.comments-toggle-btn')
    driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", toggle_btn)
    time.sleep(0.5)

    # Listen to all mouse/pointer events on window and document
    driver.execute_script("""
        window._events = [];
        ['pointerdown', 'pointerup', 'mousedown', 'mouseup', 'click'].forEach(function(type) {
            window.addEventListener(type, function(e) {
                window._events.push({
                    target: 'window',
                    type: type,
                    srcTarget: e.target.tagName + (e.target.className ? '.' + e.target.className : ''),
                    defaultPrevented: e.defaultPrevented,
                    x: e.clientX,
                    y: e.clientY
                });
            }, true);
            document.addEventListener(type, function(e) {
                window._events.push({
                    target: 'document',
                    type: type,
                    srcTarget: e.target.tagName + (e.target.className ? '.' + e.target.className : ''),
                    defaultPrevented: e.defaultPrevented,
                    x: e.clientX,
                    y: e.clientY
                });
            }, true);
        });
    """)

    print("Executing toggle_btn.click()...")
    toggle_btn.click()
    time.sleep(0.5)

    events = driver.execute_script("return window._events;")
    print(f"Recorded {len(events)} events:")
    for ev in events:
        print(" ", ev)

finally:
    driver.quit()
