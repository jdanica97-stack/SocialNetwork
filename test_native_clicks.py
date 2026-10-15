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

    # 1. Native click on toggle_btn after scroll
    toggle_btn = driver.find_element(By.CSS_SELECTOR, '.comments-toggle-btn')
    post_id = toggle_btn.get_attribute('data-post-id')
    driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", toggle_btn)
    time.sleep(0.5)

    print("Clicking toggle_btn natively...")
    toggle_btn.click()
    time.sleep(1.5)

    comments = driver.find_elements(By.CSS_SELECTOR, f'#comments-list-{post_id} .comment-item')
    print(f"Comments after toggle click: {len(comments)}")
    print(f"Toggle text: '{toggle_btn.text}'")
    print(f"Toggle aria-expanded: '{toggle_btn.get_attribute('aria-expanded')}'")

    # 2. Native click on menu_btn after scroll
    menu_btn = driver.find_element(By.CSS_SELECTOR, '.comment-menu-btn')
    parent_wrap = menu_btn.find_element(By.XPATH, '..')
    dropdown = parent_wrap.find_element(By.CSS_SELECTOR, '.comment-menu-dropdown')
    driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", menu_btn)
    time.sleep(0.5)

    print("\nClicking menu_btn natively...")
    menu_btn.click()
    time.sleep(0.5)

    print(f"Dropdown class after menu click: '{dropdown.get_attribute('class')}'")
    print(f"Dropdown is_displayed: {dropdown.is_displayed()}")

    # 3. Native click on menu_btn again to close
    print("\nClicking menu_btn again to close...")
    menu_btn.click()
    time.sleep(0.5)
    print(f"Dropdown class after 2nd menu click: '{dropdown.get_attribute('class')}'")
    print(f"Dropdown is_displayed: {dropdown.is_displayed()}")

    # 4. Collapse comments
    print("\nClicking toggle_btn to collapse...")
    driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", toggle_btn)
    time.sleep(0.5)
    toggle_btn.click()
    time.sleep(1.0)
    comments_collapsed = driver.find_elements(By.CSS_SELECTOR, f'#comments-list-{post_id} .comment-item')
    print(f"Comments after collapse: {len(comments_collapsed)}")
    print(f"Toggle text after collapse: '{toggle_btn.text}'")
    print(f"Toggle aria-expanded after collapse: '{toggle_btn.get_attribute('aria-expanded')}'")

finally:
    driver.quit()
