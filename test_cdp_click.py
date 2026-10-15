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
    post_id = toggle_btn.get_attribute('data-post-id')
    
    # Scroll and get viewport coordinates
    coords = driver.execute_script("""
        arguments[0].scrollIntoView({block: 'center'});
        var r = arguments[0].getBoundingClientRect();
        return {
            x: Math.round(r.left + r.width / 2),
            y: Math.round(r.top + r.height / 2)
        };
    """, toggle_btn)
    print("Toggle button viewport center coords:", coords)

    # Use CDP Input.dispatchMouseEvent to simulate real mouse click
    driver.execute_cdp_cmd('Input.dispatchMouseEvent', {
        'type': 'mousePressed',
        'x': coords['x'],
        'y': coords['y'],
        'button': 'left',
        'clickCount': 1
    })
    driver.execute_cdp_cmd('Input.dispatchMouseEvent', {
        'type': 'mouseReleased',
        'x': coords['x'],
        'y': coords['y'],
        'button': 'left',
        'clickCount': 1
    })
    
    time.sleep(1.5)

    comments = driver.find_elements(By.CSS_SELECTOR, f'#comments-list-{post_id} .comment-item')
    print(f"Comments after CDP click: {len(comments)}")
    print("Toggle button text:", toggle_btn.text)
    print("Toggle aria-expanded:", toggle_btn.get_attribute('aria-expanded'))

    # Now test menu_btn with CDP
    menu_btn = driver.find_element(By.CSS_SELECTOR, '.comment-menu-btn')
    parent_wrap = menu_btn.find_element(By.XPATH, '..')
    dropdown = parent_wrap.find_element(By.CSS_SELECTOR, '.comment-menu-dropdown')

    m_coords = driver.execute_script("""
        arguments[0].scrollIntoView({block: 'center'});
        var r = arguments[0].getBoundingClientRect();
        return {
            x: Math.round(r.left + r.width / 2),
            y: Math.round(r.top + r.height / 2)
        };
    """, menu_btn)
    print("\nMenu button viewport center coords:", m_coords)

    driver.execute_cdp_cmd('Input.dispatchMouseEvent', {
        'type': 'mousePressed',
        'x': m_coords['x'],
        'y': m_coords['y'],
        'button': 'left',
        'clickCount': 1
    })
    driver.execute_cdp_cmd('Input.dispatchMouseEvent', {
        'type': 'mouseReleased',
        'x': m_coords['x'],
        'y': m_coords['y'],
        'button': 'left',
        'clickCount': 1
    })
    time.sleep(0.5)

    print("Dropdown class after CDP click:", dropdown.get_attribute('class'))
    print("Dropdown is_displayed:", dropdown.is_displayed())

finally:
    driver.quit()
