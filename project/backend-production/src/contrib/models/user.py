# vim: set fileencoding=utf-8 :

from . import db
from .base import DocumentExtend
from datetime import datetime


class User(DocumentExtend):

    meta = {
        'collection': 'user'
    }

    mobile = db.StringField(required=True, unique=True)
    password = db.StringField(required=True)
    salt = db.StringField(required=True)
    actived_at = db.DateTimeField(default=None)
    created_at = db.DateTimeField(default=datetime.utcnow)
    updated_at = db.DateTimeField(default=datetime.utcnow)

    def visible(self):
        return set(['id', 'mobile'])


# vim:ts=4:sw=4
