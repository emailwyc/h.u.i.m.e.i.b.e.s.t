# vim: set fileencoding=utf-8 :

from . import *


fields = {
    'doctor': StringField('doctor', validators=[
        validators.DataRequired(message='医生必填')
    ]),
    'service': StringField('service', validators=[
        validators.AnyOf(('clinic', 'phonecall', 'consult'), message='服务类型错误')
    ]),
    'schedule': DateTimeAsUTCField('schedule', format='%Y-%m-%d %H:%M:%S %z', validators=[
        validators.DataRequired(message='日期时间格式错误，示例：2006-01-02 03:04:05 +0800')
    ]),
    'location': StringField('location', validators=[
        validators.DataRequired(message='地址必填')
    ]),
    'name': StringField('name', validators=[
        validators.DataRequired(message='姓名必填')
    ]),
    'gender': StringField('gender', validators=[
        validators.AnyOf(('female', 'male'), message='性别错误')
    ]),
    'age': IntegerField('age', validators=[
        validators.NumberRange(min=0, max=120, message='年龄错误')
    ]),
    'idcard': StringField('idcard', validators=[
        validators.DataRequired(message='身份证必填')
    ]),
    'message': StringField('message', validators=[
        validators.DataRequired(message='描述必填')
    ]),
    'datetime': DateTimeAsUTCField('datetime', format='%Y-%m-%d %H:%M:%S %z', validators=[
        validators.DataRequired(message='日期时间格式错误，示例：2006-01-02 03:04:05 +0800')
    ]),
}


class CreateForm(Form):
    doctor = fields.get('doctor')
    service = fields.get('service')
    schedule = fields.get('schedule')
    location = fields.get('location')
    name = fields.get('name')
    gender = fields.get('gender')
    age = fields.get('age')
    idcard = fields.get('idcard')
    message = fields.get('message')


class HistoryForm(Form):
    start_time = fields.get('datetime')
    end_time = fields.get('datetime')
    service = StringField('service', validators=[
        validators.AnyOf(('', 'clinic', 'phonecall', 'consult'), message='服务类型错误')
    ])
    sort = StringField('sort', validators=[
        validators.AnyOf(('', 'asc', 'desc'), message='排序类型错误')
    ])


class CursorForm(Form):
    since_order_id = ObjectIdField('since_order_id', validators=[
        validators.Optional(strip_whitespace=True)
    ])
    operator = StringField('operator', validators=[
        validators.AnyOf(('', '<', '>'), message='比较操作符错误')
    ])


class IdForm(Form):
    id = ObjectIdField('id', validators=[
        validators.DataRequired(message='id 必填')
    ])


class ReadForm(Form):
    id = ObjectIdField('id', validators=[
        validators.DataRequired(message='id 必填')
    ])


class EndForm(Form):
    id = ObjectIdField('id', validators=[
        validators.DataRequired(message='id 必填')
    ])


class CommentForm(Form):
    since_comment_id = ObjectIdField('since_comment_id', validators=[
        validators.Optional(strip_whitespace=True)
    ])


class VoipCallingForm(Form):
    id = ObjectIdField('id', validators=[
        validators.DataRequired(message='id 必填')
    ])


# vim:ts=4:sw=4
