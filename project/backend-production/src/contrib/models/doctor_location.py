# vim: set fileencoding=utf-8 :

from . import db
from .base import DocumentExtend
from .doctor import Doctor
from datetime import datetime
from mongoengine import DENY as ME_DENY


class DoctorLocation(DocumentExtend):

    meta = {
        'collection': 'doctor_location'
    }

    doctor = db.ReferenceField(Doctor, required=True, dbref=True, reverse_delete_rule=ME_DENY)
    hospital = db.StringField(required=True)
    branch = db.StringField()
    address = db.StringField(required=True)
    info = db.StringField()
    created_at = db.DateTimeField(default=datetime.utcnow)
    updated_at = db.DateTimeField(default=datetime.utcnow)

    def visible(self):
        return set(['id', 'hospital', 'branch', 'address', 'info'])


# vim:ts=4:sw=4
