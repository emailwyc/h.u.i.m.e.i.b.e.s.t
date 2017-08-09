# vim: set fileencoding=utf-8 :

from . import *


class LatestForm(Form):
    device_type = StringField('device_type', validators=[
        validators.AnyOf(('ios', 'iOS', 'android', 'Android'), message='设备类型错误')
    ])


# vim:ts=4:sw=4
