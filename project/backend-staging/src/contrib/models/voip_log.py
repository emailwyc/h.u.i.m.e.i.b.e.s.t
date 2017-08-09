# vim: set fileencoding=utf-8 :

from . import db
from datetime import datetime


class VoIPLog(db.Document):

    meta = {
        'collection': 'voip_log'
    }

    action = db.StringField(required=True)
    order_id = db.StringField(required=True)
    call_sid = db.StringField(required=True)
    request_xml = db.StringField(required=True)
    request_dict = db.DictField(required=True)
    response_xml = db.StringField(required=True)
    logged_at = db.DateTimeField(default=datetime.utcnow)


# vim:ts=4:sw=4
