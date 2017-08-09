# vim: set fileencoding=utf-8 :

from . import db
from .base import DocumentExtend
from .doctor import Doctor
from .patient import Patient
from datetime import datetime
from mongoengine import DENY as ME_DENY


class Feedback(DocumentExtend):

    meta = {
        'collection': 'feedback'
    }

    doctor = db.ReferenceField(Doctor, dbref=True, reverse_delete_rule=ME_DENY)
    patient = db.ReferenceField(Patient, dbref=True, reverse_delete_rule=ME_DENY)
    content = db.StringField()
    created_at = db.DateTimeField(default=datetime.utcnow)

    def visible(self):
        return set(['doctor', 'patient', 'content'])


# vim:ts=4:sw=4
