import time
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.options import Options

options = Options()
options.add_argument('--headless=new')
options.add_argument('--no-sandbox')
options.add_argument('--disable-dev-shm-usage')
options.add_argument('--window-size=1280,900')
options.set_capability('goog:loggingPrefs', {'browser': 'ALL'})

driver = webdriver.Chrome(options=options)

try:
    driver.get('http://localhost/SocialNetwork/public/?url=auth/login')
    time.sleep(1)
    
    # Check what is at point (371, 595) or around submit button
    btn = driver.find_element(By.CSS_SELECTOR, 'button[type="submit"]')
    rect = driver.execute_script("return arguments[0].getBoundingClientRect();", btn)
    print("Submit button rect:", rect)
    
    cx = rect['left'] + rect['width'] / 2
    cy = rect['top'] + rect['height'] / 2
    top_el = driver.execute_script("var el = document.elementFromPoint(arguments[0], arguments[1]); return el ? {tag: el.tagName, class: el.className, id: el.id, html: el.outerHTML.substring(0, 200)} : null;", cx, cy)
    print("Element at submit button center:", top_el)
    
    # If intercepted, use JS click to login
    driver.find_element(By.NAME, 'username').send_keys('alice_wonder')
    driver.find_element(By.NAME, 'password').send_keys('password123')
    driver.execute_script("arguments[0].click();", btn)
    time.sleep(1.5)
    
    print("Current URL after login:", driver.current_url)

    # Browser console logs on home feed
    logs = driver.get_log('browser')
    print("\n=== BROWSER CONSOLE LOGS ON FEED ===")
    for entry in logs:
        print(f"[{entry['level']}] {entry['message']}")
    if not logs:
        print("(No console errors on load)")

    # Test comments toggle buttons
    toggle_btns = driver.find_elements(By.CSS_SELECTOR, '.comments-toggle-btn')
    print(f"\nFound {len(toggle_btns)} comments-toggle-btn elements")
    for i, tbtn in enumerate(toggle_btns):
        post_id = tbtn.get_attribute('data-post-id')
        total = tbtn.get_attribute('data-total')
        print(f"\nToggle Button {i}: Post ID = {post_id}, Total = {total}, Text = '{tbtn.text}'")
        
        # Check if clickable via elementFromPoint
        t_rect = driver.execute_script("return arguments[0].getBoundingClientRect();", tbtn)
        print("  Toggle rect:", t_rect)
        if t_rect['width'] > 0 and t_rect['height'] > 0:
            tx = t_rect['left'] + t_rect['width'] / 2
            ty = t_rect['top'] + t_rect['height'] / 2
            top_el = driver.execute_script("var el = document.elementFromPoint(arguments[0], arguments[1]); return el ? {tag: el.tagName, class: el.className, id: el.id} : null;", tx, ty)
            print("  Element at center:", top_el)
        
        comments_before = driver.find_elements(By.CSS_SELECTOR, f'#comments-list-{post_id} .comment-item')
        print(f"  Comments in DOM before click: {len(comments_before)}")
        
        try:
            tbtn.click()
            print("  Native click() succeeded")
        except Exception as e:
            print("  Native click() failed:", e)
            print("  Attempting JS click...")
            driver.execute_script("arguments[0].click();", tbtn)
            
        time.sleep(1.5)
        
        after_logs = driver.get_log('browser')
        for entry in after_logs:
            print(f"  [After Click Log] [{entry['level']}] {entry['message']}")
            
        comments_after = driver.find_elements(By.CSS_SELECTOR, f'#comments-list-{post_id} .comment-item')
        print(f"  Comments in DOM after click: {len(comments_after)}")
        print(f"  Toggle button text now: '{tbtn.text}'")
        print(f"  Toggle button aria-expanded: '{tbtn.get_attribute('aria-expanded')}'")

    # Test comment menu buttons
    menu_btns = driver.find_elements(By.CSS_SELECTOR, '.comment-menu-btn')
    print(f"\nFound {len(menu_btns)} comment-menu-btn elements")
    for i, mbtn in enumerate(menu_btns):
        parent_wrap = mbtn.find_element(By.XPATH, '..')
        dropdown = parent_wrap.find_element(By.CSS_SELECTOR, '.comment-menu-dropdown')
        print(f"\nMenu Button {i}:")
        m_rect = driver.execute_script("return arguments[0].getBoundingClientRect();", mbtn)
        print("  Menu btn rect:", m_rect)
        mx = m_rect['left'] + m_rect['width'] / 2
        my = m_rect['top'] + m_rect['height'] / 2
        top_el = driver.execute_script("var el = document.elementFromPoint(arguments[0], arguments[1]); return el ? {tag: el.tagName, class: el.className, id: el.id} : null;", mx, my)
        print("  Element at center:", top_el)
        
        print(f"  Dropdown displayed before: {dropdown.is_displayed()}")
        print(f"  Dropdown class before: '{dropdown.get_attribute('class')}'")
        
        try:
            mbtn.click()
            print("  Native click() succeeded")
        except Exception as e:
            print("  Native click() failed:", e)
            print("  Attempting JS click...")
            driver.execute_script("arguments[0].click();", mbtn)
            
        time.sleep(0.5)
        print(f"  Dropdown displayed after: {dropdown.is_displayed()}")
        print(f"  Dropdown class after: '{dropdown.get_attribute('class')}'")
        print(f"  Menu btn aria-expanded: '{mbtn.get_attribute('aria-expanded')}'")

finally:
    driver.quit()
