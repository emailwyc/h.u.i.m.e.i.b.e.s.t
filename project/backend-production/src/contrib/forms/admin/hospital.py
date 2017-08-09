# vim: set fileencoding=utf-8 :

from . import *
from flask_wtf import Form


fields = {
    'name': StringField('名称', filters=(strip, ), validators=[
        validators.DataRequired(message='名称必填')
    ]),
    'region_id': SelectField('一级区域', choices=[], validators=[
        validators.DataRequired(message='一级区域必填')
    ]),
    'region_child_id': SelectField('二级区域', choices=[], validators=[
        validators.DataRequired(message='二级区域必填')
    ]),
    'address': StringField('医院地址', filters=(strip, ), validators=[
        validators.DataRequired(message='医院地址必填')
    ]),
    'branches': TextAreaField('分院<br><br>地址', filters=(strip, )),
    'rule': TextAreaField('挂号规则', filters=(strip, ), validators=[
        validators.DataRequired(message='规则必填')
    ]),
    'description': TextAreaField('描述', filters=(strip, ), validators=[
        validators.DataRequired(message='描述必填')
    ]),


}


class CreateForm(Form):
    name = fields.get('name')
    region_id = fields.get('region_id')
    region_child_id = fields.get('region_child_id')
    address = fields.get('address')
    branches = fields.get('branches')
    rule = fields.get('rule')
    description = fields.get('description')


class UpdateForm(Form):
    name = fields.get('name')
    region_id = fields.get('region_id')
    region_child_id = fields.get('region_child_id')
    address = fields.get('address')
    branches = fields.get('branches')
    rule = fields.get('rule')
    description = fields.get('description')


# vim:ts=4:sw=4
