# vim: set fileencoding=utf-8 :

from . import db
from .base import DocumentExtend
from .doctor import Doctor
from .patient import Patient
from datetime import datetime
from mongoengine import DENY as ME_DENY


class Tag(DocumentExtend):

    meta = {
        'collection': 'tag'
    }

    doctor = db.ReferenceField(Doctor, required=True, dbref=True, reverse_delete_rule=ME_DENY)
    scope = db.StringField(choices=('default', 'system', 'user'), required=True)
    name = db.StringField(required=True)
    patients = db.ListField(db.ReferenceField(Patient))
    created_at = db.DateTimeField(default=datetime.utcnow)
    updated_at = db.DateTimeField(default=datetime.utcnow)

    def visible(self):
        return set(['id', 'scope', 'name'])


# vim:ts=4:sw=4
