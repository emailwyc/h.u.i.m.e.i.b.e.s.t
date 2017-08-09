# vim: set fileencoding=utf-8 :

from . import *
from flask_wtf import Form


fields = {
    'name': StringField('名称', filters=(strip, ), validators=[
        validators.DataRequired(message='名称必填')
    ]),
    'parent': SelectField('上级科室', choices=[]),
    'description': TextAreaField('描述', filters=(strip, ), validators=[
        validators.DataRequired(message='描述必填')
    ]),
}


class CreateForm(Form):
    name = fields.get('name')
    parent = fields.get('parent')
    description = fields.get('description')


class UpdateForm(Form):
    name = fields.get('name')
    parent = fields.get('parent')
    description = fields.get('description')


# vim:ts=4:sw=4
