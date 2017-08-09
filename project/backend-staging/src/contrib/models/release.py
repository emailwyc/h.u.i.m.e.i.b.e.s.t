# vim: set fileencoding=utf-8 :

from . import db
from .base import DocumentExtend
from datetime import datetime
from mongoengine.queryset import queryset_manager

OS = [('ios', 'iOS'), ('iOS', 'iOS'), ('android', 'Android'), ('Android', 'Android')]


class Release(DocumentExtend):

    meta = {
        'collection': 'release'
    }

    device_type = db.StringField(required=True)
    current_version = db.IntField(required=True)
    compatible_since_version = db.IntField(required=True)
    created_at = db.DateTimeField(default=datetime.utcnow)

    @queryset_manager
    def objects(cls, queryset):
        return queryset.order_by('-created_at')

    def visible(self):
        return set(['device_type', 'current_version', 'compatible_since_version'])


# vim:ts=4:sw=4
