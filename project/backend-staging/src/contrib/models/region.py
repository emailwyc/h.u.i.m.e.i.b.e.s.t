# vim: set fileencoding=utf-8 :

from . import db
from .base import DocumentExtend
from datetime import datetime


class Region(DocumentExtend):

    meta = {
        'collection': 'region'
    }

    name = db.StringField(required=True, unique=True)
    level = db.IntField(default=1) 
    parent = db.ObjectIdField(default=None)
    weight = db.IntField(default=1)

    def visible(self):
        return set(['id', 'name', 'level','parent', 'weight'])


# vim:ts=4:sw=4
