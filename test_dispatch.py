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

    # 1. Test dispatchEvent on menu_btn
    menu_btn = driver.find_element(By.CSS_SELECTOR, '.comment-menu-btn')
    parent_wrap = menu_btn.find_element(By.XPATH, '..')
    dropdown = parent_wrap.find_element(By.CSS_SELECTOR, '.comment-menu-dropdown')
    
    print("Before dispatchEvent: dropdown class =", dropdown.get_attribute('class'))
    
    res = driver.execute_script("""
        var btn = arguments[0];
        // Dispatch click on the button
        var evt = new MouseEvent('click', { bubbles: true, cancelable: true, view: window });
        btn.dispatchEvent(evt);
        return {
            btnAria: btn.getAttribute('aria-expanded'),
            dropdownClass: arguments[1].className
        };
    """, menu_btn, dropdown)
    print("After dispatchEvent on menu_btn:", res)

    # 2. Test dispatchEvent on toggle_btn
    toggle_btn = driver.find_element(By.CSS_SELECTOR, '.comments-toggle-btn')
    post_id = toggle_btn.get_attribute('data-post-id')
    print("\nBefore dispatchEvent on toggle_btn: comments count =", len(driver.find_elements(By.CSS_SELECTOR, f'#comments-list-{post_id} .comment-item')))
    
    driver.execute_script("""
        var btn = arguments[0];
        var evt = new MouseEvent('click', { bubbles: true, cancelable: true, view: window });
        btn.dispatchEvent(evt);
    """, toggle_btn)
    
    time.sleep(1.5)
    
    comments_after = driver.find_elements(By.CSS_SELECTOR, f'#comments-list-{post_id} .comment-item')
    print("After dispatchEvent on toggle_btn: comments count =", len(comments_after))
    print("Toggle button text now:", toggle_btn.text)
    print("Toggle button aria-expanded:", toggle_btn.get_attribute('aria-expanded'))

finally:
    driver.quit()
