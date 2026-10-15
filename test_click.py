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

    menu_btn = driver.find_element(By.CSS_SELECTOR, '.comment-menu-btn')
    
    res = driver.execute_script("""
        arguments[0].scrollIntoView({behavior: 'instant', block: 'center'});
        var rect = arguments[0].getBoundingClientRect();
        var cx = rect.left + rect.width / 2;
        var cy = rect.top + rect.height / 2;
        var topEl = document.elementFromPoint(cx, cy);
        return {
            scrollY: window.scrollY,
            viewportHeight: window.innerHeight,
            rect: rect,
            cx: cx,
            cy: cy,
            topEl: topEl ? {
                tag: topEl.tagName,
                class: topEl.className,
                id: topEl.id,
                outer: topEl.outerHTML.substring(0, 150)
            } : null
        };
    """, menu_btn)
    print("Scroll and elementFromPoint result:", res)

    # Now try clicking using selenium Actions or click
    from selenium.webdriver.common.action_chains import ActionChains
    actions = ActionChains(driver)
    actions.move_to_element(menu_btn).click().perform()
    print("ActionChains click succeeded!")

    dropdown = driver.find_element(By.CSS_SELECTOR, '.comment-menu-dropdown')
    print("Dropdown class after ActionChains click:", dropdown.get_attribute('class'))

finally:
    driver.quit()
