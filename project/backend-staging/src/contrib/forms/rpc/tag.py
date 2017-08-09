# vim: set fileencoding=utf-8 :

from . import *


class CreateForm(Form):
    name = StringField('name', validators=[
        validators.DataRequired(message='名称必填')
    ])


class UpdateForm(Form):
    id = ObjectIdField('id', validators=[
        validators.DataRequired(message='id 必填')
    ])
    name = StringField('name', validators=[
        validators.DataRequired(message='名称必填')
    ])


class DeleteForm(Form):
    id = ObjectIdField('id', validators=[
        validators.DataRequired(message='id 必填')
    ])


class ResetForm(Form):
    force = OriginBooleanField('force')


class BulkInfoForm(Form):
    ids = ObjectIdArrayField('ids')


class TransferForm(Form):
    id = ObjectIdField('id', validators=[
        validators.DataRequired(message='id 必填')
    ])
    transfer_id = ObjectIdField('transfer_id', validators=[
        validators.DataRequired(message='transfer_id 必填')
    ])
    patient_id = ObjectIdField('patient_id', validators=[
        validators.DataRequired(message='patient_id 必填')
    ])


class LocateForm(Form):
    patient_id = ObjectIdField('patient_id', validators=[
        validators.DataRequired(message='patient_id 必填')
    ])


# vim:ts=4:sw=4
