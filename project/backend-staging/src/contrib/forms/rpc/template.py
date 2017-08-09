# vim: set fileencoding=utf-8 :

from . import *


class CreateForm(Form):
    content = StringField('content', validators=[
        validators.DataRequired(message='内容必填')
    ])


class UpdateForm(Form):
    id = ObjectIdField('id', validators=[
        validators.DataRequired(message='id 必填')
    ])
    content = StringField('content', validators=[
        validators.DataRequired(message='内容必填')
    ])


class DeleteForm(Form):
    id = ObjectIdField('id', validators=[
        validators.DataRequired(message='id 必填')
    ])


# vim:ts=4:sw=4
