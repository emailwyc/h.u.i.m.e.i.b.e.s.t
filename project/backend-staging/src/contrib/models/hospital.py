# vim: set fileencoding=utf-8 :

from . import db
from .base import DocumentExtend
from datetime import datetime


class Hospital(DocumentExtend):

    meta = {
        'collection': 'hospital'
    }

    name = db.StringField(required=True, unique=True)
    description = db.StringField()
    rule = db.StringField(default='')
    region_id = db.ObjectIdField()
    region_child_id = db.ObjectIdField()
    address = db.StringField()
    branches = db.ListField()
    level = db.IntField(default=3)
    order = db.IntField(default=1)
    status = db.IntField(default=1)
    lcon = db.StringField(default='')
    created_at = db.DateTimeField(default=datetime.utcnow)
    updated_at = db.DateTimeField(default=datetime.utcnow)

    def visible(self):
        return set(['id', 'name', 'description', 'rule', 'region_id', 'region_child_id',
                    'address', 'branches', 'level', 'order', 'status', 'lcon'])


# vim:ts=4:sw=4
