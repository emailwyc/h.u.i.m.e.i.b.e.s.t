# vim: set fileencoding=utf-8 :

from . import *
from flask_wtf import Form


fields = {
    'name': StringField('名称', filters=(strip, ), validators=[
        validators.DataRequired(message='名称必填')
    ]),
    'parent': SelectField('上级区域', choices=[]),
    }


class CreateForm(Form):
    name = fields.get('name')
    parent = fields.get('parent')


class UpdateForm(Form):
    name = fields.get('name')
    parent = fields.get('parent')


# vim:ts=4:sw=4
