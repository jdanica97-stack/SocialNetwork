import urllib.request
import urllib.parse
import re
import json
import http.cookiejar

cj = http.cookiejar.CookieJar()
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj))

# 1. Get login page to extract CSRF token
resp = opener.open('http://localhost/SocialNetwork/public/?url=auth/login')
html = resp.read().decode('utf-8')
csrf = re.search(r'name="csrf_token"\s+value="([^"]+)"', html).group(1)
print('CSRF token:', csrf[:10] + '...')

# 2. Post login
data = urllib.parse.urlencode({'username': 'alice_wonder', 'password': 'password123', 'csrf_token': csrf}).encode('utf-8')
req = urllib.request.Request('http://localhost/SocialNetwork/public/?url=auth/login', data=data)
resp = opener.open(req)
print('Login URL:', resp.geturl())

# 3. Request comments/fetch
req = urllib.request.Request('http://localhost/SocialNetwork/public/?url=comments/fetch&post_id=5')
req.add_header('X-Requested-With', 'XMLHttpRequest')
resp = opener.open(req)
body = resp.read().decode('utf-8')
print('Status:', resp.status)
print('Content-Type:', resp.headers.get('Content-Type'))
print('Response JSON:', body)
