# vim: set fileencoding=utf-8 :

from . import db
from .base import DocumentExtend
from .patient import Patient
from datetime import datetime
from mongoengine import DENY as ME_DENY


class PatientFamily(DocumentExtend):

    meta = {
        'collection': 'patient_family'
    }

    patient = db.ReferenceField(Patient, required=True, dbref=True, reverse_delete_rule=ME_DENY)
    name = db.StringField(required=True)
    gender = db.StringField(required=True, choices=[('female', 'Female'), ('male', 'Male')])
    age = db.IntField(required=True)
    idcard = db.StringField(required=True)
    created_at = db.DateTimeField(default=datetime.utcnow)
    updated_at = db.DateTimeField(default=datetime.utcnow)

    def visible(self):
        return set(['id', 'patient', 'name', 'gender', 'age', 'idcard'])


# vim:ts=4:sw=4
