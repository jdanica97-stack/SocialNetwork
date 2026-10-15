import time
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.action_chains import ActionChains

options = Options()
options.add_argument('--headless=new')
options.add_argument('--no-sandbox')
options.add_argument('--disable-dev-shm-usage')
options.add_argument('--window-size=1280,900')
options.set_capability('goog:loggingPrefs', {'browser': 'ALL'})

driver = webdriver.Chrome(options=options)

try:
    driver.get('http://localhost/SocialNetwork/public/?url=auth/login')
    driver.find_element(By.NAME, 'username').send_keys('alice_wonder')
    driver.find_element(By.NAME, 'password').send_keys('password123')
    btn = driver.find_element(By.CSS_SELECTOR, 'button[type="submit"]')
    driver.execute_script("arguments[0].click();", btn)
    time.sleep(1.5)

    # Attach diagnostic listener to document
    driver.execute_script("""
        window._clickLog = [];
        document.addEventListener('click', function(e) {
            window._clickLog.push({
                target: e.target.tagName + (e.target.className ? '.' + e.target.className : ''),
                targetType: typeof e.target.className,
                isCircle: e.target.tagName === 'circle',
                closestCmtBtn: e.target.closest ? (e.target.closest('.comment-menu-btn') ? true : false) : 'no closest',
                closestToggleBtn: e.target.closest ? (e.target.closest('.comments-toggle-btn') ? true : false) : 'no closest',
                defaultPrevented: e.defaultPrevented
            });
        }, true); // capturing phase!
    """)

    menu_btn = driver.find_element(By.CSS_SELECTOR, '.comment-menu-btn')
    driver.execute_script("arguments[0].scrollIntoView({behavior: 'instant', block: 'center'});", menu_btn)
    time.sleep(0.5)

    # Click using ActionChains
    actions = ActionChains(driver)
    actions.move_to_element(menu_btn).click().perform()
    time.sleep(0.5)

    click_log = driver.execute_script("return window._clickLog;")
    print("Click log after clicking menu_btn:", click_log)

    dropdown = driver.find_element(By.CSS_SELECTOR, '.comment-menu-dropdown')
    print("Dropdown class after click:", dropdown.get_attribute('class'))

    # Now test clicking the toggle button
    toggle_btn = driver.find_element(By.CSS_SELECTOR, '.comments-toggle-btn')
    driver.execute_script("arguments[0].scrollIntoView({behavior: 'instant', block: 'center'});", toggle_btn)
    time.sleep(0.5)

    actions = ActionChains(driver)
    actions.move_to_element(toggle_btn).click().perform()
    time.sleep(1.5)

    click_log2 = driver.execute_script("return window._clickLog;")
    print("Click log after clicking toggle_btn:", click_log2)
    print("Toggle button text after click:", toggle_btn.text)
    print("Toggle button aria-expanded:", toggle_btn.get_attribute('aria-expanded'))

    # Console logs
    logs = driver.get_log('browser')
    print("\nBrowser console logs:")
    for l in logs:
        print(f"[{l['level']}] {l['message']}")

finally:
    driver.quit()
