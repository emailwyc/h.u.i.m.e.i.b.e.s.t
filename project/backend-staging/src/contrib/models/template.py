# vim: set fileencoding=utf-8 :

from . import db
from .base import DocumentExtend
from .doctor import Doctor
from datetime import datetime
from mongoengine import DENY as ME_DENY


class Template(DocumentExtend):

    meta = {
        'collection': 'template'
    }

    doctor = db.ReferenceField(Doctor, required=True, dbref=True, reverse_delete_rule=ME_DENY)
    content = db.StringField(required=True)
    created_at = db.DateTimeField(default=datetime.utcnow)
    updated_at = db.DateTimeField(default=datetime.utcnow)

    def visible(self):
        return set(['id', 'content'])


# vim:ts=4:sw=4
