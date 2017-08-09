# vim: set fileencoding=utf-8 :

import os


class Config(object):
    '''only UPPERCASE keys are added to the config when using 'app.config.from_object()' in Flask'''

    RUN_MODE = 'default'
    DEBUG = False
    TESTING = False
    SECRET_KEY = 'eZ1Fb4DOcmk28itEQaK+LFyDRTvk4RLk6OoDtK8pmgs7dFqsHz'
    CSRF_ENABLED = True
    CSRF_SESSION_KEY = 'qCjnsTcbLG86i0YcXvcPU2Sn47ZiHS6ZvVt+CQN6RXqxFEVZ8j'
    APP_KEYPAIRS = {}
    SERVICE_TYPE = [('clinic', '挂号预约'), ('phonecall', '电话咨询'), ('consult', '图文咨询')]

    class ConstError(TypeError):
        pass

    def __setattr__(self, name, value):
        raise self.ConstError("can't assign to CONST '{0}'".format(name))

    def __getattr__(self, name):
        raise self.ConstError("CONST '{0}' is not defined".format(name))


class DevelopmentConfig(Config):
    RUN_MODE = 'development'
    DEBUG = True
    APP_KEYPAIRS = {
        # app_id: {app_role, app_key}
        'xsh0g8n2d8a7r8k9': {'role': 'doctor',  'os': 'shell',     'key': 'k6y3x6i6h0l8g6i3p6b4k9'},
        'xsh0h2x9f1g5k1a7': {'role': 'patient', 'os': 'shell',     'key': 'n5t3x5o7z2f1m9y8h2d5y2'},
        'adb0u2r4a6f7m9t1': {'role': 'doctor',  'os': 'android',   'key': 'v2b9q7o0t1t4x3p4e0r7t6'},
        'ios0v0s3q7t5o2m3': {'role': 'doctor',  'os': 'ios',       'key': 'b3m8c9m9q0n5c9k2z7d5u2'},
    }
    EASEMOB = {
        "appkey": "110102018872160#doctor-staging",
        "app": {
            "credentials": {
                "client_id": "YXA6EhWFQGanEeWW63-U852OBw",
                "client_secret": "YXA6oFmC75zj470b15DQNnHizBvVxMo"
            }
        }
    }


class TestingConfig(Config):
    RUN_MODE = 'testing'
    TESTING = True


class ProductionConfig(Config):
    RUN_MODE = 'production'
    APP_KEYPAIRS = {
        'xsh0o5m6v2c6g6t2': {'role': 'doctor',  'os': 'shell',     'key': 'l9n1t4m1s6c1k3d4b5i9v6'},
        'xsh0h0u0f8j2h9k5': {'role': 'patient', 'os': 'shell',     'key': 'b5s2u3a7p1o3d6m1z0e0w1'},
        'adb0y6b9e3v2j9q1': {'role': 'doctor',  'os': 'android',   'key': 'y8u6x1b7g0c3j2y3y4n9f1'},
        'ios0v3x7z9m8o0z3': {'role': 'doctor',  'os': 'ios',       'key': 'c1e6b5c9f2f3f8v0h3t5u5'},
    }
    EASEMOB = {
        "appkey": "110102018872160#doctor",
        "app": {
            "credentials": {
                "client_id": "YXA6z_bIwGM9EeWYf63l9Tgl_A",
                "client_secret": "YXA65dxFjURA7tPQP5V1l0KNrRMOzRg"
            }
        }
    }


def running_model():
    '''
    RUN_MODE=DEVELOPMENT python3 run.py
    '''
    mode = os.environ.get('RUN_MODE', 'PRODUCTION').upper()
    try:
        if mode == 'DEVELOPMENT':
            return DevelopmentConfig()
        elif mode == 'TESTING':
            return TestingConfig()
        else:
            return ProductionConfig()
    except ImportError as e:
        return Config()

# vim:ts=4:sw=4
