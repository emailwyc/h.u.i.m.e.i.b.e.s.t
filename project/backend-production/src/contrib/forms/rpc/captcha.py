# vim: set fileencoding=utf-8 :

from . import *

fields = {
    'mobile': StringField('mobile', validators=[
        validators.Regexp(r'^1[3578][0-9]{9}$', message='手机号码格式错误'),
        validators.DataRequired(message='手机号码必填')
    ]),
    'captcha': StringField('captcha', validators=[
        validators.DataRequired(message='验证码必填')
    ]),
}


class XsendForm(Form):
    mobile = fields.get('mobile')


class VerifyForm(Form):
    mobile = fields.get('mobile')
    captcha = fields.get('captcha')


# vim:ts=4:sw=4
