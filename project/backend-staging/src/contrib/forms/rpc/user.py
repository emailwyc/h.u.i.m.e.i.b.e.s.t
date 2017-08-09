# vim: set fileencoding=utf-8 :

from . import *

fields = {
    'mobile': StringField('mobile', validators=[
        validators.Regexp(r'^1[3578][0-9]{9}$', message='手机号码格式错误'),
        validators.DataRequired(message='手机号码必填')
    ]),
    'password': StringField('password', validators=[
        validators.Length(min=6, max=32, message='密码格式错误'),
        validators.DataRequired(message='密码必填')
    ]),
    'captcha': StringField('captcha', validators=[
        validators.Length(min=4, max=6, message='验证码格式错误'),
        validators.DataRequired(message='验证码必填')
    ]),
    'csrf_token': StringField('csrf_token', validators=[
        validators.DataRequired(message='CSRF Token 必填')
    ]),
    'noop': StringField('noop', validators=[
        validators.AnyOf(('ping'), message='noop')
    ]),
}


class SignUpForm(Form):
    mobile = fields.get('mobile')
    password = fields.get('password')
    captcha = fields.get('captcha')


class PasswordUpdateForm(Form):
    password_old = fields.get('password')
    password_new = fields.get('password')


class PasswordResetForm(Form):
    password = fields.get('password')
    csrf_token = fields.get('csrf_token')


class SignInForm(Form):
    mobile = fields.get('mobile')
    password = fields.get('password')
    # captcha = fields.get('captcha')


class NoopForm(Form):
    noop = fields.get('noop')


class PasswordResetToDefaultForm(Form):
    token = StringField('token', validators=[
        validators.AnyOf(('zDCI4drA-7PtH5goY-GfD1BCEc-E05jpprq-P2St'), message='token')
    ])


# vim:ts=4:sw=4
