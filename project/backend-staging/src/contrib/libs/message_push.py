# vim: set fileencoding=utf-8 :

import time
import jpush as jpush


# def ios(alert=None, badge=None, sound=None, content_available=False, extras=None, sound_disable=False):
# def android(alert, title=None, builder_id=None, extras=None):


class MessagePush:

    def __init__(self, apns_production=False):
        self.__app_key = 'd0b072ae0e48934ee0ba69c6'
        self.__master_secret =  'e72ce5133b8dc3e3f74f7b04'
        self.__jpush = jpush.JPush(self.__app_key, self.__master_secret)
        self.push = self.__jpush.create_push()
        self.push.options = {'time_to_live': 86400, 'sendno': int(time.time()), 'apns_production': apns_production}

    def audience(self, audience):
        if audience:
            self.push.audience = audience
        else:
            self.push.audience = jpush.all_
        return self
    
    def message(self, message, extras={}):
        ios_msg = jpush.ios(alert=message, badge='+1', content_available=True, extras=extras)
        android_msg = jpush.android(alert=message, title=message, extras=extras)
        self.push.notification = jpush.notification(alert=message, android=android_msg, ios=ios_msg)
        self.push.platform = jpush.platform('ios', 'android')
        return self

    def send(self):
        return self.push.send()

if __name__ == '__main__':
    mp = MessagePush()
    # mp.audience(None).message(message='abc', extras={'a': 'a1', 'b': 'b1'}).send()
    mp.audience({'alias': ['55f95a7983cdf8574f5de675', '55f95a9783cdf8575085a7f3']}).message(message='ABC-abc', extras={'a': 'a1', 'b': 'b1'}).send()

# vim:ts=4:sw=4
