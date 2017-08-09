# vim: set fileencoding=utf-8 :

from . import *


fields = {
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
    'family_id': StringField('family_id', validators=[
        validators.DataRequired(message='家庭成员必填')
    ]),
}


class InfoForm(Form):
    name = StringField('name')
    gender = fields.get('gender')
    age = fields.get('age')
    avatar = StringField('avatar')


class FamilyForm(Form):
    family_id = StringField('family_id')
    name = fields.get('name')
    gender = fields.get('gender')
    age = fields.get('age')
    idcard = fields.get('idcard')


class FamilyRemoveForm(Form):
    family_id = fields.get('family_id')


class BulkInfoForm(Form):
    ids = ObjectIdArrayField('ids')


class CommentForm(Form):
    patient_id = ObjectIdField('patient_id')
    comment = StringField('comment')


# vim:ts=4:sw=4
