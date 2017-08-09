# vim: set fileencoding=utf-8 :

from . import db
from .base import DocumentExtend
from datetime import datetime


class Department(DocumentExtend):

    meta = {
        'collection': 'department'
    }

    name = db.StringField(required=True, unique=True)
    parent = db.ObjectIdField(default=None)
    description = db.StringField()
    lcon = db.StringField(default='')
    order = db.IntField(default=1)
    status = db.IntField(default=1)
    tags = db.IntField()
    created_at = db.DateTimeField(default=datetime.utcnow)
    updated_at = db.DateTimeField(default=datetime.utcnow)

    def visible(self):
        return set(['id', 'name', 'description', 'lcon', 'order', 'status', 'tags'])


# vim:ts=4:sw=4
