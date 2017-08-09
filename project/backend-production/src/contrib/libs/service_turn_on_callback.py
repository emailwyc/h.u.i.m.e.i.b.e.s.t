# vim: set fileencoding=utf-8 :

import requests
import json
import time
import hashlib


SECRET = 'e139d82d1be57w3dd15p3e4cea4bd875'

CALLBACK_URL = {
    'development': 'http://h5test.huimeibest.com/Callback/pushMsgByDoctor',
    'production': 'http://h5.huimeibest.com/Callback/pushMsgByDoctor',
}

SERVICE_TYPE = {
    'consult': '1',
    'clinic': '2',
    'phonecall': '3',
}

def signature(payload):
    data = '&'.join([payload.get('doctor'), SECRET, payload.get('timestamp'), payload.get('type')])
    sha1sum = hashlib.sha1()
    sha1sum.update(data.encode('utf-8'))
    sign = sha1sum.hexdigest()
    payload.update({'sign': sign})
    return payload


def service_turn_on_callback(doctor, service):
    doctor_id = doctor
    service_type = SERVICE_TYPE.get(service)

    payload = {
        'doctor': doctor_id,
        'type': service_type,
        'timestamp': str(int(time.time())),
    }

    payload = signature(payload)

    url = CALLBACK_URL.get('development')
    r = requests.post(url=url, data=payload)
    error_log = {
        'payload': r.request.body,
        'response': r.text,
        'status_code': r.status_code,
    }
    print(json.dumps(error_log))


if __name__ == '__main__':
    service_turn_on_callback('55f95a5d83cdf8575b0573ef', 'clinic')


# vim:ts=4:sw=4
