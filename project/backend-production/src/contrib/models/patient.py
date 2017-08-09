# vim: set fileencoding=utf-8 :

from . import db
from .base import DocumentExtend
from .user import User
from datetime import datetime
from mongoengine import DENY as ME_DENY


class Patient(DocumentExtend):

    meta = {
        'collection': 'patient'
    }

    uref = db.ReferenceField(User, required=True, dbref=True, db_field='_uref', reverse_delete_rule=ME_DENY)
    name = db.StringField()
    gender = db.StringField(required=True, choices=[('female', 'Female'), ('male', 'Male')])
    age = db.IntField(required=True)
    avatar = db.URLField()
    created_at = db.DateTimeField(default=datetime.utcnow)
    updated_at = db.DateTimeField(default=datetime.utcnow)

    def visible(self):
        return set(['id', 'name', 'gender', 'age', 'avatar'])


# vim:ts=4:sw=4
