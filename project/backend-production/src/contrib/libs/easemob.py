# vim: set fileencoding=utf-8 :

import requests
import json
import time
from requests.auth import AuthBase
import string
import random

JSON_HEADER = {'content-type': 'application/json'}
EASEMOB_HOST = 'https://a1.easemob.com'

DEBUG = False


class Query:

    def __init__(self, auth=None):
        self.auth = auth

    def post(self, url, payload):
        r = requests.post(url, data=json.dumps(payload), headers=JSON_HEADER, auth=self.auth)
        return self.http_result(r)

    def get(self, url):
        r = requests.get(url, headers=JSON_HEADER, auth=self.auth)
        return self.http_result(r)

    def delete(self, url):
        r = requests.delete(url, headers=JSON_HEADER, auth=self.auth)
        return self.http_result(r)

    def http_result(self, r):
        if DEBUG:
            error_log = {
                'method': r.request.method,
                'url': r.request.url,
                'request_header': dict(r.request.headers),
                'response_header': dict(r.headers),
                'response': r.text,
                'status': requests.codes.ok,
                'status_code': r.status_code,
            }
            if r.request.body:
                error_log['payload'] = r.request.body
            print(json.dumps(error_log))

        if r.status_code == 401:
            raise PermissionError

        if r.status_code == requests.codes.ok:
            return True, r.json()
        else:
            return False, r.text


class Token:

    def __init__(self, application, token, expiring_at):
        self.application = application
        self.token = token
        self.expiring_at = expiring_at

    def invalid(self):
        return int(time.time()) > self.expiring_at


class EasemobAuth(AuthBase):

    def __call__(self, r):
        r.headers['Authorization'] = 'Bearer ' + self.get_token()
        return r

    def get_token(self):
        if not isinstance(self.auth, Token) or self.auth.invalid():
            self.auth = self.acquire_token()
        # print('use this token: {0}'.format(self.auth.token))
        return self.auth.token

    def get_auth(self):
        return {
            'application': self.auth.application,
            'token': self.auth.token,
            'expiring_at': self.auth.expiring_at,
        }

    def acquire_token(self):
        pass


class AppClientAuth(EasemobAuth):

    def __init__(self, org, app, client_id, client_secret, auth={}):
        super(AppClientAuth, self).__init__()
        self.client_id = client_id
        self.client_secret = client_secret
        self.url = EASEMOB_HOST + ('/%s/%s/token' % (org, app))
        self.auth = self.build_auth(auth)
        self.query = Query()

    def build_auth(self, auth):
        if auth:
            _require = ['application', 'expiring_at', 'token']
            keys = list(auth.keys())
            keys.sort()
            # print('--- build auth')
            if _require == keys:
                # print('--- build auth --- checking ok')
                return Token(auth.get('application'), auth.get('token'), auth.get('expiring_at'))
        return None

    def acquire_token(self):
        '''
        POST /{org}/{app}/token {'grant_type': 'client_credentials', 'client_id':'xxxx', 'client_secret':'xxxxx'}
        '''
        payload = {'grant_type': 'client_credentials', 'client_id': self.client_id, 'client_secret': self.client_secret}
        success, result = self.query.post(self.url, payload)
        # print('--- acquire token: {0}'.format(result))
        if success:
            return Token(result['application'], result['access_token'], result['expires_in'] + int(time.time()) - 60)
        else:
            # throws exception
            pass


class Easemob:

    def __init__(self, config, auth={}):
        self.org, self.app = self.parse_appkey(config.get('appkey'))
        self.client_id = config['app']['credentials']['client_id']
        self.client_secret = config['app']['credentials']['client_secret']
        self.app_client_auth = AppClientAuth(self.org, self.app, self.client_id, self.client_secret, auth)
        self.query = Query(auth=self.app_client_auth)

    def parse_appkey(self, appkey):
        ''' appkey 的规则是 {org}#{app} '''
        return tuple(appkey.split('#'))

    def register_user(self, username, password):
        '''
        POST /{org}/{app}/users {'username':'xxxxx', 'password':'yyyyy'}
        '''
        payload = {'username': username, 'password': password}
        url = EASEMOB_HOST + ('/%s/%s/users' % (self.org, self.app))
        return self.query.post(url, payload)

    def get_user(self, username):
        '''
        GET /{org}/{app}/users/username
        '''
        url = EASEMOB_HOST + ('/%s/%s/users/%s' % (self.org, self.app, username))
        return self.query.get(url)

    def delete_user(self, username):
        '''
        DELETE /{org}/{app}/users/{username}
        '''
        url = EASEMOB_HOST + ('/%s/%s/users/%s' % (self.org, self.app, username))
        return self.query.delete(url)

    def id_generator(self, size=8, chars=string.ascii_uppercase + string.digits):
        return ''.join(random.choice(chars) for _ in range(size))

    def send_message(self, msg_from, msg_to, message, extras=None):
        payload = {
            "target_type": "users",
            "from": msg_from,
            "target": msg_to,
            "msg": {"type": "txt", "msg": '{0}'.format(message)},
            "ext": extras,
        }
        url = EASEMOB_HOST + ('/%s/%s/messages' % (self.org, self.app))
        return self.query.post(url, payload)


if __name__ == '__main__':
    config = {
        "appkey": "110102018872160#doctor-staging",
        "app": {
            "credentials": {
                "client_id": "YXA6EhWFQGanEeWW63-U852OBw",
                "client_secret": "YXA6oFmC75zj470b15DQNnHizBvVxMo"
            }
        }
    }

    from werkzeug.contrib.cache import SimpleCache
    cache = SimpleCache()

    # auth = {'token': 'YWMtHrPqwHvKEeWQc_l42qdsFQAAAVHYoHXamVFyQiA77CC2dyd_v8qXT0IKUmM', 'application': '12158540-66a7-11e5-96eb-7f94f39d8e07', 'expiring_at': 1451038313}
    # auth = None

    def run_test(loop):
        auth = cache.get('auth')
        print('auth form cache: {0}'.format(auth))
        instance = Easemob(config=config, auth=auth)
        print('app admin token: {0}'.format(instance.app_client_auth.get_token()))
        auth_current = instance.app_client_auth.get_auth()
        if auth != auth_current:
            cache.set('auth', auth_current, timeout=7200)

        print('')

        time.sleep(1)

        app_users = []
        for i in range(loop):
            username = instance.id_generator()
            password = '123456'
            success, result = instance.register_user(username, password)
            if success:
                app_users.append(username)
                print('registered new user %s' % (username))
            else:
                print('failed to register new user %s' % (username))

        print('')
        time.sleep(1)

        for username in app_users:
            success, result = instance.get_user(username)
            if success:
                print('user %s: %s' % (username, result))
            else:
                print('failed to get user %s' % (username))

            print('')

            success, result = instance.delete_user(username)
            if success:
                print('user %s is deleted' % (username))
            else:
                print('failed to delete user %s' % (username))

            print('')

    run_test(2)
    run_test(2)

# vim:ts=4:sw=4
