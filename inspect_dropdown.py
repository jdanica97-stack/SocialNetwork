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
    parent_wrap = menu_btn.find_element(By.XPATH, '..')
    dropdown = parent_wrap.find_element(By.CSS_SELECTOR, '.comment-menu-dropdown')

    # Scroll menu button into view
    driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", menu_btn)
    time.sleep(0.5)

    print("Menu btn rect after scroll:", driver.execute_script("return arguments[0].getBoundingClientRect();", menu_btn))

    # Try native click after scrollIntoView
    try:
        menu_btn.click()
        print("Native click succeeded on menu_btn!")
    except Exception as e:
        print("Native click failed on menu_btn:", e)
        driver.execute_script("arguments[0].click();", menu_btn)

    time.sleep(0.5)

    # Inspect computed styles of dropdown
    computed = driver.execute_script("""
        var d = arguments[0];
        var s = window.getComputedStyle(d);
        var r = d.getBoundingClientRect();
        return {
            className: d.className,
            display: s.display,
            visibility: s.visibility,
            opacity: s.opacity,
            zIndex: s.zIndex,
            width: s.width,
            height: s.height,
            top: s.top,
            left: s.left,
            right: s.right,
            bottom: s.bottom,
            position: s.position,
            boundingClientRect: r,
            offsetParent: d.offsetParent ? d.offsetParent.tagName + '.' + d.offsetParent.className : null
        };
    """, dropdown)
    print("Dropdown computed info:", computed)

    # Check parent hierarchy for overflow or visibility
    parents_info = driver.execute_script("""
        var el = arguments[0];
        var arr = [];
        while (el && el !== document.body) {
            var s = window.getComputedStyle(el);
            arr.push({
                tag: el.tagName,
                class: el.className,
                id: el.id,
                overflow: s.overflow,
                overflowX: s.overflowX,
                overflowY: s.overflowY,
                display: s.display,
                visibility: s.visibility,
                opacity: s.opacity,
                rect: el.getBoundingClientRect()
            });
            el = el.parentElement;
        }
        return arr;
    """, dropdown)
    print("\nParents hierarchy:")
    for p in parents_info:
        print(f"  <{p['tag']} class='{p['class']}' id='{p['id']}'> overflow={p['overflow']} rect={p['rect']}")

finally:
    driver.quit()
