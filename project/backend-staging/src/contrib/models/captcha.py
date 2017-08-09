# vim: set fileencoding=utf-8 :

from . import db
from .base import DocumentExtend
from .user import User
from .. import SERVICE_TYPE
from datetime import datetime
from mongoengine import DENY as ME_DENY


class Captcha(DocumentExtend):

    meta = {
        'collection': 'captcha',
        'indexes': [
            'mobile',
            {'fields': ['last_send_at'], 'expireAfterSeconds': 300}
        ]
    }

    mobile = db.StringField(required=True, unique=True)
    captcha = db.StringField(required=True)
    csrf_token = db.StringField()
    send = db.DictField(required=True)
    last_send_at = db.DateTimeField(required=True)

    def visible(self):
        return set(['mobile', 'captcha', 'send', 'last_send_at'])

# vim:ts=4:sw=4
