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

    # Let's inspect all fixed or absolute elements with high z-index or that cover the screen!
    overlays = driver.execute_script("""
        var all = document.querySelectorAll('*');
        var res = [];
        for (var el of all) {
            var s = window.getComputedStyle(el);
            if (s.position === 'fixed' || s.position === 'absolute') {
                var r = el.getBoundingClientRect();
                var z = parseInt(s.zIndex, 10) || 0;
                // If it covers a significant area
                if ((r.width > 200 && r.height > 200) || z > 50) {
                    res.push({
                        tag: el.tagName,
                        id: el.id,
                        class: el.className,
                        position: s.position,
                        zIndex: s.zIndex,
                        pointerEvents: s.pointerEvents,
                        display: s.display,
                        visibility: s.visibility,
                        opacity: s.opacity,
                        rect: {top: r.top, left: r.left, width: r.width, height: r.height}
                    });
                }
            }
        }
        return res;
    """)
    print("=== HIGH Z-INDEX OR LARGE FIXED/ABSOLUTE ELEMENTS ===")
    for o in overlays:
        print(o)

    # Now let's scroll to the toggle_btn and test elementFromPoint
    toggle_btn = driver.find_element(By.CSS_SELECTOR, '.comments-toggle-btn')
    driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", toggle_btn)
    time.sleep(0.5)
    
    point_info = driver.execute_script("""
        var btn = arguments[0];
        var r = btn.getBoundingClientRect();
        var cx = r.left + r.width / 2;
        var cy = r.top + r.height / 2;
        var top = document.elementFromPoint(cx, cy);
        var path = [];
        var curr = top;
        while (curr && curr !== document.body) {
            path.push(curr.tagName + (curr.className ? '.' + curr.className : '') + (curr.id ? '#' + curr.id : ''));
            curr = curr.parentElement;
        }
        return {
            rect: r,
            cx: cx,
            cy: cy,
            topTag: top ? top.tagName : null,
            topId: top ? top.id : null,
            topClass: top ? top.className : null,
            path: path,
            isBtn: top === btn || btn.contains(top)
        };
    """, toggle_btn)
    print("\n=== TOGGLE BUTTON POINT INFO ===")
    print(point_info)

    # Now let's do the same for menu_btn
    menu_btn = driver.find_element(By.CSS_SELECTOR, '.comment-menu-btn')
    driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", menu_btn)
    time.sleep(0.5)
    
    point_info2 = driver.execute_script("""
        var btn = arguments[0];
        var r = btn.getBoundingClientRect();
        var cx = r.left + r.width / 2;
        var cy = r.top + r.height / 2;
        var top = document.elementFromPoint(cx, cy);
        var path = [];
        var curr = top;
        while (curr && curr !== document.body) {
            path.push(curr.tagName + (curr.className ? '.' + curr.className : '') + (curr.id ? '#' + curr.id : ''));
            curr = curr.parentElement;
        }
        return {
            rect: r,
            cx: cx,
            cy: cy,
            topTag: top ? top.tagName : null,
            topId: top ? top.id : null,
            topClass: top ? top.className : null,
            path: path,
            isBtn: top === btn || btn.contains(top)
        };
    """, menu_btn)
    print("\n=== MENU BUTTON POINT INFO ===")
    print(point_info2)

finally:
    driver.quit()
