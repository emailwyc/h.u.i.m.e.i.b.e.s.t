# vim: set fileencoding=utf-8 :

from . import db
from .base import DocumentExtend
from datetime import datetime


class Setting(DocumentExtend):

    meta = {
        'collection': 'h5_setting'
    }

    sign = db.StringField(required=True)
    token = db.StringField(required=True, db_field='access_token')
    expiring_at = db.StringField(required=True, db_field='last_time')
    application = db.StringField(required=True)

    def visible(self):
        return set(['sign', 'token', 'expiring_at', 'application'])

# vim:ts=4:sw=4
