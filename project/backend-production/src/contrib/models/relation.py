# vim: set fileencoding=utf-8 :

from . import db
from .base import DocumentExtend
from .doctor import Doctor
from .patient import Patient
from .. import SERVICE_TYPE
from datetime import datetime
from mongoengine import DENY as ME_DENY
from mongoengine.queryset import queryset_manager


class Relation(DocumentExtend):

    meta = {
        'collection': 'relation',
        'indexes': [
            {
                'fields': ['doctor', 'patient'],
                'unique': True
            }
        ]
    }

    doctor = db.ReferenceField(Doctor, required=True, dbref=True, reverse_delete_rule=ME_DENY)
    patient = db.ReferenceField(Patient, required=True, dbref=True, reverse_delete_rule=ME_DENY)
    comment = db.StringField()
    created_at = db.DateTimeField(default=datetime.utcnow)
    updated_at = db.DateTimeField(default=datetime.utcnow)

    def visible(self):
        return set(['doctor', 'patient', 'comment'])


# vim:ts=4:sw=4
