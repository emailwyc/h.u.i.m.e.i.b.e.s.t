# vim: set fileencoding=utf-8 :

import hashlib
import base64
import datetime
import requests
import json

class CloOpen:

    api_host = ''
    app_id = ''
    headers = {
        'Accept': 'application/json',
        'Content-Type': 'application/json;charset=utf-8',
    }


    def __init__(self, debug=False):
        self.debug = debug
        if self.debug:
            server_ip = 'sandboxapp.cloopen.com'
        else:
            server_ip = 'app.cloopen.com'
        server_port = '8883'
        server_version = '2013-12-26'
        self.api_host = 'https://{0}:{1}/{2}'.format(server_ip, server_port, server_version)


    def init_app_id(self, app_id):
       self.app_id = app_id;


    def init_account(self, account_sid, account_token):
      self.account_sid = account_sid;
      self.account_token = account_token;


    def init_sub_account(self, sub_account_sid, sub_account_token):
      self.sub_account_sid = sub_account_sid;
      self.sub_account_token = sub_account_token;


    def log(self, url, request, response):
        print('Request URL:\n{0}\n'.format(url))
        print('Request Body:\n{0}\n'.format(request))
        print('Response Body:\n{0}\n'.format(response))
        print('********************************')


    def _auth_and_url(self):
        time_string = datetime.datetime.now().strftime('%Y%m%d%H%M%S')

        src = '{0}:{1}'.format(self.sub_account_sid, time_string)
        auth = base64.encodestring(src.encode('utf-8')).strip()

        signature = '{0}{1}{2}'.format(self.sub_account_sid, self.sub_account_token, time_string)
        signature = hashlib.md5(signature.encode('utf-8')).hexdigest().upper()
        url = '{host}/SubAccounts/{sid}/Calls/Callback?sig={signature}'.format(host=self.api_host, sid=self.sub_account_sid, signature=signature)

        return auth, url


    def action_call_back(self, caller, callee, caller_showing, callee_showing, user_data, max_call_time):

        body = {
                "from": caller,
                "to": callee,
                "customerSerNum": callee_showing,
                "fromSerNum": caller_showing,
                "userData": user_data,
                "maxCallTime": max_call_time,
                "promptTone": 'connecting2patient.wav',
                "alwaysPlay": 'true',
                "countDownTime": 60,
                "countDownPrompt": '1minute.wav',
                "terminalDtmf": None,
                "hangupCdrUrl": None,
                "needBothCdr": None,
                "needRecord": 1,
                "recordPoint": 0,
        }

        auth, url = self._auth_and_url()
        self.headers.update({'Authorization': auth})

        req = requests.post(url, data=json.dumps(body), headers=self.headers)

        data = req.text
        try:
            if self.debug:
                self.log(url, body, data)
            locations = json.loads(data)
            return locations
        except Exception as e:
            print(e)
            pass


if __name__ == '__main__':

    app_id = '8a48b55151eb7d520151ec93ab110353'
    sub_account_sid = 'fcbd00aeadfb11e59288ac853d9f54f2'
    sub_account_token = '17369f19ec0ffff3a1d971f4fffa8e5f'

    # action_call_back(caller, callee, caller_showing, callee_showing, user_data, max_call_time):
    def demo(caller, callee, caller_showing, callee_showing, user_data, max_call_time):
        cloopen = CloOpen(debug=True)
        cloopen.init_app_id(app_id)
        cloopen.init_sub_account(sub_account_sid, sub_account_token)

        response = cloopen.action_call_back(caller, callee, caller_showing, callee_showing, user_data, max_call_time)
        status = response.get('statusCode')
        callback = response.get('CallBack')
        if status != '000000':
            print(response)
        for k, v in callback.items():
            print('%s: %s' % (k, v))


    # demo('18513852351', '13810261155', '4000686895', '4000686895')
    demo('13810261155', '18513852351', '4000686895', '4000686895', 'order-id-abc-123', 150)


