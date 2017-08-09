# vim: set fileencoding=utf-8 :

from . import db
from .base import DocumentExtend
from datetime import datetime

OS = [('ios', 'iOS'), ('iOS', 'iOS'), ('android', 'Android'), ('Android', 'Android')]


class Endpoint(DocumentExtend):

    meta = {
        'collection': 'endpoint',
        'indexes': ['endpoint_token']
    }

    endpoint_token = db.StringField(required=True, unique=True)
    os = db.StringField(required=True, choices=OS)
    brand = db.StringField()
    model = db.StringField()
    resolution = db.StringField()
    actived_at = db.DateTimeField(default=datetime.utcnow)
    created_at = db.DateTimeField(default=datetime.utcnow)

    def visible(self):
        return set(['endpoint_token', 'os'])

# vim:ts=4:sw=4
